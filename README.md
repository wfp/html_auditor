# Installation

## 1. Install HTML Auditor

This project require the NodeJS module [HTML Auditor](https://github.com/wfp/node-html-auditor) to be installed. Following the modules [install instructions](https://github.com/wfp/node-html-auditor#installation).

## 2. Enable environment `PATH`

Ensure that web server has access to operating system `PATH`.

```
# PHP5 using PHP-FPM
vim /etc/php5/fpm/pool.d/www.conf
# PHP7 using PHP-FPM
vim /etc/php/7.0/fpm/pool.d/www.conf

# Uncomment the env[PATH]
env[PATH] = /usr/local/bin:/usr/bin:/bin

# If needed, append install path of html-audit to env[PATH]
which html-audit
```

## 3. Install one of the sitemap generator module

  - <a href="https://www.drupal.org/project/xmlsitemap">XML sitemap</a>
  - <a href="https://www.drupal.org/project/simple_sitemap">Simple XML sitemap</a>

# Developer Guideline

```
# Install development dependencies
composer install

# Run PHP code audits
composer run phpcs

# Run PHP Copy-Paste detector
composer run phpcpd
```
