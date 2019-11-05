<?php
namespace Drupal\drupal_exporter\Services;

use Drupal\taxonomy\Entity\Term;
use Cocur\Slugify\Slugify;
use Drupal\Core\Site\Settings;

/**
 * Class ElasticSearchProcessor.
 *
 * A service container class for processing entity objects for exporting to
 * ElasticSearch.
 */
class WalExportProcessor {

  protected $config;

  protected $slugify;

  protected $logger;

  public function __construct($config = 'id') {
    $this->idMapping = $config;
    $this->slugify = new Slugify();
    $this->logger = \Drupal::logger('drupal_exporter');
  }

  /**
   * Processes entity into associative array for ElasticSearch exporting.
   *
   * @param string $type
   *  A String that describes the data type to be processed.
   * @param object $content
   *  An entity object which contains the source data.
   *
   * @return array for exporting content.
   */
  public function process($type, $content) {
    $result = [
      '_meta' => [
        'indexed' => date('c', time())
      ],
    ];
    switch ($type) {
      case 'article':
        $result['title'] = $this->_processTitle($content);
        $result['description'] = $this->_processTextField('field_description', $content);
        $result['body'] = $this->_processTextField('field_body', $content);
        $result['slug'] = $this->slugify->slugify($content->get('title')
          ->getString());
        break;
      case 'another_content_type':
        $result['published'] = $this->_processDateField('created', $content);
        $result['taxonomy_reference'] = $this->_processTerms('field_genres', $content);
        break;
      default:
        $result = NULL;
        break;
    }

    // Creates Custom ID logic that you can use. Example case is if you have content
    // types or business needs where you don't want to use the Node ID as the main ID,
    // the mappings config allows you to change that mapping for your exporting needs
    $id_field = $this->idMapping;
    if (!empty($result) && isset($id_field) && ($id_field != 'id')) {
      $result['id'] = $content->get($id_field)->value;
    }

    return $result;
  }

  /**
   * Helper function for Processing Market Refs.
   *
   * @param String $fieldName
   * Title string of content.
   *
   * @return array
   * An associative array containing search compatible title strings.
   */
  public function _formatTitle(string $title): array {
    if (empty($title)) {
      return NULL;
    }
    else {
      return $result['title'] = [
        'html' => html_entity_decode(
          strip_tags($title, '<em><i><cite>'),
          ENT_QUOTES
        ),
        'text' => html_entity_decode(
          strip_tags($title),
          ENT_QUOTES
        ),
        'sort' => preg_replace([
          '/^(a |an |the |[^0-9a-z]+)/',
          '/^[^0-9a-z]+/'
        ], '', strtolower($title)),
      ];
    }
  }

  /**
   * Helper function for Processing a Node's Title
   *
   * @param Object content
   * Title string of content.
   *
   * @return array
   * Search Compatible Array
   */
  private function _processTitle($content): array {
    return $this->_formatTitle($content->get('title')->getString());
  }

  /**
   * Helper function for Processing a Text Field.
   *
   * @param string Name of field
   * @param object Content
   *
   * @return string
   * Text string for indexing.
   */
  private function _processTextField(string $fieldName, $content): string {
    $textArray = $content->get($fieldName)->getValue();
    if (!$textArray) {
      return NULL;
    }
    return $textArray[0]['value'];
  }

  /**
   * Helper function for Processing a Date Field.
   *
   * @param string $fieldName
   * @param object $content
   *
   * @return string
   * Date string for indexing.
   */
  private function _processDateField(string $fieldName, $content) {
    $field = $content->get($fieldName);
    $fieldHasData = !$field->isEmpty();
    if ($fieldHasData) {
      $date = $content->get($fieldName)->get(0)->getValue()['value'];
      return date('c', strtotime($date));
    }
    else {
      return NULL;
    }
  }

  /**
   * Helper function to process the processing taxonomy terms.
   *
   * @param string $field
   * Name of the field to process.
   *
   * @param object $content
   * the content entity.
   *
   * @return array for indexing.
   */
  public function _processTerms($fieldName, $content) {
    $terms = [];
    $entities = $content->get($fieldName)->getValue();
    if (!empty($entities)) {
      foreach ($entities as $entity) {
        $term = Term::load($entity['target_id']);
        $termText = $term->getName();
        array_push(
          $terms,
          [
            'text' => $termText,
            'url_compat' => $this->slugify->slugify($termText),
          ]
        );
      }

      return $terms;
    }
    else {
      return NULL;
    }
  }

}
