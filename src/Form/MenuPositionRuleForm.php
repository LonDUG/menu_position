<?php

/**
 * @file
 * Contains \Drupal\menu_position\Form\MenuPositionRuleForm.
 */

namespace Drupal\menu_position\Form;

use Drupal\Core\Condition\ConditionManager;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\MenuParentFormSelector;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\menu_position\Entity\MenuPositionRule;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MenuPositionRuleForm extends EntityForm {

  /**
   * @param \Drupal\Core\Entity\Query\QueryFactory $entity_query
   *   The entity query.
   */
  public function __construct(
    QueryFactory $entity_query,
    EntityManager $entity_manager,
    MenuParentFormSelector $menu_parent_form_selector,
    ConditionManager $condition_plugin_manager,
    ContextRepositoryInterface $context_repository) {

    $this->entityQuery = $entity_query;
    $this->entity_manager = $entity_manager;
    $this->menu_parent_form_selector = $menu_parent_form_selector;
    $this->condition_plugin_manager = $condition_plugin_manager;
    $this->context_repository = $context_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.query'),
      $container->get('menu.link_tree'),
      $container->get('entity.manager'),
      $container->get('menu.parent_form_selector'),
      $container->get('plugin.manager.condition'),
      $container->get('context.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    // Set these for use when attaching condition forms.
    $form_state->setTemporaryValue('gathered_contexts', $this->context_repository->getAvailableContexts());
    $form['#tree'] = true;

    $menu_position_rule = $this->entity;
    $menu_parent_selector = $this->menu_parent_form_selector;
    $options = $menu_parent_selector->getParentSelectOptions();

    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $menu_position_rule->getLabel(),
      '#description' => $this->t("Label for the Menu Position rule."),
      '#required' => TRUE,
    );
    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $menu_position_rule->getId(),
      '#machine_name' => array(
        'exists' => array($this, 'exist'),
      ),
      '#disabled' => !$menu_position_rule->isNew(),
    );
    $form['parent'] = array(
      '#type' => 'select',
      '#title' => $this->t('Parent menu item'),
      '#required' => TRUE,
      '#default_value' => $menu_position_rule->getMenuName() . ':' . $menu_position_rule->getParent(),
      '#options' => $options,
      '#description' => $this->t('Select the place in the menu where the rule should position its menu links.'),
      '#attributes' => array(
        'class' => array('menu-parent-select'),
      ),
    );

    $form['conditions'] = array(
      'conditions_tabs' => array(
        '#type' => 'vertical_tabs',
        '#title' => t('Conditions'),
        '#description' => t('All the conditions must be met before a rule is applied.'),
        '#parents' => array(
          'conditions_tabs',
        ),
      ),
    );

    foreach ($this->condition_plugin_manager->getDefinitions() as $condition_id => $definition) {
      if ($menu_position_rule->getConditions()->has($condition_id)) {
        $condition = $menu_position_rule->getConditions()->get($condition_id);
      } else {
        $condition = $this->condition_plugin_manager->createInstance($definition['id']);
      }
      $form_state->set(['conditions', $condition_id], $condition);
      $condition_form = $condition->buildConfigurationForm([], $form_state);
      $condition_form['#type'] = 'details';
      $condition_form['#title'] = $condition->getPluginDefinition()['label'];
      $condition_form['#group'] = 'conditions_tabs';
      $form['conditions'][$condition_id] = $condition_form;
    }

    if (isset($form['conditions']['node_type'])) {
      $form['conditions']['node_type']['#title'] = $this->t('Content types');
      $form['conditions']['node_type']['bundles']['#title'] = $this->t('Content types');
      $form['conditions']['node_type']['negate']['#type'] = 'value';
      $form['conditions']['node_type']['negate']['#title_display'] = 'invisible';
      $form['conditions']['node_type']['negate']['#value'] = $form['conditions']['node_type']['negate']['#default_value'];
    }
    if (isset($form['conditions']['user_role'])) {
      $form['conditions']['user_role']['#title'] = $this->t('Roles');
      unset($form['conditions']['user_role']['roles']['#description']);
      $form['conditions']['user_role']['negate']['#type'] = 'value';
      $form['conditions']['user_role']['negate']['#value'] = $form['conditions']['user_role']['negate']['#default_value'];
    }
    if (isset($form['conditions']['request_path'])) {
      $form['conditions']['request_path']['#title'] = $this->t('Pages');
      $form['conditions']['request_path']['negate']['#type'] = 'radios';
      $form['conditions']['request_path']['negate']['#default_value'] = (int) $form['conditions']['request_path']['negate']['#default_value'];
      $form['conditions']['request_path']['negate']['#title_display'] = 'invisible';
      $form['conditions']['request_path']['negate']['#options'] = [
        $this->t('Show for the listed pages'),
        $this->t('Hide for the listed pages'),
      ];
    }
    if (isset($form['conditions']['language'])) {
      $form['conditions']['language']['negate']['#type'] = 'value';
      $form['conditions']['language']['negate']['#value'] = $form['conditions']['language']['negate']['#default_value'];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $menu_position_rule = $this->entity;

    // Set default to enabled when creating a new rule.
    if ($menu_position_rule->isNew()) {
      $menu_position_rule->setEnabled(TRUE);
    }

    // Split the parent value to set menu name and save it on our rule.
    $menu_link = explode(':', $form_state->getValue('parent'));
    $menu_position_rule->setMenuName($menu_link[0]);
    $menu_position_rule->setParent($menu_link[1]);
    $this->menuPositionEditMenuLink($menu_position_rule);

    // Submit visibility condition settings.
    foreach ($form_state->getValue('conditions') as $condition_id => $values) {
      // Allow the condition to submit the form.
      $condition = $form_state->get(['conditions', $condition_id]);
      $condition_values = (new FormState())
        ->setValues($values);
      $condition->submitConfigurationForm($form, $condition_values);
      if ($condition instanceof ContextAwarePluginInterface) {
        $context_mapping = isset($values['context_mapping']) ? $values['context_mapping'] : [];
        $condition->setContextMapping($context_mapping);
      }
      // Update the original form values.
      $condition_configuration = $condition->getConfiguration();
      $form_state->setValue(['conditions', $condition_id], $condition_configuration);
      // Update the visibility conditions on the block.
      $menu_position_rule->getConditions()->addInstanceId($condition_id, $condition_configuration);
    }

    $status = $menu_position_rule->save();

    if ($status) {
      drupal_set_message($this->t('Saved the %label Example.', array(
        '%label' => $menu_position_rule->label(),
      )));
    }
    else {
      drupal_set_message($this->t('The %label Example was not saved.', array(
        '%label' => $menu_position_rule->label(),
      )));
    }

    $form_state->setRedirect('entity.menu_position_rule.order_form');
  }


  public function exist($id) {
    $entity = $this->entityQuery->get('menu_position_rule')
      ->condition('id', $id)
      ->execute();
    return (bool) $entity;
  }

  public function menuPositionEditMenuLink(MenuPositionRule $menu_position_rule) {
    if ($menu_position_rule->isNew()) {
      $menu_link = MenuLinkContent::create();
    } else {
      $storage = $this->entity_manager->getStorage('menu_link_content');
      $menu_link = $storage->load($menu_position_rule->getMenuLinkId());
    }

    $menu_link->set('title', $this->t('@label  (menu position rule)', array('@label' => $menu_position_rule->getLabel())));
    $menu_link->set('link', ['uri' => 'internal:/menu-position/' . $menu_position_rule->getId()]);
    $menu_link->set('menu_name', $menu_position_rule->getMenuName());
    $menu_link->set('parent', $menu_position_rule->getParent());

    // Save the first level
    $menu_link->save();
    $menu_position_rule->setMenuLinkId($menu_link->id());
  }
}
