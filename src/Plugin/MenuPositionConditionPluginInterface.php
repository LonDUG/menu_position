<?php

/**
 * @file
 * Contains \Drupal\menu_position\Plugin\MenuPositionConditionPluginInterface.
 */

namespace Drupal\menu_position\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines an interface for Menu position condition plugin plugins.
 */
interface MenuPositionConditionPluginInterface extends PluginInspectionInterface {

  /**
   * Returns the vertical tab from the condition
   * @return array
   *    The form element
   */
  public function getConditionForm(&$form, FormStateInterface $form_state);

  /**
   * Submit logic for each condition
   */
  public function conditionFormSubmit(&$form, FormStateInterface $form_state);

  /**
   * Whether or not to display the menu
   * @return boolean
   */
  public function evaluateCondition($variables);

}
