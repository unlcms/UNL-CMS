tinyMCEPopup.requireLangPack();
tinyMCEPopup.onInit.add(onLoadInit);

var action, element, ed;
var codeRegex;

function onLoadInit() {
	tinyMCEPopup.resizeToInnerSize();

	codeRegex = /^(?:\s|\n)*((?:<(?:jsp|asp):[^>]* \/>)|(?:<\?(?:\?(?!>)|[^\?])*\?>)|(?:<%(?:%(?!>)|[^%])*%>))(?:\s|\n)*$/gm
	//codeRegex = tinyMCE.activeEditor.plugins.codeprotect.codeRegex;
	
	var formObj = document.forms[0];
	ed = tinyMCEPopup.editor;
	var elm = ed.selection.getNode();
	var action = "insert";
	var html = ed.dom.getAttrib(elm, 'alt') + '';
	
	//codeRegex = ed.getParam('codeprotect_regex', /((?:<(?:jsp|asp):[^>]* \/>)|(?:<([\?%])[^>]*\2>))/gm);
	//html = html.replace(/\'\s/g,"'\n");
	if (elm != null && elm.nodeName == "IMG") 
		action = "update";
	

	formObj.insert.value = ed.getLang('update', 'Insert', true); 
	if (action == "update") {
		var r = unprotectCode(html);
		
		
		//top.console.log('html: ' + html + '\nrecovered: ' + r);
		
		formObj.alt.value = r;
		window.focus();
	} 
	
	resizeInputs();
}

function resizeInputs() {
	var el = document.getElementById('codeprotectAlt');

	if (!tinymce.isIE) {
		 wHeight = self.innerHeight - 65;
		 wWidth = self.innerWidth - 16;
	} else {
		 wHeight = document.body.clientHeight - 70;
		 wWidth = document.body.clientWidth - 16;
	}

	el.style.height = Math.abs(wHeight) + 'px';
	el.style.width  = Math.abs(wWidth) + 'px';
}

/*
function setAttrib(elm, attrib, value) {
	var formObj = document.forms[0];
	var valueElm = formObj.elements[attrib];

	if (typeof(value) == "undefined" || value == null) {
		value = "";

		if (valueElm)
			value = valueElm.value;
	}

	if (value != "") {
		elm.setAttribute(attrib, value);

		eval('elm.' + attrib + "=value;");
	} else
		elm.removeAttribute(attrib);
}
*/

function saveContent() {
	var elm;

	tinyMCEPopup.restoreSelection();
	elm = ed.selection.getNode();

	var code = document.forms[0].alt.value;

	if (!codeRegex.test(code)) {
		tinyMCE.activeEditor.windowManager.alert('Codeprotect is only for PHP, JSP and ASP.\nPlease try using a snippet that is a valid PHP, JSP or ASP tag.');
		return;
	}
	
	if (elm != null && elm.nodeName == "IMG") {
		//setAttrib(elm, 'alt', code);
		if (elm.nodeName == "IMG")
			elm.setAttribute("alt", protectCode(code, {'simple-escape': true}));
	}
	else {
		var rng = ed.selection.getRng();

		if (rng.collapse)
			rng.collapse(false);

		html = '<img width="11" height="11"';
		html += ' src="' + (tinyMCEPopup.getWindowArg("plugin_url") + '/img/codeprotect_symbol.gif') +'"';
		html += ' alt="' + protectCode(code) + '" title="Protected Server Side Code" class="mceCodeProtect mceItem" />';
		
		ed.execCommand("mceInsertContent", false, html);
	}

	tinyMCEPopup.close();
}

function protectCode(code, opts) {
	var o = opts || {};
	var c = code.replace(/--/g, '__OX_DOUBLE_DASH_OX__').replace(/\n/g, '__OX_NEW_LINE_OX__');

	if (o['simple-escape'])
		return c;
	
	return c.replace(/"/g, '&quot;');
	
	/*
	var d = document.createElement('div');
	var t = document.createTextNode(c);

	d.appendChild(t);
	
	return d.innerHTML.replace(/"/g, '&quot;');
	*/
}

function unprotectCode(escapedCode) {
	return escapedCode.replace(/&quot;/g, /"/).replace(/__OX_DOUBLE_DASH_OX__/g, '--').replace(/__OX_NEW_LINE_OX__/g, '\n');

	/*
	var d = document.createElement('div');
	var r = '';
	
	d.innerHTML = escapedCode.replace(/__OX_DOUBLE_DASH_OX__/g, '--').replace(/__OX_NEW_LINE_OX__/g, '\n');
	
	d = d.firstChild;
	
	while (d) {
		if (d.nodeType == 1) {
			r +=  '<' + d.nodeName;
			
			for (var att in d.attributes) {
				r += ' ' + d.attributes[att].nodeName + '="' + d.attributes[att].nodeValue + '"';
			}
			
			r += ' />';
		}
		else
			r += d.nodeValue;

		d = d.nextSibling;
	}
	
	return r;
	*/
}