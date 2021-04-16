<?php

namespace Drupal\newsletterapi\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\newsletterapi\JsonFixerServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\Client;

/**
 * Class NewsletterForm.
 */
class NewsletterForm extends FormBase {

  /**
   * @var JsonFixerServiceInterface
   */
  protected $jsonFixerService;

  /**
   * NewsletterForm constructor.
   * @param JsonFixerServiceInterface $jsonFixerService
   */
  public function __construct(JsonFixerServiceInterface $jsonFixerService)
  {
    $this->jsonFixerService = $jsonFixerService;
  }

  /**
   * @inheritDoc
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('newsletterapi.jsonfixer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'newsletter_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['container'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'box-container'],
    ];
    // The box contains some markup that we can change on a submit request.
    $form['container']['box'] = [
      '#type' => 'markup',
      '#markup' => 'Fill Up the form',
    ];


    $form['details'] = [
      '#type' => 'details',
      '#title' => $this->t('Contact Box'),
      '#open' => TRUE,
    ];
    $form['details']['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#description' => $this->t('Fill up e-mail field'),
      '#maxlength' => 255,
      '#size' => 64,
      '#required' => TRUE,

    ];
    $form['details']['legal'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Legal'),
      '#description' => $this->t('Accept Rules'),
      '#required' => TRUE,
    ];
    $form['details']['actions'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit The post'),
      '#ajax' => [
        'callback' => '::sendPostMessage',
        'wrapper' => 'box-container',
      ],
    ];

    return $form;
  }

  /**
   *
   */
  public function sendPostMessage(array $form, FormStateInterface $form_state) {
    $element = $form['container'];
    //prevent not send post function without validation
    if (empty($form_state->getValue('email')) ||  !filter_var($form_state->getValue('email'), FILTER_VALIDATE_EMAIL)
      || ! (bool) $form_state->getValue('legal')) {
      return $element;
    }

    //endpoint from configuration
    $end_point = $this->config('newsletterapi.newsletterconfiguration')->get('url_endpoint');

    $client = new Client([
      'headers' => [ 'Content-Type' => 'application/json' ]
    ]);

    $response = $client->post($end_point,
      ['body' => json_encode(
        [
          'email' => $form_state->getValue('email'),
          'legal' => $form_state->getValue('legal')
        ]
      )]
    );

    if ($response->getStatusCode()==201) {
      $content = $response->getBody()->getContents();
      $jsonContent = json_decode($this->jsonFixerService->fix($content));
      $element['box']['#markup'] = $this->t($jsonContent->message);
    } else {
      $element['box']['#markup'] = $this->t('Wrong status message');
    }
    return $element;

  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form,$form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
