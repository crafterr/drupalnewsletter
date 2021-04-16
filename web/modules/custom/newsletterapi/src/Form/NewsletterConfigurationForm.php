<?php

namespace Drupal\newsletterapi\Form;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class NewsletterConfigurationForm.
 */
class NewsletterConfigurationForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'newsletterapi.newsletterconfiguration',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'newsletter_configuration_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('newsletterapi.newsletterconfiguration');
    $form['url_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Url Endpoint'),
      '#description' => $this->t('Please provide the url endpoint domain'),
      '#maxlength' => 250,
      '#size' => 64,
      '#default_value' => $config->get('url_endpoint'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    $url_endpoint = $form_state->getValue('url_endpoint');
    if (!UrlHelper::isValid($url_endpoint,true)) {
      $form_state->setErrorByName('url_endpoint', $this->t('The Url endpoint is wrong'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('newsletterapi.newsletterconfiguration')
      ->set('url_endpoint', $form_state->getValue('url_endpoint'))
      ->save();
  }

}
