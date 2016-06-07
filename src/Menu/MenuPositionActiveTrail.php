<?php

namespace Drupal\menu_position\Menu;

use Drupal\Core\Menu\MenuActiveTrail;

class MenuPositionActiveTrail extends MenuActiveTrail  {

  /**
   * {@inheritdoc}
   */
  public function getActiveLink($menu_name = NULL) {
    $entity_query = \Drupal::service('entity.query');
    $entity_manager = \Drupal::service('entity.manager');

    // Get all the rules.
    $query = $entity_query->get('menu_position_rule');
    $results = $query->sort('weight')->execute();
    $rules = $entity_manager->getStorage('menu_position_rule')->loadMultiple($results);

    // Iterate over the rules and conditions for each rule.
    foreach ($rules as $rule) {
      // This rule is active.
      if ($rule->isActive()) {
        $link = $entity_manager->getStorage('menu_link_content')->load($rule->getMenuLinkId());
        return $link;
      }
    }

    // Default implementation takes here.
    return parent::getActiveLink($menu_name);
  }
}
