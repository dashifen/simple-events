<?php

namespace Dashifen\SimpleEvents\Agents;

use Dashifen\WPHandler\Handlers\HandlerException;
use Dashifen\WPHandler\Agents\AbstractPluginAgent;
use Dashifen\WPHandler\Traits\PostTypeRegistrationTrait;

class EventRegistrationAgent extends AbstractPluginAgent
{
  use PostTypeRegistrationTrait;
  
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
    $this->addAction('init', 'registerEventType');
  }
  
  /**
   * registerEventType
   *
   * Registers the event post type.
   *
   * @return void
   */
  protected function registerEventType(): void
  {
    register_post_Type('simple-event', [
      'label'               => 'Event',
      'description'         => 'A simple event post type for WordPress.',
      'labels'              => $this->getPostTypeLabels('Event', 'Events'),
      'supports'            => ['title', 'editor', 'thumbnail', 'revisions', 'custom-fields', 'page-attributes'],
      'hierarchical'        => false,
      'public'              => true,
      'show_ui'             => true,
      'show_in_menu'        => true,
      'menu_position'       => 5,
      'menu_icon'           => 'dashicons-calendar-alt',
      'show_in_admin_bar'   => true,
      'show_in_nav_menus'   => true,
      'can_export'          => true,
      'has_archive'         => 'events',
      'exclude_from_search' => false,
      'publicly_queryable'  => true,
      'rewrite'             => [
        'slug'       => 'events',
        'with_front' => true,
        'pages'      => true,
        'feeds'      => true,
      ],
      'capability_type'     => 'post',
      'show_in_rest'        => true,
    ]);
  }
}
