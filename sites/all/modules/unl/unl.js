
(function ($) {

Drupal.behaviors.unl = {
  attach: function (context, settings) {
    if (!Drupal.settings.unl.use_base_tag) {
      return;
    }

    // Modify IMCE to return relative paths when a base tag is present.
    if (typeof(imce) !== 'undefined') {
      // Change IMCE base url to the current directory.
      Drupal.settings.imce.furl = ".";
      // Override IMCE getURL method to remove a trailing ./ from URLs.
      imce.realGetURL = imce.getURL;
      imce.getURL = function(fid) {
        var url = imce.realGetURL(fid);
        if (url.substr(0, 2) == './') {
          url = url.substr(2);
        }
        return url;
      };
    }

    // Make links ignore the base tag.
    $('a').click(function(e) {
      // If this link has a hash tag,
      if (!this.href.split('#')[1]) {
        return;
      }
     // and it is specifically for this page,
      if (this.getAttribute('href').split('#')[0] != '') {
        return;
      }
      // and something else hasn't already customized the link,
      if (e.isDefaultPrevented()) {
        return;
      }

      var newHash = this.href.split('#')[1];

      if (document.location.hash == '#'+newHash) {
          /**
           * Fix for chrome.
           * Avoid calling preventDefault() if there is no change in the hash
           * For example, if you load the page with #maincontent the skiplink would no longer work because
           * e.preventDefault() tells chrome to stop sending focus to #maincontent.
           * ¯\_(ツ)_/¯
           */
          return;
      }
      
      e.preventDefault();
      
      document.location.hash = newHash;
    });
  }
};

// Duplicates behavior of modules/node/node.js but with the addition of .children('label')
// so that the description is not shown in the vertical tab.
Drupal.behaviors.unlFieldsetSummaries = {
  attach: function (context) {
    if (typeof jQuery.fn.drupalSetSummary != 'undefined') {
      $('fieldset.node-form-options', context).drupalSetSummary(function (context) {
        var vals = [];

        $('input:checked', context).parent().each(function () {
          vals.push(Drupal.checkPlain($.trim($(this).children('label').text())));
        });

        if (!$('.form-item-status input', context).is(':checked')) {
          vals.unshift(Drupal.t('Not published'));
        }
        return vals.join(', ');
      });
    }
  }
};

})(jQuery);
