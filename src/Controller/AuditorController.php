<?php

/**
 * @file
 * Contains \Drupal\html_auditor\Controller\AuditorController.
 */

namespace Drupal\html_auditor\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Component\Serialization\Json;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;

class AuditorController extends ControllerBase {

  /**
   * Reports limit per page.
   */
  const REPORTS_MAX_LENGTH = 10;

  /**
   * The form builder service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder')
    );
  }

  /**
   * Constructs a AuditorController object.
   *
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder service.
   */
  public function __construct(FormBuilderInterface $form_builder) {
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public function report() {
    $reports = [];
    // Get configs.
    $config = $this->config('html_auditor.settings');
    // Get finder service.
    $finder = \Drupal::service('html_auditor.finder');
    // Get reports directory.
    $directory = \Drupal::service('file_system')->realpath(sprintf('public://%s', $config->get('sitemap.reports')));
    // Get JSON content from files.
    $finder->files()->in($directory);
    foreach ($finder as $file) {
      // Get data as an object.
      $contents = (object) Json::decode($file->getContents());
      foreach ($contents as $type => $content) {
        switch ($type) {
          // Extract a11y data.
          case 'assessibility':
            foreach ($content as $file => $data) {
              foreach ($data as $report) {
                $reports[] = [
                  'file' => \Drupal::service('file_system')->basename($file),
                  'type' => $type,
                  'level' => $this->t($report['type']),
                  'message' => $this->t($report['message']),
                ];
              }
            }
          break;
          // Extract html5 data.
          case 'html5':
            foreach ($content as $file => $data) {
              foreach ($data as $report) {
                $reports[] = [
                 'file' => \Drupal::service('file_system')->basename($file),
                 'type' => $type,
                 'level' => $this->t($report['type']),
                 'message' => $this->t($report['message']),
                ];
              }
            }
          break;
          // Extract link data.
          case 'link':
            foreach ($content as $file => $data) {
              foreach ($data as $report) {
                $reports[] = [
                 'file' => \Drupal::service('file_system')->basename($file),
                 'type' => $type,
                 'level' => $this->t('error'),
                 'message' => $this->t($report['error']),
                ];
              }
            }
          break;
        }
      }
    }
    // Filter by type.
    if (!empty($_SESSION['html_auditor_reports_filter']['type'])) {
      $reports = array_filter($reports, function($report) {
        $types = $_SESSION['html_auditor_reports_filter']['type'];
        return in_array($report['type'], $types);
      });
    }
    // Filter by error levels.
    if (!empty($_SESSION['html_auditor_reports_filter']['level'])) {
      $reports = array_filter($reports, function($report) {
        $error_levels = $_SESSION['html_auditor_reports_filter']['level'];
        return in_array($report['level'], $error_levels);
      });
    }
    // Get reports count.
    $reports_length = count($reports);
    // Get page id.
    $page = pager_find_page();
    // Initialize pager.
    pager_default_initialize($reports_length, self::REPORTS_MAX_LENGTH);
    // Chunk reports array.
    $reports = array_chunk($reports, self::REPORTS_MAX_LENGTH);
    // Get reports filter form.
    $build['reports_filter'] = $this->formBuilder->getForm('Drupal\html_auditor\Form\AuditorFilterForm');
    // Get reports.
    $build['reports'] = [
      '#theme' => 'table',
      '#header' => [
        $this->t('Filename'),
        $this->t('Type'),
        $this->t('Level'),
        $this->t('Message'),
      ],
      '#rows' => isset($reports[$page]) ? $reports[$page] : [],
      '#attached' => [
        'library' => [
          'html_auditor/report'
        ]
      ]
    ];
    $build['reports_pager'] = [
      '#type' => 'pager',
    ];
    return $build;
  }

}

