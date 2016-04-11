/**
 * @file
 * Expandable messages toggle.
 */

(function() {
  'use strict';

  var reports = document.getElementsByClassName('is-expandable');
  for (var i in reports) {
    if (reports[i].nodeType) {
      var report = reports[i];
      report.addEventListener('click', function(e) {
        this.querySelector('.message-expand').classList.toggle('hide');
      });
    }
  }
})();
