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
   * Reports directory path.
   *
   * @var \Drupal\html_auditor\Controller\AuditorController
   */
  private static $directory;
  /**
   * Constructs a AuditorController object.
   */
  public function __construct() {
    // Get config.
    $config = $this->config('html_auditor.settings');
    // Get report directory path.
    self::$directory = drupal_realpath(sprintf('public://%s', $config->get('sitemap.reports')));
  }
  /**
   * {@inheritdoc}
   */
  public function report() {
    // Get JSON content from files.
    $finder = new Finder();
    $finder->files()->in(self::$directory);
    foreach ($finder as $file) {
      $contents = (object) Json::decode($file->getContents());
      // Extract link data.
      if (isset($contents->link)) {
        foreach ($contents->link as $file => $content) {
          foreach ($content as $data) {
            $this->reports[] = [
              $data['error'],
            ];
          }
        }
      }
      // Extract a11y data.
      if (isset($contents->a11y)) {
        foreach ($contents->a11y as $file => $content) {
          foreach ($content as $data) {
            $this->reports[] = [
              $data['message'],
            ];
          }
        }
      }
      // Extract html5 data.
      if (isset($contents->html5)) {
        foreach ($contents->html5 as $key => $content) {
          $this->reports[] = [
            $content['message'],
          ];
        }
      }
    }
    // Push report data in template.
    $build = [
      '#theme' => 'table',
      '#header' => [
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

