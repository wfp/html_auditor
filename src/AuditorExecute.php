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
   * Command warning message.
   */
  const HTML_AUDITOR_WARNING_MESSAGE = 'You should install one of <a href="https://www.drupal.org/project/xmlsitemap">XML sitemap</a> or <a href="https://www.drupal.org/project/simple_sitemap">Simple XML sitemap</a> to generate an sitemap.xml file in order to fully take advantage of HTML Auditor.';

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
   * Check whether the html auditor node tools are installed or not.
   */
  public function isHtmlAuditorEnabled() {
    // Use OS level 'type' to test for presence of CLI tools.
    $process_audit = new Process('type ' . self::HTML_AUDITOR_HTML_AUDIT);
    try {
      $process_audit->mustRun();
    }
    catch (ProcessFailedException $e) {
      return $e->getMessage();
    }
  }

  /**
   * Check whether the sitemap module is enabled or not.
   */
  public function isSitemapEnabled() {
    return \Drupal::moduleHandler()->moduleExists('simplesitemap') || \Drupal::moduleHandler()->moduleExists('xmlsitemap');
  }

  /**
   * Execute process.
   *
   * @param string $command
   *   Command string.
   * @param $type
   *   Command type.
   */
  public function process_execute($command, $type) {
    // Create new process.
    $process = new Process($command);
    // Get html-audit logger.
    $log = $this->loggerFactory->get(self::HTML_AUDITOR_HTML_AUDIT);
    try {
      // Success message.
      $message = $type . ' run successfully.';
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
   * Runs html auditor.
   *
   * Runs html-audit fetch, html-audit a11y, html-audit html5, html-audit link node commands.
   */
  public function run() {
    global $base_url;
    // Check for sitemap modules.
    if (!$this->isSitemapEnabled()) {
      return drupal_set_message(t(self::HTML_AUDITOR_WARNING_MESSAGE), 'warning');
    }

    // Get html auditor configration.
    $config = $this->configFactory->get('html_auditor.settings');
    $files = $this->fileSystem->realpath((sprintf('public://%s', $config->get('sitemap.files'))));
    $report = $this->fileSystem->realpath((sprintf('public://%s', $config->get('sitemap.reports'))));
    // Get sitemap uri.
    $uri = $config->get('sitemap.uri');
    $parse_uri = parse_url($uri);
    if (!isset($parse_uri['scheme'], $parse_uri['host'])) {
      $uri = $base_url . '/' . ltrim($parse_uri['path'], '/');
    }

    // Get lastmod.
    $lastmod = $config->get('lastmod');
    // Build --ignore argument for a11y.;
    $ignore = implode(';', array_keys(array_filter($config->get('a11y.ignore'), function($value){
      return $value === 0;
    }, ARRAY_FILTER_USE_BOTH)));
    // Set date.
    $date = date_iso8601(time() - (int) $config->get('sitemap.last_modified') * 3600);
    // Execute fetch html.
    $this->process_execute(sprintf('%s %s --uri %s --dir %s --map %s/%s --lastmod %s',
      self::HTML_AUDITOR_HTML_AUDIT, self::HTML_AUDITOR_HTML_FETCH, $uri, $files, $report, 'map', $date), self::HTML_AUDITOR_HTML_FETCH);
    // Execute a11y audit.
    $this->process_execute(sprintf('%s %s --path %s --report %s --standard %s --ignore %s --map %s/%s.json  --lastmod',
      self::HTML_AUDITOR_HTML_AUDIT, self::HTML_AUDITOR_ACCESSIBILITY_AUDIT, $files, $report, $config->get('a11y.standard'), "'$ignore'", $report, 'map'), self::HTML_AUDITOR_ACCESSIBILITY_AUDIT);
    // Execute html5 audit.
    $this->process_execute(sprintf('%s %s --path %s --report %s --errors-only %d --map %s/%s.json --lastmod',
      self::HTML_AUDITOR_HTML_AUDIT, self::HTML_AUDITOR_HTML5_AUDIT, $files, $report, $config->get('html5.errors_only'), $report, 'map'), self::HTML_AUDITOR_HTML5_AUDIT);
    // Execute link audit.
    $this->process_execute(sprintf('%s %s --path %s --report %s --report-verbose %d --base-uri %s --map %s/%s.json --lastmod',
        self::HTML_AUDITOR_HTML_AUDIT, self::HTML_AUDITOR_LINK_AUDIT, $files, $report, $config->get('link.report_verbose'), $base_url, $report, 'map'), self::HTML_AUDITOR_LINK_AUDIT);
  }

}
