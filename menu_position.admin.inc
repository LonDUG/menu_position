<?php
// $Id$

/**
 * @file
 * Provides infrequently used functions and hooks for menu_position.
 */

/**
 * Implements hook_menu().
 */
function _menu_position_menu() {
  $items['admin/config/user-interface/menu-position'] = array(
    'title' => 'Menu position rules',
    'description' => 'Configure rules for menu positions.',
    'access arguments' => array('administer menu positions'),
    'page callback' => 'drupal_get_form',
    'page arguments' => array('menu_position_rules_form'),
    'type' => MENU_NORMAL_ITEM,
    'file' => 'menu_position.admin.inc',
  );
  $items['admin/config/user-interface/menu-position/add'] = array(
    'title' => 'Add menu position rule',
    'description' => 'Add a new menu position rule.',
    'access arguments' => array('administer menu positions'),
    'page callback' => 'drupal_get_form',
    'page arguments' => array('menu_position_add_rule_form'),
    'type' => MENU_LOCAL_ACTION,
    'file' => 'menu_position.admin.inc',
  );
  $items['admin/config/user-interface/menu-position/edit'] = array(
    'title' => 'Edit menu position rule',
    'description' => 'Edit a menu position rule.',
    'access arguments' => array('administer menu positions'),
    'page callback' => 'drupal_get_form',
    'page arguments' => array('menu_position_edit_rule_form'),
    'type' => MENU_CALLBACK,
    'file' => 'menu_position.admin.inc',
  );
  $items['admin/config/user-interface/menu-position/delete'] = array(
    'title' => 'Delete menu position rule',
    'description' => 'Delete a menu position rule.',
    'access arguments' => array('administer menu positions'),
    'page callback' => 'drupal_get_form',
    'page arguments' => array('menu_position_delete_rule_form'),
    'type' => MENU_CALLBACK,
    'file' => 'menu_position.admin.inc',
  );
  return $items;
}

/**
 * Menu callback: orders rules.
 */
function menu_position_rules_form($form, &$form_state) {
  $form['#attached']['css'] = array(drupal_get_path('module', 'menu') . '/menu.css');

  $rules = db_query('SELECT rid, admin_title, weight FROM {menu_position_rules} ORDER BY weight, rid');
  $delta = $rules->rowCount();

  // Default message if no rules.
  if ($delta == 0) {
    $form['rules'] = array(
      '#markup' => '<p>' . t('No rules have been created yet.') . '</p>',
    );
  }
  else {
    $form['rules'] = array(
      '#tree' => TRUE,
      '#theme' => 'menu_position_rules_order',
    );
    foreach ($rules as $rule) {
      $form['rules'][$rule->rid] = array(
        'title' => array(
          '#markup' => check_plain($rule->admin_title),
        ),
        'enabled' => array(
          '#type' => 'checkbox',
          '#default_value' => $rule->enabled,
        ),
        'weight' => array(
          '#type' => 'weight',
          '#default_value' => $rule->weight,
          '#delta' => max($delta, 5),
          '#id' => 'edit-rule-' . $rule->rid,
        ),
        'operations' => array(
          'edit-link' => array(
            '#type' => 'link',
            '#title' => t('edit'),
            '#href' => 'admin/config/user-interface/menu-position/edit/' . $rule->rid,
          ),
          'delete-link' => array(
            '#type' => 'link',
            '#title' => t('delete'),
            '#href' => 'admin/config/user-interface/menu-position/delete/' . $rule->rid,
          ),
        ),
      );
    }
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save'),
    );
  }

  return $form;
}

/**
 * Handles form submission for menu_position_rules_form().
 */
function menu_position_rules_form_submit($form, &$form_state) {
  foreach ($form_state['values']['rules'] as $rid => $rule) {
    db_update('menu_position_rules')
      ->fields(array(
        'weight' => $rule['weight'],
        ))
      ->condition('rid', $rid)
      ->execute();
  }
  drupal_set_message(t('The new rules ordering has been applied.'));
}

