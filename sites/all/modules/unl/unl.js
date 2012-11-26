/**
 * On IMCE pages, modify IMCE to return relative paths when a base tag is being used.
 */
jQuery('document').ready(function() {
	// If this isn't an IMCE page, we don't need to do anything.
	if (typeof(imce) == 'undefined') {
		return;
	}
	// If we aren't using a base tag, we don't need to do anything.
	if (!Drupal.settings.unl.use_base_tag) {
		return;
	}
	// Change imce's base url to the current directory.
	Drupal.settings.imce.furl = ".";
	// Override imce's getURL method to remove a trailing ./ from URLs.
	imce.realGetURL = imce.getURL;
	imce.getURL = function(fid) {
		var url = imce.realGetURL(fid);
		if (url.substr(0, 2) == './') {
			url = url.substr(2);
		}
		return url;
	};
});

jQuery('document').ready(function() {
	// if there's no base tag on the page, we don't have to worry about this
	if (jQuery('base').length == 0) {
		return;
	}
	jQuery('a').click(function(e) {
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
