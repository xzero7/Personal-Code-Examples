<?php

namespace Drupal\drupal_exporter\Services;

use Drupal\Core\Site\Settings;

  /**
   * Class ElasticSearchModel.
   */
class ElasticSearchModel {

  /**
  *  ElasticSearchModel constructor
  *  Builds a Model to talk to an Elasticsearch instance.
  *  The host is configured from the Settings files.
  *  The configuration for which index it should use is determined by who is using
  *  this Model. If left empty Operations will fail which are caught with error
  *  handling measures in place.
  *
  * @param $index
  *   This sets up which index the loader will be sending the data to.
  * @param $createIndex
  *   This is a Flag to tell the Model that it needs to create the index.
  */
  public function __construct($indexConfig = NULL, $createIndex = FALSE) {

    $host = Settings::get('drupal_exporter_es_host');
    $this->client = \Elasticsearch\ClientBuilder::create()
      ->setHosts([$host])
      ->build();
    $this->logger = \Drupal::logger('drupal_exporter');

    // Set the proper Index Config
    $this->setIndex($indexConfig, $createIndex);
  }

  public function setIndex($indexConfig, $createIndex = FALSE) {
    // Set the proper Index Config
    if(!empty($indexConfig)) {
      // Creates Index if flag is set
      // If not then just sets the name of the index.
      if($createIndex) {
        $this->_createIndex($indexConfig);
        $this->index  = $indexConfig['name'];
      } else {
        $this->index = $indexConfig['name'];
      }
    }
  }

  /**
  * A function that allows the Model to check if an index Exists
  */
  public function indexExists($indexName) {
    // Expected structure for client call
    $params['index'] = $indexName;
    // Return the check of index
    return $this->client->indices()->exists($params);
  }

  private function _createIndex($indexConfig) {
    // Check to see if the index already exists.
    $indexExists = $this->indexExists($indexConfig['name']);
    if(!$indexExists) {
      // Build parameters to create an index
      $params['index'] = $indexConfig['name'];
      // Set index settings if configuration for settings is passed
      if (!empty($indexConfig['settings'])) {
        $params['body']['settings'] = $indexConfig['settings'];
      }
      // Set index mappings if configuration for mappings is passed
      if (!empty($indexConfig['mappings'])) {
        $params['body']['mappings'] = $indexConfig['mappings'];
      }
      $response = $this->client->indices()->create($params);
      //These notices are not outputting TODO figure out why
      $this->logger->notice('Succesfully created Index with the name [ @indexName ] . . .', ['@indexName' => $indexConfig['name']]);
    } else {
      //These notices are not outputting TODO figure out why
      $this->logger->notice('Index you are trying to create already exists, no further action taken. [ @indexName ] . . .', ['@indexName' => $indexConfig['name']]);
    }
  }

  private function _exportToElasticSearch($params) {
    $error = FALSE;
    try {
      $indexResponse = $this->client->index($params);
    } catch(\Exception $e) {
      $this->logger->error($e->getMessage());
      $error = TRUE;
    }
    if(!$error) {
      return array(
        'error' => FALSE,
        'data' => $indexResponse,
        'message' => 'Successfully able to index to Elasticsearch',
      );
    } else {
      return array(
        'error' => TRUE,
        'data' => NULL,
        'message' => 'Something went wrong while trying to index Elasticsearch. Failed to index to Elasticsearch',
      );
    }
  }
  private function _deleteFromElasticSearch($params) {
    $error = FALSE;
    try {
      $deleteResponse = $this->client->delete($params);
    } catch(\Exception $e) {
      $this->logger->error($e->getMessage());
      $error = TRUE;
    }

    if(!$error) {
      return array(
        'error' => FALSE,
        'data' => $deleteResponse,
        'message' => 'Succesfully deleted item from Elasticsearch',
      );
    } else {
      return array(
        'error' => TRUE,
        'data' => NULL,
        'message' => 'Something went wrong while trying to delete entry in Elasticsearch. Failed to delete from Elasticsearch',
      );
    }
  }

  /**
  * Will remove data from the index based on its type and id.
  *
  * @param string $type
  *  A String that describes the data type that the index will use.
  * @param string $id
  *  An associative array of processed data for indexing.
  */
  public function remove(string $type, string $id) : array {
    // Prepare parameters to send Elasticsearch
    $params = [
      'index' => $this->index,
      'type' => $type,
      'id' => $id
    ];

    $response = $this->_deleteFromElasticSearch($params);

    return $response;
  }

  /**
   * Will add a piece of data to index.
   *
   * @param $type
   *  A String that describes the data type that the index will use.
   * @param $data
   *  An associative array of processed data for exporting.
   *
   * @return
   *  A message describing failure or sucesss of indexing.
   */
  public function load($type, $data) {
    if (empty($data)) {
      // Silence if there is nothing to index.
      return [];
    };
    $response = [];
    // Sanitize data array by removing ID from the payload.
    $id = $data['id'];
    unset($data['id']);

    // Prepare parameters to send Elasticsearch
    $params = [
      'index' => $this->index,
      'type' => $type,
      'id' => $id,
      'body' => $data
    ];

    $response = $this->_exportToElasticSearch($params);

    return $response;
  }
}
