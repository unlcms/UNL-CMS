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

