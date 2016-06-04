<?php

/**
 * @file
 * Contains \Drupal\menu_position\Form\MenuPositionRuleForm.
 */

namespace Drupal\menu_position\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\MenuLinkTree;
use Drupal\Core\Menu\MenuParentFormSelector;
use Drupal\Core\Menu\MenuTreeParameters;
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
    MenuLinkTree $menu_tree,
    EntityManager $entity_manager,
    MenuParentFormSelector $menu_parent_form_selector) {

    $this->entityQuery = $entity_query;
    $this->menu_tree = $menu_tree;
    $this->entity_manager = $entity_manager;
    $this->menu_parent_form_selector = $menu_parent_form_selector;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.query'),
      $container->get('menu.link_tree'),
      $container->get('entity.manager'),
      $container->get('menu.parent_form_selector')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

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

    // Place holder for all condition plug-ins.
    $form['conditions_title'] = array(
      '#type' => 'item',
      '#title' => t('Conditions'),
      '#description' => t('All the conditions must be met before a rule is applied.'),
    );

    $form['conditions'] = array(
      '#type' => 'vertical_tabs',
      '#default_tab' => 'edit-publication',
    );

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
