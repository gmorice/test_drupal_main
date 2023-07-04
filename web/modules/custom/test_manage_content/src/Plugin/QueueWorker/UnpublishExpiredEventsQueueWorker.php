<?php

namespace Drupal\test_manage_content\Plugin\QueueWorker;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Unpublish expired Events.
 *
 * @QueueWorker(
 *   id = "unpublish_expired_events",
 *   title = @Translation("Dépublie les contenus de type Evenements expirés."),
 *   cron = {"time" = 30}
 * )
 */
class UnpublishExpiredEventsQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;


  /**
   * Constructs a new SameEventTypeBlock.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  public function processItem($data) {
    /** @var NodeInterface $node */
    $node = $this->entityTypeManager->getStorage('node')->load($data->nid);
    if ($node instanceof NodeInterface && $node->isPublished()) {
        return $this->unpublishNode($node);
    }
  }

  protected function unpublishNode($node) {
    $node->setUnpublished();
    return $node->save();
  }

}
