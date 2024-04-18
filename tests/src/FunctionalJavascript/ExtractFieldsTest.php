<?php

namespace Drupal\Tests\image_google_document_ai\FunctionalJavascript;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\image_google_document_ai_test\ImageGoogleDocumentAIDataExtractorTest;
use Drupal\Tests\image\Kernel\ImageFieldCreationTrait;
use Drupal\Tests\TestFileCreationTrait;

/**
 * Tests using AJAX to fill an entity form with data extracted from an image.
 *
 * @group image_google_document_ai
 */
class ExtractFieldsTest extends WebDriverTestBase {

  use ImageFieldCreationTrait;
  use TestFileCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'file',
    'image',
    'node',
    'image_google_document_ai',
    'image_google_document_ai_test',
  ];

  /**
   * Content type for these tests.
   *
   * @var string
   */
  private $type = 'article';

  /**
   * Image field name.
   *
   * @var string
   */
  private $imageFieldName = 'images';

  /**
   * Field that is not related to image extraction funtionality.
   *
   * @var string
   */
  private $otherField = 'otherField';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->setUpContentType();
    $this->setUpUser();
  }

  /**
   * Setup content type with image with extract fields on.
   */
  private function setUpContentType() {
    $this->drupalCreateContentType(['type' => $this->type]);
    $this->setUpImageFields();
    $this->setUpTextFields();
  }

  /**
   * Creates text fields necessary for this test.
   */
  private function setUpTextFields() {
    $this->createTextField(ImageGoogleDocumentAIDataExtractorTest::$field_from_image);
    $this->createTextField($this->otherField);
  }

  /**
   * Creates a text field in a given bundle.
   *
   * @param string $field_name
   *   Field name.
   */
  private function createTextField(string $field_name) {
    $field_storage = FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'node',
      'type' => 'text',
      'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
    ]);
    $field_storage->save();

    FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => $this->type,
    ])->save();

    /** @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface $display_repository */
    $display_repository = \Drupal::service('entity_display.repository');

    $display_repository->getFormDisplay('node', $this->type)
      ->setComponent($field_name, [
        'type' => 'string_textfield',
      ])
      ->save();
  }

  /**
   * Setup image field.
   */
  private function setUpImageFields() {
    // Field to extract data from.
    $storage_settings = [
      'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
    ];
    $field_settings = ['alt_field_required' => 0];
    $this->createImageField($this->imageFieldName, $this->type, $storage_settings, $field_settings);
    $this->enableExtractFields();

    // Field to test unsupported field types.
    $this->createImageField(ImageGoogleDocumentAIDataExtractorTest::$unsupported_field, $this->type);
  }

  /**
   * Enable field extraction.
   */
  private function enableExtractFields() {
    FieldStorageConfig::loadByName('node', $this->imageFieldName)->setThirdPartySetting(
      'image_google_document_ai',
      'extract_fields',
      1
    )->save();
  }

  /**
   * Creates and login user.
   */
  private function setUpUser() {
    $user = $this->drupalCreateUser([
      'access content',
      'create ' . $this->type . ' content',
    ]);
    $this->drupalLogin($user);
  }

  /**
   * Test callback.
   */
  public function testExtractFields() {

    $this->drupalGet('node/add/' . $this->type);

    // Upload image.
    $page = $this->getSession()->getPage();
    $images = array_slice($this->getTestFiles('image'), 0, 1);
    foreach ($images as $key => $image) {
      $field_html_name = 'files[' . $this->imageFieldName . '_' . $key . '][]';
      $page->attachFileToField(
        $field_html_name,
        \Drupal::service('file_system')->realpath($image->uri)
      );
    }
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Press button to extract field from image.
    // @see \Drupal\image_google_document_ai_test\ImageGoogleDocumentAIDataExtractorTest
    $page->pressButton('Extract fields from image');
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Field extracted from image has expected value.
    $this->assertSession()->fieldValueEquals(
      ImageGoogleDocumentAIDataExtractorTest::$field_from_image . '[0][value]',
      ImageGoogleDocumentAIDataExtractorTest::$field_value
    );

    // Field unrelated to image has not changed.
    $this->assertSession()->fieldValueEquals(
      $this->otherField . '[0][value]',
      ''
    );

    // Field with unsupported type has not changed.
    $this->assertSession()->fieldValueEquals(
      'files[' . ImageGoogleDocumentAIDataExtractorTest::$unsupported_field . '_0]',
      ''
    );
  }

}
