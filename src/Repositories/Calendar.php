<?php

namespace Dashifen\SimpleEvents\Repositories;

use WP_Post;
use Dashifen\Repository\Repository;
use Dashifen\SimpleEvents\SimpleEvents;
use Dashifen\Repository\RepositoryException;
use Dashifen\Transformer\TransformerException;
use Dashifen\WPHandler\Handlers\HandlerException;

/**
 * @property-read int $month
 * @property-read int $year
 * @property-read int $type
 */
class Calendar extends Repository
{
  protected int $month;
  protected int $year;
  protected int $type;
  
  private SimpleEvents $handler;
  
  /**
   * AbstractRepository constructor.
   *
   * If given an associative data array, loops over its values settings
   * properties that match indices therein.
   *
   * @param array        $data
   * @param SimpleEvents $handler
   *
   * @throws RepositoryException
   */
  public function __construct(array $data, SimpleEvents $handler)
  {
    parent::__construct($data);
    $this->handler = $handler;
  }
  
  /**
   * render
   *
   * Renders our calendar for the screen.
   *
   * @return string
   * @throws RepositoryException
   * @throws TransformerException
   * @throws HandlerException
   */
  public function render(): string
  {
    // first, we make a timestamp for 00:00:01 on the first of the specified
    // month and year.  this lets us pass that timestamp to each of the calls
    // to the date function below.  then we get the total number of days in
    // that month and the number corresponding to the day of the first of that
    // month (e.g. is it a Monday or a Thursday or whatever).
    
    $timestamp = mktime(0, 0, 1, $this->month, 1, $this->year);
    $calendar = '<div class="simple-events-calendar">';
    $calendar .= '<header class="simple-events-calendar-caption">' . date('F Y', $timestamp) . '</header>';
    $calendar .= '<div class="simple-events-calendar-day-heading">Sunday</div>';
    $calendar .= '<div class="simple-events-calendar-day-heading">Monday</div>';
    $calendar .= '<div class="simple-events-calendar-day-heading">Tuesday</div>';
    $calendar .= '<div class="simple-events-calendar-day-heading">Wednesday</div>';
    $calendar .= '<div class="simple-events-calendar-day-heading">Thursday</div>';
    $calendar .= '<div class="simple-events-calendar-day-heading">Friday</div>';
    $calendar .= '<div class="simple-events-calendar-day-heading">Saturday</div>';
    
    // next, we print a bunch of empty cells to take up space on the days prior
    // to the first of the month.  the "w" format gives us a numeric
    // representation of the day of the week where 0 is Sunday and 6 is
    // Saturday.  we can use str_repeat to add our empties, and since the "w"
    // format is zero indexed, we can repeat our string the exact number of
    // times that the date function returns.
    
    $firstDay = date('w', $timestamp);
    $calendar .= str_repeat('<div class="simple-events-calendar-day-empty"></div>', $firstDay);
    
    // now to print our actual calendar.  we print a cell for each day with
    // the date in the cell.  we go from the first of the month until the last
    // day of the month (which is what the "t" format gives us), including that
    // last day.
    
    $totalDays = date('t', $timestamp);
    for ($i = 1; $i <= $totalDays; $i++) {
      $events = $this->getEvents(strtotime('+' . ($i - 1) . ' days', $timestamp));
      
      $classes = ['simple-events-calendar-day'];
      $classes[] = ($eventCount = sizeof($events)) === 0
        ? 'simple-events-calendar-day-without-events'
        : 'simple-events-calendar-with-events';
      
      $calendar .= '<div class="' . join(' ', $classes)
        . '" data-event-count="' . $eventCount . '">';
      
      $calendar .= '<header>' . $i . '</header>';
      
      if ($eventCount > 0) {
        $calendar .= '<ol>';
        foreach ($events as $event) {
          $event = new Event($event->ID, $this->handler);
          $calendar .= '<li>' . $event->render() . '</li>';
        }
        $calendar .= '</ol>';
      }
      
      $calendar .= '</div>';
    }
    
    // and, to close things out, we need more empties.  this time, the way we
    // get them is a little harder.  we add our total number of days to our
    // timestamp and get that weekday number.  then, we print a number of empty
    // cells to fill out the rest of the week-row.  we can calculate that
    // number by subtracting our last weekday from 7.
    
    $lastWeekday = date('w', strtotime('+' . $totalDays . ' days', $timestamp));
    $calendar .= str_repeat('<div class="simple-events-calendar-day-empty"></div>', 7 - $lastWeekday);
    return $calendar . '</div>';
  }
  
  /**
   * getEvents
   *
   * Returns an array of events that take place on the specified day.
   *
   * @param int $timestamp
   *
   * @return WP_Post[]
   */
  protected function getEvents(int $timestamp): array
  {
    $start = date('Y-m-d 00:00:00', $timestamp);
    $end = date('Y-m-d 23:59:59', $timestamp);
    return get_posts([
      'post_type'  => SimpleEvents::POST_TYPE,
      'orderby'    => 'datetime',
      'order'      => 'ASC',
      'meta_query' => [
        'datetime' => [
          'key'     => $this->handler->getPostMetaPrefix() . 'datetime',
          'value'   => [$start, $end],
          'type'    => 'DATETIME',
          'compare' => 'BETWEEN',
        ],
      ],
    ]);
  }
  
  /**
   * setMonth
   *
   * Sets the month property.
   *
   * @param int $month
   *
   * @return void
   * @throws RepositoryException
   */
  public function setMonth(int $month): void
  {
    if ($month < 1 || $month > 12) {
      throw new RepositoryException('Invalid month: ' . $month,
        RepositoryException::INVALID_VALUE);
    }
    $this->month = $month;
  }
  
  /**
   * setYear
   *
   * Sets the year property.
   *
   * @param int $year
   *
   * @return void
   */
  public function setYear(int $year): void
  {
    // if the system sends us a two digit year, we assume that it's a year in
    // the 21st century and add 2000 to it.  thus, 21 becomes 2021.
    
    $this->year = $year < 100 ? 2000 + $year : $year;
  }
  
  /**
   * setType
   *
   * Sets the type property.
   *
   * @param int $type
   *
   * @return void
   * @throws RepositoryException
   */
  public function setType(int $type): void
  {
    $types = get_terms([
      'taxonomy'   => SimpleEvents::TAXONOMY,
      'hide_empty' => false,
      'fields'     => 'ids',
    ]);
    
    if (!in_array($type, $types) && $type !== 0) {
      throw new RepositoryException('Invalid type ID: ' . $type,
        RepositoryException::INVALID_VALUE);
    }
    
    $this->type = $type;
  }
}
