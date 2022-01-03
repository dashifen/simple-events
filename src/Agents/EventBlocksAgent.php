<?php

namespace Dashifen\SimpleEvents\Agents;

use Dashifen\SimpleEvents\SimpleEvents;
use Dashifen\Repository\RepositoryException;
use Dashifen\SimpleEvents\Repositories\Event;
use Dashifen\Transformer\TransformerException;
use Dashifen\SimpleEvents\Repositories\Calendar;
use Dashifen\WPHandler\Handlers\HandlerException;
use Dashifen\WPHandler\Agents\AbstractPluginAgent;

/**
 * EventBlocksAgent
 *
 * While the PHP would allow any type of handler, we that the handler that is
 * linked to this agent is our SimpleEvents object because of the work done in
 * the ../simple-events.php file.  Since this object needs to access public
 * methods of its handler, we'll type hint it here.
 *
 * @property SimpleEvents $handler
 */
class EventBlocksAgent extends AbstractPluginAgent
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
      $this->addAction('init', 'registerBlocks');
      $this->addAction('enqueue_block_editor_assets', 'addEditorAssets');
      $this->addFilter('block_categories_all', 'addEventBlockCategory');
    }
  }
  
  /**
   * registerBlocks
   *
   * Adds the simple-events blocks (event and calendar) to the editor.
   *
   * @return void
   */
  protected function registerBlocks(): void
  {
    register_block_type(
      'simple-events/event',
      [
        'render_callback' => [$this, 'renderEvent'],
        'attributes'      => [],
      ]
    );
    
    register_block_type(
      'simple-events/calendar',
      [
        'render_callback' => [$this, 'renderCalendar'],
        'attributes'      => [
          'types'       => ['type' => 'array'],
          'withPrivate' => ['type' => 'boolean'],
        ],
      ]
    );
  }
  
  /**
   * renderEvent
   *
   * Renders the event block.
   *
   * @param array $attributes
   *
   * @return string
   * @throws HandlerException
   * @throws TransformerException
   */
  public function renderEvent(array $attributes): string
  {
    try {
      $event = new Event($attributes['postId'] ?? null, $this->handler);
      return $event->render();
    } catch (RepositoryException $e) {
      return $e->getMessage();
    }
  }
  
  /**
   * renderCalendar
   *
   * Renders the calendar block.
   *
   * @param array $attributes
   *
   * @return string
   */
  public function renderCalendar(array $attributes): string
  {
    $attributes = wp_parse_args($attributes, [
      'type'  => 0,
      'year'  => date('Y'),
      'month' => date('n'),
    ]);
    
    try {
      $calendar = new Calendar($attributes, $this->handler);
      return $calendar->render();
    } catch (RepositoryException $e) {
      return $e->getMessage();
    }
  }
  
  /**
   * addEditorAssets
   *
   * Adds then necessary JS to the editor so that our blocks function.
   *
   * @return void
   */
  protected function addEditorAssets(): void
  {
    $this->enqueue('assets/simple-events-blocks.min.js');
  }
  
  /**
   * addEventBlockCategory
   *
   * Adds the simple-events block category to the editor which contains our
   * blocks.
   *
   * @param array $blockCategories
   *
   * @return array
   */
  protected function addEventBlockCategory(array $blockCategories): array
  {
    $blockCategories[] = [
      'title' => 'Events',
      'slug'  => 'simple-events',
      'icon'  => 'calendar-alt',
    ];
    
    return $blockCategories;
  }
}
