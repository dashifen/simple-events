<?php

namespace Dashifen\SimpleEvents\Repositories;

use Timber\Timber;
use Dashifen\Repository\Repository;
use Dashifen\SimpleEvents\SimpleEvents;
use Dashifen\Repository\RepositoryException;
use Dashifen\Transformer\TransformerException;
use Dashifen\SimpleEvents\Agents\EventMetaAgent;
use Dashifen\WPHandler\Handlers\HandlerException;

/**
 * @property-read int    $id
 * @property-read string $title
 * @property-read string $host
 * @property-read string $date
 * @property-read string $time
 * @property-read string $duration
 * @property-read string $location
 * @property-read string $visibility
 */
class Event extends Repository
{
  protected int $id;
  protected string $title;
  protected string $host;
  protected string $date;
  protected string $time;
  protected string $duration;
  protected string $location;
  protected string $visibility;
  
  /**
   * Event constructor.
   *
   * @param int|null     $eventId
   * @param SimpleEvents $handler
   *
   * @throws RepositoryException
   * @throws TransformerException
   * @throws HandlerException
   */
  public function __construct(?int $eventId, SimpleEvents $handler)
  {
    if ($eventId === null || is_wp_error($event = get_post($eventId))) {
      throw new RepositoryException('Cannot construct Event.',
        RepositoryException::INVALID_VALUE);
    }
    
    $data = [
      'id'    => $eventId,
      'title' => $event->post_title,
    ];
    
    $mapper = fn($key) => $handler->getPostMeta($eventId, $key,
      $key === 'visibility' ? 'public' : 'TBD',   // visibility's default is public; otherwise, TBD.
      $key !== 'visibility'                       // transform everything but visibility
    );
    
    $keys = array_keys(EventMetaAgent::POST_META);
    $keys = array_filter($keys, fn($key) => property_exists($this, $key));
    $data = array_merge($data, array_combine($keys, array_map($mapper, $keys)));
    parent::__construct($data);
  }
  
  public function render(): string
  {
    $context = $this->toArray();
    $context['permalink'] = get_permalink($this->id);
    
    $context['classes'] = [];
    $context['classes']['event'] = apply_filters('simple-event-classes', 'simple-events-event');
    $context['classes']['header'] = apply_filters('simple-event-header-classes', 'simple-events-header');
    foreach (array_keys(EventMetaAgent::POST_META) as $key) {
      $context['classes'][$key] = apply_filters(
        'simple-events-' . $key . '-classes',
        'simple-events-' . $key
      );
    }
    
    return Timber::fetch('simple-events-event.twig', $context);
  }
  
  /**
   * setId
   *
   * Sets the id property.
   *
   * @param int $id
   *
   * @return void
   */
  public function setId(int $id): void
  {
    $this->id = $id;
  }
  
  /**
   * setTitle
   *
   * Sets the title property.
   *
   * @param string $title
   *
   * @return void
   */
  public function setTitle(string $title): void
  {
    $this->title = $title;
  }
  
  /**
   * setVisibility
   *
   * Sets the visibility property.
   *
   * @param string $visibility
   *
   * @return void
   */
  public function setVisibility(string $visibility): void
  {
    $this->visibility = $visibility;
  }
  
  /**
   * setDate
   *
   * Sets the date property.
   *
   * @param string $date
   *
   * @return void
   */
  public function setDate(string $date): void
  {
    $this->date = $date;
  }
  
  /**
   * setTime
   *
   * Sets the time property.
   *
   * @param string $time
   *
   * @return void
   */
  public function setTime(string $time): void
  {
    $this->time = $time;
  }
  
  /**
   * setDuration
   *
   * Sets the duration property.
   *
   * @param string $duration
   *
   * @return void
   */
  public function setDuration(string $duration): void
  {
    $this->duration = $duration;
  }
  
  /**
   * setHost
   *
   * Sets the host property.
   *
   * @param string $host
   *
   * @return void
   */
  public function setHost(string $host): void
  {
    $this->host = $host;
  }
  
  /**
   * setLocation
   *
   * Sets the location property.
   *
   * @param string $location
   *
   * @return void
   */
  public function setLocation(string $location): void
  {
    $this->location = $location;
  }
}
