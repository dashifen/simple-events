<?php

namespace Dashifen\SimpleEvents\Agents;

use Timber\Timber;
use Dashifen\SimpleEvents\SimpleEvents;
use Dashifen\Transformer\TransformerException;
use Dashifen\WPHandler\Handlers\HandlerException;
use Dashifen\WPHandler\Agents\AbstractPluginAgent;
use Dashifen\WPHandler\Traits\PostMetaManagementTrait;

class EventMetaAgent extends AbstractPluginAgent
{
  use PostMetaManagementTrait;
  
  public const POST_META = [
    '_time'     => 'Time',
    '_date'     => 'Date',
    '_duration' => 'Duration',          // in minutes
    'datetime'  => 'Date & Time',
    'host'      => 'Host',
  ];
  
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
    $this->addAction('add_meta_boxes', 'addEventMetabox');
  }
  
  protected function addEventMetabox(): void
  {
    add_meta_box(SimpleEvents::POST_TYPE . '-metabox', 'Event Information',
      [$this, 'showEventMetaBox'], SimpleEvents::POST_TYPE, 'normal',
      'high', ['__block_editor_compatible_meta_box' => true]);
  }
  
  /**
   * showEventMetaBox
   *
   * Displays the event metabox in the editor.  Public because it's called via
   * an internal callback within WordPress core and we're not sure exactly how
   * we could add that as a hook within this agent.
   *
   * @return void
   * @throws HandlerException
   * @throws TransformerException
   */
  public function showEventMetaBox(): void
  {
    $id = get_the_ID();
    $keys = array_keys(self::POST_META);
    
    // since the values for our metadata all get selected with the same method,
    // we can use array_map to "loop" over the keys and call getPostMeta for
    // each of them. then, when we render our twig, we can combine the keys and
    // their selected values as the context for it.
    
    $values = array_map(fn($key) => $this->getMetaValue($id, $key), $keys);
    Timber::render('event-metabox.twig', array_combine($keys, $values));
  }
  
  /**
   * getMetaValue
   *
   * While the getPostMeta method will handle the majority of the work we need
   * to do when getting meta data, this method enhances it by specifying better
   * defaults based on which key we're selecting.
   *
   * @param int    $postId
   * @param string $metaKey
   *
   * @return string
   * @throws HandlerException
   * @throws TransformerException
   */
  private function getMetaValue(int $postId, string $metaKey): string
  {
    switch($metaKey) {
      default:
        return $this->getPostMeta($postId, $metaKey);
        
      case '_duration':
        return $this->getPostMeta($postId, $metaKey, 1);
        
      case 'host':
        return $this->getPostMeta($postId, $metaKey, wp_get_current_user()->display_name);
    }
  }
  
  /**
   * getPostMetaNames
   *
   * Inherited from the PostMetaManagementTrait, this method returns an array
   * of valid post meta names for use within that trait's isPostMetaValid
   * method.
   *
   * @return array
   */
  protected function getPostMetaNames(): array
  {
    return array_keys(self::POST_META);
  }
  
  /**
   * getPostMetaNamePrefix
   *
   * Returns the prefix that that is used to differentiate the post meta for
   * this handler's sphere of influence from others.  By default, we return
   * an empty string, but we assume that this will likely get overridden.
   * Public in case an agent needs to ask their handler what prefix to use.
   *
   * @return string
   */
  public function getPostMetaNamePrefix(): string
  {
    // our post type is "simple-event" so by returning it along with a hyphen,
    // our post meta will have keys like simple-event-host in the database.
    
    return SimpleEvents::POST_TYPE . '-';
  }
}
