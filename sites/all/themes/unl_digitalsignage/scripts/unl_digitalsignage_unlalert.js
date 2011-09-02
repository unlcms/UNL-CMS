/* Constructor */
var unlAlerts = function() {};

UNL.digitalSignage.unlalert = (function() {
	var activeIds = [], calltimeout, pulseTimeout;
	
	var data_url = 'http://alert1.unl.edu/json/unlcap.js';
	//var data_url = 'http://ucommrasmussen.unl.edu/unlcap.js';
	
	return {
		
		init : function() {
			console.log('Initializing the UNL Alert Plugin');
			if ("https:" != document.location.protocol) {
				// Don't break authenticated sessions
				UNL.digitalSignage.unlalert._callServer();
			}
		},
		
		dataReceived: function() {
			console.log('UNL Alert data received');
			clearTimeout(calltimeout);
			calltimeout = setTimeout(UNL.digitalSignage.unlalert._callServer, 30000);
		},
		
		_callServer: function() {
			console.log('Checking the alert server at '+data_url);
			var head = document.getElementsByTagName('head').item(0);
			var old  = document.getElementById('lastLoadedCmds');
			if (old) {
				head.removeChild(old);
			}
			var currdate = new Date();
			script = document.createElement('script');
			script.src = data_url+'?'+currdate.getTime();
			script.type = 'text/javascript';
			script.defer = true;
			script.id = 'lastLoadedCmds';
			head.appendChild(script);
		},
		
		alertUser: function(root) {
			if (jQuery('#unlalert-wrapper').length == false) {
				// Pause the video
				try {
					document.getElementById('unl-digitalsignage-video').pause();
				} catch (e) {}
				// Insert the alert div
				jQuery('body').prepend('<div id="unlalert-wrapper">'+
										'<div id="unlalert-sent"></div>'+
										'<div id="unlalert-desc">'+root.info.description+'</div>'+
										'<div id="unlalert-contact">University Police:<br />402-472-2222 or 911</div>'+
										'<div id="unlalert-bg-1"></div>'+
										'<div id="unlalert-bg-2"></div>'+
										'</div>');
				UNL.digitalSignage.unlalert.pulseStart();
			}
			jQuery('#unlalert-desc').html(root.info.description);
			jQuery('#unlalert-sent').html(UNL.digitalSignage.unlalert.formatDate(root.sent));
		},
		
		closeAlert: function() {
			try {
				document.getElementById('unl-digitalsignage-video').play();
			} catch (e) {}
			if (jQuery('#unlalert-wrapper')) {
				jQuery('#unlalert-wrapper').remove();
			}
			cleartimeout(pulseTimeout);
			// Start the video
		},
		
		formatDate : function(date) {
			// Parse date from feed
			var d = new Date(Date.parse(date));
			var h = d.getHours();
			var m = d.getMinutes();
			var dd = "AM";
			if (h >= 12) {
				h = h-12;
				dd = "PM";
			}
			if (h == 0) {
				h = 12;
			}
			m = m<10?"0"+m:m;
			
			// Current time
			var cur = new Date();
			
			// Difference between two
			var diff = Date.parse(cur) - Date.parse(date);
			var ago = Math.round(diff/1000/60);
			
			return 'Issued '+ago+' minutes ago at '+h+':'+m+' '+dd;
		},
		
		pulseStart : function() {
			pulseTimeout = setInterval(function() {
					jQuery('#unlalert-bg-2').fadeIn(900);
					jQuery('#unlalert-wrapper').animate({
							color : '#eee'
						}, 900, function(){
							jQuery('#unlalert-bg-2').fadeOut(900);
							jQuery('#unlalert-wrapper').animate({
								color : '#111'
							}, 700);
							});
				}, 6000);
		}
		
	};
})();

// unlAlerts.server.init called from within the data_url script that is appended to the dom
unlAlerts.server = {
	init: function() {
		UNL.digitalSignage.unlalert.dataReceived();
		
		// unlAlerts.data comes from the data_url js file
		var alert = unlAlerts.data.alert;
		
		if (alert.info) {
			console.log("Found an alert, calling UNL.digitalSignage.unlalert.alertUser()");
			UNL.digitalSignage.unlalert.alertUser(alert);
			return true;
		} else {
			console.log("No urgent alert, calling UNL.digitalSignage.unlalert.closeAlert()");
			UNL.digitalSignage.unlalert.closeAlert();
		}
		
		return false;
	}
};

jQuery(document).ready(function(){
	UNL.digitalSignage.unlalert.init();
});