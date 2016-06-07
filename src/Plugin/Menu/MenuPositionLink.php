<?php

namespace Drupal\menu_position\Plugin\Menu;

use Drupal\Core\Menu\MenuLinkBase;

class MenuPositionLink extends MenuLinkBase {

  /**
   * {@inheritdoc}
   */
  protected $overrideAllowed = array();

  public function getTitle() {
    return 'Menu Position Rule';
  }

  public function getDescription() {
    return 'Menu Position Rule';
  }

  public function updateLink(array $new_definition_values, $persist) {
    return $new_definition_values;
  }
}

