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

  private $active_rule = null;
  private $settings;

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
    $this->settings = \Drupal::config('menu_position.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function getActiveLink($menu_name = NULL) {
    // Get all the rules.
    $query = $this->entity_query->get('menu_position_rule');
    $results = $query->sort('weight')->execute();

    // Try and locate an active rule.
    if (false === $rule = $this->getActiveRule()) {
      return parent::getActiveLink($menu_name);
    }

    // Get correct menu link based on the settings.
    $menu_link = $this->menuLinkManager->createInstance($rule->getMenuLink());
    switch ($this->settings->get('link_display')) {
      case 'child':
        // Set this menu link to active.
        return $menu_link;
        break;
      case 'parent':
        return $this->menuLinkManager->createInstance($menu_link->getParent());
        break;
      case 'none':
        return null;
        break;
      default:
        return null;
        break;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getActiveTrailIds($menu_name) {
    $trail_ids = parent::getActiveTrailIds();
    if ($rule = $this->getActiveRule()) {
      if ($this->settings->get('link_display') == 'child') {
        $request = \Drupal::request();
        if ($route = $request->attributes->get(\Symfony\Cmf\Component\Routing\RouteObjectInterface::ROUTE_OBJECT)) {
          $title = \Drupal::service('title_resolver')->getTitle($request, $route);
        }
        $id = 'menu_position_rule:' . $rule->getId() . ':' . $title;
        $trail_ids[$id] = $id;
      }
    }
    return $trail_ids;
  }

  /**
   * Sets the active rule if it is not set and returns the active rule.
   *
   * @return MenuPositionRule|false
   */
  public function getActiveRule() {
    // Rule has been set, return it.
    if ($this->active_rule !== null) {
      return $this->active_rule;
    }

    // Iterate over the rules.
    $rules = $this->entity_manager->getStorage('menu_position_rule')->loadMultiple($results);
    foreach ($rules as $rule) {
      // This rule is active, set it and return.
      if ($rule->isActive()) {
        $this->active_rule = $rule;
        return $this->active_rule;
      }
    }

    // No active rule, false.
    $this->active_rule = false;
    return $this->active_rule;
  }

  /**
   * Clears the active rule so that calling "getActiveRule" again re-evaluates
   * the rules.
   */
  public function resetRule() {
    $this->active_rule = null;
  }
}
