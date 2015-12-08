<?php

/**
 * @file
 * Contains \Drupal\html_auditor\Form\AuditorFilterForm.
 */

namespace Drupal\html_auditor\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Provides the html reports filter form.
 */
class AuditorFilterForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'html_auditor_reports_filter_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Get session.
    $session = isset($_SESSION['html_auditor_reports_filter']) ? $_SESSION['html_auditor_reports_filter'] : [];
    // Build form.
    $form['filter'] = array(
      '#type' => 'details',
      '#title' => $this->t('Filter reports'),
      '#open' => !empty($session),
    );
    $form['filter']['status']['type'] = [
      '#title' => t('Type'),
      '#type' => 'select',
      '#multiple' => TRUE,
      '#size' => 8,
      '#options' => [
        'assessibility' => 'Assessibility',
        'html5' => 'HTML5',
        'link' => 'Link',
      ],
    ];
    if (isset($session['type'])) {
      $form['filter']['status']['type']['#default_value'] = $session['type'];
    }
    $form['filter']['status']['level'] = [
      '#title' => t('Level'),
      '#type' => 'select',
      '#multiple' => TRUE,
      '#size' => 8,
      '#options' => [
        'notice' => $this->t('Notice'),
        'warning' => $this->t('Warning'),
        'error' => $this->t('Error'),
      ],
    ];
    if (isset($session['level'])) {
      $form['filter']['status']['level']['#default_value'] = $session['level'];
    }
    $form['filter']['actions'] = array(
      '#type' => 'actions',
    );
    $form['filter']['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Filter'),
    ];
    if (!empty($session)) {
      $form['filter']['actions']['reset'] = [
        '#type' => 'submit',
        '#value' => $this->t('Reset'),
        '#limit_validation_errors' => [],
        '#submit' => ['::resetForm'],
      ];
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->isValueEmpty('type') && $form_state->isValueEmpty('level')) {
      $form_state->setErrorByName('type', $this->t('You must select something to filter by.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $_SESSION['html_auditor_reports_filter'] = [
      'type' => $form_state->getValue('type'),
      'level' => $form_state->getValue('level'),
    ];
  }

  /**
   * Resets the filter form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function resetForm(array &$form, FormStateInterface $form_state) {
    $_SESSION['html_auditor_reports_filter'] = array();
  }

}
