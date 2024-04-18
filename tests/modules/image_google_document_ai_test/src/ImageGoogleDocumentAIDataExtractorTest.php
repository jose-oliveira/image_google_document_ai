<?php

namespace Drupal\image_google_document_ai_test;

class ImageGoogleDocumentAIDataExtractorTest {

  static $field_from_image = 'field_from_image';
  static $field_value = 'Extracted from image.';
  static $unsupported_field = 'field_unsupported';

  /**
   * Returns dummy data for these tests.
   *
   * @param string $image_fid
   *   The image fid.
   *
   * @return array
   *   An array of field names and values.
   */
  public function extractDataFromImage(string $image_fid): array {
    return [
      self::$field_from_image => self::$field_value,
      self::$unsupported_field => 'Should not be displayed.',
    ];
  }

}
