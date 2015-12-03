<?php

/**
 * @file
 * Contains \Drupal\html_auditor\Controller\AuditorController.
 */

namespace Drupal\html_auditor\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Component\Serialization\Json;
use Symfony\Component\Finder\Finder;

class AuditorController extends ControllerBase {

  /**
   * Reports rows data for theme table.
   *
   * @var \Drupal\html_auditor\Controller\AuditorController
   */
  private $reports = [];

  /**
   * Get reports directory.
   *
   * @return string
   *   Full path to the reports directory.
   */
  private function getReportsDirectory() {
    // Get html_auditor.settings config.
    $config = $this->config('html_auditor.settings');
    return \Drupal::service('file_system')->realpath(sprintf('public://%s', $config->get('sitemap.reports')));
  }

  /**
   * Get basename.
   *
   * @param string $file
   *   File with full path.
   * @return string
   *   File basename.
   */
  private function getFileBasename($file) {
    return \Drupal::service('file_system')->basename($file);
  }

  /**
   * {@inheritdoc}
   */
  public function report() {
    // Get JSON content from files.
    $finder = new Finder();
    $finder->files()->in($this->getReportsDirectory());
    foreach ($finder as $file) {
      // Get data as an object.
      $contents = (object) Json::decode($file->getContents());
      foreach ($contents as $type => $content) {
        switch ($type) {
          // Extract a11y data.
          case 'assessibility':
            foreach ($content as $file => $data) {
              foreach ($data as $report) {
                $this->reports[] = [
                  $this->getFileBasename($file),
                  $type,
                  $this->t($report['type']),
                  $this->t($report['message']),
                ];
              }
            }
          break;
          // Extract html5 data.
          case 'html5':
            foreach ($content as $file => $data) {
              foreach ($data as $report) {
                $this->reports[] = [
                  $this->getFileBasename($file),
                  $type,
                  $this->t($report['type']),
                  $this->t($report['message']),
                ];
              }
            }
          break;
          // Extract link data.
          case 'link':
            foreach ($content as $file => $data) {
              foreach ($data as $report) {
                $this->reports[] = [
                  $this->getFileBasename($file),
                  $type,
                  $this->t('error'),
                  $this->t($report['error']),
                ];
              }
            }
          break;
        }
      }
    }
    // Push report data in template.
    $build = [
      '#theme' => 'table',
      '#header' => [
        $this->t('Filename'),
        $this->t('Type'),
        $this->t('Level'),
        $this->t('Message'),
      ],
      '#rows' => $this->reports,
      '#attached' => [
        'library' => [
          'html_auditor/report'
        ]
      ]
    ];
    return $build;
  }

}

