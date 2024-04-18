<?php

namespace Drupal\image_google_document_ai\Plugin\Field\FieldWidget;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\image\Plugin\Field\FieldWidget\ImageWidget;

/**
 * Plugin implementation of the 'image_google_document_ai_image' widget.
 *
 * @FieldWidget(
 *   id = "image_google_document_ai_image",
 *   label = @Translation("Google Document AI Image"),
 *   field_types = {
 *     "image_google_document_ai"
 *   }
 * )
 */
class ImageGoogleDocumentAiWidget extends ImageWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $fids = $element['#default_value']['fids'];
    if (empty($fids)) {
      return $element;
    }

    $item = $element['#default_value'];

    $element['image_hash'] = [
      '#type' => 'hidden',
      '#default_value' => $item['image_hash'] ?? '',
    ];

    $element['data'] = [
      '#type' => 'hidden',
      '#default_value' => $item['data'] ?? '',
    ];

    $element['api_response_details'] = [
      '#type' => 'details',
      '#title' => $this->t('Response from Google Document AI API.'),
      '#open' => FALSE,
      '#description' => $item['data'] ? print_r($item['data'], TRUE) : $this->t('Click on "Extract fields from image" to get data from Google Document AI API.'),
      '#attributes' => [
        'class' => [
          'api_response_details',
        ],
      ],
    ];

    $element['extract_fields'] = [
      '#type' => 'button',
      '#value' => $this->t('Extract fields from image'),
      '#ajax' => [
        'callback' => [static::class, 'extractFieldsAjax'],
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Extracting fields...'),
        ],
      ],
    ];

    return $element;
  }

  /**
   * Ajax callback from clicking the extract fields button.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state from the entity of the button being clicked.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The Ajax response.
   */
  public static function extractFieldsAjax(array $form, FormStateInterface $form_state) {
    $triggering_field_name = $form_state->getTriggeringElement()['#parents'][0];
    $field_index = $form_state->getTriggeringElement()['#parents'][1];

    $fid = reset($form_state->getValue($triggering_field_name)[$field_index]['fids']);
    $api_response = \Drupal::service('image_google_document_ai.data_extractor')->extractDataFromImage($fid);

    $response = new AjaxResponse();
    // Set values for the hidden properties of this field.
    $response->addCommand(new InvokeCommand("[name='$triggering_field_name" . "[$field_index][data]']", 'val', [serialize($api_response)]));
    // @todo Save image hash and disable "Extract fields from image" button if same image was reuploaded.
    $response->addCommand(new InvokeCommand("[name='$triggering_field_name" . "[$field_index][image_hash]']", 'val', [""]));

    $api_response_text = '<pre><code>' . print_r($api_response, TRUE) . '</code></pre>';
    $response->addCommand(new ReplaceCommand('.api_response_details .details-wrapper > div', $api_response_text));

    /** @var \Drupal\Core\Entity\EntityFormInterface  $form_object */
    $form_object = $form_state->getFormObject();
    $entity = $form_object->getEntity();

    // @todo Trigger an event to allow modules to decide how to extract and map field values.
    self::setFieldsByLabel($api_response, $entity, $form, $response);

    return $response;
  }

  /**
   * Maps labels returned from the Google Document API into Drupal fields.
   *
   * @param array $api_response
   *   API response from Google Document API.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Entity containing this field.
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Ajax\AjaxResponse $response
   *   AJAX response to be returned.
   */
  protected static function setFieldsByLabel(
    array $api_response,
    ContentEntityInterface $entity,
    array &$form,
    AjaxResponse $response,
  ) {

    $fields_by_label = self::getFieldsByLabel($entity);

    foreach ($api_response as $label => $value) {
      if (!isset($fields_by_label[$label])) {
        continue;
      }

      $field_machine_name = $fields_by_label[$label];
      // Check if field has its name accessible directly from the widget (like
      // list fields), if not try the "traditional" way.
      $field_name = $form[$field_machine_name]['widget']['#name'] ?: $form[$field_machine_name]['widget'][0]['value']['#name'];
      $response->addCommand(new InvokeCommand("[name='$field_name']", 'val', [$value]));
    }
  }

  /**
   * Get entity fields by label.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity to retrieve the fields from.
   *
   * @return array
   *   An array of field machine names keyed by field label.
   */
  protected static function getFieldsByLabel(ContentEntityInterface $entity): array {
    $labels = [];
    foreach ($entity->getFields() as $field_name => $field) {
      $field_label = strval($field->getFieldDefinition()->getLabel());
      $labels[$field_label] = $field_name;
    }

    return $labels;
  }

}
