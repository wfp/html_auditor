<?php

/**
 * @file
 * Contains \Drupal\html_auditor\Controller\AuditorController.
 */

namespace Drupal\html_auditor\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\File\FileSystem;
use Drupal\Core\Url;
use Drupal\Component\Serialization\Json;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;

/**
 * Returns responses for html_auditor module routes.
 */
class AuditorController extends ControllerBase {

  /**
   * Reports limit per page.
   */
  const REPORTS_MAX_LENGTH = 25;

  /**
   * Regular expression for matching report files.
   */
  const REPORT_FILES_REGEX = '/[a-z0-9]+\-report.json$/';

  /**
   * The form builder service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystem
   */
  protected $fileSystem;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder'),
      $container->get('file_system')
    );
  }

  /**
   * Constructs a AuditorController object.
   *
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder service.
   * @param \Drupal\Core\File\FileSystem $file_system
   *   The file system service.
   */
  public function __construct(FormBuilderInterface $form_builder, FileSystem $file_system) {
    $this->formBuilder = $form_builder;
    $this->fileSystem = $file_system;
  }

  /**
   * Filters the reports by type or level.
   *
   * @param array $reports
   *   Reports data.
   * @param string $type
   *   Type of filter.
   *
   * @return object
   *   Current object.
   */
  private function reportsFilter(array &$reports, $type) {
    // Filter by type or level.
    if (!empty($_SESSION['html_auditor_reports_filter'][$type])) {
      $reports = array_filter($reports, function($report) use ($type) {
        $types = $_SESSION['html_auditor_reports_filter'][$type];
        return in_array($report[$type], $types);
      });
    }

    return $this;
  }

  /**
   * Sort reports.
   *
   * @param array $reports
   *   Reports data.
   *
   * @return object
   *   Current object.
   */
  private function reportsSortable(&$reports) {
    // Sort reports.
    if (isset($reports)) {
      $type = \Drupal::request()->query->get('order', '');
      $sort = \Drupal::request()->query->get('sort', '');
      usort($reports, function($prev, $next) use ($type) {
        if (isset($prev[$type], $next[$type])) {
          return strcmp($prev[$type], $next[$type]);
        }
      });
      if ($sort === 'desc') {
        $reports = array_reverse($reports);
      }
    }

    return $this;
  }

  /**
   * Display reports.
   *
   * Renders reports filter form.
   * Renders reports table.
   * Renders reports pager.
   *
   * @param array $rows
   *   Rows of reports.
   *
   * @return array
   *   HTML reports structured array tree.
   */
  private function reportsDisplay($rows) {
    // Render reports filter form.
    $build['reports_filter'] = $this->formBuilder->getForm('Drupal\html_auditor\Form\AuditorFilterForm');
    // Render reports table.
    $build['reports_table'] = [
      '#theme' => 'table',
      '#header' => [
        ['data' => $this->t('url'), 'field' => 'Url'],
        ['data' => $this->t('type'), 'field' => 'Type'],
        ['data' => $this->t('level'), 'field' => 'Level'],
        $this->t('Message'),
      ],
      '#rows' => $rows,
      '#empty' => $this->t('There are no HTML audit reports to display.'),
      '#attached' => [
        'library' => [
          'html_auditor/report',
        ],
      ],
    ];
    // Render pager.
    $build['reports_pager'] = [
      '#type' => 'pager',
    ];

    return $build;
  }

  /**
   * Displays a listing of HTML reports.
   *
   * Ten reports are available per page.
   * Reports fields are sortable.
   *
   * @return array
   *   A render array as expected by drupal_render().
   */
  public function reportsPage() {
    $reports = [];
    // Get reports directory.
    $directory = $this->fileSystem->realpath('public://') . '/html_auditor/reports';
    // Get report files.
    $files = file_scan_directory($directory, self::REPORT_FILES_REGEX);
    // Display empty message when report files don't exits.
    if (!$files) {
      return $this->reportsDisplay([]);
    }

    $maps = [];
    // New Finder instance.
    $report_files = new Finder();
    // New Finder instance.
    $report_map = new Finder();
    // Get map.json content.
    $report_map->files()->in($directory)->name('map.json');
    foreach ($report_map as $map) {
      $maps = Json::decode($map->getContents());
    }

    // Get JSON content from files.
    $report_files->files()->in($directory)->name(self::REPORT_FILES_REGEX);
    foreach ($report_files as $file) {
      // Get data as an object.
      $contents = (object) Json::decode($file->getContents());
      foreach ($contents as $type => $content) {
        foreach ($content as $file => $data) {
          foreach ($data as $report) {
            // Get uri from map.json.
            $uri = $maps['uris'][$this->fileSystem->basename($file)];
            $uri_parse = parse_url($uri);
            if ($type === 'assessibility' || $type === 'html5') {
              // Extract a11y data.
              // Extract html5 data.
              $reports[] = [
                'file' => $this->l($uri_parse['path'], Url::fromUri($uri)),
                'type' => $type,
                'level' => $this->t($report['type']),
                'message' => $this->t((string) $report['message']),
              ];
            }
            elseif ($type === 'link') {
              // Extract link data.
              $reports[] = [
                'file' => $this->l($uri_parse['path'], Url::fromUri($uri)),
                'type' => $type,
                'level' => $this->t('error'),
                'message' => $this->t((string) $report['error']),
              ];
            }
          }
        }
      }
    }

    // Filter by type or / and level.
    $this->reportsFilter($reports, 'type')->reportsFilter($reports, 'level');
    // Get reports count.
    $reports_length = count($reports);
    // Initialize pager.
    pager_default_initialize($reports_length, self::REPORTS_MAX_LENGTH);
    // Chunk reports array.
    $reports = array_chunk($reports, self::REPORTS_MAX_LENGTH);
    // Get page id.
    $page = pager_find_page();
    // Make the reports sortable.
    $this->reportsSortable($reports[$page]);
    // Table rows.
    $rows = [];
    if (isset($page, $reports[$page])) {
      $rows = $reports[$page];
    }

    return $this->reportsDisplay($rows);
  }

}
