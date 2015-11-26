# html_auditor

## Write html auditor bash script in scripts/html_auditor.sh file.

### Example:
```
#!/bin/bash

html-fetch --uri https://www.xml-sitemaps.com/download/wfpnew.2264950/sitemap.xml --dir /home/lashab/wfp/sites/default/files/sitemaps \
&&
a11y-audit --path /home/lashab/wfp/sites/default/files/sitemaps --report /home/lashab/wfp/sites/default/files/reports --standard WCAG2AA \
&&
html5-audit --path /home/lashab/wfp/sites/default/files/sitemaps --report /home/lashab/wfp/sites/default/files/reports \
&&
link-audit --path /home/lashab/wfp/sites/default/files/sitemaps --report /home/lashab/wfp/sites/default/files/reports --base-uri http://wfpnew.picktek.org

```
## Configuring cron job

```
crontab -e 

* * * * * /{/FULL/PATH/TO/MODULES}/html_auditor/scripts/html_auditor.sh
```

Set cron <a href="https://www.drupal.org/node/23714">time</a>.

## TODO

Install <a href="https://www.drupal.org/project/xmlsitemap"XML sitemap module</a> when stable.
