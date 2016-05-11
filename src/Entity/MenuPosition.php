<?php
/**
 * @file
 * Contains \Drupal\example\Entity\MenuPosition.
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
 *     "access" = "Drupal\menu_postion\MenuPositionAccessControlHandler",
 *     "view_builder" = "Drupal\menu_postion\MenuPositionViewBuilder",
 *     "list_builder" = "Drupal\menu_postion\MenuPositionListBuilder",
 *     "form" = {
 *       "default" = "Drupal\menu_position\Form\MenuPositionForm",
 *       "delete" = "Drupal\menu_position\Form\MenuPositionDeleteForm"
 *     }
 *   },
 *   admin_permission = "administer menu positions",
 *   entity_keys = {
 *     "id" = "id"
 *   },
 *   links = {
 *     "edit-form" = "/admin/structure/menu-position/{menu_position}/edit",
 *     "delete-form" = "/admin/config/system/example/{menu_position}/delete"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "enabled",
 *     "conditions",
 *     "plid",
 *     "mlid",
 *     "weight",
 *     "machine_name"
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
  public function getConditions() {
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
    $this->label = $label;
  }

  /**
   * {@inheritdoc}
   */
  public function setEnabled($enabled) {
    $this->enabled = $enabled;
  }

  /**
   * {@inheritdoc}
   */
  public function setConditions($conditions, $plugin) {
    $this->conditions = $conditions;
  }

  /**
   * {@inheritdoc}
   */
  public function setMenuName($menu_name) {
    $this->menu_name = $menu_name;
  }

  /**
   * {@inheritdoc}
   */
  public function setPlid($plid) {
    $this->plid = $plid;
  }

  /**
   * {@inheritdoc}
   */
  public function setMlid($mlid) {
    $this->mlid = $mlid;
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($weight) {
    $this->weight = $weight;
  }

  /**
   * {@inheritdoc}
   */
  public function setMachineName($machine_name) {
    $this->machine_name = $machine_name;
  }
}
