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
  $items['admin/structure/menu-position'] = array(
    'title' => 'Menu position rules',
    'description' => 'Configure rules for menu positions.',
    'access arguments' => array('administer menu positions'),
    'page callback' => 'drupal_get_form',
    'page arguments' => array('menu_position_rules_form'),
    'type' => MENU_NORMAL_ITEM,
    'file' => 'menu_position.admin.inc',
  );
  $items['admin/structure/menu-position/add'] = array(
    'title' => 'Add menu position rule',
    'description' => 'Add a new menu position rule.',
    'access arguments' => array('administer menu positions'),
    'page callback' => 'drupal_get_form',
    'page arguments' => array('menu_position_add_rule_form'),
    'type' => MENU_LOCAL_ACTION,
    'file' => 'menu_position.admin.inc',
  );
  $items['admin/structure/menu-position/edit'] = array(
    'title' => 'Edit menu position rule',
    'description' => 'Edit a menu position rule.',
    'access arguments' => array('administer menu positions'),
    'page callback' => 'drupal_get_form',
    'page arguments' => array('menu_position_edit_rule_form'),
    'type' => MENU_CALLBACK,
    'file' => 'menu_position.admin.inc',
  );
  $items['admin/structure/menu-position/delete'] = array(
    'title' => 'Delete menu position rule',
    'description' => 'Delete a menu position rule.',
    'access arguments' => array('administer menu positions'),
    'page callback' => 'drupal_get_form',
    'page arguments' => array('menu_position_delete_rule_form'),
    'type' => MENU_CALLBACK,
    'file' => 'menu_position.admin.inc',
  );
  $items['menu-position'] = array(
    'title' => 'Menu position router',
    'description' => 'Sets access to all menu position links.',
    'access arguments' => array('access content'),
    'page callback' => 'menu_position_router',
    'type' => MENU_CALLBACK,
    'file' => 'menu_position.admin.inc',
  );
  return $items;
}

/**
 * Routes menu_position links to homepage; normally overridden.
 */
function menu_position_router() {
  drupal_goto('<front>');
}

/**
 * Implements hook_form_FORM_ID_alter().
 */
function _menu_position_form_menu_overview_form_alter(&$form, &$form_state) {
  // Retrieve all of the rules' mlids.
  $rules = db_query('SELECT mlid FROM {menu_position_rules} WHERE enabled = :enabled ORDER BY weight, rid', array(':enabled' => 1));
  foreach ($rules as $rule) {
    $mlid = $rule->mlid;
    if (!empty($form['mlid:' . $mlid]['#item']['mlid']) && $mlid == $form['mlid:' . $mlid]['#item']['mlid']) {
      // Ensure that the menu item cannot be enabled.
      $form['mlid:' . $mlid]['hidden']['#disabled'] = TRUE;
      // Remove the edit link for this menu item.
      unset($form['mlid:' . $mlid]['operations']['edit']);
    }
  }
}

/**
 * Implements hook_form_FORM_ID_alter().
 */
function _menu_position_form_menu_edit_item_alter(&$form, &$form_state) {
  // Retrieve all of the rules' mlids.
  $result = db_query('SELECT mlid FROM {menu_position_rules} WHERE mlid = :mlid ORDER BY weight, rid', array(':mlid' => (int) $form['mlid']['#value']));
  if ($result->rowCount()) {
    // If the form hasn't been submitted, display a warning.
    if (empty($form_state['input'])) {
      drupal_set_message(t('This menu item cannot be edited.'), 'warning');
    }
    // Disable all the normal form elements.
    foreach (array('link_title', 'description', 'enabled', 'expanded', 'parent', 'weight') as $key) {
      $form[$key]['#disabled'] = TRUE;
    }
  }
  // Remove the validator.
  $key = array_search('menu_edit_item_validate', $form['#validate']);
  if ($key !== FALSE) {
    unset($form['#validate'][$key]);
  }
  // Replace the standard submit handler with our own.
  $key = array_search('menu_edit_item_submit', $form['#submit']);
  if ($key !== FALSE) {
    $form['#submit'][$key] = 'menu_position_edit_item_submit';
  }
  // Replace the Save button with a Cancel button.
  unset($form['actions']['submit']);
  $form['actions']['cancel'] = array(
    '#type' => 'submit',
    '#value' => t('Cancel'),
  );
}

