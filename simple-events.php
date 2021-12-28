<?php

namespace Dashifen;

use Dashifen\Exception\Exception;
use Dashifen\SimpleEvents\SimpleEvents\SimpleEvents;
use Dashifen\SimpleEvents\Agents\EventTaxonomyAgent;
use Dashifen\SimpleEvents\Agents\EventRegistrationAgent;
use Dashifen\WPHandler\Agents\Collection\Factory\AgentCollectionFactory;

// if the SimpleEvents object is not available, then it's very likely that
// we're working in a development environment.  therefore, we'll include the
// plugin's copy of the autoloader so that we can access it's functionality.
// otherwise, in a live environment, the site's autoloader will already have
// included this information and we don't need the local autoloader at all.

if (!class_exists(SimpleEvents::class)) {
  require 'vendor/autoload.php';
}

(function() {
  
  // by doing all of our work for this plugin within this anonymous function,
  // it prevents any leakage of the object constructed herein into the rest of
  // the WordPress global space.
  
  try {
    $simpleEvents = new SimpleEvents();
    $agentCollectionFactory = new AgentCollectionFactory();
    $agentCollectionFactory->registerAgent(EventRegistrationAgent::class);
    $agentCollectionFactory->registerAgent(EventTaxonomyAgent::class);
    $simpleEvents->setAgentCollection($agentCollectionFactory);
    $simpleEvents->initialize();
  } catch (Exception $e) {
    
    // if we were unable to initialize our plugin, then we're just going to
    // keel over and die.  this is unlikely outside of development, so we'll
    // simply hope it doesn't happen for the moment.
    
    SimpleEvents::catcher($e);
  }
})();
