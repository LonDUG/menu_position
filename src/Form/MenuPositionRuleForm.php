<?php

/**
 * @file
 * Contains \Drupal\menu_position\Form\MenuPositionRuleForm.
 */

namespace Drupal\menu_position\Form;

use Drupal\Core\Condition\ConditionManager;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\MenuLinkManagerInterface;
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
    MenuLinkManagerInterface $menu_link_manager,
    ConditionManager $condition_plugin_manager,
    ContextRepositoryInterface $context_repository) {

    $this->entity_query = $entity_query;
    $this->entity_manager = $entity_manager;
    $this->menu_parent_form_selector = $menu_parent_form_selector;
    $this->menu_link_manager = $menu_link_manager;
    $this->condition_plugin_manager = $condition_plugin_manager;
    $this->context_repository = $context_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.query'),
      $container->get('entity.manager'),
      $container->get('menu.parent_form_selector'),
      $container->get('plugin.manager.menu.link'),
      $container->get('plugin.manager.condition'),
      $container->get('context.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    // Allow parent to construct base form, set tree value.
    $form = parent::form($form, $form_state);
    $form['#tree'] = true;

    // Set these for use when attaching condition forms.
    $form_state->setTemporaryValue('gathered_contexts', $this->context_repository->getAvailableContexts());

    // Get the menu position rule entity.
    $rule = $this->entity;

    // Get the menu link for this rule.
    $menu_link = $rule->getMenuLinkPlugin();

    // Menu position label.
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $rule->getLabel(),
      '#description' => $this->t("Label for the Menu Position rule."),
      '#required' => TRUE,
    );

    // Menu position machine name.
    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $rule->getId(),
      '#machine_name' => array(
        'exists' => array($this, 'exist'),
      ),
      '#disabled' => !$rule->isNew(),
    );

    // Menu position parent menu tree item.
    $options = $this->menu_parent_form_selector->getParentSelectOptions();
    $form['parent'] = array(
      '#type' => 'select',
      '#title' => $this->t('Parent menu item'),
      '#required' => TRUE,
      '#default_value' => (!$rule->isNew()) ? $menu_link->getMenuName() . ':' . $menu_link->getParent() : null,
      '#options' => $options,
      '#description' => $this->t('Select the place in the menu where the rule should position its menu links.'),
      '#attributes' => array(
        'class' => array('menu-parent-select'),
      ),
    );

    // Menu position conditions vertical tabs.
    $form['conditions'] = array(
      'conditions_tabs' => array(
        '#type' => 'vertical_tabs',
        '#title' => $this->t('Conditions'),
        '#description' => $this->t('All the conditions must be met before a rule is applied.'),
        '#parents' => array(
          'conditions_tabs',
        ),
      ),
    );

    // Get all available plugins from the plugin manager.
    foreach ($this->condition_plugin_manager->getDefinitions() as $condition_id => $definition) {

      // If this condition exists already on the rule, use that.
      if ($rule->getConditions()->has($condition_id)) {
        $condition = $rule->getConditions()->get($condition_id);
      } else {
        $condition = $this->condition_plugin_manager->createInstance($definition['id']);
      }

      // Set conditions in the form state for extraction later.
      $form_state->set(['conditions', $condition_id], $condition);

      // Allow condition plugins to build their own forms.
      $condition_form = $condition->buildConfigurationForm([], $form_state);

      // Exclude theme condition plugin.
      if (!isset($condition_form['theme'])) {
        $condition_form['#type'] = 'details';
        $condition_form['#title'] = $condition->getPluginDefinition()['label'];
        $condition_form['#group'] = 'conditions_tabs';
        $form['conditions'][$condition_id] = $condition_form;
      }
    }

    // Custom form alters for core conditions (lifted from BlockForm.php).
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
    if (isset($form['conditions']['current_theme'])) {
      $form['conditions']['current_theme']['theme']['#empty_value'] = '';
      $form['conditions']['current_theme']['theme']['#empty_option'] = $this->t('- Any -');
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
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Don't allow the user to select a menu name instead of a menu item.
    list($menu_name, $parent) = explode(':', $form_state->getValue('parent'));
    if (empty($parent)) {
      $form_state->setErrorByName('parent', $this->t('Please select a menu item. You have selected the name of a menu.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    // Get menu position rule.
    $rule = $this->entity;
    $is_new = $rule->isNew();

    // Break apart parent selector for menu link creation.
    $link_parts = explode(':', $form_state->getValue('parent'));
    $menu_name = array_shift($link_parts);
    $parent= implode(':', $link_parts);

    // This is a new menu position rule.
    if ($is_new) {
      // Set to enabled by default.
      $rule->setEnabled(TRUE);

      // Add new menu link plugin definition for this menu position rule.
      $menu_link = $this->menu_link_manager->addDefinition('menu_position_link:' . $rule->getId(), $this->getPluginDefinition($menu_name, $parent));
      $rule->setMenuLink($menu_link->getPluginId());
    } else {

      // Update existing menu link definition.
      $menu_link = $this->menu_link_manager->updateDefinition('menu_position_link:' . $rule->getId(), $this->getPluginDefinition($menu_name, $parent));
    }

    // Submit visibility condition settings.
    foreach ($form_state->getValue('conditions') as $condition_id => $values) {
      // Allow the condition to submit the form.
      $condition = $form_state->get(['conditions', $condition_id]);
      $condition_values = (new FormState())
        ->setValues($values);
      $condition->submitConfigurationForm($form, $condition_values);

      // Set context mapping values.
      if ($condition instanceof ContextAwarePluginInterface) {
        $context_mapping = isset($values['context_mapping']) ? $values['context_mapping'] : [];
        $condition->setContextMapping($context_mapping);
      }

      // Update the original form values.
      $condition_configuration = $condition->getConfiguration();
      $form_state->setValue(['conditions', $condition_id], $condition_configuration);

      // Update the conditions on the menu position rule.
      $rule->getConditions()->addInstanceId($condition_id, $condition_configuration);
    }

    // Save the menu position rule and get the status for messaging.
    $status = $rule->save();
    if ($status && $is_new) {
      drupal_set_message($this->t('Rule %label has been added.', ['%label' => $rule->getLabel()]));
    } else if ($status) {
      drupal_set_message($this->t('Rule %label has been updated.', ['%label' => $rule->getLabel()]));
    } else {
      drupal_set_message($this->t('Rule %label was not saved.', ['%label' => $rule->getLabel()]), 'warning');
    }

    // Redirect back to the menu position rule order form.
    $form_state->setRedirect('entity.menu_position_rule.order_form');
  }

  /**
   * Helper function that returns a plugin definition array for a menu position
   * link.
   *
   * @return array MenuPositionLink plugin definition array.
   */
  protected function getPluginDefinition($menu_name, $parent) {
    $rule = $this->entity;

    $definition = array();
    $definition['menu_name'] = $menu_name;
    $definition['parent'] = $parent;
    $definition['title'] = $this->t('@label  (menu position rule)', array('@label' => $rule->getLabel()));
    $definition['url'] = 'base:/menu-position/' . $rule->getId();
    $definition['route_name'] = null;
    $definition['route_parameters'] = [];
    $definition['enabled'] = false;
    $definition['metadata'] = array('entity_id' => $rule->getId());
    $definition['provider'] = 'menu_position';
    $definition['class'] = 'Drupal\menu_position\Plugin\Menu\MenuPositionLink';

    return $definition;
  }

  /**
   * Returns boolean indicating whether or not this entity exists.
   *
   * @param  string $id The id of the entity.
   * @return bool       Whether or not the entity exists already.
   */
  public function exist($id) {
    $entity = $this->entity_query->get('menu_position_rule')
      ->condition('id', $id)
      ->execute();
    return (bool) $entity;
  }
}