/**
 * Process menu and menu item add/edit form submissions for menu_position links.
 */
function menu_position_edit_item_submit($form, &$form_state) {
  // Redirect to the menu edit form and display a message.
  list($menu_name, ) = explode(':', $form['parent']['#default_value']);
  $form_state['redirect'] = 'admin/structure/menu/manage/' . $menu_name;
  drupal_set_message(t('Your configuration was not saved.'), 'error');
}

/**
 * Menu callback: orders rules.
 */
function menu_position_rules_form($form, &$form_state) {
  $form['#attached']['css'] = array(drupal_get_path('module', 'menu') . '/menu.css');

  $rules = db_query('SELECT rid, admin_title, enabled, weight FROM {menu_position_rules} ORDER BY weight, rid');
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
            '#href' => 'admin/structure/menu-position/edit/' . $rule->rid,
          ),
          'delete-link' => array(
            '#type' => 'link',
            '#title' => t('delete'),
            '#href' => 'admin/structure/menu-position/delete/' . $rule->rid,
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
        'enabled' => $rule['enabled'],
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
  return menu_position_rule_form($form, $form_state);
}

/**
 * Menu callback; Edits rules.
 */
function menu_position_edit_rule_form($form, &$form_state, $rid = 0) {
  // Make sure rid is set.
  if ($rid == 0) {
    drupal_goto('admin/structure/menu-position');
    return;
  }
  // Grab the rule from the database.
  $form_state['#menu-position-rule'] = db_query('SELECT * FROM {menu_position_rules} WHERE rid = :rid', array(':rid' => $rid))->fetchAssoc();
  $form_state['#menu-position-rule']['conditions'] = unserialize($form_state['#menu-position-rule']['conditions']);
  return menu_position_rule_form($form, $form_state);
}

/**
 * Returns form to add or edit a menu position rule.
 */
function menu_position_rule_form($form, &$form_state) {
  // Set the default values.
  $rid           = !empty($form_state['#menu-position-rule']['rid'])         ? $form_state['#menu-position-rule']['rid'] : '';
  $admin_title   = !empty($form_state['#menu-position-rule']['admin_title']) ? $form_state['#menu-position-rule']['admin_title'] : '';
  $plid          = !empty($form_state['#menu-position-rule']['plid'])        ? $form_state['#menu-position-rule']['plid'] : NULL;

  $form['rid'] = array(
    '#type' => 'hidden',
    '#value' => $rid,
  );
  $form['admin_title'] = array(
    '#type' => 'textfield',
    '#default_value' => $admin_title,
    '#title' => t('Administrative title'),
    '#description' => t('This title will be used administratively to identify this rule.'),
    '#required' => TRUE,
  );
  // Place holder for all condition plug-ins.
  $form['conditions'] = array();

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

  // Add conditions.
  foreach (menu_position_get_plugins() as $plugin) {
    // Load the required include file.
    if (!empty($plugin['file'])) {
      $file = pathinfo($plugin['file']);
      module_load_include($file['extension'], $plugin['module'], $file['filename']);
    }
    // Call form callback to add additional form elements.
    $function = $plugin['form_callback'];
    if (function_exists($function)) {
      $function($form, $form_state);
    }
  }

  // Form validation and submission.
  $form['#validate'][] = 'menu_position_rule_form_validate';
  $form['#submit'][] = 'menu_position_rule_form_submit';

  return $form;
}

/**
 * Validates the form for menu_position_rule_form().
 */