function theme_menu_position_rules_order($variables) {
  $element = $variables['element'];
  drupal_add_tabledrag('menu-position-rules', 'order', 'sibling', 'rule-weight');

  $variables = array(
    'header' => array(
      t('Rule'),
      array(
        'data' => t('Enabled'),
        'class' => array('checkbox'),
      ),
      t('Weight'),
      array(
        'data' => t('Operations'),
        'colspan' => '2',
      ),
    ),
    'rows' => array(),
    'attributes' => array('id' => 'menu-position-rules'),
  );

  // Generate table of draggable menu names.
  foreach (element_children($element) as $rule) {
    // Add special classes to be used for tabledrag.js.
    $element[$rule]['weight']['#attributes']['class'] = array('rule-weight');
    // Render the title, enabled, and weight columns.
    $data = array(
      drupal_render($element[$rule]['title']),
      array(
        'data' => drupal_render($element[$rule]['enabled']),
        'class' => array('checkbox', 'menu-enabled'),
      ),
      drupal_render($element[$rule]['weight']),
    );
    // Render the operations links.
    foreach (element_children($element[$rule]['operations']) as $op) {
      $data[] = array(
        'data' => drupal_render($element[$rule]['operations'][$op]),
        'class' => array('menu-operations'),
      );
    }
    $variables['rows'][] = array(
      'data' => $data,
      'class' => array('draggable'),
    );
  }

  return theme('table', $variables);
}

/**
 * Menu callback; Adds rules.
 */
function menu_position_add_rule_form($form, &$form_state) {
  return menu_position_configure_rule_form($form, $form_state);
}

/**
 * Menu callback; Edits rules.
 */
function menu_position_edit_rule_form($form, &$form_state, $rid = 0) {
  // Make sure rid is 
  if ($rid == 0) {
    drupal_goto('admin/config/user-interface/menu-position');
    return;
  }
  $form_state['values'] = db_query('SELECT * FROM {menu_position_rules} WHERE rid = :rid', array(':rid' => $rid))->fetchAssoc();
  return menu_position_configure_rule_form($form, $form_state);
}

/**
 * Returns form to add or edit a menu position rule.
 */
