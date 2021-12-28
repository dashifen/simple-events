<?php

namespace Dashifen\SimpleEvents\Agents;

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
    $this->addFilter('manage_' . SimpleEvents::POST_TYPE . '_posts_columns', 'addColumns');
    $this->addAction('manage_' . SimpleEvents::POST_TYPE . '_posts_custom_column', 'addColumnData', 10, 2);
    //$this->addFilter('manage_edit-' . SimpleEvents::POST_TYPE . '_sortable_columns', 'addSortableColumns');
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
    foreach ($original as $column => $heading) {
      $modified[$column] = $heading;
      
      if ($column === 'taxonomy-' . SimpleEvents::TAXONOMY) {
        
        // if we just found our taxonomy's column, then we'll add our event
        // meta data after it.  the information we want to add here can be
        // found in the POST_META constant of our EventMetaAgent object.
        
        foreach (EventMetaAgent::POST_META as $key => $display) {
          if (substr($key, 0, 1) !== '_') {
            
            // there are some private meta keys for which we don't want to add
            // columns.  therefore, only if the first character of our keys is
            // not an underscore do we actually add this information to our
            // modified list of columns.
            
            $modified[$key] = $display;
          }
        }
      }
    }
    
    return $modified;
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
}
