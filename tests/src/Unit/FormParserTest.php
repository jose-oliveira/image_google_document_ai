<?php

namespace Drupal\Tests\image_google_document_ai\Unit;

use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\image_google_document_ai\Plugin\ImageGoogleDocumentAiProcessorType\FormParser;
use Drupal\Tests\UnitTestCase;
use Google\Cloud\DocumentAI\V1\Document;
use Google\Cloud\DocumentAI\V1\Document\Page;
use Google\Cloud\DocumentAI\V1\Document\Page\FormField;
use Google\Cloud\DocumentAI\V1\Document\Page\Layout;
use Google\Cloud\DocumentAI\V1\Document\TextAnchor;
use Google\Cloud\DocumentAI\V1\ProcessResponse;

/**
 * Test description.
 *
 * @group image_google_document_ai
 */
class FormParserTest extends UnitTestCase {

  /**
   * Tests that mock response was propperly processed.
   */
  public function testprocessGoogleDocumentAiResponseFromFormParserPlugin() {
    $form_parser = new FormParser([], '', '', $this->createMock(LoggerChannelInterface::class));
    $fields = $form_parser->processGoogleDocumentAiResponse($this->getSampleResponse());
    $expected_fields = [
      'Sample field name.' => 'Sample value.',
    ];
    $this->assertEquals($fields, $expected_fields);
  }

  /**
   * Sample Google Documents API response.
   * @see https://cloud.google.com/php/docs/reference/cloud-document-ai/latest
   *
   * @return \Google\Cloud\DocumentAI\V1\ProcessResponse
   *   Sample response.
   */
  private function getSampleResponse() : ProcessResponse {
    return new ProcessResponse([
      'document' => new Document(
        [
          'pages' => [
            new Page([
              'form_fields' => [
                new FormField([
                  'field_name' => new Layout([
                    'text_anchor' => new TextAnchor([
                      'content' => 'Sample field name.',
                    ]),
                  ]),
                  'field_value' => new Layout([
                    'text_anchor' => new TextAnchor([
                      'content' => 'Sample value.',
                    ]),
                  ]),
                ]),
              ],
            ]),
          ],
        ],
      ),
    ]);
  }

}
