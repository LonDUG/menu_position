<?php

/**
 * @file
 * Contains Drupal\menu_position\Form\MenuPositionSettings.
 */

namespace Drupal\menu_position\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Composer\Autoload\ClassLoader;
use Symfony\Component\HttpFoundation\Request;

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
        'parent' => t('Mark the rule\'s parent menu item as being "active".'),
        'child' => t("Insert the current page's title into the menu tree."),
        'none' => t('Don\'t mark any menu item as being "active".'),
      ),
      '#default_value' => $config->get('link_display'),
      '#description' => t("By default, a matching menu position rule will mark the rule's parent menu item as active."),
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
    drupal_flush_all_caches();
  }

}
