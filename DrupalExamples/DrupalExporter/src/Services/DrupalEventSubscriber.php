<?php

namespace Drupal\custom_module\Services;

use Drupal\hook_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\hook_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\hook_event_dispatcher\Event\Entity\EntityDeleteEvent;
use Drupal\hook_event_dispatcher\Event\Cron\CronEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherEvents;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\node\NodeInterface;

use Drupal\Core\Messenger\MessengerInterface;

/**
 * Class DrupalEventSubscriber.
 *
 * By Using the contributed module hook_event_dispatcher, this sample class will listen
 * to insert, update, and delete events for Nodes. Using Drupal's Plugin System we can define how
 * the event listener should react to the events configured. In this example case, we listen
 * the events and send them to a custom ElasticSearch Plugin (ESArticle) to process
 * the drupal information into somethin we want want to send to ElasticSearch and then
 * export it to ElasticSearch as the final step. To add a different export destination just
 * simple configure a new plugin.
 *
 */
class DrupalEventSubscriber implements EventSubscriberInterface {

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * DrupalEventSubscriber constructor.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   *
   */
  public function __construct(MessengerInterface $messenger) {
    $this->messenger = $messenger;
    // To add another layer customization you can configure which nodes should be reacted to.
    // For example you and define to export Article Node Types but Ignore BasicPage Node type actions.
    $this->supportedTypes = \Drupal::entityQuery('export')
      ->execute();
  }

  /**
  * A needed part of the interface that defines what Drupal Events to react to.
  * For this example we listen to insert, update, and delete evemts for Nodes
  */
  public static function getSubscribedEvents() {
    return [
      HookEventDispatcherEvents::ENTITY_INSERT => 'entityInsert',
      HookEventDispatcherEvents::ENTITY_UPDATE => 'entityUpdate',
      HookEventDispatcherEvents::ENTITY_DELETE => 'entityDelete',
    ];
  }

  /**
   * Entity update.
   *
   * @param \Drupal\hook_event_dispatcher\Event\Entity\EntityUpdateEvent $event
   *   The event.
   */
  public function entityInsert(EntityInsertEvent $event) {
    $entity = $event->getEntity();
    if ($entity instanceof NodeInterface) {
      $this->exportToElasticSearch($entity);
      // Maybe Send the data to a MYSQL DB plugin
      // $this->exportToMYSQLDB($entity);
    }
  }

  /**
   * Entity update.
   *
   * @param \Drupal\hook_event_dispatcher\Event\Entity\EntityUpdateEvent $event
   *   The event.
   */
  public function entityUpdate(EntityUpdateEvent $event) {
    $entity = $event->getEntity();
    if ($entity instanceof NodeInterface) {
      $this->exportToElasticSearch($entity);
    }
  }

  /**
   * Entity delete.
   *
   * @param \Drupal\hook_event_dispatcher\Event\Entity\EntityDeleteEvent $event
   *   The event.
   */
  public function entityDelete(EntityDeleteEvent $event) {
    $entity = $event->getEntity();
    if ($entity instanceof NodeInterface) {
      $this->deleteFromElasticSearch($entity);
    }
  }

  /**
   * Index Entity.
   *
   * @param object $entity
   * The entity to be indexed.
   *
   * @return bool success
   */
  public function exportToElasticSearch(object $entity): bool {
    // First Check permissions from user.
    if (!\Drupal::currentUser()->hasPermission('export content')) return false;
    $type = $entity->bundle();
    // To allow exporting to different sources you should namespace your content type by
    // exporter source name.
    // Example: You can send an Article Content Type to
    // es_article which sends article data to ElasticSearch
    // mysql_article which sends article data to a different MYSQL DB
    $configKey = 'es_' . $type;

    // If the entity has been configured to be indexed, index it.
    if (in_array($configKey, $this->supportedTypes)) {
      $config = \Drupal::config('custom_module.export.es_' . $type);
      $pluginID = $config->get('export_plugin');
      $response = \Drupal::service('plugin.manager.custom_module.export')
        ->createInstance($pluginID)
        ->exportSingleItem($entity);

      if ($response['error']) {
        $this->messenger->addError($response['message']);
        return FALSE;
      }
      else {
        $this->messenger->addMessage($response['message']);
        return TRUE;
      }
    } else {
      return FALSE;
    }
  }

  /**
   * Delete Entity Data from Index.
   *
   * @param object $entity
   * The entity which stored the data to be deleted.
   *
   * @return bool success
   */
  public function deleteFromElasticSearch($entity) {
    if (!\Drupal::currentUser()->hasPermission('export content')) return false;
    $type = $entity->bundle();
    $configKey = 'es_' . $type;

    // If the entity has been configured to be indexed, index it.
    if (in_array($configKey, $this->supportedTypes)) {
      $config = \Drupal::config('custom_module.export.es_' . $type);
      $pluginID = $config->get('export_plugin');
      $response = \Drupal::service('plugin.manager.custom_module.export')
        ->createInstance($pluginID)
        ->removeSingleItem($entity);

      if ($response['error']) {
        $this->messenger->addError($response['message']);
      }
      else {
        $this->messenger->addMessage($response['message']);
        return TRUE;
      }
    } else {
      return FALSE;
    }
  }
}
