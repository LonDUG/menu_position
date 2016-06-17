<?php

namespace Drupal\menu_position\Plugin\Menu;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Menu\MenuLinkBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MenuPositionLink extends MenuLinkBase implements ContainerFactoryPluginInterface {

  /**
   * Constructs a Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityManagerInterface $entity_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entity_manager = $entity_manager;
    $this->settings = \Drupal::config('menu_position.settings');

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected $overrideAllowed = array();

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    // When we're in an admin route we want to display the name of the menu
    // position rule.
    // @todo Ensure this translates properly when using configuration
    //   translation.
    if (\Drupal::service('router.admin_context')->isAdminRoute()) {
      return $this->pluginDefinition['title'];
    }
    // When we're on a non-admin route we want to display the page title.
    else {
      $request = \Drupal::request();
      $route_match = \Drupal::routeMatch();
      $title = \Drupal::service('title_resolver')->getTitle($request, $route_match->getRouteObject());
      return $title;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->getPluginDefinition()['description'];
  }

  /**
   * {@inheritdoc}
   */
  public function updateLink(array $new_definition_values, $persist) {
    return $new_definition_values;
  }

  /**
   * {@inheritdoc}
   */
  public function isDeletable() {
    return TRUE;
  }

  public function isEnabled() {
    return (bool) ($this->settings->get('link_display') === 'child');
  }

  /**
   * {@inheritdoc}
   */
  public function deleteLink() {
    // noop
  }

  /**
   * {@inheritdoc}
   */
  public function getEditRoute() {
    $storage = $this->entity_manager->getStorage('menu_position_rule');
    $entity_id = $this->pluginDefinition['metadata']['entity_id'];
    $entity = $storage->load($entity_id);
    return $entity->urlInfo();
  }
}

