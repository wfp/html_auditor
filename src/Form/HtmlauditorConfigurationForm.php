<?php

/**
 * @file
 * Contains \Drupal\html_auditor\Form\HtmlauditorConfigurationForm.
 */

namespace Drupal\html_auditor\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form that configures html_auditor module settings.
 */
class HtmlauditorConfigurationForm extends ConfigFormBase {

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
    $form['sitemap_uri'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Path to XML sitemap'),
      '#default_value' => $config->get('sitemap.uri'),
      '#size' => 40,
    );
    $form['sitemap_files'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Downloaded HTML sitemaps directory name or path'),
      '#default_value' => $config->get('sitemap.files'),
      '#size' => 40,
    );
    $form['sitemap_reports'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Reported HTML sitemaps directory name or path'),
      '#default_value' => $config->get('sitemap.reports'),
      '#size' => 40,
    );
    $form['a11y_standard'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Accessibility standard'),
      '#options' => array(
        'WCAG2A' => 'WCAG2A',
        'WCAG2AA' => 'WCAG2AA',
        'WCAG2AAA' => 'WCAG2AAA',
      ),
      '#default_value' => $config->get('a11y.standard'),
    );
    $form['a11y_ignore'] = array(
      '#type' => 'checkboxes',
      '#title' => $this->t('Ignore accessibility levels'),
      '#options' => array(
        'notice' => $this->t('notice'),
        'warning' => $this->t('warning'),
        'error' => $this->t('error'),
      ),
      '#default_value' => array_keys(array_filter($config->get('a11y.ignore'))),
    );
    $form['html5_errors_only'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('HTML5 audit errors only'),
      '#default_value' => $config->get('html5.errors_only'),
    );
    $form['link_base_uri'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Link audit base URI'),
      '#default_value' => $config->get('link.base_uri'),
      '#size' => 40,
    );
    $form['link_report_verbose'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Link audit report verbose'),
      '#default_value' => $config->get('link.report_verbose'),
    );
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->config('html_auditor.settings')
      ->set('sitemap.uri', $values['sitemap_uri'])
      ->set('sitemap.files', $values['sitemap_files'])
      ->set('sitemap.reports', $values['sitemap_reports'])
      ->set('a11y.standard', $values['a11y_standard'])
      ->set('a11y.ignore', $values['a11y_ignore'])
      ->set('html5.errors_only', $values['html5_errors_only'])
      ->set('link.base_uri', $values['link_base_uri'])
      ->set('link.report_verbose', $values['link_report_verbose'])
      ->save();
  }

}