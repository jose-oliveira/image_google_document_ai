<?php

namespace Drupal\image_google_document_ai_test;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Modifies the ImageGoogleDocumentAIDataExtractor service.
 */
class ImageGoogleDocumentAITestServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {

    if (!$container->hasDefinition('image_google_document_ai.data_extractor')) {
      return;
    }

    $definition = $container->getDefinition('image_google_document_ai.data_extractor');
    $definition
      ->setClass(ImageGoogleDocumentAIDataExtractorTest::class)
      ->setArguments([]);
  }
}
