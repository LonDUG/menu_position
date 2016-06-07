<?php

namespace Drupal\menu_position;

use Symfony\Component\DependencyInjection\Reference;
use \Drupal\Core\DependencyInjection\ContainerBuilder;
use \Drupal\Core\DependencyInjection\ServiceProviderBase;

class MenuPositionServiceProvider extends ServiceProviderBase{

  public function alter(ContainerBuilder $container) {
    require_once('/Users/luke/Sites/menu-position/modules/contrib/devel/kint/kint/Kint.class.php');

    // Override the menu active trail with a new class.
    $definition = $container->getDefinition('menu.active_trail');
    $definition->setClass('Drupal\menu_position\Menu\MenuPositionActiveTrail');
    $definition->addArgument(new Reference('entity.query'));
    $definition->addArgument(new Reference('entity.manager'));
  }
}