function menu_position_configure_rule_form($form, &$form_state) {
  // Set the default values.
  $rid           = !empty($form_state['values']['rid']) ? $form_state['values']['rid'] : '';
  $admin_title   = !empty($form_state['values']['admin_title']) ? $form_state['values']['admin_title'] : '';
  $node_type     = !empty($form_state['values']['node_type']) ? $form_state['values']['node_type'] : '';
  $tids          = !empty($form_state['values']['tid']) ? $form_state['values']['tid'] : NULL;
  $php_condition = !empty($form_state['values']['php_condition']) ? $form_state['values']['php_condition'] : NULL;
  $plid          = !empty($form_state['values']['plid']) ? $form_state['values']['plid'] : NULL;
  $weight        = !empty($form_state['values']['weight']) ? $form_state['values']['weight'] : 0;

  $form['rid'] = array(
    '#type' => 'hidden',
    '#value' => $rid,
  );

  // Administrative title.
  $form['admin_title'] = array(
    '#type' => 'textfield',
    '#default_value' => $admin_title,
    '#title' => t('Administrative title'),
    '#description' => t('This title will be used administratively to identify this rule.'),
    '#required' => TRUE,
  );

  // Node type.
  $form['node_type'] = array(
    '#type' => 'select',
    '#title' => t('Content type'),
    '#default_value' => $node_type,
    '#options' => array('' => '<' . t('none') . '>')
  );
  foreach (node_type_get_types() as $type) {
    $form['node_type']['#options'][$type->type] = $type->name;
  }

  // Taxonomy terms.
  // @TODO Implement taxonomy
  /*
  if (module_exists('taxonomy')) {
    $form['tid'] = array(
      '#type' => 'fieldset',
      '#title' => t('Categories'),
      '#attributes' => array('class' => 'menu_position_categories'),
    );
    $vocabularies = taxonomy_get_vocabularies();
    $none_option = '<' . t('none') . '>';
    $vid2tid = array();
    if (is_array($tids)) {
      foreach ($tids as $tid) {
        if ($tid > 0) {
          $term = taxonomy_get_term($tid);
          $vid2tid[$term->vid] = $tid;
        }
        elseif ($tid < 0) {
          $vid2tid[-$tid] = $tid;
        }
      }
    }
    foreach ($vocabularies as $vocabulary) {
      $taxonomy_form = taxonomy_form($vocabulary->vid);
      unset($taxonomy_form['#options']['']);
      $taxonomy_form['#multiple'] = FALSE;
      $taxonomy_form['#default_value'] = $vid2tid[$vocabulary->vid] ? $vid2tid[$vocabulary->vid] : 0;
      unset($taxonomy_form['#size']);
      unset($all_option);
      $all_option->option = array(-$vocabulary->vid => '<' . t('any') . '>');
      array_unshift($taxonomy_form['#options'], $all_option);
      array_unshift($taxonomy_form['#options'], $none_option);
      $form['tid']['vid_' . $vocabulary->vid] = $taxonomy_form;
    }
  }
  */

  // PHP condition.
  $form['php_condition'] = array(
    '#type' => 'textarea',
    '#rows' => 1,
    '#title' => t('Condition'),
    '#description' => t('Optional PHP expression, e. g., $node->type == \'story\' || $node->type == \'news\''),
    '#default_value' => $php_condition,
  );

  // Parent menu item.
  $form['plid'] = array(
    '#type' => 'select',
    '#title' => t('Menu item'),
    '#required' => TRUE,
    '#options' => menu_parent_options(menu_get_menus(), array('mlid' => 0)),
    '#default_value' => 'main-menu:0',
  );
  if ($plid) {
    $menu_item = menu_link_load($plid);
    $form['plid']['#default_value'] = $menu_item['menu_name'] . ':' . $plid;
  }

  // Weight.
  $form['weight'] = array(
    '#type' => 'weight',
    '#title' => t('Weight'),
    '#default_value' => $weight,
    '#required' => TRUE,
    '#delta' => 25,
  );

  // Form validation and submission.
  $form['#validate'][] = 'menu_position_configure_rule_form_validate';
  $form['#submit'][] = 'menu_position_configure_rule_form_submit';

  if ($rid) {
    $form['delete'] = array(
      '#type' => 'submit',
      '#value' => t('Delete'),
    );
  }
  else {
    $form['cancel'] = array(
      '#type' => 'submit',
      '#value' => t('Cancel'),
    );
  }
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save'),
  );

  return $form;
}

/**
 * Validates the form for menu_position_configure_rule_form().
 */
function menu_position_configure_rule_form_validate($form, &$form_state) {
  // Check if the user cancelled the form.
  if ($form_state['values']['op'] == $form['cancel']['#value']) {
    drupal_goto('admin/config/user-interface/menu-position');
    return;
  }
  // Check if the user deleted the rule.
  if ($form_state['values']['op'] == $form['delete']['#value']) {
    drupal_goto('admin/config/user-interface/menu-position/delete/' . $form_state['values']['rid']);
    return;
  }
  if ($form_state['values']['node_type'] == '' && empty($form_state['values']['php_condition'])) {
    //form_set_error('', t('Please provide a node type, a taxonomy term or a PHP condition for this rule.'));
    form_set_error('', t('Please provide a node type or a PHP condition for this rule.'));
  }
  // Don't allow the user to select a menu name instead of a menu item.
  list($menu_name, $plid) = explode(':', $form_state['values']['plid']);
  if ($plid == 0) {
    form_set_error('plid', t('Please select a menu item. You have selected the name of a menu.'));
  }
}

/**
 * Handles form submission for menu_position_configure_rule_form().
 */
