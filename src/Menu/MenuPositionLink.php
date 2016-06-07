<?php

namespace Drupal\menu_position\Menu;

use Drupal\Core\Menu\MenuLinkBase;

class MenuPositionLink extends MenuLinkBase {

  /**
   * {@inheritdoc}
   */
  protected $overrideAllowed = array(
    'menu_name' => 1,
    'parent' => 1,
    'weight' => 1,
    'expanded' => 1,
    'enabled' => 1,
    'title' => 1,
    'description' => 1,
    'route_name' => 1,
    'route_parameters' => 1,
    'url' => 1,
    'options' => 1,
  );

  public function getTitle() {
    return 'Menu Position Rule';
  }

  public function getDescription() {
    return 'Menu Position Rule';
  }

  /**
   * Updates the definition values for a menu link.
   *
   * Depending on the implementation details of the class, not all definition
   * values may be changed. For example, changes to the title of a static link
   * will be discarded.
   *
   * In general, this method should not be called directly, but will be called
   * automatically from MenuLinkManagerInterface::updateDefinition().
   *
   * @param array $new_definition_values
   *   The new values for the link definition. This will usually be just a
   *   subset of the plugin definition.
   * @param bool $persist
   *   TRUE to have the link persist the changed values to any additional
   *   storage.
   *
   * @return array
   *   The plugin definition incorporating any allowed changes.
   */
  public function updateLink(array $new_definition_values, $persist) {
    // noop
  }
}

