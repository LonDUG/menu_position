<?php

/**
 * @file
 * Contains \Drupal\menu_position\MenuPositionListBuilder.
 */

namespace Drupal\menu_position;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of Example.
 */
class MenuPositionListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Example');
    $header['id'] = $this->t('Machine name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $this->getLabel($entity);
    $row['id'] = $entity->id();

    // You probably want a few more properties here...

    return $row + parent::buildRow($entity);
  }

}
