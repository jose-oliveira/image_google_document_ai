<?php

namespace Drupal\image_google_document_ai;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManagerInterface;
use Drupal\file\Entity\File;
use Google\Cloud\DocumentAI\V1\DocumentProcessorServiceClient;
use Google\Cloud\DocumentAI\V1\RawDocument;

/**
 * Class to handle calls to Google Document AI API.
 */
class ImageGoogleDocumentAIDataExtractor {


  /**
   * The processor type plugin manager.
   *
   * @var \Drupal\image_google_document_ai\ImageGoogleDocumentAiProcessorTypePluginManager
   */
  protected $processorTypePluginManager;

  /**
   * The processor type plugin.
   *
   * @var \Drupal\image_google_document_ai\ImageGoogleDocumentAiProcessorTypeInterface
   */
  protected $processorType;

  /**
   * The Google Document AI API client.
   *
   * @var \Google\Cloud\DocumentAI\V1\DocumentProcessorServiceClient
   */
  protected $client;

  /**
   * The variable holding the Google Document AI processor name.
   *
   * @var string
   */
  protected $processorName;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\StreamWrapper\StreamWrapperManagerInterface $streamWrapperManager
   *   The stream wrapper manager service.
   * @param \Drupal\image_google_document_ai\ImageGoogleDocumentAiProcessorTypePluginManager $processor_type_plugin_manager
   *   The plugin manager for processor types.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(
    protected StreamWrapperManagerInterface $streamWrapperManager,
    ImageGoogleDocumentAiProcessorTypePluginManager $processor_type_plugin_manager,
    ConfigFactoryInterface $config_factory
  ) {

    $config = $config_factory->get('image_google_document_ai.settings');
    // Based on https://cloud.google.com/document-ai/docs/libraries#client-libraries-usage-php.
    $projectId = $config->get('projectId');
    $location = $config->get('location');
    $processor = $config->get('processor');

    $this->processorTypePluginManager = $processor_type_plugin_manager;
    $this->processorType = $this->processorTypePluginManager->createInstance($config->get('processor_type'));

    $this->client = new DocumentProcessorServiceClient([
      'credentials' => json_decode(file_get_contents($config->get('credentials')), TRUE),
    ]);
    $this->processorName = $this->client->processorName($projectId, $location, $processor);

  }

  /**
   * Changes processor type.
   *
   * @param string $processor_type_id
   *   The id for the new processor type.
   */
  public function setProcessorType(string $processor_type_id) {
    $this->processorType = $this->processorTypePluginManager->createInstance($processor_type_id);
  }

  /**
   * Returns data extracted from an image file.
   *
   * @param string $image_fid
   *   The image to be sent to Google Document AI API.
   *
   * @return array
   *   An array of field names and values.
   */
  public function extractDataFromImage(string $image_fid): array {
    $file = File::load($image_fid);

    if (!$file) {
      return [];
    }

    // "Drupalized" version of https://cloud.google.com/document-ai/docs/libraries#client-libraries-usage-php.
    $opened_path = "";
    $stream_wrapper = $this->streamWrapperManager->getViaUri($file->getFileUri());
    $stream_wrapper->stream_open($file->getFileUri(), 'rb', STREAM_USE_PATH, $opened_path);
    $contents = $stream_wrapper->stream_read($file->getSize());
    $stream_wrapper->stream_close();

    // Load File contents into RawDocument.
    $rawDocument = new RawDocument([
      'content' => $contents,
      'mime_type' => $file->getMimeType(),
    ]);

    $response = $this->client->processDocument($this->processorName, [
      'rawDocument' => $rawDocument,
    ]);

    return $this->processorType->processGoogleDocumentAiResponse($response);
  }

}
