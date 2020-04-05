var reBlockElements = /^(HTML|HEAD|BODY|BR|TITLE|TABLE|SCRIPT|TR|TBODY|P|DIV|H1|H2|H3|H4|H5|H6|ADDRESS|PRE|OL|UL|LI)$/i;

// Some methods of this class is in the editor_php.js/editor_aspx.js
function BXNode(parent)
{
	this.oParent = parent;
	this.iLevel = 0;
	if(parent)
	{
		parent.arNodes[parent.arNodes.length] = this;
		this.iLevel = parent.iLevel+1;
	}
	this.arNodes = [];
	this.arAttributes = {};
	this.type = null;
	this.text = "";
}

BXNode.prototype = {
GetHTML: function(bFormatted)
{
	//try{
		var id = this.arAttributes["id"];
		if (id)
		{
			var bxTag = this.pParser.pMainObj.GetBxTag(id);
			if(bxTag.tag)
			{
				switch(bxTag.tag)
				{
					case 'img':
						if (!bxTag.params)
							return '';

						// width, height
						var
							w = this.arStyle.width || this.arAttributes.width || '',
							h = this.arStyle.height || this.arAttributes.height || '';

						if (~w.indexOf('%'))
							w = parseInt(w) + '%';
						if (~h.indexOf('%'))
							h = parseInt(h) + '%';
						if (w && !isNaN(w))
							bxTag.params.width = w;
						if (h && !isNaN(h))
							bxTag.params.height = h;

						bxTag.params['class'] = this.arAttributes['class'] ||'';
						bxTag.params['align'] = this.arAttributes['align'] ||'';

						var i, res = '<img ';
						for (i in bxTag.params)
							if (bxTag.params[i])
							{
								if (this.pParser.isPhpAttribute(bxTag.params[i]))
									res += i + '="' + bxTag.params[i] + '" ';
								else
									res += i + '="' + BX.util.htmlspecialchars(bxTag.params[i]) + '" ';
							}
						res += ' />';

						return res;
					case 'a':
						if (!bxTag.params)
							return '';

						bxTag.params['class'] = this.arAttributes['class'] ||'';

						var i, res = '<a ';
						for (i in bxTag.params)
							if (bxTag.params[i] && i != 'noindex')
							{
								if (this.pParser.isPhpAttribute(bxTag.params[i]))
									res += i + '="' + bxTag.params[i] + '" ';
								else if (i == 'href')
									res += i + '="' + bxTag.params[i] + '" ';
								else
									res += i + '="' + BX.util.htmlspecialchars(bxTag.params[i]) + '" ';
							}
						res += '>';

						for(i = 0; i < this.arNodes.length; i++)
							res += this.arNodes[i].GetHTML(bFormatted);

						res += '</a>';

						if (bxTag.params.noindex)
							res = '<noindex>' + res + '</noindex>';

						return res;
					case 'php':
						var pMainObj = this.pParser.pMainObj;
						if (pMainObj.bDotNet || (pMainObj.limit_php_access && pMainObj.pComponent2Taskbar))
							break;
						return bxTag.params.value;
					case 'noscript':
					case 'noindex':
						var res = "", i;
						for(i = 0; i < this.arNodes.length; i++)
							res += this.arNodes[i].GetHTML(bFormatted);
						return "<" + bxTag.tag + ">" + res + "</" + bxTag.tag + ">";
					case 'break':
						return '<BREAK />';
					case 'break_page':
						return '<div style="page-break-after: always"><span style="display: none">&nbsp;</span></div>';
					case 'hr':
						if (!BX.browser.IsIE() && bxTag.params && bxTag.params.value)
							return bxTag.params.value;
						break;
					case 'flash':
						if (!bxTag.params)
							return '';
						var i, res = '<embed type="application/x-shockwave-flash" pluginspage="http:/' + '/www.macromedia.com/go/getflashplayer" ';
						for (i in bxTag.params)
							if (bxTag.params[i])
								res += i + '="' + bxTag.params[i] + '" ';
						res += '></embed>';
						return res;
					case 'cursor':
						return '#BXCURSOR#';
					default:
						var customRes = this.CustomUnParse();

						if (customRes)
							return customRes;

						// Symbols
						if (this.pParser.arSym[bxTag.tag])
							return this.pParser.arSym[bxTag.tag][0];

						// comments, script
						if (bxTag.params && bxTag.params.value)
							return '\n' + bxTag.params.value + '\n';
				}
			}
		}

		if(this.arAttributes["_moz_editor_bogus_node"])
			return '';

		var f;
		this.bDontUseSpecialchars = false;
		for (var i = 0, l = arNodeUnParsers.length; i < l; i++)
		{
			f = arNodeUnParsers[i];
			if (f && typeof(f) == 'function')
				f(this, this.pParser.pMainObj);
		}

		res = this.GetHTMLLeft();

		var bNewLine = false;
		var sIndent = '';

		if(bFormatted && this.type != 'text')
		{
			if(reBlockElements.test(this.text) && !(this.oParent && this.oParent.text && this.oParent.text.toLowerCase() == 'pre'))
			{
				for(var j = 0; j < this.iLevel - 3; j++)
					sIndent += "  ";
				bNewLine = true;
				res = "\r\n" + sIndent + res;
			}
		}

		for(var i=0; i< this.arNodes.length; i++)
			res += this.arNodes[i].GetHTML(bFormatted);

		res += this.GetHTMLRight();
		if(bNewLine)
			res += "\r\n" + (sIndent=='' ? '' : sIndent.substr(2));

		return res;
	//}catch(e){_alert("Error BXNode.prototype.GetHTML : \n type = "+this.type+"\ntext = "+this.text);}
},

CustomUnParse: function(res)
{
	if (!res)
		res = false;

	var fUnParser, i, l;
	for (i = 0, l = arBXTaskbars.length; i < l; i++)
	{
		fUnParser = window[arBXTaskbars[i].name].prototype.UnParseElement;
		if (fUnParser)
			arUnParsers.push(fUnParser);
	}

	for (var j = 0; j < arUnParsers.length; j++)
	{
		fUnParser = arUnParsers[j];
		if (fUnParser)
		{
			res = fUnParser(this, this.pParser.pMainObj);
			if (res !== false)
				break;
		}
	}

	return res;
},

IsPairNode: function()
{
	return (this.text.substr(0, 1) != 'h' && this.text != 'br' && this.text != 'img' && this.text != 'input');
},

GetHTMLLeft: function()
{
	if(this.type == 'text')
		return this.bDontUseSpecialchars ? this.text : bxhtmlspecialchars(this.text);

	var atrVal, attrName, res;
	if(this.type == 'element')
	{
		res = "<"+this.text;

		for(attrName in this.arAttributes)
		{
			atrVal = this.arAttributes[attrName];
			if(attrName.substring(0,4).toLowerCase() == '_moz')
				continue;

			if(this.text.toUpperCase()=='BR' && attrName.toLowerCase() == 'type' && atrVal == '_moz')
				continue;

			if(attrName == 'style')
			{
				if (atrVal.length > 0 && atrVal.indexOf('-moz') != -1)
					atrVal = BX.util.trim(atrVal.replace(/-moz.*?;/ig, '')); // Kill -moz* styles from firefox

				if (this.text == 'td')
				{
					// Kill border-image: none; styles from firefox for <td>
					atrVal = BX.util.trim(atrVal.replace(/border-image:\s*none;/ig, '')); //

					// kill border-color: for ie
					atrVal = BX.util.trim(atrVal.replace(/border-bottom-color:\s*;?/ig, ''));
					atrVal = BX.util.trim(atrVal.replace(/border-top-color:\s*;?/ig, ''));
					atrVal = BX.util.trim(atrVal.replace(/border-right-color:\s*;?/ig, ''));
					atrVal = BX.util.trim(atrVal.replace(/border-left-color:\s*;?/ig, ''));
				}

				if(atrVal.length <= 0)
					 continue;
			}
			res += ' ' + attrName + '="' + (this.bDontUseSpecialchars ? atrVal : bxhtmlspecialchars(atrVal)) + '"';
		}

		if(this.arNodes.length <= 0 && !this.IsPairNode())
			return res+" />";
		return res+">";
	}
	return "";
},

GetHTMLRight: function()
{
	if(this.type == 'element' && (this.arNodes.length>0 || this.IsPairNode()))
		return "</"+this.text+">";
	return "";
}
};

