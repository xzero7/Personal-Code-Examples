<?php
namespace Drupal\drupal_exporter\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\drupal_exporter\Entity\ExportInterface;

/**
 * Defines the Export entity.
 *
 * @ConfigEntityType(
 *   id = "export",
 *   label = @Translation("Export"),
 *   config_prefix = "export",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "export_plugin" = "export_plugin",
 *     "source" = "source",
 *     "process" = "process"
 *   }
 * )
 */

class Export extends ConfigEntityBase implements ExportInterface {

  /**
   * The Export ID.
   *
   * @var string
   */
  public $id;

  /**
   * The Export Plugin ID.
   *
   * @var string
   */
  public $exportPlugin;

  /**
   * The Source Object.
   *
   * @var string
   */
  public $source;

  /**
   * The Process Object.
   *
   * @var string
   */
  public $process;

}
