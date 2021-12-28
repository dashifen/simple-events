<?php

namespace Dashifen\SimpleEvents\Agents;

use Dashifen\WPHandler\Handlers\HandlerException;
use Dashifen\WPHandler\Agents\AbstractPluginAgent;
use Dashifen\WPHandler\Traits\TaxonomyRegistrationTrait;

class EventTaxonomyAgent extends AbstractPluginAgent
{
  use TaxonomyRegistrationTrait;
  
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
    // not sure if the slightly delayed registration is needed here, but
    // because the post type to which this taxonomy will be linked is
    // registered at priority level 10, we'll register it at 15 to be sure that
    // its type exists
    
    $this->addAction('init', 'registerTaxonomy', 15);
  }
  
  /**
   * registerTaxonomy
   *
   * Registers the event-type taxonomy.
   *
   * @return void
   */
  protected function registerTaxonomy()
  {
    register_taxonomy('event-type', ['simple-event'], [
      'labels'            => $this->getTaxonomyLabels('Event Type', 'Event Types'),
      'hierarchical'      => true,
      'public'            => true,
      'show_ui'           => true,
      'show_admin_column' => true,
      'show_in_nav_menus' => true,
      'show_tagcloud'     => false,
      'rewrite'           => [
        'slug'         => 'events/types',
        'with_front'   => true,
        'hierarchical' => true,
      ],
      'show_in_rest'      => true,
    ]);
  }
}
