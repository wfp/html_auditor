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
   * HTML fetch command name - html-fetch.
   */
  const HTML_AUDITOR_HTML_FETCH = 'html-fetch';

  /**
   * Accessibility audit command name - a11y-audit.
   */
  const HTML_AUDITOR_ACCESSIBILITY_AUDIT = 'a11y-audit';

  /**
   * HTML5 audit command name - html5-audit.
   */
  const HTML_AUDITOR_HTML5_AUDIT = 'html5-audit';

  /**
   * Link audit command name - link-audit.
   */
  const HTML_AUDITOR_LINK_AUDIT = 'link-audit';

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
    $files = $this->fileSystem->realpath((sprintf('public://%s', $config->get('sitemap.files'))));
    $report = $this->fileSystem->realpath((sprintf('public://%s', $config->get('sitemap.reports'))));
    // Build --ignore string for a11y.
    $ignore = implode(';', array_filter($config->get('a11y.ignore')));

    // Create new process for html-fetch.
    $process = new Process(sprintf('%s --uri %s --dir %s --map %s',
      self::HTML_AUDITOR_HTML_FETCH, $uri, $files, $report));
    // Get html-fetch logger.
    $log = $this->loggerFactory->get(self::HTML_AUDITOR_HTML_FETCH);
    try {
      // Run command.
      $process->mustRun();
      // Log success run.
      $log->info(sprintf(self::HTML_AUDITOR_SUCCESS_MESSAGE, self::HTML_AUDITOR_HTML_FETCH));
    }
    catch (ProcessFailedException $e) {
      // Log errors.
      $log->error($process->getErrorOutput());
    }

    // Create new process for a11y-audit.
    $process = new Process(sprintf('%s --path %s --report %s --standard %s --ignore %s',
      self::HTML_AUDITOR_ACCESSIBILITY_AUDIT, $files, $report, $config->get('a11y.standard'), "'$ignore'"));
    // Get a11y-audit logger.
    $log = $this->loggerFactory->get(self::HTML_AUDITOR_ACCESSIBILITY_AUDIT);
    try {
      // Run command.
      $process->mustRun();
      // Log success run.
      $log->info(sprintf(self::HTML_AUDITOR_SUCCESS_MESSAGE, self::HTML_AUDITOR_ACCESSIBILITY_AUDIT));
    }
    catch (ProcessFailedException $e) {
      // Log errors.
      $log->error($process->getErrorOutput());
    }

    // Create new process for html5-audit.
    $process = new Process(sprintf('%s --path %s --report %s --errors-only %d',
      self::HTML_AUDITOR_HTML5_AUDIT, $files, $report, $config->get('html5.errors_only')));
    // Get html5-audit logger.
    $log = $this->loggerFactory->get(self::HTML_AUDITOR_HTML5_AUDIT);
    try {
      // Run command.
      $process->mustRun();
      // Log success run.
      $log->info(sprintf(self::HTML_AUDITOR_SUCCESS_MESSAGE, self::HTML_AUDITOR_HTML5_AUDIT));
    }
    catch (ProcessFailedException $e) {
      // Log errors.
      $log->error($process->getErrorOutput());
    }

    // Create new process for link-audit.
    $process = new Process(sprintf('%s --path %s --report %s --report-verbose %d --base-uri %s',
      self::HTML_AUDITOR_LINK_AUDIT, $files, $report, $config->get('link.report_verbose'), $base_url));
    // Get link-audit logger.
    $log = $this->loggerFactory->get(self::HTML_AUDITOR_LINK_AUDIT);
    try {
      // Run command.
      $process->mustRun();
      // Log success run.
      $log->info(sprintf(self::HTML_AUDITOR_SUCCESS_MESSAGE, self::HTML_AUDITOR_LINK_AUDIT));
    }
    catch (ProcessFailedException $e) {
      // Log errors.
      $log->error($process->getErrorOutput());
    }
  }

}
