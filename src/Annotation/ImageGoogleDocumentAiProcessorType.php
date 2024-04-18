<?php

namespace Drupal\image_google_document_ai\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines image_google_document_ai_processor_type annotation object.
 *
 * @Annotation
 */
class ImageGoogleDocumentAiProcessorType extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $title;

}
