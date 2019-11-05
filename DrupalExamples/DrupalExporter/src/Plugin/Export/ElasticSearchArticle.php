<?php
// @Todo Refactor boilerplate into ExportBase class.
namespace Drupal\drupal_exporter\Plugin\Export;

use Drupal\drupal_exporter\Plugin\ExportBase;

use Drupal\drupal_exporter\Services\ElasticSearchProcessor;
use Drupal\drupal_exporter\Services\ElasticSearchModel;

/**
 * Creates a Drupal to ElasticSearch ETL Pipeline for Content Type. In this example
 * a simple Article type. To designate the source es_ is used for identificaiton
 *
 * @Export(
 *   id = "es_article"
 * )
 */
class ESArticle extends ExportBase {
  protected $sourceIdMapping;
  protected $processor;
  protected $loader;
  protected $logger;

  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $loaderConfig = \Drupal::config('drupal_exporter.export.es_article')->get('loader.index_config');
    $this->sourceIdMapping = \Drupal::config('drupal_exporter.export.es_article')->get('process.id');
    $this->processor = new ElasticSearchProcessor($this->sourceIdMapping);
    $this->loader = new ElasticSearchModel($loaderConfig);
    $this->logger = \Drupal::logger('drupal_exporter');
  }

  /**
   * Export Item
   *
   * Exports a single node by ID.
   */
  public function exportSingleItem($entity) {
    $processedData =  $this->processor->process('article', $entity);
    $this->logger->notice('Exporting Single Article Item');
    $response = $this->loader->load('article', $processedData);
    return $response;
  }

  /**
   * Remove Item
   *
   * Remove a single node's data by ID.
   *
   * @return array Response Message.
   */
  public function removeSingleItem($entity) {
    $this->logger->notice('Exporting Single Article Item');
    $response = $this->loader->remove('article', $entity->get($this->sourceIdMapping)->getString());
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function run() {

  }
}
