<?php
/**
 * @file
 * Contains \Drupal\menu_position\MenuPositionInterface.
 */

namespace Drupal\menu_position;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining a Example entity.
 */
interface MenuPositionInterface extends ConfigEntityInterface {
  /**
   * Returns the administrative title of the menu position rule
   * @return string
   *    The administrative title of the menu position rule
   */
  public function getAdminTitle();

  /**
   * Returns the parent menu item
   * @return integer
   *    The parent menu item plid
   */
  public function getPlid();

  /**
   * Returns the content type conditions
   * @param string $plugin
   *    machine_name of plugin
   * @return array
   *    The array of configuration for content types
   */
  public function getConfigOptions($plugin);

  /**
   * Returns weight for the particular menu position rule
   * @return integer
   *    Weight for the particular rule
   */
  public function getWeight();

  /**
   * Sets the administrative title of the menu position rule
   * @param string $admin_title
   *    The administrative title of the menu position rule
   */
  public function setAdminTitle($admin_title);

  /**
   * Sets the parent menu item
   * @return integer $plid
   *    The parent menu item plid
   */
  public function setPlid($plid);

  /**
   * Sets the configuration options for the menu position rules
   * @param array $config_options
   *    array of $config_options
   * @param string $plugin
   *    machine plugin name
   */
  public function setConfigOptions($config_options, $plugin);

  /**
   * Sets weight for the particular menu position rule
   * @param integer $weight
   *    Weight for the particular rule
   */
  public function setWeight($weight);

}