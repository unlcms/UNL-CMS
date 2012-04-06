WDN.loadJQuery(function () {
  // if there's no base tag on the page, we don't have to worry about this
  if (WDN.jQuery('base').length == 0) {
    return;
  }
  WDN.jQuery('a').click(function(e) {
    // if this link has a hash tag
    if (!this.href.split('#')[1]) {
      return;
    }
    // and it is specifically for this page
    if (this.getAttribute('href').split('#')[0] != '') {
      return;
    }
    // and something else hasn't already customized the link
    if (e.isDefaultPrevented()) {
      return;
    }

    // fix clicking the link so that it ignores the base tag
    e.preventDefault();
    document.location.hash = this.href.split('#')[1];
  });

});