<?php
/**
 * @file
 * Contains \Drupal\example\Entity\MenuPositionRule.
 */

namespace Drupal\menu_position\Entity;

use Drupal\Core\Condition\ConditionPluginCollection;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
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
 *       "default" = "Drupal\menu_position\Form\MenuPositionRuleForm",
 *       "delete" = "Drupal\menu_position\Form\MenuPositionRuleDeleteForm"
 *     }
 *   },
 *   config_prefix = "menu_position_rule",
 *   admin_permission = "administer menu positions",
 *   entity_keys = {
 *     "id" = "id"
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/menu-position/add",
 *     "edit-form" = "/admin/structure/menu-position/{menu_position_rule}/edit",
 *     "delete-form" = "/admin/strcuture/menu-position/{menu_position_rule}/delete",
 *     "collection" = "/admin/structure/menu-position"
 *   }
 * )
 */
class MenuPositionRule extends ConfigEntityBase implements MenuPositionRuleInterface, EntityWithPluginCollectionInterface {

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
  protected $conditions = array();

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
  protected $parent;

  /**
   * The menu link id for this rule.
   *
   * @var string
   */
  protected $menu_link_id;

  /**
   * The weight of this rule.
   *
   * @var integer
   */
  protected $weight;

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
    if (!isset($this->conditionCollection)) {
      $this->conditionCollection = new ConditionPluginCollection($this->conditionPluginManager(), $this->get('conditions'));
    }
    return $this->conditionCollection;
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
  public function getParent() {
    return $this->parent;
  }

  /**
   * {@inheritdoc}
   */
  public function getMenuLinkId() {
    return $this->menu_link_id;
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
  public function setParent($parent) {
    $this->parent = $parent;
  }

  /**
   * {@inheritdoc}
   */
  public function setMenuLinkId($menu_link_id) {
    $this->menu_link_id = $menu_link_id;
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
  public function getPluginCollections() {
    return [
      'conditions' => $this->getConditions(),
    ];
  }

  /**
   * Gets the condition plugin manager.
   *
   * @return \Drupal\Core\Executable\ExecutableManagerInterface
   *   The condition plugin manager.
   */
  protected function conditionPluginManager() {
    $this->conditionPluginManager;
    if (!isset($this->conditionPluginManager)) {
      $this->conditionPluginManager = \Drupal::service('plugin.manager.condition');
    }
    return $this->conditionPluginManager;
  }
}
