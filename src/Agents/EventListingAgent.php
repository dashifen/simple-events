<?php

namespace Dashifen\SimpleEvents\Agents;

use WP_Term;
use WP_Query;
use Timber\Timber;
use Dashifen\SimpleEvents\SimpleEvents;
use Dashifen\Transformer\TransformerException;
use Dashifen\WPHandler\Handlers\HandlerException;
use Dashifen\WPHandler\Agents\AbstractPluginAgent;

/**
 * EventListingAgent
 *
 * While the PHP would allow any type of handler, we that the handler that is
 * linked to this agent is our SimpleEvents object because of the work done in
 * the ../simple-events.php file.  Since this object needs to access public
 * methods of its handler, we'll type hint it here.
 *
 * @property SimpleEvents $handler
 */
class EventListingAgent extends AbstractPluginAgent
{
  /**
   * initialize
   *
   * Uses addAction and/or addFilter to attach protected methods of this object
   * to the ecosystem of WordPress action and filter hooks.
   *
   * @return void
   * @throws HandlerException
   */
  public function initialize(): void
  {
    if (!$this->isInitialized()) {
      $this->addFilter('manage_' . SimpleEvents::POST_TYPE . '_posts_columns', 'addColumns');
      $this->addAction('manage_' . SimpleEvents::POST_TYPE . '_posts_custom_column', 'addColumnData', 10, 2);
      $this->addFilter('manage_edit-' . SimpleEvents::POST_TYPE . '_sortable_columns', 'makeColumnsSortable');
      
      // the default date filter uses the date on which a post is added to the
      // database and not the date on which an event takes place.  we'll get
      // rid of that filter to avoid confusion.  then, we add some of our own.
      
      $this->addFilter('months_dropdown_results', 'removeDefaultDateFilter');
      $this->addAction('restrict_manage_posts', 'addFilters');
      
      // these two modifications handle sorts and filters.  notice that we add
      // our sort first and then our filter via the different priority levels.
      // this means that by the time we get to our filters, the sort will be
      // set appropriately.
      
      $this->addAction('pre_get_posts', 'handleSorts');
      $this->addAction('pre_get_posts', 'handleFilters', 15);
    }
  }
  
  /**
   * addColumns
   *
   * Adds custom columns to the event listing display.
   *
   * @param array $original
   *
   * @return array
   */
  protected function addColumns(array $original): array
  {
    $modified = [];
    
    // first, to avoid confusion with respect to the event's date vs. the
    // post's publication date, we'll remove the latter.  then, we loop over
    // the remaining columns adding them to our modified array.
    
    unset($original['date']);
    foreach ($original as $column => $heading) {
      $modified[$column] = $heading;
      
      if ($column === 'taxonomy-' . SimpleEvents::TAXONOMY) {
        
        // if we just found our taxonomy's column, then we'll add our event
        // meta data after it.  the information we want to add here can be
        // found in the POST_META constant of our EventMetaAgent object.
        
        foreach ($this->getPertinentMeta() as $key => $display) {
          $modified[$key] = $display;
        }
      }
    }
    
    return $modified;
  }
  
  private function getPertinentMeta(): array
  {
    return array_filter(
      EventMetaAgent::POST_META,
      fn($key) => in_array($key, ['datetime', 'duration', 'host', 'private']),
      ARRAY_FILTER_USE_KEY
    );
  }
  
  /**
   * addColumnData
   *
   * Fills our custom columns with the appropriate data for the screen.
   *
   * @param string $column
   * @param int    $postId
   *
   * @return void
   * @throws HandlerException
   * @throws TransformerException
   */
  protected function addColumnData(string $column, int $postId): void
  {
    // as long as the column for which we're adding data can be found as a key
    // within our event meta, then we'll get the value at that meta and place
    // it on-screen.  note:  this is a WP action, not a filter, so we echo
    // directly to the screen rather than returning any data here.
    
    if (array_key_exists($column, EventMetaAgent::POST_META)) {
      echo $this->handler->getPostMeta($postId, $column);
    }
  }
  
