<?php

namespace Drupal\image_google_document_ai\Plugin\ImageGoogleDocumentAiProcessorType;

use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\image_google_document_ai\ImageGoogleDocumentAiProcessorTypePluginBase;
use Google\Cloud\DocumentAI\V1\ProcessResponse;
use Symfony\Component\DependencyInjection\ContainerInterface as DependencyInjectionContainerInterface;

/**
 * Plugin implementation of the image_google_document_ai_processor_type.
 *
 * @ImageGoogleDocumentAiProcessorType(
 *   id = "form_parser",
 *   label = @Translation("Form Parser"),
 * )
 */
class FormParser extends ImageGoogleDocumentAiProcessorTypePluginBase implements ContainerFactoryPluginInterface {

  /**
   * Constructs a FormParser object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $loggerChannel
   *   The logger channel.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected LoggerChannelInterface $loggerChannel,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    DependencyInjectionContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
        $configuration,
        $plugin_id,
        $plugin_definition,
        $container->get('logger.channel.image_google_document_ai'),
      );
  }

  /**
   * {@inheritdoc}
   */
  public function processGoogleDocumentAiResponse(ProcessResponse $response): array {

    $pages = $response->getDocument()->getPages();
    if (!$pages) {
      return [];
    }

    $fields = [];
    foreach ($pages->getIterator() as $page) {
      foreach ($page->getFormFields() as $formField) {
        try {
          $fieldName = rtrim($formField->getFieldName()->getTextAnchor()->getContent());
          $fieldValue = rtrim($formField->getFieldValue()->getTextAnchor()->getContent());
          $fields[$fieldName] = $fieldValue;
        }
        catch (\Exception $e) {
          $this->loggerChannel->error(
            'Failed to retrieve field from Google Document AI: {message}. Stack: {stack}.',
            [
              'message' => $e->getMessage(),
              'stack' => $e->getTraceAsString(),
            ]);
          throw $e;
        }
      }
    }
    return $fields;
  }

}
