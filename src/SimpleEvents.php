<?php

namespace Dashifen\SimpleEvents;

use Dashifen\Transformer\TransformerException;
use Dashifen\SimpleEvents\Agents\EventMetaAgent;
use Dashifen\WPHandler\Handlers\HandlerException;
use Dashifen\WPHandler\Handlers\Plugins\AbstractPluginHandler;

class SimpleEvents extends AbstractPluginHandler
{
  public const POST_TYPE = 'simple-event';
  public const TAXONOMY = 'event-type';
  
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
    // at priority level zero we'll initialize our agents.  this uses a method
    // of our parent object so you won't find initializeAgents defined herein.
    // we do this at priority zero so that agents can use the default priority
    // level (10) for their work and we don't have to worry about whether or
    // not they've "missed" their window.
    
    $this->addAction('init', 'initializeAgents', 0);
    $this->addFilter('timber/locations', 'addTwigLocation');
    $this->addAction('admin_enqueue_scripts', 'addAdminAssets');
  }
  
  /**
   * addTwigLocation
   *
   * Adds the location of this plugin's twig files to Timber's internal list
   * of such locations.
   *
   * @param array $locations
   *
   * @return array
   */
  protected function addTwigLocation(array $locations): array
  {
    $locations[] = $this->getPluginDir() . '/assets/twigs/';
    return $locations;
  }
  
  /**
   * addAdminAssets
   *
   * Adds scripts and/or styles to the appropriate screens within the WordPress
   * dashboard.
   *
   * @return void
   */
  protected function addAdminAssets(): void
  {
    if ($this->isEventEditor()) {
      $this->enqueue('assets/simple-events-postmeta.min.js');
      $this->enqueue('assets/simple-events.css');
    }
  }
  
  /**
   * isEventEditor
   *
   * Returns true if we're looking at the post editor for our post type.
   *
   * @return bool
   */
  private function isEventEditor(): bool
  {
    $screen = get_current_screen();
    return $screen->base === 'post' && $screen->post_type === self::POST_TYPE;
  }
  
  /**
   * getPostMeta
   *
   * This method accesses the EventMetaAgent to fetch post meta information
   * from the database and returns it to the calling scope.
   *
   * @param int    $postId
   * @param string $postMeta
   *
   * @return string
   * @throws HandlerException
   * @throws TransformerException
   */
  public function getPostMeta(int $postId, string $postMeta): string
  {
    return $this->getMetaAgent()->getPostMeta($postId, $postMeta);
  }
  
  /**
   * getMetaAgent
   *
   * To assist other methods in being able to use method completion, this
   * method simply returns our EventMetaAgent.  It also makes other methods
   * a little more readable, but in the end, it's mostly for convenience.
   *
   * @return EventMetaAgent
   */
  private function getMetaAgent(): EventMetaAgent
  {
    return $this->agentCollection[EventMetaAgent::class];
  }
  
  /**
   * getPostMetaPrefix
   *
   * Passes the request for this plugin's post meta prefix over to the Agent
   * that handles such thing.
   *
   * @return string
   */
  public function getPostMetaPrefix(): string
  {
    return $this->getMetaAgent()->getPostMetaNamePrefix();
  }
}
