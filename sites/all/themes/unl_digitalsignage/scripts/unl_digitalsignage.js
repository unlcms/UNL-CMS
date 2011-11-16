UNL.digitalSignage = (function() {
	var width = 1920;
	var height = 1080;
	var maxItems = {
		'news' : 5,
		'videos' : 10
	};
	
	return {
		// Populated by template.php
		feeds : {},
		
		init : function() {
			console.log('UNL.digitalSignage.init called');
			
			if ('1360' < window.innerWidth && window.innerWidth < '1372') {
				width = 1366;
				height = 768;
			}
			
			for (feed in UNL.digitalSignage.feeds) {
				if (UNL.digitalSignage.feeds.hasOwnProperty(feed)) {
					UNL.digitalSignage.setupFeed(feed);
				}
			}
			
			UNL.digitalSignage.rotateBeautyShots();
		},
		
		setupFeed : function(field) {
			console.log('UNL.digitalSignage.setupFeed called for: '+field);
			
			// How often to grab a new set of results from the feed
			var time;
			// Call it once to start us off, later we'll setInterval on this
			UNL.digitalSignage.updateFeed(field);
			
			switch(field) {
				case 'field_newssources':
					time = 600000;
					break;
				default:
					time = 500000;
			}
			
			// Video feed will be updated by calling updateFeed with an ended event on the last video
			if (field != 'field_videosources') {
				//setInterval(function(){UNL.digitalSignage.updateFeed(field);}, time);
			}
			return false;
		},
		
		updateFeed : function(field) {
			// Switch out the - with  _ if this is a recursive call
			field = field.replace('-', '_');
			
			console.log('UNL.digitalSignage.updateFeed feed called for: '+field);
			console.log('Getting data from: '+UNL.digitalSignage.feeds[field]);
			
			jQuery.getJSON(UNL.digitalSignage.feeds[field], function(data) {
				console.log('Success! Got the '+field+' data: '+data);
				// Switch out the _ with the - that is used by css identifiers
				field = field.replace('_', '-');
				// Empty out the existing content
				if (field !== 'field-twitter') {
					jQuery('.field-name-'+field).html('');
				}
				
				switch(field) {
					case 'field-newssources':
						var news = [];
						jQuery.each(data.query.results.item, function(key, val) {
							if (news.length >= maxItems['news']) {
								return false;
							}
							
							news.push('<li class="field-item '+(key%2 ? 'odd' : 'even')+'">'+
										'<h3><span>'+val.title+'</span></h3>'+
										'<div class="'+field+'-desc">'+val.description+'</div>'+
										'<div class="'+field+'-link">'+val.link+'</div>'+
										'<div class="'+field+'-qrcode"></div>'+
										'</li>');
							
							var sizeSmall = 42, sizeBig = 120;
							if (width < '1920') {
								var sizeSmall = 32;
								var sizeBig = 88;
							}
							// Small QR Code
							UNL.digitalSignage.addQrCode('.field-name-'+field+' .field-items .field-item:nth-child('+news.length+')', 'background-image', sizeSmall, val.link);
							// Big QR Code
							UNL.digitalSignage.addQrCode('.field-name-'+field+' .field-items .field-item:nth-child('+news.length+') .'+field+'-qrcode', 'img', sizeBig, val.link);
						});
						
						// Add the list of news stories
						jQuery('<ul />', {
							'class' : 'field-items',
							'html' : news.join('')
						}).appendTo('.field-name-'+field);
						
						// Add the div that will display the story that is showing
						jQuery('<div />', {
							'class' : 'field-display',
							'html' : '<div class="qrcode"></div><div class="link"></div><div class="desc"></div>'
						}).appendTo('.field-name-'+field);
						
						UNL.digitalSignage.rotateNews();
						break;
					case 'field-videosources':
						var videos = [];
						var videoCounter = 0;
						jQuery.each(data.query.results.item, function(key, val) {
							videos.push({link:val.link, title:val.title, description:val.description});
						});
						
						jQuery('<div/>', {
							'class': 'field-items',
							'html': '<div class="field-item even">'+
										'<div id="unl-digitalsignage-video-wrapper">'+
											'<video autoplay id="unl-digitalsignage-video"></video>'+
										'</div>'+
										'<div class="'+field+'-desc"></div>'+
									'</div>'
						}).appendTo('.field-name-'+field);
						
						var video = document.getElementById('unl-digitalsignage-video');
						
						// "Helper" function due to trouble removing a listener that calls a function with parameters i.e. video.addEventListener('ended', callVideoUpdate(i+1), false);
						var callVideoUpdate = function() {
							videoUpdate(videoCounter+1);
						};
						
						var videoUpdate = function() {
							video.removeEventListener('ended', callVideoUpdate, false);
							
							video.src = videos[videoCounter].link;
							console.log('src for video #'+videoCounter+' loaded');
							
							// Add the description
							jQuery('.'+field+'-desc').html(videos[videoCounter].description);
							// Truncate with ellipsis plugin if too long
							jQuery('.field-videosources-desc').wrapInner('<span class="ellipsis_text" />');
							jQuery('.field-videosources-desc').ThreeDots({ max_rows:14 });
							// Add title of video, must be done after ellipsis call to avoid stripping h3 tag
							jQuery('.'+field+'-desc').prepend('<h3>'+videos[videoCounter].title+'</h3>');
							
							// Set up recursion
							if (videos[videoCounter+1] !== undefined && videoCounter < maxItems['videos']) {
								console.log('Added "ended" listener that will call callVideoUpdate() i.e. videoUpdate('+videoCounter+'+1)');
								// If there are more videos to show set up listener that will swap out video src
								video.addEventListener('ended', callVideoUpdate, false);
							} else {
								// Otherwise start over by grabbing the video feed again
								console.log('Reached the end of the video list, "ended" listener scheduled to call updateFeed() to restart');
								video.addEventListener('ended', function(){UNL.digitalSignage.updateFeed(field);}, false);
							}
							videoCounter++;
						};
						
						// Attach the first video to the dom to get the ball rolling
						videoUpdate();
						break;
					case 'field-twitter':
						var tweets = [];
						jQuery.each(data, function(key, val) {
							tweets.push({
								retweeted_status : {
									user : {
										name : (val.retweeted_status ? val.retweeted_status.user.name : undefined),
										screen_name : (val.retweeted_status ? val.retweeted_status.user.screen_name : undefined),
										profile_image_url : (val.retweeted_status ? val.retweeted_status.user.profile_image_url : undefined)
									},
									text : (val.retweeted_status ? val.retweeted_status.text : undefined)
								},
								text : val.text,
								user : {
									name : val.user.name,
									screen_name : val.user.screen_name,
									profile_image_url : val.user.profile_image_url
								}
							});
						});
						UNL.digitalSignage.rotateTweets(tweets);
						break;
					default:
				}
			
			});
			return false;
		},
		
		rotateBeautyShots : function() {
			// Store the initial css values of the page title
			var pageTitle = [];
			pageTitle['padding-left'] = jQuery('#page-title').css('padding-left');
			pageTitle['color'] = jQuery('#page-title').css('color');
			pageTitle['text-shadow'] = jQuery('#page-title').css('text-shadow');
			
			// Things to give opacity to when beauty shot is full screen
			var opacityElements = '.field-name-field-videosources .field-videosources-desc, .field-name-field-newssources .field-items, .field-name-field-newssources .field-display, .field-name-field-twitter';
			
			// Populate this var to make code below easier to read
			var fi = '.field-name-field-beautyshots .field-items .field-item';
			
			var rotate = function() {
				// Get the first image
				var current = (jQuery(fi+'.show') ? jQuery(fi+'.show') : jQuery(fi+':first'));
				// Get next image, when it reaches the end, rotate it back to the first image
				var next = ((current.next().length) ? ((current.next().hasClass('show')) ? jQuery(fi+':first') : current.next()) : jQuery(fi+':first'));
				
				// Hide the current image
				current.removeClass('show').animate({opacity: 0.0}, 3000);
				// Show the next image
				next.css({opacity: 0.0}).addClass('show');
				
				// Decide how to animate fading in the new image and whether to move the background (the +/- 2 is just for a little fudging)
				if (current.width() < width+2 && next.width() >= width-2) {
					next.animate({opacity: 1.0}, 3000, function() {
						jQuery('#page-title').animate({
							backgroundColor : 'rgba(255, 255, 255, 0.50)',
							paddingLeft : '20px',
							color : 'rgba(60, 60, 60, 1.0)',
							textShadow : '#FFFFFF 0 0 0'
							}, 2000);
						jQuery('#unl_digitalsignage_background').animate({'left' : '-'+width+'px'}, 2000, function() {
							jQuery(opacityElements).css('background-image','none');
						});
					});
				} else if (current.width() >= width-2 && next.width() < width+2) {
					jQuery('#page-title').animate({
						backgroundColor : 'rgba(255, 255, 255, 0)',
						paddingLeft : pageTitle['padding-left'],
						color : pageTitle['color'],
						textShadow : pageTitle['text-shadow']
						}, 2000);
					jQuery('#unl_digitalsignage_background').animate({'left' : '0px'}, 2000, function() {
						next.animate({opacity : 1.0}, 3000);
						jQuery(opacityElements).css('background-image','inherit');
					});
				} else {
					next.animate({opacity : 1.0}, 3000);
				}
			};
			
			// Set the opacity of all images to 0
			jQuery(fi).css({opacity: 0.0});
			
			// Get the first image and display it (gets set to full opacity)
			jQuery(fi+':first').css({opacity: 1.0});
			
			// Call the rotator function to run the slideshow, (2000 = change to next image after 2 seconds)
			rotate();
			setInterval(function(){rotate()}, 20000);
		},
		
		rotateNews : function() {
			var fi = '.field-name-field-newssources .field-items .field-item';
			var rotate = function() {
				// Get the first story
				var current = (jQuery(fi+'.show') ? jQuery(fi+'.show') : jQuery(fi+':first'));
				// Get next story, when it reaches the end, rotate it back to the first story
				var next = ((current.next().length) ? ((current.next().hasClass('show')) ? jQuery(fi+':first') : current.next()) : jQuery(fi+':first'));
				
				// Populate the display area with the content from the current (.show) li
				jQuery('.field-name-field-newssources .field-display .desc').html(next.find('.field-newssources-desc').html());
				jQuery('.field-name-field-newssources .field-display .link').html(next.find('.field-newssources-link').html());
				jQuery('.field-name-field-newssources .field-display .qrcode').html(next.find('.field-newssources-qrcode').html());
				
				next.addClass('show');
				current.removeClass('show');
			};
			
			// Call the rotator function to run the slideshow, (2000 = change to next story after 2 seconds)
			rotate();
			setInterval(function(){rotate()}, 10000);
		},
		
		rotateTweets : function(tweets) {
			var fi = '.field-name-field-twitter .field-items .field-item';
			var counter = 0;
			var tweet = [];
			var rotate = function() {
				if (counter > tweets.length-1) {
					clearInterval(tweetInterval);
					UNL.digitalSignage.updateFeed('field_twitter');
					return false;
				}
				
				if (tweets[counter].retweeted_status && tweets[counter].retweeted_status.user 
					&& tweets[counter].retweeted_status.user.screen_name && tweets[counter].retweeted_status.user.profile_image_url && tweets[counter].retweeted_status.text) {
					// Retweet
					tweet['screen_name'] = tweets[counter].retweeted_status.user.screen_name;
					tweet['name'] = tweets[counter].retweeted_status.user.name;
					tweet['profile_image_url'] = tweets[counter].retweeted_status.user.profile_image_url;
					tweet['text'] = tweets[counter].retweeted_status.text;
					tweet['retweeted_by'] = tweets[counter].user.screen_name;
				} else {
					// Regular tweet
					tweet['screen_name'] = tweets[counter].user.screen_name;
					tweet['name'] = tweets[counter].user.name;
					tweet['profile_image_url'] = tweets[counter].user.profile_image_url;
					tweet['text'] = tweets[counter].text;
					tweet['retweeted_by'] = undefined;
				}
				
				jQuery(fi).fadeOut('slow', function() {
					jQuery(fi).html('<div class="tweet">'+
									'<img src="'+tweet['profile_image_url']+'" alt="Twitter Profile Icon" />'+
									'<div class="tweet-user"><span class="tweet-user-name">@'+tweet['screen_name']+'</span><span class="tweet-full-name">'+tweet['name']+'</span></div>'+
									'<div class="tweet-text">'+tweet['text']+'</div>'+
									'</div>');
					
					if (tweet['retweeted_by']) {
						jQuery(fi).append('<div class="retweet"><span>retweeted by</span> @'+tweet['retweeted_by']+'</div>');
					}
					jQuery(fi).fadeIn();
					counter++;
				});
				
			};
			
			// Call the rotator function (2000 = change to next tweet after 2 seconds)
			rotate();
			var tweetInterval = setInterval(function(){rotate()}, 15000);
		},
		
		addQrCode : function(element, type, size, url) {
			jQuery.post('http://go.unl.edu/api_create.php', { 'theURL' : url }, function(data) {
				console.log("GoURL generated URL is: "+data);
				var qrlink = 'http://chart.apis.google.com/chart?cht=qr&chs='+size+'&chld=L|0&chl='+data;
				if (type == 'background-image') {
					jQuery(element+' h3').css('background-image','url("'+qrlink+'")');
					// Change the long story url to the newly created GoURL
					jQuery(element+' .field-newssources-link').html(data);
				} else {
					jQuery(element).html('<img src="'+qrlink+'" alt="QR Code" />');
				}
			});
		},
		
		clock : function() {
			
		
		}
	};
})();

jQuery(document).ready(function() {
	UNL.digitalSignage.init();
});
