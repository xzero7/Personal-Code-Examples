<?php
namespace Drupal\drupal_exporter\Plugin;

use Drupal\Component\Plugin\Factory\DefaultFactory;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Provides an Export plugin manager.
 *
 * @see Drupal\Export\Annotation\Export
 * @see Drupal\Export\Plugin\ExportInterface
 * @see plugin_api
 */
class ExportPluginManager extends DefaultPluginManager {

  /**
   * Constructs a ExportPluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, \Drupal\Core\Extension\ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/Export',
      $namespaces,
      $module_handler,
      'Drupal\drupal_exporter\Plugin\ExportInterface',
      'Drupal\drupal_exporter\Annotation\Export'
    );
    $this->alterInfo('export_info');
    $this->setCacheBackend($cache_backend, 'export_info_plugins');
  }
}
