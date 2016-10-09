<?php

/**
 * @file
 * Contains \Drupal\menu_position\Form\MenuPositionRuleDeleteForm.
 */

namespace Drupal\menu_position\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\Core\Routing\RouteBuilder;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Builds the form to delete a Example.
 */

class MenuPositionRuleDeleteForm extends EntityConfirmFormBase {

  /**
   * @param \Drupal\Core\Entity\Query\QueryFactory $entity_query
   *   The entity query.
   */
  public function __construct(
    MenuLinkManagerInterface $menu_link_manager,
    RouteBuilder $route_builder) {

    $this->menu_link_manager = $menu_link_manager;
    $this->route_builder = $route_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.menu.link'),
      $container->get('router.builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the %name rule?', array('%name' => $this->entity->label()));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.menu_position_rule.order_form');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->menu_link_manager->removeDefinition($this->entity->getMenuLink());
    $this->entity->delete();
    drupal_set_message($this->t('The %label rule has been deleted.', array('%label' => $this->entity->getLabel())));

    // Flush appropriate menu cache
    $this->route_builder->rebuild();

    $form_state->setRedirectUrl($this->getCancelUrl());
  }
}
