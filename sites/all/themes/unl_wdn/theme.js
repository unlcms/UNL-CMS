WDN.jQuery(document).ready(function () {
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
	
	// checking using ajax if user is logged in. then the technical feedback div is shown
	var userLoggedIn = '';
	
	WDN.jQuery.ajax({
		url: "user/unl/whoami",
		dataType: "text",
		success: function(data) {
			userLoggedIn = String(data);
			
			if (userLoggedIn =='user_loggedin') {
				var technicalFeedbackHtml = WDN.jQuery.ajax({
					url: "user/unl/technical_feedback",
					dataType: "html",
					success: function(data) {
						
						var technicalFeedback = '<a id="technicalFeedbackLink">Found a bug? Report any issue with the cms (like when editing docs, uploading etc.) or give feedback</a>';
						technicalFeedback += '<div id="technicalFeedbackForm"></div>';
						
						WDN.jQuery("#footer>div:nth-child(2)").append(technicalFeedback);
						
						WDN.jQuery("#technicalFeedbackLink").click(function() {
							WDN.jQuery("#technicalFeedbackForm").append(data);
						});
					}
				});
			} // end of if userLoggedIn == 'user_loggedin'
		} // end of success: function(data)
	});
});