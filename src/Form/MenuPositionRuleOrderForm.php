<?php

/**
 * @file
 * Contains Drupal\menu_position\Form\MenuPositionRuleOrderForm.
 */

namespace Drupal\menu_position\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MenuPositionRuleOrderForm.
 *
 * @package Drupal\menu_position\Form
 */
class MenuPositionRuleOrderForm extends FormBase {

  public function __construct(
    QueryFactory $entity_query,
    MenuLinkManagerInterface $menu_link_manager,
    EntityManager $entity_manager) {

    $this->entity_query = $entity_query;
    $this->menu_link_manager = $menu_link_manager;
    $this->entity_manager = $entity_manager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.query'),
      $container->get('plugin.manager.menu.link'),
      $container->get('entity.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'menu_position_rule_order_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('menu_position.menupositionruleorder_config');

    // Get all the rules.
    $query = $this->entity_query->get('menu_position_rule');
    $results = $query->sort('weight')->execute();
    $rules = $this->entity_manager->getStorage('menu_position_rule')->loadMultiple($results);

    // Menu Position rules order (tabledrag).
    $form['#tree'] = TRUE;
    $form['rules'] = array(
      '#type' => 'table',
      '#empty' => $this->t('No rules have been created yet.'),
      '#title' => $this->t('Rules processing order'),
      '#header' => array(
        $this->t('Rule'),
        $this->t('Affected Menu'),
        $this->t('Enabled'),
        $this->t('Weight'),
        $this->t('Operations'),
      ),
      '#tabledrag' => array(
        array(
         'action' => 'order',
         'relationship' => 'sibling',
         'group' => 'rules-weight',
        ),
      ),
    );

    // Display table of rules.
    foreach ($rules as $rule) {
      $menu_link = $rule->getMenuLinkPlugin();
      $parent = $this->menu_link_manager->createInstance($menu_link->getParent());
      $form['rules'][$rule->getId()] = array(
        '#attributes' => array('class' => array('draggable')),
        'title' => array(
          '#markup' => '<strong>' . $rule->getLabel() . '</strong> (' . $this->t('Positioned under: %title', array('%title' => $parent->getTitle())) . ')',
        ),
        'menu_name' => array(
          '#markup' => $menu_link->getMenuName(),
        ),
        'enabled' => array(
          '#type' => 'checkbox',
          '#default_value' => $rule->getEnabled(),
        ),
        'weight' => array(
          '#type' => 'weight',
          '#title' => $this->t('Weight for @title', array('@title' => $rule->getLabel())),
          '#title_display' => 'invisible',
          '#default_value' => $rule->getWeight(),
          '#delta' => max($rule->getWeight(), 5),
          '#attributes' => array('class' => array('rules-weight')),
        ),
        'operations' => array(
          '#type' => 'dropbutton',
          '#links' => array(
            'edit' => array(
              'title' => $this->t('Edit'),
              'url' => Url::fromRoute('entity.menu_position_rule.edit_form', array('menu_position_rule' => $rule->getId())),
            ),
            'delete' => array(
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.menu_position_rule.delete_form', array('menu_position_rule' => $rule->getId())),
            ),
          ),
        ),
      );
    }

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save configuration'),
      '#button_type' => 'primary',
    );

    // By default, render the form using theme_system_config_form().
    $form['#theme'] = 'system_config_form';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $storage = $this->entity_manager->getStorage('menu_position_rule');
    $values = $form_state->getValue('rules');
    $rules = $storage->loadMultiple(array_keys($values));

    foreach ($rules as $rule) {
      $value = $values[$rule->getId()];
      $rule->setEnabled((bool) $value['enabled']);
      $rule->setWeight((float) $value['weight']);
      $storage->save($rule);
    }

    drupal_set_message($this->t('The new rules ordering has been applied.'));
  }
}
