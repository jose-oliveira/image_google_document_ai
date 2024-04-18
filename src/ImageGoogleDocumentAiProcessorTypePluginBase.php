<?php

namespace Drupal\image_google_document_ai;

use Drupal\Component\Plugin\PluginBase;

/**
 * Base class for image_google_document_ai_processor_type plugins.
 */
abstract class ImageGoogleDocumentAiProcessorTypePluginBase extends PluginBase implements ImageGoogleDocumentAiProcessorTypeInterface {

  /**
   * {@inheritdoc}
   */
  public function label() {
    // Cast the label to a string since it is a TranslatableMarkup object.
    return (string) $this->pluginDefinition['label'];
  }

}
