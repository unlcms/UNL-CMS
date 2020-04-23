/*
 * jQuery.autopager v1.0.0
 *
 * Copyright (c) lagos
 * Dual licensed under the MIT and GPL licenses.
 */
(function($) {
	var window = this, options = {},
		content, currentUrl, nextUrl, prevUrl,
		active = false,
		defaults = {
			autoLoad: true,
			page: 1,
			content: '.content',
			link: 'a[rel=next]',
			insertBefore: null,
			appendTo: null,
			start: function() {},
			load: function() {},
			disabled: false,
      permalink: true,
      noAutoScroll: 4,
      more_link: '<a>Load more</a>',
      link_prev: null,
      prev_text: '<a>Load previous</a>',
      page_arg: 'page'
		};

	$.autopager = function(_options) {
		var autopager = this.autopager;

		if (typeof _options === 'string' && $.isFunction(autopager[_options])) {
			var args = Array.prototype.slice.call(arguments, 1),
				value = autopager[_options].apply(autopager, args);

			return value === autopager || value === undefined ? this : value;
		}

		_options = $.extend({}, defaults, _options);
		autopager.option(_options);

		content = $(_options.content).filter(':last');
		if (content.length) {
			if (!_options.insertBefore && !_options.appendTo) {
				var insertBefore = content.next();
				if (insertBefore.length) {
					set('insertBefore', insertBefore);
				} else {
					set('appendTo', content.parent());
				}
			}
		}

		setUrl();

		if (_options.link_prev) {
		  prevUrl = $(_options.link_prev).attr('href');
      if (prevUrl && currentUrl.match(_options.page_arg)) {
        var load_prev_link = '<div id="autopager-load-prev">' + options.prev_text + '</div>';
        $(load_prev_link).insertBefore('#content').click($.autopager.load_prev);
      }
		}

		return this;
	};

	$.extend($.autopager, {
		option: function(key, value) {
			var _options = key;

			if (typeof key === "string") {
				if (value === undefined) {
					return options[key];
				}
				_options = {};
				_options[key] = value;
			}

			$.each(_options, function(key, value) {
				set(key, value);
			});
			return this;
		},

		enable: function() {
			set('disabled', false);
			return this;
		},

		disable: function() {
			set('disabled', true);
			return this;
		},

		destroy: function() {
			this.autoLoad(false);
			options = {};
			content = currentUrl = nextUrl = undefined;
			return this;
		},

		autoLoad: function(value) {
			return this.option('autoLoad', value);
		},

		urlGetArg: function (arg, url) {
		  if (url && (url.indexOf('?') > 0)) {
  		  args = url.split('?')[1].split('&');
  		  for (a in args) {
  		    if (args[a].split('=')[0] == arg) {
  		      return args[a].split('=')[1];
  		    }
  		  }
		  }
		  return false;
		},

		load: function() {
			if (active || !nextUrl || options.disabled) {
				return;
			}
			$('#autopager-load-more').remove();
			active = true;
			options.start(currentHash(), nextHash());
			$.get(nextUrl, insertContent);
			return this;
		},

		load_prev: function() {
		  if (active || !prevUrl || options.disabled || !options.link_prev) {
        return;
      }
      $('#autopager-load-prev').remove();

      active = true;
      options.start(currentHash(), nextHash());
      $.get(prevUrl, insertContent);
      return this;
		}

	});

	function set(key, value) {
		switch (key) {
			case 'autoLoad':
				if (value && !options.autoLoad) {
					$(window).scroll(loadOnScroll);
				} else if (!value && options.autoLoad) {
					$(window).unbind('scroll', loadOnScroll);
				}
				break;
			case 'insertBefore':
				if (value) {
					options.appendTo = null;
				}
				break
			case 'appendTo':
				if (value) {
					options.insertBefore = null;
				}
				break
		}
		options[key] = value;
	}

	function setUrl(context) {
		currentUrl = nextUrl || window.location.href;
		nextUrl = $(options.link, context).attr('href');
	}

	function loadOnScroll() {

		if (content.offset().top + content.height() < $(document).scrollTop() + $(window).height()) {
	    var page = (typeof(nextUrl) != 'undefined') ? nextUrl.replace(/.*page=(\d+).*/, "$1") : false;

	    //add link to load more.
	    if ((options.noAutoScroll > 0) && (page % options.noAutoScroll == 0)) {
	      var load_more_link = '<div id="autopager-load-more">' + options.more_link + '</div>';
	      if (page && ($('#autopager-load-more').length == 0)) {
  	      if (options.insertBefore) {
  	        $(load_more_link).insertBefore(options.insertBefore).click($.autopager.load);
  	      } else {
  	        $(load_more_link).appendTo(options.appendTo).click($.autopager.load);
  	      }
        }
	    } else {
	      $.autopager.load();
	    }

		}
	}

	function insertContent(res) {
		var _options = options,
			nextPage = $('<div/>').append(res.replace(/<script(.|\s)*?\/script>/g, "")),
			nextContent = nextPage.find(_options.content),
			nextLink = nextPage.find(_options.link).attr('href'),
			nextNum = parseInt($.autopager.urlGetArg(_options.page_arg, nextLink)),
			currentNum = parseInt($.autopager.urlGetArg(_options.page_arg, currentUrl)),
			loadingPrevious = ((typeof(_options.link_prev) == 'string') && nextNum <= currentNum);

    if (nextUrl && options.permalink && (typeof window.history.replaceState == 'function')) {
          window.history.replaceState({}, document.title, nextUrl);
    }

		set('page', nextNum);
		setUrl(nextPage);

    if (loadingPrevious) {
      var $content= $(_options.content + ':first');
      var $html = $('html');
      var height = $(options.content).parent().height();

      //add content with a fadein
      nextContent.hide()
      $content.before(nextContent);
      nextContent.fadeIn(250)

      //scroll to previous location
      $html.scrollTop( $html.scrollTop() - height + $content.parent().height());

      //add previous content link
      prevUrl = nextPage.find(_options.link_prev).attr('href');
      if (prevUrl) {
        $('<div id="autopager-load-prev">' + options.prev_text + '</div>').prependTo($(_options.content + ':first'));
        $('#autopager-load-prev').click($.autopager.load_prev)
      }

      options.load(); //dont forget the load callbacks
    } else {
  		if (nextContent.length) {
  			if (_options.insertBefore) {
  				nextContent.insertBefore(_options.insertBefore);
  			} else {
  		    nextContent.appendTo(_options.appendTo);
  			}
  			_options.load.call(nextContent.get(), currentHash(), nextHash());
  			content = nextContent.filter(':last');
  		}
    }
		active = false;
	}

	function currentHash() {
		return {
			page: options.page,
			url: currentUrl
		};
	}

	function nextHash() {
		return {
			page: options.page + 1,
			url: nextUrl
		};
	}
})(jQuery);
