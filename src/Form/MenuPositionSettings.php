<?php

/**
 * @file
 * Contains Drupal\menu_position\Form\MenuPositionSettings.
 */

namespace Drupal\menu_position\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class MenuPositionSettings.
 *
 * @package Drupal\menu_position\Form
 */
class MenuPositionSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'menu_position.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'menu_position_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('menu_position.settings');

    $form = array();
    $form['menu_position_active_link_display'] = array(
      '#type' => 'radios',
      '#title' => t('When a menu position rule matches:'),
      '#options' => array(
        'child' => t("Insert the current page's title into the menu tree."),
        'parent' => t('Mark the rule\'s parent menu item as being "active".'),
        'none' => t('Don\'t mark any menu item as being "active".'),
      ),
      '#default_value' => $config->get('link_display'),
      '#description' => t("By default, a matching menu position rule will insert the current page's title into the menu tree just below the rule's parent menu item."),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('menu_position.settings')
      ->set('link_display', $form_state->getValue('menu_position_active_link_display'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
