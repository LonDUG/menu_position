<?php

namespace Drupal\menu_position\Menu;

use Drupal\Core\Menu\MenuActiveTrail;
use Drupal\Core\Plugin\ContextAwarePluginInterface;

class MenuPositionActiveTrail extends MenuActiveTrail  {

  /**
   * {@inheritdoc}
   */
  public function getActiveLink($menu_name = NULL) {
    $entity_query = \Drupal::service('entity.query');
    $entity_manager = \Drupal::service('entity.manager');
    $context_repository = \Drupal::service('context.repository');

    // Get all the rules.
    $query = $entity_query->get('menu_position_rule');
    $results = $query->sort('weight')->execute();
    $rules = $entity_manager->getStorage('menu_position_rule')->loadMultiple($results);

    // Iterate over the rules and conditions for each rule.
    foreach ($rules as $rule) {
      // Rules are good unless told otherwise by the conditions.
      $active = true;
      foreach ($rule->getConditions() as $condition) {
        // Need to get context for this condition.
        if ($condition instanceof ContextAwarePluginInterface) {
          $contexts = $context_repository->getRuntimeContexts($condition->getContextMapping());
          foreach ($condition->getContextMapping() as $name => $context) {
            if (isset($contexts[$context])) {
              $condition->setContext($name, $contexts[$context]);
            }
          }
        }

        // If this rule evaluates to false don't fire.
        if (!$condition->evaluate()) {
          $active = false;
        }
      }

      // This rule is active.
      if ($active) {
        // Do something...
      }
    }

    // Default implementation takes here.
    return parent::getActiveLink($menu_name);
  }
}
