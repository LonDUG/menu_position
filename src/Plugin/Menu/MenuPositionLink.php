<?php

namespace Drupal\menu_position\Plugin\Menu;

use Drupal\Core\Menu\MenuLinkBase;

class MenuPositionLink extends MenuLinkBase {

  /**
   * {@inheritdoc}
   */
  protected $overrideAllowed = array();

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->getPluginDefinition()['title'];
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


  /**
   * {@inheritdoc}
   */
  public function deleteLink() {
    // noop
  }
}

