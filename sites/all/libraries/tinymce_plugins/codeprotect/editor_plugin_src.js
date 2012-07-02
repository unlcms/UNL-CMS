(function() {
	// Load plugin specific language pack
	tinymce.PluginManager.requireLangPack('codeprotect');

	tinymce.create('tinymce.plugins.codeprotectPlugin', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {
			var t = this;
			
			t.editor = ed;
			t.url = url;

			function isCodeProtectElm(n) {
				return /\bmceCodeProtect\b/.test(n.className);
			};
			
			t.codeRegex = ed.getParam('codeprotect_regex', /((?:<(?:jsp|asp):[^>]* \/>)|(?:<\?(?:\?(?!>)|[^\?])*\?>)|(?:<%(?:%(?!>)|[^%])*%>))/gm);

			// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mcecodeprotect');
			ed.addCommand('mcecodeprotect', function() {
				ed.windowManager.open({
					file : url + '/codeprotect.htm',
					width : 320 + ed.getLang('codeprotect.delta_width', 0),
					height : 120 + ed.getLang('codeprotect.delta_height', 0),
					inline : 1
				}, {
					plugin_url : url // Plugin absolute URL
					//some_custom_arg : 'custom arg' // Custom argument
				});
			});

			// Register codeprotect button
			ed.addButton('codeprotect', {
				title : 'codeprotect.desc',
				cmd : 'mcecodeprotect',
				image : url + '/img/codeprotect.png'
			});
			
			// Turn String -> HTML ( _code_ -> IMG )
			ed.onBeforeSetContent.add(function(ed, o) {
				//top.console.log('pre content: ' + o.content);
				o.content = o.content.replace(t.codeRegex, function (m, b) {
					//top.console.log('MATCH! ' + b);
					var img = '<img width="11" height="11" src="' + url + '/img/codeprotect_symbol.gif' +'" alt="' + t._protectCode(b) + '" title="Protected Server Side Code" class="mceCodeProtect mceItem" />';
					//top.console.log(img);
					return img;
				});
			});

			// Turn HTML -> String ( IMG -> _code_ )
			ed.onPostProcess.add(function(ed, o) {
                if (o.get) {
                	var startPos = -1;
					while ((startPos = o.content.indexOf('<img', startPos+1)) != -1) {
						var endPos = o.content.indexOf('/>', startPos);
						var attribs = t._parseAttributes(o.content.substring(startPos + 4, endPos));
						endPos += 2;
                	
                    	if (!attribs['src'] || !attribs['alt'] || -1 === attribs['src'].indexOf('codeprotect_symbol.gif')) {
							startPos += 3;
							continue;
						}
	
						// Insert embed/object chunk
						chunkBefore = o.content.substring(0, startPos);
						chunkAfter = o.content.substring(endPos);
						//o.content = chunkBefore + codeLeftDelim + attribs['alt'] + codeRightDelim + chunkAfter;
						o.content = chunkBefore + attribs['alt'] + chunkAfter;
                    }
                }
            });
			
			ed.onNodeChange.add(function(ed, cm, n) {
				cm.setActive('codeprotect', n.nodeName == 'IMG' && isCodeProtectElm(n));
			});
		},
		
		/**
		 * Creates control instances based in the incomming name. This method is normally not
		 * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
		 * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
		 * method can be used to create those.
		 *
		 * @param {String} n Name of the control to create.
		 * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
		 * @return {tinymce.ui.Control} New control instance or null if no control was created.
		 */
		createControl : function(n, cm) {
			return null;
		},

		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
				longname : 'CodeProtect Ex plugin for TinyMCE v3.x',
				author : 'Vorapoap Lohwongwatana',
				authorurl : 'http://tinymce.moxiecode.com',
				infourl : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/codeprotect',
				version : "0.9.3"
			};
		},
		_protectCode : function(code) {
			var d = document.createElement('div');
			var t = document.createTextNode(code.replace(/--/g, '__OX_DOUBLE_DASH_OX__').replace(/\n/g, '__OX_NEW_LINE_OX__'));
			
			d.appendChild(t);
			
			//top.console.log('protected:' + d.innerHTML.replace(/"/g, '&quot;'));
			
			return d.innerHTML.replace(/"/g, '&quot;');
		},
		_unprotectCode : function(escapedCode) {
/*			var r = escapedCode.replace(/__OX_DOUBLE_DASH_OX__/g, '--')
			                                          .replace(/__OX_NEW_LINE_OX__/g, '\n\n') //two newlines because Tiny will collapse consecutive newlines
			                                          .replace(/&gt;/, '>');
			top.console.log('unprotected: ' + r);
			return r;
*/
			var d = document.createElement('div');
			
			d.innerHTML = this._stripTags(escapedCode).replace(/__OX_DOUBLE_DASH_OX__/g, '--')
			                                          .replace(/__OX_NEW_LINE_OX__/g, '\n\n') //two newlines because Tiny will collapse consecutive newlines
			                                          .replace(/&gt;/, '>');
			
			return d.childNodes[0] ? d.childNodes[0].nodeValue : '';

		},
		_stripTags : function(html) {
			return html;
			//return html.replace(/<\/?[^>]+>/gi, '');
		},
		_parseAttributes : function(attribute_string) {
			var attributeName = '';
			var attributeValue = '';
			var withInName;
			var withInValue;
			var attributes = new Array();
			var whiteSpaceRegExp = /^[ \n\r\t]+/g;
	
			if (attribute_string == null || attribute_string.length < 2)
				return null;
	
			attribute_string = attribute_string.replace(/'/g, "&#39;");
	
			withInName = withInValue = false;
			for (var i=0; i < attribute_string.length; i++) {
				var chr = attribute_string.charAt(i);
	
				if (chr == '"' && !withInValue)
					withInValue = true;
				else if (chr == '"' && withInValue) {
					withInValue = false;
									
					var pos = attributeName.lastIndexOf(' ');
					if (pos != -1)
						attributeName = attributeName.substring(pos+1);
						
					if (attributeName.toLowerCase() == 'alt')
						attributeValue = this._unprotectCode(attributeValue).replace(/&#39;/g,"'");

					attributes[attributeName.toLowerCase()] = attributeValue.substring(1);
	
					attributeName = '';
					attributeValue = '';
				} else if (!whiteSpaceRegExp.test(chr) && !withInName && !withInValue)
					withInName = true;
	
				if (chr == '=' && withInName)
					withInName = false;
	
				if (withInName)
					attributeName += chr;
	
				if (withInValue)
					attributeValue += chr;
			}
	
			return attributes;
		}
	});

	// Register plugin
	tinymce.PluginManager.add('codeprotect', tinymce.plugins.codeprotectPlugin);
})();
