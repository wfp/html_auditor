<?php

/**
 * @file
 * Contains \Drupal\html_auditor\Controller\AuditorController.
 */

namespace Drupal\html_auditor\Controller;

use Drupal\Core\Controller\ControllerBase;

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
    $files = file_scan_directory($directory, '/[a-z0-9]+\-report.json$/', array(
      'callback' => function($file) {
	  		    debug(json_decode(file_get_contents($file)));
      } 
    ));
    $build = array(
      '#type' => 'markup',
      '#markup' => t('Hello World!'),
    );
    return $build;
  }

}