function menu_position_rule_form_validate($form, &$form_state) {
  // Check if the user cancelled the form.
  if (!empty($form['cancel']) && $form_state['values']['op'] == $form['cancel']['#value']) {
    drupal_goto('admin/structure/menu-position');
    return;
  }
  // Check if the user deleted the rule.
  if (!empty($form['delete']) && $form_state['values']['op'] == $form['delete']['#value']) {
    drupal_goto('admin/structure/menu-position/delete/' . $form_state['values']['rid']);
    return;
  }
  // Don't allow the user to select a menu name instead of a menu item.
  list($menu_name, $plid) = explode(':', $form_state['values']['plid']);
  if ($plid == 0) {
    form_set_error('plid', t('Please select a menu item. You have selected the name of a menu.'));
  }
}

/**
 * Handles form submission for menu_position_rule_form().
 */
function menu_position_rule_form_submit($form, &$form_state) {
  list($menu_name, $plid) = explode(':', $form_state['values']['plid']);
  $rule = array(
    'admin_title' => $form_state['values']['admin_title'],
    'conditions'  => serialize($form_state['values']['conditions']),
    'plid'        => $plid,
  );
  // Add the rule to the database.
  if ($form_state['values']['rid'] == '') {
    menu_position_add_rule($rule);
    drupal_set_message(t('Rule has been added.'));
  }
  // Update an exisiting rule.
  else {
    $rule['rid'] = $form_state['values']['rid'];
    menu_position_edit_rule($rule);
    drupal_set_message(t('Rule has been added.'));
  }

  $form_state['redirect'] = 'admin/structure/menu-position';
}

/**
 * Adds a menu position rule.
 */
function menu_position_add_rule($rule) {
  $fields = array(
    'admin_title' => $rule['admin_title'],
    'conditions' =>  $rule['conditions'],
    'plid' =>        $rule['plid'],
  );
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
      'alter' => TRUE,
      'attributes' => array('class' => array('menu-position-link')),
    ),
  );
  $mlid = menu_position_add_menu_link($rid, $rule['plid'], $rule['admin_title']);

  // Now add the mlid back to the rule.
  db_update('menu_position_rules')
    ->fields(array('mlid' => $mlid))
    ->condition('rid', $rid)
    ->execute();
}

/**
 * Adds a menu position rule.
 *
 * @param $rid
 *   ID of the rule needing a menu link.
 * @param $plid
 *   The mlid of the parent menu link specified in the rule.
 * @param $admin_title
 *   The administrative title of the rule.
 * @return
 *   The mlid of the rule's new menu link.
 */
function menu_position_add_menu_link($rid, $plid, $admin_title = '') {
  // Add a menu link to handle matching nodes.
  $item = array(
    'link_path' => 'menu-position/' . $rid,
    'link_title' => $admin_title ? 'Menu position rule (' . $admin_title . ')' : 'Menu position rule #' . $rid,
    'mlid' => NULL,
    'plid' => $plid,
    'hidden' => 1,
    'module' => 'menu_position',
    'options' => array(
      'alter' => TRUE,
      'attributes' => array('class' => array('menu-position-link')),
    ),
  );
  return menu_link_save($item);
}

/**
 * Edits a menu position rule.
 */
function menu_position_edit_rule($rule) {
  $fields = array(
    'admin_title' => $rule['admin_title'],
    'conditions' =>  $rule['conditions'],
    'plid' =>        $rule['plid'],
  );
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
    drupal_goto('admin/structure/menu-position');
    return;
  }
  $form['rid'] = array('#type' => 'hidden', '#value' => $rid);

  $title = db_query('SELECT admin_title FROM {menu_position_rules} WHERE rid = :rid', array(':rid' => $rid))->fetchField();

  return confirm_form($form, t('Are you sure you want to delete the %title rule?', array('%title' => $title)), 'admin/structure/menu-position/edit/' . $rid, NULL, t('Delete'), t('Cancel'));
}

/**
 * Handles form submission for menu_position_delete_rule_form().
 */
function menu_position_delete_rule_form_submit($form, &$form_state) {
  $title = db_query('SELECT admin_title FROM {menu_position_rules} WHERE rid = :rid', array(':rid' => $form_state['values']['rid']))->fetchField();

  menu_position_delete_rule($form_state['values']['rid']);

  drupal_set_message(t('The %title rule has been deleted.', array('%title' => $title)));
  $form_state['redirect'] = 'admin/structure/menu-position';
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