  /**
   * makeColumnsSortable
   *
   * Adds the datetime column to the list of the ones that can be used to
   * sort our display.
   *
   * @param array $sortableColumns
   *
   * @return array
   */
  protected function makeColumnsSortable(array $sortableColumns): array
  {
    $sortableColumns['datetime'] = 'datetime';
    return $sortableColumns;
  }
  
  /**
   * removeDefaultDateFilter
   *
   * This method removes the default date filter on the events listing page
   * and only on that page.
   *
   * @param array $months
   *
   * @return array
   */
  protected function removeDefaultDateFilter(array $months): array
  {
    return $this->isEventListing() ? [] : $months;
  }
  
  /**
   * isEventListing
   *
   * Returns true if this is the page that lists events.
   *
   * @return bool
   */
  private function isEventListing(): bool
  {
    if (!function_exists('get_current_screen')) {
      
      // if we can't even get the current screen, then we definitely can't
      // be on the event listing.  therefore, we just return false.
      
      return false;
    }
    
    $screen = get_current_screen();
    return ($screen->post_type ?? false) === SimpleEvents::POST_TYPE
      && ($screen->base ?? false) === 'edit';
  }
  
  /**
   * addFilters
   *
   * Adds a filter for event visibility, timing, and if there are event types
   * in the database, for types.
   *
   * @return void
   */
  protected function addFilters(): void
  {
    if ($this->isEventListing()) {
      $this->addTimingFilter();
      $this->addVisibilityFilter();
      $this->maybeAddTypeFilter();
    }
  }
  
  /**
   * addTimingFilter
   *
   * Adds a filter for event timing (i.e. prior or future events).
   *
   * @return void
   */
  private function addTimingFilter(): void
  {
    Timber::render('simple-events-filters.twig', [
      'id'        => 'filter-by-timing',
      'name'      => 'timing',
      'label'     => 'Timing',
      'all'       => 'Upcoming events',
      'all_value' => 'upcoming',
      'current'   => $_GET['timing'] ?? 'upcoming',
      'options'   => [
        'prior' => 'Prior events',
      ],
    ]);
  }
  
  /**
   * addVisibilityFilter
   *
   * Adds a filter for event visibility (i.e. public vs. private events).
   *
   * @return void
   */
  private function addVisibilityFilter(): void
  {
    Timber::render('simple-events-filters.twig', [
      'id'        => 'filter-by-visibility',
      'name'      => 'visibility',
      'label'     => 'Visibility',
      'all'       => 'Public and private events',
      'all_value' => 'both',
      'current'   => $_GET['visibility'] ?? 'both',
      'options'   => [
        'public'  => 'Public events',
        'private' => 'Private events',
      ],
    ]);
  }
  
  /**
   * maybeAddTypeFilter
   *
   * Determines whether or not we can add an event type filter and, if we can,
   * does so.
   *
   * @return void
   */
  private function maybeAddTypeFilter(): void
  {
    $types = get_terms(['taxonomy' => SimpleEvents::TAXONOMY]);
    if (sizeof($types) > 0) {
      
      // equipped with our list of types, we can create a filter for them.
      // but, get_terms returns an array of WP_Term objects and our filter
      // wants a map from slugs to names.  then, we grab the taxonomy
      // information itself for its labels.
      
      $slugs = array_map(fn(WP_Term $term) => $term->slug, $types);
      $names = array_map(fn(WP_Term $term) => $term->name, $types);
      $taxonomy = get_taxonomy(SimpleEvents::TAXONOMY);
      
      Timber::render('simple-events-filters.twig', [
        'id'      => esc_attr('filter-by-' . $taxonomy->name),
        'name'    => esc_attr($taxonomy->name),
        'label'   => $taxonomy->label,
        'all'     => $taxonomy->labels->all_items,
        'current' => $_GET[$taxonomy->name] ?? '',
        'options' => array_combine($slugs, $names),
      ]);
    }
  }
  
