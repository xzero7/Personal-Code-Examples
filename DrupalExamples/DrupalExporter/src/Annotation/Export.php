<?php
namespace Drupal\drupal_exporter\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a reusable export plugin annotation object.
 *
 * @Annotation
 */
class Export extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;
}