function menu_position_configure_rule_form_submit($form, &$form_state) {
  $tids = array();
  foreach ($form_state['values'] as $key => $value) {
    if (substr($key, 0, 4) == 'vid_' && $value != 0) {
      $tids[] = $value;
    }
  }
  list($menu_name, $plid) = explode(':', $form_state['values']['plid']);
  $rule = array(
    'admin_title' => $form_state['values']['admin_title'],
    'node_type' => $form_state['values']['node_type'],
    'tid' => isset($tids[0]) ? $tids[0] : NULL,
    'plid' => $plid,
    'weight' => $form_state['values']['weight'],
    'php_condition' => $form_state['values']['php_condition'],
  );
  if ($form_state['values']['rid'] == '') {
    menu_position_add_rule($rule);
    drupal_set_message(t('Rule has been added.'));
  }
  else {
    $rule['rid'] = $form_state['values']['rid'];
    menu_position_edit_rule($rule);
    drupal_set_message(t('Rule has been added.'));
  }

  $form_state['redirect'] = 'admin/config/user-interface/menu-position';
}

/**
 * Adds a menu position rule.
 */
function menu_position_add_rule($rule) {
  $fields = array(
    'admin_title' =>   $rule['admin_title'],
    'plid' =>          $rule['plid'],
    'weight' =>        $rule['weight'],
    'php_condition' => $rule['php_condition'],
  );
  if ($rule['node_type']) {
    $fields['node_type'] = $rule['node_type'];
  }
  if ($rule['tid']) {
    $fields['tid'] = $rule['tid'];
  }
  $rid = db_insert('menu_position_rules')
    ->fields($fields)
    ->execute();

  // Add a menu link to handle matching nodes.
  $item = array(
    'link_path' => 'menu-position/' . $rid,
    'link_title' => 'Menu position rule #' . $rid,
    'mlid' => NULL,
    'plid' => $rule['plid'],
    'hidden' => 1,
    'module' => 'menu_position',
    'options' => array(
      'attributes' => array('class' => array('menu-position-link')),
    ),
  );
  $mlid = menu_link_save($item);

  // Now add the mlid back to the rule.
  db_update('menu_position_rules')
    ->fields(array('mlid' => $mlid))
    ->condition('rid', $rid)
    ->execute();
}

/**
 * Edits a menu position rule.
 */
function menu_position_edit_rule($rule) {
  $fields = array(
    'admin_title' =>   $rule['admin_title'],
    'plid' =>           $rule['plid'],
    'weight' =>        $rule['weight'],
    'php_condition' => $rule['php_condition'],
  );
  if ($rule['node_type']) {
    $fields['node_type'] = $rule['node_type'];
  }
  if ($rule['tid']) {
    $fields['tid'] = $rule['tid'];
  }
  db_update('menu_position_rules')
    ->condition('rid', $rule['rid'])
    ->fields($fields)
    ->execute();
}

/**
 * Menu callback: confirms deletion of rule.
 */
function menu_position_delete_rule_form($form, &$form_state, $rid = 0) {
  // Make sure rid is set.
  if ($rid == 0) {
    drupal_goto('admin/config/user-interface/menu-position');
    return;
  }
  $form['rid'] = array('#type' => 'hidden', '#value' => $rid);

  $title = db_query('SELECT admin_title FROM {menu_position_rules} WHERE rid = :rid', array(':rid' => $rid))->fetchField();

  return confirm_form($form, t('Are you sure you want to delete the %title rule?', array('%title' => $title)), 'admin/config/user-interface/menu-position/edit/' . $rid, NULL, t('Delete'), t('Cancel'));
}

/**
 * Handles form submission for menu_position_delete_rule_form().
 */
function menu_position_delete_rule_form_submit($form, &$form_state) {
  $title = db_query('SELECT admin_title FROM {menu_position_rules} WHERE rid = :rid', array(':rid' => $rid))->fetchField();

  menu_position_delete_rule($form_state['values']['rid']);

  drupal_set_message(t('The %title rule has been deleted.', array('%title' => $title)));
  $form_state['redirect'] = 'admin/config/user-interface/menu-position';
}

/**
 * Deletes a menu position rule.
 */
function menu_position_delete_rule($rid) {
  db_delete('menu_position_rules')
    ->condition('rid', $rid)
    ->execute();
  menu_link_delete(NULL, 'menu-position/' . $rid);
}
