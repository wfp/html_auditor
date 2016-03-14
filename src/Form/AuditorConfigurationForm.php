<?php

/**
 * @file
 * Contains \Drupal\html_auditor\Form\AuditorConfigurationForm.
 */

namespace Drupal\html_auditor\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Component\Utility\Xss;
use GuzzleHttp\Exception\RequestException;

/**
 * Defines a form that configures html_auditor module settings.
 */
class AuditorConfigurationForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'html_auditor_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'html_auditor.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('html_auditor.settings');
    $service = \Drupal::service('html_auditor');
    $sitemap = $service->isSitemapEnabled();
    $auditor = $service->isHtmlAuditorEnabled();
    $disabled = FALSE;
    if (!$sitemap->enabled) {
      $disabled = TRUE;
      drupal_set_message($sitemap->message, 'warning');
    }

    if (!$auditor->enabled) {
      $disabled = TRUE;
      drupal_set_message($auditor->message, 'warning');
    }

    $form['html_auditor']['sitemap_uri'] = [
      '#type' => 'textfield',
      '#title' => $this->t('XML Sitemap file path or URL'),
      '#description' => $this->t('Enter a file path such as <em>/sitemap.xml</em> or a URL such as <em>http://example.com/sitemap.xml</em>'),
      '#default_value' => $config->get('sitemap.uri'),
      '#size' => 40,
      '#required' => TRUE,
    ];
    $form['html_auditor']['last_modified'] = [
      '#type' => 'number',
      '#title' => $this->t('Audit pages modified since'),
      '#description' => $this->t('Only audit pages which have been modified within this many hours.'),
      '#field_suffix' => t('hours'),
      '#default_value' => $config->get('sitemap.last_modified'),
      '#size' => 40,
      '#min' => 1,
      '#required' => TRUE,
    ];
    $form['html_auditor']['a11y_standard'] = [
      '#type' => 'radios',
      '#title' => $this->t('Accessibility standard'),
      '#description' => $this->t('The accessibility standard to use when testing pages.'),
      '#options' => [
        'Section508' => $this->t('Section508'),
        'WCAG2A' => $this->t('WCAG2A'),
        'WCAG2AA' => $this->t('WCAG2AA'),
        'WCAG2AAA' => $this->t('WCAG2AAA'),
      ],
      '#default_value' => $config->get('a11y.standard'),
      '#required' => TRUE,
    ];
    $form['html_auditor']['a11y_ignore'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Accessibility reporting level'),
      '#description' => $this->t('The level of message to fail on'),
      '#options' => [
        'error' => $this->t('error'),
        'warning' => $this->t('warning'),
        'notice' => $this->t('notice'),
      ],
      '#default_value' => array_keys(array_filter($config->get('a11y.ignore'))),
    ];
    $form['html_auditor']['html5_errors_only'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('HTML5 audit errors only'),
      '#default_value' => $config->get('html5.errors_only'),
      '#prefix' => '<strong>HTML5 audit</strong>',
    ];
    $form['html_auditor']['link_report_verbose'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Verbose Link audit report'),
      '#default_value' => $config->get('link.report_verbose'),
      '#prefix' => '<strong>Link audit</strong>',
    ];
    $form['html_auditor']['run'] = [
      '#type' => 'submit',
      '#value' => $this->t('Perform audit'),
      '#disabled' => $disabled,
      '#submit' => ['::runAuditor'],
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->config('html_auditor.settings')
      ->set('sitemap.uri', $values['sitemap_uri'])
      ->set('sitemap.last_modified', $values['last_modified'])
      ->set('a11y.standard', $values['a11y_standard'])
      ->set('a11y.ignore', $values['a11y_ignore'])
      ->set('html5.errors_only', $values['html5_errors_only'])
      ->set('link.report_verbose', $values['link_report_verbose'])
      ->save();
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Get sitemap uri or path.
    $uri = $form_state->getValue('sitemap_uri');
    $parse_uri = parse_url($uri);
    if (!isset($parse_uri['scheme'], $parse_uri['host'])) {
      global $base_url;
    }

    if ($base_url) {
      $uri = sprintf('%s/%s', $base_url, ltrim($uri, '/'));
    }

    // Create http request and check whether the sitemap URI exists or not.
    $client = \Drupal::httpClient();
    try {
      $response = $client->get($uri);
    }
    catch (RequestException $e) {
      $form_state->setErrorByName('sitemap_uri', $this->t('Sitemap XML not found.<br><br>Error: ' . $e->getMessage()));
    }
  }

  /**
   * Runs HTML auditor.
   */
  public function runAuditor() {
    \Drupal::service('html_auditor')->run();
  }

}
