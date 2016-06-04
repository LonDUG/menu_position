<?php

/**
 * @file
 * Contains \Drupal\menu_position\Plugin\MenuPositionConditionPluginManager.
 */

namespace Drupal\menu_position\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides the Menu position condition plugin plugin manager.
 */
class MenuPositionConditionPluginManager extends DefaultPluginManager {

  /**
   * Constructor for MenuPositionConditionPluginManager objects.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/MenuPositionConditionPlugin', $namespaces, $module_handler, 'Drupal\menu_position\Plugin\MenuPositionConditionPluginInterface', 'Drupal\menu_position\Annotation\MenuPositionConditionPlugin');

    $this->alterInfo('menu_position_menu_position_condition_plugin_info');
    $this->setCacheBackend($cache_backend, 'menu_position_menu_position_condition_plugin_plugins');
  }

}
