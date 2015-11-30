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
    $form['sitemap_xml_uri'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Path to XML sitemap'),
      '#default_value' => $config->get('sitemap_xml_uri'),
      '#size' => 40,
    );
    $form['sitemaps_files_path'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Path to downloaded HTML pages'),
      '#default_value' => $config->get('sitemaps_files_path'),
      '#size' => 40,
    );
    $form['accessibility_standard'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Accessibility standard'),
      '#options' => array(
        'WCAG2A' => 'WCAG2A',
        'WCAG2AA' => 'WCAG2AA',
        'WCAG2AAA' => 'WCAG2AAA',
      ),
      '#default_value' => $config->get('a11y.standard'),
    );
    $form['accessibility_levels'] = array(
      '#type' => 'checkboxes',
      '#title' => $this->t('Ignore Accessibility levels'),
      '#options' => array(
        'notice' => $this->t('notice'),
        'warning' => $this->t('warning'),
        'error' => $this->t('error'),
      ),
      '#default_value' => array_keys(array_filter($config->get('a11y.ignore'))),
    );
    $form['html5_errors_only'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('HTML5 errors only'),
      '#default_value' => $config->get('html5.errors_only'),
    );
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->config('html_auditor.settings')
      ->set('sitemap_xml_uri', $values['sitemap_xml_uri'])
      ->set('sitemaps_files_path', $values['sitemaps_files_path'])
      ->set('a11y.standard', $values['accessibility_standard'])
      ->set('a11y.ignore', $values['accessibility_levels'])
      ->set('html5.errors_only', $values['html5_errors_only'])
      ->save();
  }

}