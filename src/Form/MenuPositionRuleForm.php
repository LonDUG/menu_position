<?php

/**
 * @file
 * Contains \Drupal\menu_position\Form\MenuPositionRuleForm.
 */

namespace Drupal\menu_position\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\MenuLinkTree;
use Drupal\Core\Menu\MenuTreeParameters;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MenuPositionRuleForm extends EntityForm {

  /**
   * @param \Drupal\Core\Entity\Query\QueryFactory $entity_query
   *   The entity query.
   */
  public function __construct(QueryFactory $entity_query, MenuLinkTree $menu_tree) {
    $this->entityQuery = $entity_query;
    $this->menu_tree = $menu_tree;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.query'),
      $container->get('menu.link_tree')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $menu_position_rule = $this->entity;

    $menu_tree = \Drupal::menuTree();
    $parameters = new MenuTreeParameters();
    $parameters->onlyEnabledLinks();
    $tree = $menu_tree->load('main', $parameters);

    $manipulators = array(
      array('callable' => 'menu.default_tree_manipulators:checkAccess'),
      array('callable' => 'menu.default_tree_manipulators:generateIndexAndSort'),
      array('callable' => 'toolbar_menu_navigation_links'),
    );
    $tree = $menu_tree->transform($tree, $manipulators);
    $subtrees = array();
    foreach ($tree as $element) {
      /** @var \Drupal\Core\Menu\MenuLinkInterface $link */
      $link = $element->link;
      if ($element->subtree) {
        $subtree = $menu_tree->build($element->subtree);
        $output = drupal_render($subtree);
      }
      else {
        $output = '';
      }
    }

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
    $form['plid'] = array(
      '#type' => 'select',
      '#title' => $this->t('Menu'),
      '#default_value' => $menu_position_rule->getMenuName(),
      '#options' => array(),
      '#description' => $this->t('Things and stuff.'),
    );

    // You will need additional form elements for your custom properties.

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $menu_position_rule = $this->entity;
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
}
