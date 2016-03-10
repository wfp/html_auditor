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
    $sitemap_disabled = FALSE;
    $service = \Drupal::service('html_auditor');
    if (!$service->isSitemapEnabled()) {
      drupal_set_message(t($service::HTML_AUDITOR_WARNING_MESSAGE), 'warning');
      $sitemap_disabled = TRUE;
    }
    $form['html_auditor']['sitemap_uri'] = [
      '#type' => 'textfield',
      '#title' => $this->t('XML Sitemap file path or URL'),
      '#description' => $this->t('Enter a file path such as <em>/sitemap.xml</em> or a URL such as <em>http://example.com/sitemap.xml</em>'),
      '#default_value' => $config->get('sitemap.uri'),
      '#size' => 40,
      '#required' => TRUE,
    ];
    $form['html_auditor']['sitemap_files'] = [
      '#type' => 'textfield',
      '#title' => $this->t('HTML download directory'),
      '#description' => $this->t('Sub-directory that HTML pages are download into in the <em>files/</em> directory'),
      '#default_value' => $config->get('sitemap.files'),
      '#size' => 40,
      '#required' => TRUE,
    ];
    $form['html_auditor']['sitemap_reports'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Report download directory'),
      '#description' => $this->t('Sub-directory where reports are generated in the <em>files/</em> directory'),
      '#default_value' => $config->get('sitemap.reports'),
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
      '#disabled' => $sitemap_disabled,
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
      ->set('sitemap.files', Xss::filter($values['sitemap_files']))
      ->set('sitemap.reports', Xss::filter($values['sitemap_reports']))
      ->set('sitemap.last_modified', $values['last_modified'])
      ->set('a11y.standard', $values['a11y_standard'])
      ->set('a11y.ignore', $values['a11y_ignore'])
      ->set('html5.errors_only', $values['html5_errors_only'])
      ->set('link.report_verbose', $values['link_report_verbose'])
      ->save();
  }

  /**
   * Runs HTML auditor.
   */
  public function runAuditor() {
    \Drupal::service('html_auditor')->run();
  }

}
