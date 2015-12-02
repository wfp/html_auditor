<?php

/**
 * @file
 * Contains \Drupal\html_auditor\Controller\AuditorController.
 */

namespace Drupal\html_auditor\Controller;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Extension\InfoParser;

class AuditorController extends ControllerBase {
  /**
   * {@inheritdoc}
   */
  public function report() {

    // Get config.
    $config = $this->config('html_auditor.settings');
    // Get report directory path.
    $directory = drupal_realpath(sprintf('public://%s', $config->get('sitemap.reports')));
    // Get files from report directory.
    $files = file_scan_directory($directory, '/[a-z0-9]+\-report.json$/');

    $report = [];

    foreach ($files as $file) {
      $json = file_get_contents($file->uri);
      $report_data = Json::decode($json);
      $report_data['name'] = $file->name;
      $report[] = $report_data;
    }

    debug($report);

    $build = [
      '#theme' => 'report',
      '#reports' => $report,
      '#name' => 'luka',
      '#attached' => [
        'library' => [
          'html_auditor/reporting-lib'
        ]
      ]
    ];

    return $build;
  }

}

