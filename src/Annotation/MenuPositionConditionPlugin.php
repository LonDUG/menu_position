<?php

/**
 * @file
 * Contains \Drupal\menu_position\Annotation\MenuPositionConditionPlugin.
 */

namespace Drupal\menu_position\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Menu position condition plugin item annotation object.
 *
 * @see \Drupal\menu_position\Plugin\MenuPositionConditionPluginManager
 * @see plugin_api
 *
 * @Annotation
 */
class MenuPositionConditionPlugin extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

}
