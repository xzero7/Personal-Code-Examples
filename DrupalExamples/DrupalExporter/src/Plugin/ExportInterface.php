<?php
namespace Drupal\drupal_exporter\Plugin;

use Drupal\Component\Plugin\DerivativeInspectionInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Interface for exports.
 */
interface ExportInterface extends PluginInspectionInterface, DerivativeInspectionInterface {
  public function getID();
  public function run();
}
