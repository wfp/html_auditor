# Installation

## 1. Install HTML Auditor

This project require the NodeJS module [HTML Auditor](https://github.com/wfp/node-html-auditor) to be installed. Following the modules [install instructions](https://github.com/wfp/node-html-auditor#installation).

## 2. Enable environment `PATH`

Ensure that web server has access to operating system `PATH`.

## 3. Install one of sitemap generator module.

  - <a href="https://www.drupal.org/project/xmlsitemap">XML sitemap</a>
  - <a href="https://www.drupal.org/project/simple_sitemap">Simple XML sitemap</a>

```
# PHP5 example using fpm
vim /etc/php5/fpm/pool.d/www.conf

# Uncomment the env[PATH]
env[PATH] = /usr/local/bin:/usr/bin:/bin
```

# Developer Guideline

```
# Install development dependencies
composer install

# Run PHP code audits
composer run phpcs

# Run PHP Copy-Paste detector
composer run phpcpd
```
