<?php

namespace Drupal\image_google_document_ai\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\image\Plugin\Field\FieldType\ImageItem;

/**
 * Plugin implementation of the 'image_google_document_ai' field type.
 *
 * @todo Add config to allow user to select processor type for this field.
 *
 * @FieldType(
 *   id = "image_google_document_ai",
 *   label = @Translation("Google Document AI Image"),
 *   description = @Translation("Image field plus Google Document AI data."),
 *   category = @Translation("Reference"),
 *   default_widget = "image_google_document_ai_image",
 *   default_formatter = "image",
 *   list_class = "\Drupal\file\Plugin\Field\FieldType\FileFieldItemList",
 *   constraints = {"ReferenceAccess" = {}, "FileValidation" = {}}
 * )
 */
class ImageGoogleDocumentAiItem extends ImageItem {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {

    $schema = parent::schema($field_definition);

    $schema['columns']['hash'] = [
      'description' => "The hash for this image.",
      'type' => 'char',
      'length' => 64,
    ];

    $schema['columns']['data'] = [
      'description' => "The extracted data for this image.",
      'type' => 'blob',
      'size' => 'big',
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);

    $properties['hash'] = DataDefinition::create('string');
    $properties['data'] = DataDefinition::create('string');

    return $properties;
  }

}
