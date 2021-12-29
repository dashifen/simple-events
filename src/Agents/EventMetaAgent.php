<?php

namespace Dashifen\SimpleEvents\Agents;

use Dashifen\SimpleEvents\SimpleEvents;
use Dashifen\WPHandler\Handlers\HandlerException;
use Dashifen\CaseChangingTrait\CaseChangingTrait;
use Dashifen\WPHandler\Agents\AbstractPluginAgent;
use Dashifen\WPHandler\Traits\PostMetaManagementTrait;
use Dashifen\SimpleEvents\Services\PostMetaTransformer;
use Dashifen\WPHandler\Handlers\Plugins\PluginHandlerInterface;
use Dashifen\Transformer\StorageTransformer\StorageTransformerInterface;

class EventMetaAgent extends AbstractPluginAgent
{
  use CaseChangingTrait;
  use PostMetaManagementTrait;
  
  public const POST_META = [
    'host'     => 'Host',
    'time'     => 'Time',
    'date'     => 'Date',
    'datetime' => 'Date & Time',
    'duration' => 'Duration',       // in hours rounded to the nearest quarter
    'location' => 'Location',
    'private'  => 'Private',
  ];
  
  private StorageTransformerInterface $transformer;
  
  /**
   * AbstractPluginService constructor.
   *
   * @param PluginHandlerInterface           $handler
   * @param StorageTransformerInterface|null $transformer
   *
   * @throws HandlerException
   */
  public function __construct(
    PluginHandlerInterface       $handler,
    ?StorageTransformerInterface $transformer = null
  ) {
    parent::__construct($handler);
    $this->transformer = $transformer ?? new PostMetaTransformer();
  }
  
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
    $this->addAction('init', 'registerMeta');
  }
  
  /**
   * registerMeta
   *
   * Registers our post metadata with WordPress Core so that the JavaScript
   * editor plugin can get and set them.
   *
   * @return void
   */
  protected function registerMeta(): void
  {
    foreach (array_keys(self::POST_META) as $metaKey) {
      $fullMetaKey = $this->getFullPostMetaName($metaKey);
      register_post_meta(SimpleEvents::POST_TYPE, $fullMetaKey, [
        'type'              => $metaKey === 'duration' ? 'number' : 'string',
        'show_in_rest'      => true,
        'single'            => true,
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback'     => function () {
          return current_user_can('edit_posts');
        },
      ]);
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
    // our post type is "simple-event" so by returning it along with a hyphen
    // and then converting to snake case, our meta names become something like
    // simple_event_host.  we use snake case for them because that's what works
    // best for JavaScript object properties.
    
    return $this->kebabToSnakeCase(SimpleEvents::POST_TYPE . '-');
  }
}
