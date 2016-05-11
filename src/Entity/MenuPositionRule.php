<?php
/**
 * @file
 * Contains \Drupal\example\Entity\MenuPositionRule.
 */

namespace Drupal\menu_position\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\menu_position\MenuPositionRuleInterface;

/**
 * Defines the MenuPositionRule entity.
 *
 * @ConfigEntityType(
 *   id = "menu_position_rule",
 *   label = @Translation("Menu Position Rule"),
 *   handlers = {
 *     "list_builder" = "Drupal\menu_position\Controller\MenuPositionRuleListBuilder",
 *     "form" = {
 *       "add" = "Drupal\menu_position\Form\MenuPositionRuleForm",
 *       "edit" = "Drupal\menu_position\Form\MenuPositionRuleForm",
 *       "delete" = "Drupal\menu_position\Form\MenuPositionRuleDeleteForm"
 *     }
 *   },
 *   config_prefix = "menu_position_rule",
 *   admin_permission = "administer menu positions",
 *   entity_keys = {
 *     "id" = "id"
 *   },
 *   links = {
 *     "edit-form" = "/admin/structure/menu-position/{menu_position_rule}/edit",
 *     "delete-form" = "/admin/strcuture/menu-position/{menu_position_rule}/delete",
 *     "collection" = "/admin/structure/menu-position"
 *   }
 * )
 */
class MenuPositionRule extends ConfigEntityBase implements MenuPositionRuleInterface {

  /**
   * The MenuPositionRule ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The MenuPositionRule label.
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
