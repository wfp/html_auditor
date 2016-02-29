<?php

/**
 * @file
 * Contains \Drupal\html_auditor\AuditorExecute.
 */

namespace Drupal\html_auditor;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\File\FileSystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Execute HTML auditor node binaries.
 */
class AuditorExecute {

  /**
   * HTML audit command name - html-audit.
   */
  const HTML_AUDITOR_HTML_AUDIT = 'html-audit';

  /**
   * HTML fetch command name - fetch.
   */
  const HTML_AUDITOR_HTML_FETCH = 'fetch';

  /**
   * Accessibility audit command name - a11y.
   */
  const HTML_AUDITOR_ACCESSIBILITY_AUDIT = 'a11y';

  /**
   * HTML5 audit command name - html5.
   */
  const HTML_AUDITOR_HTML5_AUDIT = 'html5';

  /**
   * Link audit command name - link.
   */
  const HTML_AUDITOR_LINK_AUDIT = 'link';

  /**
   * Command success message.
   */
  const HTML_AUDITOR_SUCCESS_MESSAGE = '%s run successfully.';

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystem
   */
  protected $fileSystem;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The logger factory service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  protected $loggerFactory;

  /**
   * Constructs a AuditorExecute object.
   *
   * @param \Drupal\Core\File\FileSystem $file_system
   *   The file system service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   */
  public function __construct(FileSystem $file_system, ConfigFactoryInterface $config_factory, LoggerChannelFactory $logger_factroy) {
    $this->fileSystem = $file_system;
    $this->configFactory = $config_factory;
    $this->loggerFactory = $logger_factroy;
  }

  /**
   * Runs html auditor.
   *
   * Runs html-fetch, a11y-audit, html5-audit, link-audit node binaries.
   * Logs each executed bin success or error using logger.
   */
  public function run() {
    global $base_url;
    // Get html auditor configration.
    $config = $this->configFactory->get('html_auditor.settings');
    $uri = $config->get('sitemap.uri');
    $lastmod = $config->get('lastmod');
    $files = $this->fileSystem->realpath((sprintf('public://%s', $config->get('sitemap.files'))));
    $report = $this->fileSystem->realpath((sprintf('public://%s', $config->get('sitemap.reports'))));
    // Build --ignore string for a11y.
    $ignore = implode(';', array_filter($config->get('a11y.ignore')));
    $date = date_iso8601(time() - (int) $config->get('sitemap.last_modified') * 3600);
    // Create new process for html-fetch.
    $process = new Process(sprintf('%s %s --uri %s --dir %s --map %s/%s --lastmod %s',
      self::HTML_AUDITOR_HTML_AUDIT, self::HTML_AUDITOR_HTML_FETCH, $uri, $files, $report, 'map', $date));
    // Get html-fetch logger.
    $log = $this->loggerFactory->get(self::HTML_AUDITOR_HTML_AUDIT);
    try {
      // Success message.
      $message = sprintf(self::HTML_AUDITOR_SUCCESS_MESSAGE, self::HTML_AUDITOR_HTML_FETCH);
      $process->setTimeout(3600);
      // Run command.
      $process->mustRun();
      // Sets a success message to display to the user.
      drupal_set_message($message);
      // Log success run.
      $log->info($message);
    }
    catch (ProcessFailedException $e) {
      // Error message.
      $message = $e->getMessage();
      // Sets a error message to display to the user.
      drupal_set_message($message, 'error');
      // Log errors.
      $log->error($message);
    }

    // Create new process for a11y-audit.
    $process = new Process(sprintf('%s %s --path %s --report %s --standard %s --ignore %s --map %s/%s.json  --lastmod',
      self::HTML_AUDITOR_HTML_AUDIT, self::HTML_AUDITOR_ACCESSIBILITY_AUDIT, $files, $report, $config->get('a11y.standard'), "'$ignore'", $report, 'map'));
    try {
      // Success message.
      $message = sprintf(self::HTML_AUDITOR_SUCCESS_MESSAGE, self::HTML_AUDITOR_ACCESSIBILITY_AUDIT);
      $process->setTimeout(3600);
      // Run command.
      $process->mustRun();
      // Sets a success message to display to the user.
      drupal_set_message($message);
      // Log success run.
      $log->info($message);
    }
    catch (ProcessFailedException $e) {
      // Error message.
      $message = $e->getMessage();
      // Sets a error message to display to the user.
      drupal_set_message($message, 'error');
      // Log errors.
      $log->error($message);
    }

    // Create new process for html5-audit.
    $process = new Process(sprintf('%s %s --path %s --report %s --errors-only %d --map %s/%s.json --lastmod',
      self::HTML_AUDITOR_HTML_AUDIT, self::HTML_AUDITOR_HTML5_AUDIT, $files, $report, $config->get('html5.errors_only'), $report, 'map'));
    try {
      $message = sprintf(self::HTML_AUDITOR_SUCCESS_MESSAGE, self::HTML_AUDITOR_HTML5_AUDIT);
      $process->setTimeout(3600);
      // Run command.
      $process->mustRun();
      // Sets a success message to display to the user.
      drupal_set_message($message);
      // Log success run.
      $log->info($message);
    }
    catch (ProcessFailedException $e) {
      // Error message.
      $message = $e->getMessage();
      // Sets a error message to display to the user.
      drupal_set_message($message, 'error');
      // Log errors.
      $log->error($message);
    }

    // Create new process for link-audit.
    $process = new Process(sprintf('%s %s --path %s --report %s --report-verbose %d --base-uri %s --map %s/%s.json --lastmod',
      self::HTML_AUDITOR_HTML_AUDIT, self::HTML_AUDITOR_LINK_AUDIT, $files, $report, $config->get('link.report_verbose'), $base_url, $report, 'map'));
    // Get link-audit logger.
    $log = $this->loggerFactory->get(self::HTML_AUDITOR_HTML_AUDIT);
    try {
      // Success message.
      $message = sprintf(self::HTML_AUDITOR_SUCCESS_MESSAGE, self::HTML_AUDITOR_LINK_AUDIT);
      $process->setTimeout(3600);
      // Run command.
      $process->mustRun();
      // Sets a success message to display to the user.
      drupal_set_message($message);
      // Log success run.
      $log->info($message);
    }
    catch (ProcessFailedException $e) {
      // Error message.
      $message = $e->getMessage();
      // Sets a error message to display to the user.
      drupal_set_message($message, 'error');
      // Log errors.
      $log->error($message);
    }
  }

  /**
   * Runs test for html-audit.
   */
  public function runTest() {
    // Use OS level 'type' to test for presence of CLI tools.
    $process_audit = new Process('type ' . self::HTML_AUDITOR_HTML_AUDIT);

    try {
      $process_audit->mustRun();
    }
    catch (ProcessFailedException $e) {
      return $e->getMessage();
    }
  }

}
