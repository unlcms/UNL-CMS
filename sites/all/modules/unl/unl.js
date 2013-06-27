
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

      e.preventDefault();
      document.location.hash = this.href.split('#')[1];
    });
  }
};

})(jQuery);
