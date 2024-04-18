<?php

namespace Drupal\image_google_document_ai\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link as Link;
use Drupal\Core\Url;
use Drupal\image_google_document_ai\ImageGoogleDocumentAiProcessorTypePluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure Image Google Document AI settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * Constructs a \Drupal\system\ConfigFormBase object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\image_google_document_ai\ImageGoogleDocumentAiProcessorTypePluginManager $processorTypePluginManager
   *   The plugin manager for processor types.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    protected ImageGoogleDocumentAiProcessorTypePluginManager $processorTypePluginManager
  ) {
    parent::__construct($config_factory);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('plugin.manager.image_google_document_ai_processor_type'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'image_google_document_ai_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['image_google_document_ai.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['credentials'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Credentials'),
      '#description' => $this->t('Path to service account credentials file.'),
      '#default_value' => $this->config('image_google_document_ai.settings')->get('credentials'),
    ];

    $form['setup_auth_link'] = [
      '#markup' => Link::fromTextAndUrl(
        $this->t('Setup authentication'),
        Url::fromUri('https://cloud.google.com/document-ai/docs/setup#auth')
      )->toString(),
    ];

    $form['projectId'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Project ID'),
      '#default_value' => $this->config('image_google_document_ai.settings')->get('projectId'),
    ];

    $form['processor'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Processor ID'),
      '#default_value' => $this->config('image_google_document_ai.settings')->get('processor'),
    ];

    $form['location'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Location'),
      '#default_value' => $this->config('image_google_document_ai.settings')->get('location') ?? 'us',
    ];

    $processor_types = array_map(
      function ($plugin_definition) {
        return $plugin_definition['label'];
      },
      $this->processorTypePluginManager->getDefinitions()
    );
    $form['processor_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Default processor type'),
      '#options' => $processor_types,
      '#default_value' => $this->config('image_google_document_ai.settings')->get('processor_type'),
    ];

    $form['processors_page'] = [
      '#markup' => Link::fromTextAndUrl(
        $this->t('Processors page on Google Cloud console.'),
        Url::fromUri('https://console.cloud.google.com/ai/document-ai/processors')
      )->toString(),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('image_google_document_ai.settings')
      ->set('credentials', $form_state->getValue('credentials'))
      ->set('projectId', $form_state->getValue('projectId'))
      ->set('processor', $form_state->getValue('processor'))
      ->set('processor_type', $form_state->getValue('processor_type'))
      ->set('location', $form_state->getValue('location'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
