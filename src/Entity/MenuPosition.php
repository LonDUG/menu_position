<?php
/**
 * @file
 * Contains \Drupal\example\Entity\Example.
 */

namespace Drupal\menu_position\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\menu_position\MenuPositionInterface;

/**
 * Defines the MenuPosition entity.
 *
 * @ConfigEntityType(
 *   id = "menu_position",
 *   label = @Translation("Menu Position"),
 *   handlers = {
 *     "list_builder" = "Drupal\menu_postion\Controller\MenuPositionListBuilder",
 *     "form" = {
 *       "add" = "Drupal\menu_position\Form\MenuPositionForm",
 *       "edit" = "Drupal\menu_position\Form\MenuPositionEditForm",
 *       "delete" = "Drupal\menu_position\Form\MenuPositionDeleteForm"
 *     }
 *   },
 *   config_prefix = "menu_position",
 *   admin_permission = "administer menu positions",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   links = {
 *     "edit-form" = "/admin/structure/menu-position/{menu_position}/edit",
 *     "delete-form" = "/admin/config/system/example/{menu_position}/delete"
 *   }
 * )
 */
class Example extends ConfigEntityBase implements MenuPositionInterface {

  /**
   * The MenuPosition ID.
   *
   * @var string
   */
  public $id;

  /**
   * The MenuPosition label.
   *
   * @var string
   */
  public $label;

  
}