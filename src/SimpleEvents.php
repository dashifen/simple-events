<?php

namespace Dashifen\SimpleEvents;

use Dashifen\WPHandler\Handlers\AbstractHandler;
use Dashifen\WPHandler\Handlers\HandlerException;

class SimpleEvents extends AbstractHandler
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
    // at priority level zero we'll initialize our agents.  this uses a method
    // of our parent object so you won't find initializeAgents defined herein.
    // we do this at priority zero so that agents can use the default priority
    // level (10) for their work and we don't have to worry about whether or
    // not they've "missed" their window.
    
    $this->addAction('init', 'initializeAgents', 0);
  }
}
