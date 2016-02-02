<?php

/**
 * @file
 * Contains \Drupal\Core\Controller\ControllerBase.
 */
namespace Drupal\menu_position\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;

class MenuPositionRouter extends ControllerBase {
  public function router() {
    return $this->redirect('<front>');

    // return new \Symfony\Component\HttpFoundation\RedirectResponse(\Drupal::url('<front>'));
    // return new RedirectResponse(\Drupal::url('<front>'));
  }
}
