<?php

namespace Drupal\menu_position\Menu;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Entity\Query\QueryFactoryInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Menu\MenuActiveTrail;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;

class MenuPositionActiveTrail extends MenuActiveTrail  {

  /**
   * Constructs a \Drupal\Core\Menu\MenuActiveTrail object.
   *
   * @param \Drupal\Core\Menu\MenuLinkManagerInterface $menu_link_manager
   *   The menu link plugin manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   A route match object for finding the active link.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend.
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   *   The lock backend.
   */
  public function __construct(
    MenuLinkManagerInterface $menu_link_manager,
    RouteMatchInterface $route_match,
    CacheBackendInterface $cache,
    LockBackendInterface $lock,
    QueryFactory $entity_query,
    EntityManagerInterface $entity_manager) {

    parent::__construct($menu_link_manager, $route_match, $cache, $lock);
    $this->entity_query = $entity_query;
    $this->entity_manager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getActiveLink($menu_name = NULL) {
    // Get all the rules.
    $query = $this->entity_query->get('menu_position_rule');
    $results = $query->sort('weight')->execute();
    $rules = $this->entity_manager->getStorage('menu_position_rule')->loadMultiple($results);

    // Iterate over the rules.
    foreach ($rules as $rule) {
      // This rule is active.
      if ($rule->isActive()) {
        $menu_link = $this->menuLinkManager->createInstance($rule->getMenuLink());
        $settings = \Drupal::config('menu_position.settings');
        switch ($settings->get('link_display')) {
          case 'child':
            return $menu_link;
            break;
          case 'parent':
            return $this->menuLinkManager->createInstance($menu_link->getParent());
            break;
          case 'none':
            return null;
            break;
        }
      }
    }

    // Default implementation takes here.
    return parent::getActiveLink($menu_name);
  }
}
