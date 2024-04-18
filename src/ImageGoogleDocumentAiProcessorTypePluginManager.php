<?php

namespace Drupal\image_google_document_ai;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * ImageGoogleDocumentAiProcessorType plugin manager.
 */
class ImageGoogleDocumentAiProcessorTypePluginManager extends DefaultPluginManager {

  /**
   * Constructs ImageGoogleDocumentAiProcessorTypePluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/ImageGoogleDocumentAiProcessorType',
      $namespaces,
      $module_handler,
      'Drupal\image_google_document_ai\ImageGoogleDocumentAiProcessorTypeInterface',
      'Drupal\image_google_document_ai\Annotation\ImageGoogleDocumentAiProcessorType'
    );
    $this->alterInfo('image_google_document_ai_processor_type_info');
    $this->setCacheBackend($cache_backend, 'image_google_document_ai_processor_type_plugins');
  }

}
