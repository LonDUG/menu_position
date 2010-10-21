<?php
// $Id$

/**
 * @file
 * Provides the "Node type" rule plugin for the Menu Position module.
 */

/**
 * Adds form elements for the "node type" plugin to the rule configuration form.
 */
function menu_position_rule_node_type_form(&$form, &$form_state) {
  // If this is an existing rule, load the variables stored in the rule for this plugin.
  $variables = !empty($form_state['#menu-position-rule']['conditions']['node_type']) ? $form_state['#menu-position-rule']['conditions']['node_type'] : array();

  $form['conditions']['node_type'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Content type(s)'),
    '#default_value' => !empty($variables['node_type']) ? $variables['node_type'] : array(),
    '#options' => node_type_get_names(),
    '#description' => t('Apply this rule only on pages that display content of the given type(s). If you select no types, there will be no type-specific limitation.'),
    '#weight' => -20,
  );

  // If we had a validation handler, we could add it this way. Or we could add
  // a per-element validation handler with '#element_validate' above.
  //$form['#validate'][] = 'menu_position_rule_node_type_form_validate';

  // Add a submit handler.
  $form['#submit'][] = 'menu_position_rule_node_type_form_submit';
}

/**
 * Prepares the "node type" variables to be stored in the rule.
 */
function menu_position_rule_node_type_form_submit($form, &$form_state) {
  // The user has added our plugin's form elements as a condition for the rule.
  if (!empty($form_state['values']['node_type'])) {
    // Remove any 0 valued options.
    foreach ($form_state['values']['node_type'] as $key => $value) {
      if ($value === 0) {
        unset($form_state['values']['node_type'][$key]);
      }
    }
    // Since our form elements indicate that our plugin is to be used for this
    // rule, we need to add the appropriate variables to
    // $form_state['values']['conditions']['PLUGIN'] so that they can be stored statically
    // with the rule.
    $variables = array(
      'node_type' => $form_state['values']['node_type'],
    );
    $form_state['values']['conditions']['node_type'] = $variables;
  }
  // If $form_state['conditions']['PLUGIN'] is not set, the plugin will not be
  // added as a condition for this rule.
}

/**
 * Checks if the node is of a certain type.
 *
 * @return
 *   TRUE if condition applies successfully. Otherwise FALSE.
 */
function menu_position_condition_node_type($variables) {
  // Grab the variables stored statically in the rule.
  $node_type = $variables['node_type'];

  // $node is dynamically added and may not exist.
  $node = isset($variables['node']) ? $variables['node'] : NULL;

  return ($node && in_array($node->type, $node_type)) ? TRUE : FALSE;
}
