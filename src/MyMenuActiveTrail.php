<?php

namespace Drupal\menu_position;

use Drupal\Core\Menu\MenuActiveTrail;

class MyMenuActiveTrail extends MenuActiveTrail  {

  /**
   * {@inheritdoc}
   */
  public function getActiveLink($menu_name = NULL) {
    parent::getActiveLink();
  }

}