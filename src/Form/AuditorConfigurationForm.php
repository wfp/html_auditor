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
    $form['html_auditor']['sitemap_uri'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Path to XML sitemap'),
      '#default_value' => $config->get('sitemap.uri'),
      '#size' => 40,
      '#required' => TRUE,
    );
    $form['html_auditor']['sitemap_files'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Downloaded HTML sitemaps directory name or path'),
      '#default_value' => $config->get('sitemap.files'),
      '#size' => 40,
      '#required' => TRUE,
    );
    $form['html_auditor']['sitemap_reports'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Reported HTML sitemaps directory name or path'),
      '#default_value' => $config->get('sitemap.reports'),
      '#size' => 40,
      '#required' => TRUE,
    );
    $form['html_auditor']['last_modified'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Last modified date e.g: (2015-09-01T12:00:00+00:00 or 2015-09-01)'),
      '#default_value'	=>	$config->get('sitemap.last_modified'),
      '#size' => 40,
      '#required' => TRUE,
    );
    $form['html_auditor']['a11y_standard'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Accessibility standard'),
      '#options' => array(
        'WCAG2A' => $this->t('WCAG2A'),
        'WCAG2AA' => $this->t('WCAG2AA'),
        'WCAG2AAA' => $this->t('WCAG2AAA'),
      ),
      '#default_value' => $config->get('a11y.standard'),
      '#required' => TRUE,
    );
    $form['html_auditor']['a11y_ignore'] = array(
      '#type' => 'checkboxes',
      '#title' => $this->t('Ignore accessibility levels'),
      '#options' => array(
        'notice' => $this->t('notice'),
        'warning' => $this->t('warning'),
        'error' => $this->t('error'),
      ),
      '#default_value' => array_keys(array_filter($config->get('a11y.ignore'))),
    );
    $form['html_auditor']['html5_errors_only'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('HTML5 audit errors only'),
      '#default_value' => $config->get('html5.errors_only'),
    );
    $form['html_auditor']['link_report_verbose'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Link audit report verbose'),
      '#default_value' => $config->get('link.report_verbose'),
    );
    $form['html_auditor']['run'] = [
      '#type' => 'submit',
      '#value' => $this->t('Perform audit'),
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
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $sitemaps_uri = $form_state->getValue('sitemap_uri');
    if (!UrlHelper::isValid($sitemaps_uri, TRUE)) {
      $form_state->setErrorByName('sitemap_uri', $this->t('URL is not valid.'));
    }
  }

  /**
   * Runs HTML auditor.
   */
  public function runAuditor() {
    \Drupal::service('html_auditor')->run();
  }

}
