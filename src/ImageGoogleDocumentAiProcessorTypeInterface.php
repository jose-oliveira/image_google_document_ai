<?php

namespace Drupal\image_google_document_ai;

use Google\Cloud\DocumentAI\V1\ProcessResponse;

/**
 * Interface for image_google_document_ai_processor_type plugins.
 */
interface ImageGoogleDocumentAiProcessorTypeInterface {

  /**
   * Returns the translated plugin label.
   *
   * @return string
   *   The translated title.
   */
  public function label();

  /**
   * Processes response from Google Document AI.
   *
   * @param \Google\Cloud\DocumentAI\V1\ProcessResponse $response
   *   The Google Document AI response.
   *
   * @return array
   *   An array of field names and values.
   */
  public function processGoogleDocumentAiResponse(ProcessResponse $response): array;

}