  /**
   * handleSorts
   *
   * Handles the datetime meta query sort for our events.
   *
   * @param WP_Query $query
   *
   * @return void
   */
  protected function handleSorts(WP_Query $query): void
  {
    if ($this->isEventListing()) {
      $orderBy = $query->get('orderby');
      
      // by default, we'll sort by the event date time so that things are in
      // chronological order.  we check for an empty sort first; we don't want
      // to undo anything that a visitor has chosen.
      
      if (empty($orderBy)) {
        $query->set('orderby', ($orderBy = 'datetime'));
        $query->set('order', 'ASC');
      }
      
      // now, if the order by is the datetime (either cause it was chosen or
      // because we set it above) we have to tell WP how to do that.  that
      // means setting a meta query and switching our sort over to the value
      // of that meta query.
      
      if ($orderBy === 'datetime') {
        $fullMetaKey = $this->handler->getPostMetaPrefix() . 'datetime';
        $this->addMetaQuery($query, ['datetime' => ['key' => $fullMetaKey]]);
        $query->set('meta_type', 'DATETIME');
      }
    }
  }
  
  /**
   * addMetaQuery
   *
   * Merges a new meta query into any existing queries already present within
   * this query.
   *
   * @param WP_Query $query
   * @param array    $metaQuery
   *
   * @return void
   */
  private function addMetaQuery(WP_Query $query, array $metaQuery): void
  {
    $priorMetaQuery = $query->get('meta_query', []);
    $newMetaQuery = array_merge($priorMetaQuery, $metaQuery);
    $query->set('meta_query', $newMetaQuery);
  }
  
  /**
   * handleFilters
   *
   * We add custom filters by date and host; here we make sure WP understands
   * how to actually filter based on those values.
   *
   * @param WP_Query $query
   *
   * @return void
   */
  protected function handleFilters(WP_Query $query): void
  {
    if ($this->isEventListing()) {
      
      // as long as we're on the event listing, we may need to update our
      // query so that it only grabs certain types of events.  the event type
      // filter works automatically because its name is the same as the
      // taxonomy.  but, for the visibility and timing filters, we'll need
      // to handle those here.
      
      $this->handleTimingFilter($query);
      $this->handleVisibilityFilter($query);
    }
  }
  
  private function handleTimingFilter(WP_Query $query): void
  {
    // unlike the visibility filter above, we always want to do this one.
    // that's because showing all events is pretty useless, but showing either
    // upcoming or prior events is cognitively easier to grasp for our puny
    // human minds.
    
    $timing = $_GET['timing'] ?? 'upcoming';
    $this->addMetaQuery($query, [
      'timing-filter' => [
        'compare' => $timing === 'upcoming' ? '>=' : '<=',
        'key'     => $this->handler->getPostMetaPrefix() . 'datetime',
        'value'   => date('Y-m-d h:i'),
        'type'    => 'DATETIME',
      ],
    ]);
    
    // now, if our sort is also based on the datetime, we'll want to change
    // the order based on our timing as well.  by default, we want to sort
    // upcoming events in ascending order so the next event is at the top of
    // the list.  but, for prior events, we sort in reverse chronological
    // order so the most recent one is first.  but, if the sort is based on
    // some other value or if the order is specified on the query string, we
    // leave it alone.
    
    if ($query->get('orderby') === 'datetime' && !isset($_GET['order'])) {
      $query->set('order', $timing === 'upcoming' ? 'ASC' : 'DESC');
    }
  }
  
  /**
   * handleVisibilityFilter
   *
   * When needed, alters the query to limit events to either public or
   * private events.
   *
   * @param WP_Query $query
   *
   * @return void
   */
  private function handleVisibilityFilter(WP_Query $query): void
  {
    $visibility = $_GET['visibility'] ?? 'both';
    if ($visibility !== 'both') {
      
      // if we want to show either public or private events, here's where we
      // do that.  if we're showing both, then there's no need to even be in
      // here, hence the if-statement.
      
      $this->addMetaQuery($query, [
        'visibility-filter' => [
          'key'   => $this->handler->getPostMetaPrefix() . 'private',
          'value' => $visibility,
        ],
      ]);
    }
  }
}




