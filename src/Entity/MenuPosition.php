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
class MenuPosition extends ConfigEntityBase implements MenuPositionInterface {

  /**
   * The MenuPosition ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The MenuPosition label.
   *
   * @var string
   */
  protected $label;

  /**
   * Whether the rule is enabled or not.
   *
   * @var boolean
   */
  protected $enabled;

  /**
   * The serialized conditions for this rule.
   *
   * @var sequence
   */
  protected $conditions;

  /**
   * The menu of the menu link for this rule.
   *
   * @var string
   */
  protected $menu_name;

  /**
   * The parent menu link id for this rule.
   *
   * @var integer
   */
  protected $plid;

  /**
   * The menu link id for this rule.
   *
   * @var integer
   */
  protected $mlid;

  /**
   * The weight of this rule.
   *
   * @var integer
   */
  protected $weight;

  /**
   * The machine name.
   *
   * @var string
   */
  protected $machine_name;

  /**
   * {@inheritdoc}
   */
  public function getId() {
    return $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->label;
  }

  /**
   * {@inheritdoc}
   */
  public function getEnabled() {
    return $this->enabled;
  }

  /**
   * {@inheritdoc}
   */
  public function getConditions($plugin) {
    return $this->conditions;
  }

  /**
   * {@inheritdoc}
   */
  public function getMenuName() {
    return $this->menu_name;
  }

  /**
   * {@inheritdoc}
   */
  public function getPlid() {
    return $this->plid;
  }

  /**
   * {@inheritdoc}
   */
  public function getMlid() {
    return $this->mlid;
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return $this->weight;
  }

  /**
   * {@inheritdoc}
   */
  public function getMachineName() {
    return $this->machine_name;
  }

  /**
   * {@inheritdoc}
   */
  public function setLabel($label) {

  }

  /**
   * {@inheritdoc}
   */
  public function setEnabled() {

  }

  /**
   * {@inheritdoc}
   */
  public function setConditions($conditions, $plugin) {

  }

  /**
   * {@inheritdoc}
   */
  public function setMenuName() {

  }

  /**
   * {@inheritdoc}
   */
  public function setPlid($plid) {

  }

  /**
   * {@inheritdoc}
   */
  public function setMlid() {

  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($weight) {

  }

  /**
   * {@inheritdoc}
   */
  public function setMachineName() {

  }

}
