<?php
/**
 * @file
 * Contains \Drupal\menu_position\MenuPositionRuleInterface.
 */

namespace Drupal\menu_position;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining a Example entity.
 */
interface MenuPositionRuleInterface extends ConfigEntityInterface {
  /**
   * Returns the ID of the menu position rule
   * @return integer
   *    The unique identifier of the menu position rule
   */
  public function getId();

  /**
   * Returns the administrative title of the menu position rule
   * @return string
   *    The administrative title of the menu position rule
   */
  public function getLabel();

  /**
   * Returns the status of the menu position rule
   * @return boolean
   *    The status of the menu position rule
   */
  public function getEnabled();

  /**
   * Returns the content type conditions
   * @param string $plugin
   *    machine_name of plugin
   * @return array
   *    The array of configuration for content types
   */
  public function getConditions();

  /**
   * Returns the name of the menu where the position rule lives
   * @return string
   *    The name of the menu where the position rule lives
   */
  public function getMenuName();

  /**
   * Returns the parent menu item
   * @return integer
   *    The parent menu item plid
   */
  public function getPlid();

  /**
   * Returns the menu item
   * @return integer
   *    The menu item
   */
  public function getMlid();

  /**
   * Returns weight for the particular menu position rule
   * @return integer
   *    Weight for the particular rule
   */
  public function getWeight();

  /**
   * Returns machine name for the particular menu position rule
   * @return string
   *    Machine name for the particular rule
   */
  public function getMachineName();

  /**
   * Sets the administrative title of the menu position rule
   * @param string $label
   *    The administrative title of the menu position rule
   */
  public function setLabel($label);

  /**
   * Sets the status menu position rule
   * @param boolean
   *    The status of the menu position rule
   */
  public function setEnabled($enabled);

  /**
   * Sets the configuration options for the menu position rules
   * @param array $conditions
   *    array of $conditions
   * @param string $plugin
   *    machine plugin name
   */
  public function setConditions($conditions, $plugin);

  /**
   * Sets the name of the menu where the position rule lives
   * @return string
   *    The name of menu where the position rule lives
   */
  public function setMenuName($menu_name);

  /**
   * Sets the parent menu item
   * @return integer $plid
   *    The parent menu item plid
   */
  public function setPlid($plid);

  /**
   * Sets the menu item
   * @return integer
   *    The menu item
   */
  public function setMlid($mlid);

  /**
   * Sets weight for the particular menu position rule
   * @param integer $weight
   *    Weight for the particular rule
   */
  public function setWeight($weight);

  /**
   * Sets machine name for the particular menu position rule
   * @param string
   *    Machine name for the particular rule
   */
  public function setMachineName($machine_name);
}
