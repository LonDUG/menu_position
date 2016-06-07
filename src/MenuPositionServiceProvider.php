<?php

namespace Drupal\menu_position;

use \Drupal\Core\DependencyInjection\ServiceProviderBase;
use \Drupal\Core\DependencyInjection\ContainerBuilder;

class MenuPositionServiceProvider extends ServiceProviderBase{

  public function alter(ContainerBuilder $container) {
    // Override the menu active trail with a new class.
    $definition = $container->getDefinition('menu.active_trail');
    $definition->setClass('Drupal\menu_position\Menu\MenuPositionActiveTrail');
  }
}
