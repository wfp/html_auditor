<?php
/**
 * @file
 * Contains \Drupal\html_auditor\Controller\HtmlAuditorController.
 */

namespace Drupal\html_auditor\Controller;

use Drupal\Core\Controller\ControllerBase;
use \Symfony\Component\HttpFoundation\Request;

class HtmlAuditorController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function content(Request $request) {
    $output = array();
    exec('/usr/bin/link-audit --path modules/custom/html_auditor/index.html --report modules/custom/html_auditor/report/ --base-uri http://www.wfp.org', $output);
    print '<pre>';
    print_r($output);
    exit;

    $user = $request->get('user');

    $build = array(
      '#type' => 'markup',
      '#markup' => $user,
    );
    return $build;
  }

}