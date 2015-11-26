#!/bin/bash

html-fetch --uri https://www.xml-sitemaps.com/download/wfpnew.2264950/sitemap.xml --dir /home/lashab/wfp/sites/default/files/sitemaps \
&&
a11y-audit --path /home/lashab/wfp/sites/default/files/sitemaps --report /home/lashab/wfp/sites/default/files/reports --standard WCAG2AA \
&&
html5-audit --path /home/lashab/wfp/sites/default/files/sitemaps --report /home/lashab/wfp/sites/default/files/reports \
&&
link-audit --path /home/lashab/wfp/sites/default/files/sitemaps --report /home/lashab/wfp/sites/default/files/reports --base-uri http://wfpnew.picktek.org

