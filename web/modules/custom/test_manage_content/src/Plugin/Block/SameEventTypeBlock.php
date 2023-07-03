<?php

namespace Drupal\test_manage_content\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Same Event Type' Block.
 *
 * @Block(
 *   id = "same_event_type_block",
 *   admin_label = @Translation("Same Event Type Block"),
 *   category = @Translation("Same Event Type Block"),
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Node"))
 *   }
 * )
 */
class SameEventTypeBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Define quantity of other Events to display.
   */
  const NB_EVENTS = 3;

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

  /**
   * {@inheritdoc}
   */
  public function build() {
    $items = [];

    // Get Taxonomy Category of current content.
    $node = $this->getContextValue('node');
    // Use the Taxo Term Drupal ID to filter other contents with the same referenced category.
    $category_id = $node->hasField('field_event_type') ? $node->get('field_event_type')->getString() : NULL;

    $nodes = $this->getEvents($category_id);

    // Render each node with the view builder.
    $view_builder = $this->entityTypeManager->getViewBuilder('node');
    foreach ($nodes as $node) {
      $items[] = $view_builder->view($node, 'teaser');
    }

    return [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#items' => $items,
    ];
  }

  /**
   * Retrieve Events for a given category.
   */
  protected function getEvents($category_id = NULL) {
    // Retrieve 3 contents, which end date is not passed and sort by begin date.
    $query = $this->entityTypeManager->getStorage('node')->getQuery()->accessCheck(FALSE);
    $query->condition('status', TRUE);
    // And end date not passed.
    $query->condition('field_date_range.end_value', new DrupalDateTime(), '>');
    // Order by begin date.
    $query->sort('field_date_range.value', 'ASC');
    $query_extended = clone $query;

    // Get 3 first results with the same Taxonomy category.
    if ($category_id) {
      $query->condition('field_event_type', $category_id);
    }
    $query->range(0, self::NB_EVENTS);
    $nids = $query->execute();
    $other_nids = [];

    // If there are less than 3 results, extend the request to others Categories.
    $count = count($nids);
    if ($count < self::NB_EVENTS) {
      $query_extended->range(0, (self::NB_EVENTS - $count));
      $query_extended->condition('field_event_type', $category_id, 'NOT IN');
      $nids += $query_extended->execute();
    }

    return Node::loadMultiple($nids);
  }

}