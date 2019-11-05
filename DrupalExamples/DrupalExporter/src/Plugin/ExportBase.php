<?php
namespace Drupal\drupal_exporter\Plugin;

use Drupal\Core\Plugin\PluginBase;
use Drupal\drupal_exporter\Plugin\ExportInterface;

/**
 * Defines default behaviors of an export plugin.
 */
class ExportBase extends PluginBase implements ExportInterface {

  /**
   * Retrieves ID of Plugin.
   */
  public function getID() {
    return $this->pluginDefinition['id'];
  }

  /**
   * Runs export.
   */
  public function run() {
    return $this->pluginDefinition['id'];
  }
}