// Some methods of this class is in the editor_php.js/editor_aspx.js
function BXParser(pMainObj)
{
	this.pMainObj = pMainObj;
	this.arSym = {'bxshy' : ['&shy;', '-'], 'bxnbsp' : ['&nbsp;', ' ']};
	this.systemCSS = "img.bxed-anchor{background-image: url(" + global_iconkit_path + ")!important; background-position: -260px 0!important; height: 20px!important; width: 20px!important;}\n" +
	"span.bxed-noscript{color: #0000a0!important; padding: 2px!important; font-style:italic!important; font-size: 90%!important;}\n" +
	"span.bxed-noindex{color: #004000!important; padding: 2px!important; font-style:italic!important; font-size: 90%!important;}\n" +
	"img.bxed-flash{border: 1px solid #B6B6B8!important; background: url(" + image_path + "/flash.gif) #E2DFDA center center no-repeat !important;}\n" +
	"img.bxed-hr{padding: 2px!important; width: 100%!important; height: 2px!important;}\n";
}

BXParser.prototype = {
_RecursiveParse: function (oParentNode, oBXNode)
{
	switch(oParentNode.nodeType)
	{
		case 9:
			oBXNode.type = 'document';
			break;
		case 1:
			if(oParentNode.__bxID && oParentNode.__bxID == this.__bxID)
				return;
			oParentNode.__bxID = this.__bxID;
			if(oParentNode.tagName.length<=0 || oParentNode.tagName.substring(0, 1)=="/")
				return;

			oBXNode.type = 'element';
			oBXNode.text = oParentNode.tagName.toLowerCase();
			var j, attr = oParentNode.attributes, l = attr.length ;
			for(j = 0; j < l; j++)
			{
				if(attr[j].specified || (oBXNode.text == "input" && attr[j].nodeName.toLowerCase()=="value"))
				{
					var attrName = attr[j].nodeName.toLowerCase();
					if(attrName == '__bxid')
						continue;

					if(attrName=="style")
						oBXNode.arAttributes[attrName] = oParentNode.style.cssText;
					else if(attrName=="src" || attrName=="href"  || attrName=="width"  || attrName=="height")
						oBXNode.arAttributes[attrName] = oParentNode.getAttribute(attrName, 2);
					else
						oBXNode.arAttributes[attrName] = attr[j].nodeValue;
				}
			}
			if (oParentNode.style)
				oBXNode.arStyle = oParentNode.style;
			break;
		case 3:
			oBXNode.type = 'text';
			var res = oParentNode.nodeValue;
			if(!(oBXNode.oParent && oBXNode.oParent.text && oBXNode.oParent.text.toLowerCase() == 'pre'))
			{
				res = res.replace(/\n+/g, ' ');
				res = res.replace(/ +/g, ' ');
			}

			oBXNode.text = res;
			break;
	}

	var arChilds = oParentNode.childNodes;
	var oNode, oBXChildNode;

	for(var i = 0; i < arChilds.length; i++)
	{
		oNode = arChilds[i];
		oBXChildNode = new BXNode(oBXNode);
		oBXChildNode.pParser = this;
		this._RecursiveParse(oNode, oBXChildNode);
	}
},

Parse: function ()
{
	// Limit Component Access: if it's not first parsing and all components was converted to html
	if (!this.pMainObj.bDotNet && lca && this.pMainObj.pComponent2Taskbar && _$arComponents !== false && _$LCAContentParser_execed)
	{
		_$arComponents = {};
		_$compLength = 0;
	}

	this.arNodeParams = {};
	this.__bxID = parseInt(Math.random() * 100000) + 1;
	this.pNode = new BXNode(null);
	this.pNode.pParser = this;
	this._RecursiveParse(this.pMainObj.pEditorDocument, this.pNode);
},

GetHTML: function (bFormatted)
{
	return this.pNode.GetHTML(bFormatted);
},

SystemParse: function(sContent)
{
	var _this = this;
	this.strStyleNodes = this.systemCSS;

	sContent = this.ClearFromHBF(sContent);
	sContent = sContent.replace(/(<td[^>]*>)\s*(<\/td>)/ig, "$1<br _moz_editor_bogus_node='on'>$2");

	//TODO: at the beginning of the parsing - replace <? ?> for something,  at the end unparse it back. For easier regexps.
	// Image
	sContent = sContent.replace(/<img([\s\S]*?(?:.*?[^\?%]{1})??)>/ig,
		function(str, s1)
		{
			var arParams = _this.GetAttributesList(s1), i , val, res = "", bPhp = false;

			if (arParams && arParams.id)
			{
				var oTag = _this.pMainObj.GetBxTag(arParams.id);
				if (oTag.tag)
					return str;
			}

			res = "<img id=\"" + _this.pMainObj.SetBxTag(false, {tag: 'img', params: arParams}) + "\" ";
			for (i in arParams)
			{
				if (typeof arParams[i] == 'string' && i != 'id')
				{
					if (i == 'src' && window.SITE_TEMPLATE_PATH )
					{
						var len = SITE_TEMPLATE_PATH.length;
						var src = arParams[i];
						if (arParams[i].indexOf('SITE_TEMPLATE_PATH') != -1)
						{
							src = src.replace(/<\?=\s*SITE_TEMPLATE_PATH;?\s*\?>/i, SITE_TEMPLATE_PATH);
							src = src.replace(/<\?\s*echo\s*SITE_TEMPLATE_PATH;?\s*\?>/i, SITE_TEMPLATE_PATH);
						}
						res += 'src="' + src + '" ';
						continue;
					}

					bPhp = _this.isPhpAttribute(arParams[i]);
					if (!bPhp) // No php in attribute
						res += i + '="' + BX.util.htmlspecialchars(arParams[i]) + '" ';
				}
			}
			res += " />";
			return res;
		}
	);

	// Link
	sContent = sContent.replace(/(<noindex>)*?<a(\s[\s\S]*?(?:.*?[^\?%\/]{1})??)(>([\s\S]*?)<\/a>)(<\/noindex>)*/ig,
		function(str, s0, s1, s2, innerHtml, s3)
		{
			var arParams = _this.GetAttributesList(s1), i , res, bPhp = false;
			if (s0 && s3 && s0.toLowerCase().indexOf('noindex') != -1 && s3.toLowerCase().indexOf('noindex') != -1)
			{
				arParams.noindex = true;
				arParams.rel = "nofollow";
			}

			// It's anchor
			if (arParams.name && (BX.util.trim(innerHtml) == ""))
				return str;

			res = "<a id=\"" + _this.pMainObj.SetBxTag(false, {tag: 'a', params: arParams}) + "\" ";
			for (i in arParams)
			{
				if (typeof arParams[i] == 'string' && i != 'id' && i != 'noindex')
				{
					bPhp = _this.isPhpAttribute(arParams[i]);
					if (i == 'href') // Php in href
					{
						if (bPhp)
							res += 'href="bx_bogus_href" ';
						else
							res += 'href="' + arParams[i] + '" ';
					}
					else if (!bPhp) // No php in attribute
						res += i + '="' + BX.util.htmlspecialchars(arParams[i]) + '" ';
				}
			}
			res += s2;

			return res;
		}
	);

	// APP - Advanced PHP parcer
	if (!this.pMainObj.bDotNet && this.pMainObj.bUseAPP)
		sContent = this.pMainObj.APP_Parse(sContent);

	if (this.pMainObj.bDotNet && this.pMainObj.bUseAAP)
		sContent = this.pMainObj.AAP_Parse(sContent);

	//Parse content as string...
	sContent = this.CustomParse(sContent);

	if (!this.pMainObj.bDotNet)
		sContent = this.pMainObj.SystemParse_ex(sContent);

	sContent = sContent.replace(/<break \/>/ig, "<img src=\"" + image_path + "/break_tag.gif\" id=\"" + this.pMainObj.SetBxTag(false, {tag: 'break'}) + "\"/>");

	// Flash parsing
	sContent = sContent.replace(/<embed([^>]+?)(?:>(?:\s|\S)*?<\/embed)?(?:\/?)?>/ig, function(sContent, s1){return _this.UnparseFlash(sContent, s1);});

	if (BX.browser.IsIE())
	{
		sContent = sContent.replace(/<area([^>]*?>[^>]*?)<\/area>/ig, "<bxarea$1</bxarea>");
		sContent = sContent.replace(/<area([^>]*?>[^>]*?)>/ig, "<bxarea$1>");

		sContent = sContent.replace(/<noindex[^>]*?>([\s\S]*?)<\/noindex>/ig, "<span class=\"bxed-noindex\" id=\"" + this.pMainObj.SetBxTag(false, {tag: 'noindex'}) + "\">$1</span>");
	}
	else
	{
		sContent = sContent.replace(/<hr([^>]*)>/ig, function(sContent, params)
			{
				return '<img class="bxed-hr" src="' + image_path + '/break_page.gif" id="' + _this.pMainObj.SetBxTag(false, {tag: "hr", params: {value : sContent}}) + '"/>';
			}
		);
	}

	sContent = sContent.replace(/<noscript[^>]*?>([\s\S]*?)<\/noscript>/ig, "<span class=\"bxed-noscript\" id=\"" + this.pMainObj.SetBxTag(false, {tag: 'noscript'}) + "\">$1</span>");
	sContent = sContent.replace(/<style[\s\S]*?>([\s\S]*?)<\/style>/gi, function(sContent, s1){return _this.UnparseStyleNode(sContent, s1)});
	sContent = sContent.replace(/<script[\s\S]*?\/script>/gi, function(sContent){return '<img id="' + _this.pMainObj.SetBxTag(false, {tag: "script", params: {value : sContent}}) + '" src="' + image_path + '/script.gif" />';});
	sContent = sContent.replace(/<!--[\s\S]*?-->/ig, function(sContent){return '<img id="' + _this.pMainObj.SetBxTag(false, {tag: "comments", params: {value : sContent}}) + '" src="' + image_path + '/comments.gif" />';});

	sContent = sContent.replace(/<a\s([\s\S]*?)>\s*?<\/a>/ig, function(sContent, s1){return _this.UnparseAnchors(sContent, s1);});
	sContent = sContent.replace(/<a(\s[\s\S]*?)\/>/ig, function(sContent, s1){return _this.UnparseAnchors(sContent, s1);});
	//sContent = sContent.replace(/<a(\s[\s\S]*?)(?:>\s*?<\/a)?(?:\/?)?>/ig, function(sContent, s1){return _this.UnparseAnchors(sContent, s1);});
	sContent = this.SymbolsParse(sContent);

	if (this.strStyleNodes.length > 0)
		setTimeout(function(){_this.AppendCSS(_this.strStyleNodes);}, 300);

	sContent = sContent.replace(/#BXCURSOR#/ig, '<a href="#" id="' + this.pMainObj.lastCursorId + '">|</a>');

	return sContent;
},

GetAttributesList: function(str)
{
	str = " " + str + " ";

	var arParams = {}, arPHP = [], bPhp = false, _this = this;
	// 1. Replace PHP by #BXPHP#
	str = str.replace(/<\?.*?\?>/ig, function(s)
	{
		arPHP.push(s);
		return "#BXPHP" + (arPHP.length - 1) + "#";
	});

	// 2.0 Parse params - without quotes
	str = str.replace(/([^\w]??\s)(\w+?)=([^\s\'"]+?)\s/ig, function(s, b0, b1, b2)
	{
		b2 = b2.replace(/#BXPHP(\d+)#/ig, function(s, num){return arPHP[num] || s;});
		arParams[b1.toLowerCase()] = _this.isPhpAttribute(b2) ? b2 : BX.util.htmlspecialcharsback(b2);
		return b0;
	});

	// 2.1 Parse params
	str = str.replace(/([^\w]??\s)(\w+?)\s*=\s*("|\')([^\3]*?)\3/ig, function(s, b0, b1, b2, b3)
	{
		// 3. Replace PHP back
		b3 = b3.replace(/#BXPHP(\d+)#/ig, function(s, num){return arPHP[num] || s;});
		arParams[b1.toLowerCase()] = _this.isPhpAttribute(b3) ? b3 : BX.util.htmlspecialcharsback(b3);
		return b0;
	});

	return arParams;
},

isPhpAttribute: function(str)
{
	if (typeof str == 'number' || typeof str == 'object')
		return false;
	return str.indexOf("<?") != -1 || str.indexOf("?>") != -1 || (str.indexOf("#PHP") != -1 && this.pMainObj.limit_php_access);
},

SystemUnParse: function(sContent)
{
	if (BX.browser.IsIE())
	{
		sContent = sContent.replace(/<bxarea([^>]*?>[^>]*?)<\/bxarea>/ig, "<area$1</area>");
		sContent = sContent.replace(/<bxarea([^>]*?>[^>]*?)>/ig, "<area$1>");
	}

	// APP - Advanced PHP parcer, AAP - Advanced Aspx Parser
	if (!this.pMainObj.bDotNet && this.pMainObj.bUseAPP)
		sContent = this.pMainObj.APP_Unparse(sContent);
	if (this.pMainObj.bDotNet && this.pMainObj.bUseAAP)
		sContent = this.pMainObj.AAP_Unparse(sContent);

	//Replace entities
	sContent = this.HTMLEntitiesReplace(sContent);
	sContent = this.CustomContentUnParse(sContent);
	return sContent;
},

UnparseAnchors: function(sContent, s1)
{
	if (s1.indexOf("bxid_") != -1)
		return sContent;

	if(sContent.toLowerCase().indexOf("href") > 0)
		return sContent;

	var id = this.pMainObj.SetBxTag(false, {tag: "anchor", params: {value : sContent}});
	return '<img id="' + id + '" src="' + one_gif_src + '" class="bxed-anchor" />';
},

UnparseFlash: function(sContent, s1)
{
	if (s1.indexOf('.swf') === false || s1.indexOf('flash') === false) // not a flash
		return str;

	s1 = s1.replace(/[\r\n]+/ig, ' ');
	s1 = s1.replace(/\s+/ig, ' ');
	s1 = s1.trim();

	var
		arParams = {},
		w, h, style, id;

	s1 = s1.replace(/([^\w]??)(\w+?)\s*=\s*("|\')([^\3]+?)\3/ig, function(s, b0, b1, b2, b3){arParams[b1] = b3; return b0;});

	w = (parseInt(arParams.width) || 50) + 'px';
	h = (parseInt(arParams.height) || 25) + 'px';
	style = 'width: ' + w + '; height: ' + h + ';';

	return '<img id="' + this.pMainObj.SetBxTag(false, {tag: "flash", params: arParams}) + '" class="bxed-flash" style="' + style + '" src="' + one_gif_src + '"/>';
},

GetAnchorName: function(html, newName)
{
	if (newName)
		return html.replace(/([\s\S]*?name\s*=\s*("|'))([\s\S]*?)(\2[\s\S]*?(?:>\s*?<\/a)?(?:\/?)?>)/ig, "$1" + newName + "$4");

	return html.replace(/([\s\S]*?name\s*=\s*("|'))([\s\S]*?)(\2[\s\S]*?(?:>\s*?<\/a)?(?:\/?))?>/ig, "$3");
},

UnparseStyleNode: function(sContent, s1)
{
	if (typeof s1 == 'string' && s1.length > 0)
		this.strStyleNodes += s1 + "\n";

	var id = this.pMainObj.SetBxTag(false, {tag: "style", params: {value : sContent}});
	return '<img id="' + id + '" src="' + image_path + '/style.gif" />';
},

GetHBF: function(sContent, bContentWithHBF)
{
	sContent = sContent.replace(/(^[\s\S]*?)(<body.*?>)/i, "");
	this.pMainObj._head = RegExp.$1;
	this.pMainObj._body = RegExp.$2;
	sContent = sContent.replace(/(<\/body>[\s\S]*?$)/i, "");
	this.pMainObj._footer = RegExp.$1;
	if (!bContentWithHBF)
		return sContent;
	return this.AppendHBF(sContent, true);
},

ClearHBF: function()
{
	this.pMainObj._head = this.pMainObj._body = this.pMainObj._footer = '';
},

AppendHBF: function(sContent, bDontClear)
{
	if (!bDontClear)
		sContent = this.ClearFromHBF(sContent);
	return this.pMainObj._head + this.pMainObj._body + sContent + this.pMainObj._footer;
},

ClearFromHBF: function(sContent)
{
	sContent = sContent.replace(/^[\s\S]*?<body.*?>/i, "");
	sContent = sContent.replace(/<\/body>[\s\S]*?$/i, "");
	return sContent;
},

CustomParse: function(str)
{
	var str1, i, l;
	for (i = 0, l = arContentParsers.length; i < l; i++)
	{
		try{
			str1 = arContentParsers[i](str, this.pMainObj);
			if (str1 !== false && str1 !== null)
				str = str1;
		}catch(e){_alert('ERROR: '+e.message+'\n'+'BXParser.prototype.CustomParse'+'\n'+'Type: '+e.name);}
	}
	return str;
},

CustomContentUnParse: function(str)
{
	try{
		var str1, i, l;
		for (i = 0, l = arContentUnParsers.length; i < l; i++)
			if (str1 = arContentUnParsers[i](str, this.pMainObj))
				str = str1;
	}catch(e){_alert('ERROR: '+e.message+'\n'+'BXParser.prototype.CustomContentUnParse'+'\n'+'Type: '+e.name);}
	return str;
},

HTMLEntitiesReplace: function (str)
{
	var lEn = this.pMainObj.arEntities.length;
	for(var i_ = 0; i_ < lEn; i_++)
		str = str.replace(this.pMainObj.arEntities_h[i_], this.pMainObj.arEntities[i_], 'g');
	return str;
},

DOMHandle: function()
{
	try{
		for (var i = 0, l = arDOMHandlers.length; i < l; i++)
			arDOMHandlers[i](this.pMainObj.pEditorDocument);
	}catch(e){_alert('ERROR: '+e.message+'\n'+'BXParser.prototype.DOMHandle'+'\n'+'Type: '+e.name);}
},

AppendCSS : function(styles)
{
	styles = styles.trim();
	if (styles.length <= 0)
		return false;

	var
		pDoc = this.pMainObj.pEditorDocument,
		pHeads = pDoc.getElementsByTagName("HEAD");

	if(pHeads.length != 1)
		return false;

	if(BX.browser.IsIE())
	{
		setTimeout(function(){pDoc.styleSheets[0].cssText += styles;}, 5);
	}
	else
	{
		var xStyle = pDoc.createElement("STYLE");
		pHeads[0].appendChild(xStyle);
		xStyle.appendChild(pDoc.createTextNode(styles));
	}
	return true;
},

SymbolsParse: function(sContent)
{
	for (var s in this.arSym)
		if (typeof this.arSym[s] == 'object')
			sContent = sContent.replace(new RegExp(this.arSym[s][0], 'ig'), "<span id='" + this.pMainObj.SetBxTag(false, {tag: s}) + "'>" + this.arSym[s][1] + "</span>");

	return sContent;
}
}
