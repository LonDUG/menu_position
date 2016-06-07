<?php

/**
 * @file
 * Contains \Drupal\menu_position\Form\MenuPositionRuleForm.
 */

namespace Drupal\menu_position\Form;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Condition\ConditionManager;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityManager;
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
    EntityManager $entity_manager,
    MenuParentFormSelector $menu_parent_form_selector,
    MenuLinkManagerInterface $menu_link_manager,
    ConditionManager $condition_plugin_manager,
    ContextRepositoryInterface $context_repository,
    UuidInterface $uuid) {

    $this->entity_manager = $entity_manager;
    $this->menu_parent_form_selector = $menu_parent_form_selector;
    $this->menu_link_manager = $menu_link_manager;
    $this->condition_plugin_manager = $condition_plugin_manager;
    $this->context_repository = $context_repository;
    $this->uuid = $uuid;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('menu.parent_form_selector'),
      $container->get('plugin.manager.menu.link'),
      $container->get('plugin.manager.condition'),
      $container->get('context.repository'),
      $container->get('uuid')
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
    $menu_position_rule = $this->entity;


    // Menu position label.
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $menu_position_rule->getLabel(),
      '#description' => $this->t("Label for the Menu Position rule."),
      '#required' => TRUE,
    );

    // Menu position machine name.
    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $menu_position_rule->getId(),
      '#machine_name' => array(
        'exists' => array($this, 'exist'),
      ),
      '#disabled' => !$menu_position_rule->isNew(),
    );

    // Menu position parent menu tree item.
    $menu_parent_selector = $this->menu_parent_form_selector;
    $options = $menu_parent_selector->getParentSelectOptions();
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
      if ($menu_position_rule->getConditions()->has($condition_id)) {
        $condition = $menu_position_rule->getConditions()->get($condition_id);
      } else {
        $condition = $this->condition_plugin_manager->createInstance($definition['id']);
      }

      // Set conditions in the form state for extraction later.
      $form_state->set(['conditions', $condition_id], $condition);

      // Allow condition plugins to build their own forms.
      $condition_form = $condition->buildConfigurationForm([], $form_state);
      $condition_form['#type'] = 'details';
      $condition_form['#title'] = $condition->getPluginDefinition()['label'];
      $condition_form['#group'] = 'conditions_tabs';
      $form['conditions'][$condition_id] = $condition_form;
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
    // Get menu position rule.
    $menu_position_rule = $this->entity;

    // Set default to enabled when creating a new rule.
    if ($menu_position_rule->isNew()) {
      $menu_position_rule->setEnabled(TRUE);
    }

    // Set the parent value for the menu position rule.
    $menu_link = explode(':', $form_state->getValue('parent'));
    $menu_position_rule->setMenuName(array_shift($menu_link));
    $menu_position_rule->setParent(implode(':', $menu_link));

    // Call helper function to alter menu position rule.
    $this->menuPositionEditMenuLink();

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
      $menu_position_rule->getConditions()->addInstanceId($condition_id, $condition_configuration);
    }

    // Save the menu position rule and get the status for messaging.
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

    // Redirect back to the menu position rule order form.
    $form_state->setRedirect('entity.menu_position_rule.order_form');
  }

  /**
   * Alters the menu link for the menu position rule.
   */
  protected function menuPositionEditMenuLink() {
    $menu_position_rule = $this->entity;
    if ($menu_position_rule->isNew()) {
      $menu_link = $this->menu_link_manager->addDefinition('menu_position_link:' . $this->uuid->generate(), $this->getPluginDefinition());
    } else {
      $menu_link = $this->menu_link_manager->updateDefinition($menu_position_rule->getMenuLink(), $this->getPluginDefinition());
    }

    // Save the new menu link.
    $menu_position_rule->setMenuLink($menu_link->getPluginId());
  }

  protected function getPluginDefinition() {
    $definition = array();
    $definition['class'] = 'Drupal\menu_position\Plugin\Menu\MenuPositionLink';
    $definition['menu_name'] = $this->entity->getMenuName();
    $definition['link'] = ['uri' => 'internal:/menu-position/' . $this->uuid->generate()];
    $definition['url'] = 'base:/menu-position/' . $this->uuid->generate();
    $definition['title'] = $this->t('@label  (menu position rule)', array('@label' => $this->entity->getLabel()));
    $definition['parent'] = $this->entity->getParent();
    $definition['enabled'] = $this->entity->getEnabled();
    $definition['route_name'] = null;
    $definition['provider'] = 'menu_position';

    return $definition;
  }
}
