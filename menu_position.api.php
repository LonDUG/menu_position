<?php
/**
 * @file
 * Hooks provided by the Menu Position module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Registers rule plugins with menu_position module.
 *
 * Modules implementing menu position rule plugins should return an associative
 * array of data. Each key in the array should be the name of plugin implemented
 * by the module. Each key's value should be an associative array with the
 * following optional data:
 * - form_callback: (optional) The name of the function to use to add a
 *   configurable form elements to the rule definition form. Defaults to
 *   "MODULE_menu_position_rule_PLUGIN_form".
 * - condition_callback: (optional) The name of the function to use when the
 *   rule is being tested. Defaults to "MODULE_menu_position_condition_PLUGIN".
 * - file: (optional) Include file containing form and condition callback
 *   function definitions. The hook_menu_position_rule_plugins() implementation
 *   must be in a .module file, but the include file will only be loaded if a
 *   rule is configured to use the plugin's condition.
 *
 * @return
 *   An associative array containing the information about each plugin.
 */
function hook_menu_position_rule_plugins() {
  return array(
    // Register the "foo" plugin.
    'foo' => array(
      'form_callback' => 'my_module_menu_position_rule_foo_form',
      'condition_callback' => 'my_module_menu_position_condition_foo',
      'file' => 'my_module.foo.inc',
    ),
    // Register the "bar" plugin.
    'bar' => array(), // Use the defaults for all options.
  );
}

/**
 * @} End of "addtogroup hooks".
 */
