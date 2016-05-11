<?php

/**
 * @file
 * Contains Drupal\menu_position\Form\MenuPositionRuleOrderForm.
 */

namespace Drupal\menu_position\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MenuPositionRuleOrderForm.
 *
 * @package Drupal\menu_position\Form
 */
class MenuPositionRuleOrderForm extends ConfigFormBase {

  /**
   * Drupal\Core\Entity\Query\QueryFactory definition.
   *
   * @var Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entity_query;
  protected $entity_repository;

  public function __construct(
    ConfigFactoryInterface $config_factory,
    QueryFactory $entity_query,
    EntityRepository $entity_repository
  ) {
    parent::__construct($config_factory);
    $this->entity_query = $entity_query;
    $this->entity_repository = $entity_repository;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity.query'),
      $container->get('entity.repository')
    );
  }


  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'menu_position.menupositionruleorder_config'
    ];
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
    $query = $this->entity_query->get('menu_position_rule');
    $results = $query->execute();

    $form['#tree'] = TRUE;
    // Menu Position rules order (tabledrag).
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
      '#tree' => FALSE,
      '#input' => FALSE,
      '#theme_wrappers' => array('form_element'),
    );

    // Display table of rules.
    foreach ($results as $result) {
      $rule = $this->entity_repository->loadEntityByConfigTarget('menu_position_rule', $result);
      $menu_link['title'] = $rule->getPlid();
      if ($menu_link === FALSE) {
        $menu_link = array('title' => '[' . $this->t('deleted menu item') . ']');
      }
      $form['rules'][$rule->getId()] = array(
        '#attributes' => array('class' => array('draggable')),
        'title' => array(
          '#markup' => '<strong>' . $rule->getLabel() . '</strong> (' . $this->t('Positioned under: %title', array('%title' => $rule->getPlid())) . ')',
        ),
        'menu_name' => array(
          '#markup' => $rule->getMenuName(),
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
          '#delta' => max($delta, 5),
          '#id' => 'edit-rule-' . $rule->getId(),
          '#parents' => array('menu_position', $rule->getId(), 'weight'),
          '#attributes' => array('class' => array('menu_position-rules-weight')),
        ),
        'operations' => array(
          '#type' => 'dropbutton',
          '#links' => array(
            'edit' => array(
              'title' => $this->t('Edit'),
              'url' => Url::fromRoute('entity.menu_position_rule.edit_form', array('menu_position_rule' => $rule->getId())),
            ),
            'delete' => array(
              'title' => $this->t('delete'),
              'url' => Url::fromRoute('entity.menu_position_rule.delete_form', array('menu_position_rule' => $rule->getId())),
            ),
          ),
        ),
      );
    }

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
    parent::submitForm($form, $form_state);

    $this->config('menu_position.menupositionruleorder_config')
      ->save();
  }

}
