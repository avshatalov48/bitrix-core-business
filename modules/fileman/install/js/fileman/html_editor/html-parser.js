/**
 * Bitrix HTML Editor 3.0
 * Date: 24.04.13
 * Time: 4:23
 *
 * Parser class
 */


/**
 * HTML Sanitizer
 * Rewrites the HTML based on given rules
*/

(function()
{
	function BXEditorParser(editor)
	{
		this.editor = editor;
		this.specialParsers = {};

		// Rename unknown tags to this
		this.DEFAULT_NODE_NAME = "span",
		this.WHITE_SPACE_REG_EXP = /\s+/,
		this.defaultRules = {
			tags: {},
			classes: {}
		};
		this.convertedBxNodes = [];
		this.rules = {};

		if (!this.editor.util.FirstLetterSupported())
		{
			this.firstNodeCheck = false;
			this.FIRST_LETTER_CLASS = 'bxe-first-letter';
			this.FIRST_LETTER_CLASS_CHROME = 'bxe-first-letter-chrome';
			this.FIRST_LETTER_SPAN = 'bxe-first-letter-s';
		}
	}

	BXEditorParser.prototype = {
		/**
		 * Iterates over all childs of the element, recreates them, appends them into a document fragment
		 * which later replaces the entire body content
		 */
		Parse: function(content, rules, doc, cleanUp, parseBx)
		{
			if (!doc)
			{
				doc = document;
			}

			this.convertedBxNodes = [];
			this.firstNodeCheck = false;

			var
				frag = doc.createDocumentFragment(),
				el = this.GetAsDomElement(content, doc),
				newNode, addInvisibleNodes,
				firstChild;

			this.SetParseBxMode(parseBx);

			this.pasteNodeIndexTmp = BX.clone(this.editor.pasteNodeIndex);

			while (el.firstChild)
			{
				firstChild = el.firstChild;
				el.removeChild(firstChild);
				newNode = this.Convert(firstChild, cleanUp, parseBx, newNode);

				if (newNode)
				{
					addInvisibleNodes = !parseBx && this.CheckBlockNode(newNode);
					// mantis: 101249
					if (BX.browser.IsFirefox() && newNode.nodeName == 'DIV')
					{
						addInvisibleNodes = false;
					}

					frag.appendChild(newNode);

					if (addInvisibleNodes)
					{
						frag.appendChild(this.editor.util.GetInvisibleTextNode());
					}
				}
			}

			// Clear element contents
			el.innerHTML = "";

			// Insert new DOM tree
			el.appendChild(frag);

			content = this.RegexpContentParse(this.editor.GetInnerHtml(el), parseBx);

			return content;
		},

		SetParseBxMode: function(bParseBx)
		{
			this.bParseBx = !!bParseBx;
		},

		// here we can parse content as string, not as DOM
		CodeParse: function(content)
		{
			return content;
		},

		GetAsDomElement: function(html, doc)
		{
			if (!doc)
				doc = document;

			var el = doc.createElement("DIV");

			if (typeof(html) === "object" && html.nodeType)
			{
				el.appendChild(html);
			}
			else if (this.editor.util.CheckHTML5Support())
			{
				el.innerHTML = html;
			}
			else if (this.editor.util.CheckHTML5FullSupport())
			{
				el.style.display = "none";
				doc.body.appendChild(el);
				try {
					el.innerHTML = html;
				} catch(e) {}
				doc.body.removeChild(el);
			}

			return el;
		},

		Convert: function(oldNode, cleanUp, parseBx, prevNode)
		{
			var
				bCleanNodeAfterPaste = false,
				oldNodeType = oldNode.nodeType,
				oldChilds = oldNode.childNodes,
				newNode,
				newChild,
				i, bxTag;

			if (oldNodeType == 1)
			{
				if (oldNode.nodeName == 'IMG')
				{
					if (!oldNode.getAttribute("data-bx-orig-src"))
						oldNode.setAttribute("data-bx-orig-src", oldNode.getAttribute('src'));
					else
						oldNode.setAttribute("src", oldNode.getAttribute('data-bx-orig-src'));
				}

				if (this.editor.pasteHandleMode && (parseBx || this.editor.bbParseContentMode))
				{
					if (oldNode.id == 'bx-cursor-node')
					{
						return oldNode.ownerDocument.createTextNode(this.editor.INVISIBLE_CURSOR);
					}

					var bxPasteFlag = oldNode.getAttribute('data-bx-paste-flag');

					bCleanNodeAfterPaste = bxPasteFlag !== 'Y' && !this.pasteNodeIndexTmp[bxPasteFlag];

					if (oldNode && oldNode.id)
					{
						bxTag = this.editor.GetBxTag(oldNode.id);
						if (bxTag.tag)
						{
							bCleanNodeAfterPaste = false;
						}
					}

					if (bCleanNodeAfterPaste)
					{
						oldNode = this.CleanNodeAfterPaste(oldNode, prevNode);
						if (!oldNode)
						{
							return null;
						}
						oldChilds = oldNode.childNodes;
						oldNodeType = oldNode.nodeType;
					}
					oldNode.removeAttribute('data-bx-paste-flag');
					if (this.pasteNodeIndexTmp[bxPasteFlag])
						delete this.pasteNodeIndexTmp[bxPasteFlag];
				}
				else
				{
					if (oldNode.id == 'bx-cursor-node')
					{
						return oldNode.cloneNode(true);
					}
				}

				// Doublecheck nodetype
				if (oldNodeType == 1)
				{
					if (!oldNode.__bxparsed)
					{
						if (this.IsAnchor(oldNode) && !oldNode.getAttribute('data-bx-replace_with_children'))
						{
							newNode = oldNode.cloneNode(true);
							newNode.innerHTML = '';
							newChild = null;

							for (i = 0; i < oldChilds.length; i++)
							{
								newChild = this.Convert(oldChilds[i], cleanUp, parseBx, newChild);
								if (newChild)
								{
									newNode.appendChild(newChild);
								}
							}

							var attr = {};
							for (i = 0; i < newNode.attributes.length; i++)
							{
								if (newNode.attributes[i].name !== 'name')
									attr[newNode.attributes[i].name] = newNode.attributes[i].value;
							}

							oldNode = this.editor.phpParser.GetSurrogateNode("anchor", BX.message('BXEdAnchor') + ": #" + newNode.name, null, {
								html: newNode.innerHTML,
								name: newNode.name,
								attributes: attr
							});
						}
						else if(this.IsPrintBreak(oldNode))
						{
							oldNode = this.GetPrintBreakSurrogate(oldNode);
						}

						if (oldNode && oldNode.id)
						{
							bxTag = this.editor.GetBxTag(oldNode.id);
							if(bxTag.tag)
							{
								oldNode.__bxparsed = 1;
								// We've found bitrix-made node
								if (this.bParseBx)
								{
									newNode = oldNode.ownerDocument.createTextNode('~' + bxTag.id + '~');
									this.convertedBxNodes.push(bxTag);
								}
								else
								{
									newNode = oldNode.cloneNode(true);
								}
								return newNode;
							}
						}

						if (!newNode && oldNode.nodeType)
						{
							newNode = this.ConvertElement(oldNode, parseBx);
						}
					}
				}
			}
			else if (oldNodeType == 3)
			{
				newNode = this.HandleText(oldNode);
			}

			if (!newNode)
			{
				return null;
			}

			for (i = 0; i < oldChilds.length; i++)
			{
				newChild = this.Convert(oldChilds[i], cleanUp, parseBx);
				if (newChild)
				{
					newNode.appendChild(newChild);
				}
			}

			if (newNode.nodeType == 1)
			{
				// Cleanup style="" attribute for elements
				if (newNode.style && BX.util.trim(newNode.style.cssText) == '' && newNode.removeAttribute)
				{
					newNode.removeAttribute('style');
				}

				// Cleanup senseless <span> elements
				if (this.editor.config.cleanEmptySpans && cleanUp && newNode.childNodes.length <= 1 && newNode.nodeName.toLowerCase() === this.DEFAULT_NODE_NAME && !newNode.attributes.length)
				{
					return newNode.firstChild;
				}
			}

			return newNode;
		},

		ConvertElement: function(oldNode, parseBx)
		{
			var
				rule, i, value,
				newNode,
				new_rule,
				tagRules = this.editor.GetParseRules().tags,
				nodeName = oldNode.nodeName.toLowerCase(),
				scopeName = oldNode.scopeName;

			// We already parsed this element ignore it!
			if (oldNode.__bxparsed)
			{
				return null;
			}

			oldNode.__bxparsed = 1;

			if (oldNode.className === "bx-editor-temp")
			{
				return null;
			}

			if (scopeName && scopeName != "HTML")
			{
				nodeName = scopeName + ":" + nodeName;
			}

			/**
			 * Repair node
			 * IE is a bit bitchy when it comes to invalid nested markup which includes unclosed tags
			 * A <p> doesn't need to be closed according HTML4-5 spec, we simply replace it with a <div> to preserve its content and layout
			 */
			if (
				"outerHTML" in oldNode &&
					!this.editor.util.AutoCloseTagSupported() &&
					oldNode.nodeName === "P" &&
					oldNode.outerHTML.slice(-4).toLowerCase() !== "</p>")
			{
				nodeName = "div";
			}

			// chrome bug, mantis: #61981
			if (!this.editor.util.FirstLetterSupported() && oldNode.className)
			{
				if (oldNode.className == this.FIRST_LETTER_CLASS && !this.bParseBx)
				{
					this.HandleFirstLetterNode(oldNode);
				}
				else if (oldNode.className == this.FIRST_LETTER_CLASS_CHROME && this.bParseBx)
				{
					this.HandleFirstLetterNodeBack(oldNode);
				}
			}

			// Add "data-bx-no-border"="Y" for tables without borders
			if (nodeName == "table" && !this.bParseBx)
			{
				var border = parseInt(oldNode.getAttribute('border'), 10);
				if (!border)
				{
					oldNode.removeAttribute("border");
					oldNode.setAttribute("data-bx-no-border", "Y");
				}
			}

			if (nodeName in tagRules)
			{
				rule = tagRules[nodeName];
				if (!rule || rule.remove)
				{
					return null;
				}

				if (rule.clean_empty &&
					// Only empty node
					(oldNode.innerHTML === "" || oldNode.innerHTML === this.editor.INVISIBLE_SPACE)
					&&
					(!oldNode.className || oldNode.className == "")
					&&
					// We check lastCreatedId to prevent cleaning elements which just were created
					(!this.editor.lastCreatedId || this.editor.lastCreatedId != oldNode.getAttribute('data-bx-last-created-id'))
					)
				{
					return null;
				}

				rule = typeof(rule) === "string" ? {rename_tag: rule} : rule;

				// New rule can be applied throw the attribute 'data-bx-new-rule'
				new_rule = oldNode.getAttribute('data-bx-new-rule');
				if (new_rule)
				{
					rule[new_rule] = oldNode.getAttribute('data-bx-' + new_rule);
				}
			}
			else if (oldNode.firstChild)
			{
				rule = {rename_tag: this.DEFAULT_NODE_NAME};
			}
			else
			{
				// Remove empty unknown elements
				return null;
			}

			if (rule.convert_attributes && parseBx == false)
			{
				for (i in rule.convert_attributes)
				{
					if (rule.convert_attributes.hasOwnProperty(i) && oldNode.getAttribute(i))
					{
						value = this.ConvertAttributeValueToCss(i,rule.convert_attributes[i], oldNode.getAttribute(i));
						if (value !== false)
						{
							rule.replace_with_children = false;
							oldNode.style[rule.convert_attributes[i]] = value;
						}
						oldNode.removeAttribute(i);
					}
				}
			}

			if (rule.replace_with_children)
			{
				newNode = oldNode.ownerDocument.createDocumentFragment();
			}
			else
			{
				newNode = oldNode.ownerDocument.createElement(rule.rename_tag || nodeName);
				this.HandleAttributes(oldNode, newNode, rule, parseBx);
			}

			if (new_rule)
			{
				rule[new_rule] = null;
				delete rule[new_rule];
			}

			if ((!newNode.className || newNode.className == '') && newNode.removeAttribute)
			{
				newNode.removeAttribute('class');
			}

			oldNode = null;
			return newNode;
		},

		CleanNodeAfterPaste: function(oldNode, prevNode)
		{
			var
				name, i,
				nodeName = oldNode.nodeName,
				innerHtml = oldNode.innerHTML,
				innerHtmlTrimed = BX.util.trim(innerHtml),
				whiteAttributes = {align: 1, alt: 1, bgcolor: 1, border: 1, cellpadding: 1, cellspacing: 1, color:1, colspan:1, height: 1, href: 1, rowspan: 1, size: 1, span: 1, src: 1, style: 1, target: 1, title: 1, type: 1, value: 1, width: 1},
				decorNodes = {"B": 1, "STRONG": 1, "I": 1, "EM": 1, "U": 1, "DEL": 1, "S": 1, "STRIKE": 1},
				cleanEmpty = {"A": 1, "SPAN": 1, "B": 1, "STRONG": 1, "I": 1, "EM": 1, "U": 1, "DEL": 1, "S": 1, "STRIKE": 1, "H1": 1, "H2": 1, "H3": 1, "H4": 1, "H5": 1, "H6": 1, "ABBR": 1, "TIME": 1, "FIGURE": 1,  "FIGCAPTION": 1};

			// Clean iframes
			if (nodeName == 'IFRAME')
			{
				return null;
			}

			// Clean items with display: none
			if (oldNode.style.display == 'none' || oldNode.style.visibility == 'hidden')
			{
				return null;
			}

			// Clean empty nodes
			if (cleanEmpty[nodeName] && innerHtml == '')
			{
				return null;
			}

			var cleanAttribute = oldNode.getAttribute('data-bx-clean-attribute');
			if (cleanAttribute)
			{
				oldNode.removeAttribute(cleanAttribute);
				oldNode.removeAttribute('data-bx-clean-attribute');
			}

			// Drag & Drop of the images
			if (nodeName == 'IMG')
			{
				var alt = oldNode.getAttribute('alt');
				if(alt === '')
				{
					oldNode.removeAttribute('alt');
				}
				else if(typeof alt == 'string' && alt !== '' && alt.indexOf('://') !== -1)
				{
					this.CheckAltImage(oldNode);
				}
				oldNode.removeAttribute('class');
				this.CleanNodeCss(oldNode);
				return oldNode;
			}

			// Clean anchors
			if (nodeName == 'A' && (innerHtmlTrimed == '' || innerHtmlTrimed == '&nbsp;'))
			{
				return null;
			}

			if (nodeName === 'A')
			{
				BX.onCustomEvent(this.editor, 'OnAfterLinkInserted', [oldNode.getAttribute('href')]);
			}

			// Clean class
			oldNode.removeAttribute('class');
			oldNode.removeAttribute('id');

			// Clean attributes corresponding to white list from above
			i = 0;
			while (i < oldNode.attributes.length)
			{
				name = oldNode.attributes[i].name;
				if (!whiteAttributes[name] || oldNode.attributes[i].value == '')
				{
					oldNode.removeAttribute(name);
				}
				else
				{
					i++;
				}
			}

			//mantis:74639
			if (nodeName == 'THEAD')
			{
				var trs = oldNode.getElementsByTagName('TR'), st;
				for (i = 0; i < trs.length; i++)
				{
					if (trs[i] && trs[i].getAttribute)
					{
						st = trs[i].getAttribute('style');
						if (st
							&& st.indexOf('mso-yfti-irow') !== -1
							&& st.indexOf('mso-yfti-irow:0') === -1
							&& st.indexOf('mso-yfti-irow:-1') === -1
							&& st.indexOf('mso-yfti-firstrow:yes') === -1
						)
						{
							oldNode.setAttribute('data-bx-new-rule', 'rename_tag');
							oldNode.setAttribute('data-bx-rename_tag', 'TBODY');
							break;
						}
					}
				}
			}

			// Clean pasted div's
			if (nodeName == 'DIV' || oldNode.style.display == 'block' || nodeName == 'FORM')
			{
				if (!oldNode.lastChild || (oldNode.lastChild && oldNode.lastChild.nodeName != 'BR'))
				{
					oldNode.appendChild(oldNode.ownerDocument.createElement("BR")).setAttribute('data-bx-paste-flag', 'Y');
				}

				// mantis #54501
				if (prevNode && typeof prevNode == 'object' && prevNode.nodeType == 3 && oldNode.firstChild)
				{
					oldNode.insertBefore(oldNode.ownerDocument.createElement("BR"), oldNode.firstChild).setAttribute('data-bx-paste-flag', 'Y');
				}

				oldNode.setAttribute('data-bx-new-rule', 'replace_with_children');
				oldNode.setAttribute('data-bx-replace_with_children', '1');
			}

			// Content pasted from google docs sometimes comes with unused <b style="font-weight: normal"> wrapping
			if (nodeName == 'B' && oldNode.style.fontWeight == 'normal')
			{
				oldNode.setAttribute('data-bx-new-rule', 'replace_with_children');
				oldNode.setAttribute('data-bx-replace_with_children', '1');
			}

			if (decorNodes[nodeName] && this.editor.config.pasteSetDecor)
			{
				oldNode.setAttribute('data-bx-new-rule', 'replace_with_children');
				oldNode.setAttribute('data-bx-replace_with_children', '1');
			}

			if (decorNodes[nodeName] && this.editor.config.pasteSetDecor)
			{
				oldNode.setAttribute('data-bx-new-rule', 'replace_with_children');
				oldNode.setAttribute('data-bx-replace_with_children', '1');
			}

			if (this.IsAnchor(oldNode) && (oldNode.name == '' || BX.util.trim(oldNode.name == '')))
			{
				oldNode.setAttribute('data-bx-new-rule', 'replace_with_children');
				oldNode.setAttribute('data-bx-replace_with_children', '1');
			}

			if (BX.util.in_array(nodeName, this.editor.TABLE_TAGS) && this.editor.config.pasteClearTableDimen)
			{
				oldNode.removeAttribute('width');
				oldNode.removeAttribute('height');
			}

			this.CleanNodeCss(oldNode);

			// Clear useless spans
			if (nodeName == 'SPAN' && oldNode.style.cssText == '')
			{
				oldNode.setAttribute('data-bx-new-rule', 'replace_with_children');
				oldNode.setAttribute('data-bx-replace_with_children', '1');
			}

			// Replace <p>&nbsp;</p> ==> <p> </p>, <span>&nbsp;</span> ==> <span> </span>
			if ((nodeName == 'P' || nodeName == 'SPAN' || nodeName == 'FONT') && BX.util.trim(oldNode.innerHTML) == "&nbsp;")
			{
				oldNode.innerHTML = ' ';
			}

			return oldNode;
		},

		CleanNodeCss: function(node)
		{
			var
				styles, j, styleName, styleValue, i,
				nodeName = node.nodeName,
				whiteCssList = {
					'width': ['auto'],
					'height': ['auto']
				};

			if (BX.util.in_array(nodeName, this.editor.TABLE_TAGS) && this.editor.config.pasteClearTableDimen)
			{
				whiteCssList = {};
			}

			if (!this.editor.config.pasteSetColors)
			{
				whiteCssList['color'] = ['#000000', '#000', 'black'];
				whiteCssList['background-color'] = ['transparent', '#fff', '#ffffff', 'white'];
				whiteCssList['background'] = 1;
			}

			if (!this.editor.config.pasteSetBorders)
			{
				whiteCssList['border-collapse'] = 1;
				whiteCssList['border-color'] = ['transparent', '#fff', '#ffffff', 'white'];
				whiteCssList['border-style'] = ['none', 'hidden'];
				whiteCssList['border-top'] = ['0px', '0'];
				whiteCssList['border-right'] = ['0px', '0'];
				whiteCssList['border-bottom'] = ['0px', '0'];
				whiteCssList['border-left'] = ['0px', '0'];
				whiteCssList['border-top-color'] = ['transparent', '#fff', '#ffffff', 'white'];
				whiteCssList['border-right-color'] = ['transparent', '#fff', '#ffffff', 'white'];
				whiteCssList['border-bottom-color'] = ['transparent', '#fff', '#ffffff', 'white'];
				whiteCssList['border-left-color'] = ['transparent', '#fff', '#ffffff', 'white'];
				whiteCssList['border-top-style'] = ['none', 'hidden'];
				whiteCssList['border-right-style'] = ['none', 'hidden'];
				whiteCssList['border-bottom-style'] = ['none', 'hidden'];
				whiteCssList['border-left-style'] = ['none', 'hidden'];
				whiteCssList['border-top-width'] = ['0px', '0'];
				whiteCssList['border-right-width'] = ['0px', '0'];
				whiteCssList['border-bottom-width'] = ['0px', '0'];
				whiteCssList['border-left-width'] = ['0px', '0'];
				whiteCssList['border-width'] = ['0px', '0'];
				whiteCssList['border'] = ['0px', '0'];
			}

			if (!this.editor.config.pasteSetDecor)
			{
				whiteCssList['font-style'] = ['normal'];
				whiteCssList['font-weight'] = ['normal'];
				whiteCssList['text-decoration'] = ['none'];
			}

			// Clean style
			if (node.style && BX.util.trim(node.style.cssText) != '' && nodeName !== 'BR')
			{
				styles = [];
				for (i in node.style)
				{
					if (node.style.hasOwnProperty(i))
					{
						if (parseInt(i).toString() === i)
						{
							styleName = node.style[i];
							styleValue = node.style.getPropertyValue(styleName);
						}
						else
						{
							styleName = i;
							styleValue = node.style.getPropertyValue(styleName);
						}

						if (styleValue === null)
							continue;

						if (!whiteCssList[styleName] ||
							styleValue.match(/^-(moz|webkit|ms|o)/ig) ||
							styleValue == 'inherit' ||
							styleValue == 'initial' ||
							(styleName == 'color' && nodeName == 'A') || // Color for links
							((nodeName == 'SPAN' || nodeName == 'P') && (styleName == 'width' || styleName == 'height')) || // Sizes for SPAN and P
							(typeof whiteCssList[styleName] == 'object' && BX.util.in_array(styleValue.toLowerCase(), whiteCssList[styleName])))
						{

							continue;
						}

						// Clean colors like rgb(0,0,0)
						if (styleName.indexOf('color') !== -1)
						{
							styleValue = this.editor.util.RgbToHex(styleValue);
							if ((typeof whiteCssList[styleName] == 'object' && BX.util.in_array(styleValue.toLowerCase(), whiteCssList[styleName])) ||
								styleValue == 'transparent')
							{
								continue;
							}
						}

						// Clean hidden borders, for example: border-top: medium none;
						if (styleName.indexOf('border') !== -1 && styleValue.indexOf('none') !== -1)
						{
							continue;
						}

						styles.push({name: styleName, value: styleValue});
					}

				}

				node.removeAttribute('style');
				if (styles.length > 0)
				{
					for (j = 0; j < styles.length; j++)
					{
						node.style[styles[j].name] = styles[j].value;
					}
				}
			}
			else
			{
				node.removeAttribute('style');
			}
		},

		CheckAltImage: function(img)
		{
			var doc = this.editor.GetIframeDoc();

			function getImageBySrc(src)
			{
				var
					i,
					imgs = doc.getElementsByTagName('IMG');

				for (i = 0; i < imgs.length; i++)
				{
					if (imgs[i].src === src)
					{
						return imgs[i];
					}
				}
			}

			function onload()
			{
				if (img.src === img.alt && img.getAttribute('data-bx-orig-src') !== img.src)
				{
					img.setAttribute("data-bx-orig-src", img.getAttribute('src'));
				}

				BX.unbind(img, 'load', onload);
				BX.unbind(img, 'error', onerror);
			}

			function onerror()
			{
				var
					alt = img.getAttribute('alt'),
					imgNode = getImageBySrc(img.src);

				if (!imgNode)
				{
					BX.unbind(img, 'load', onload);
					BX.unbind(img, 'error', onerror);
					return;
				}
				if (img.getAttribute('src') !== img.alt)
				{
					imgNode.setAttribute("src", alt);
				}
				else
				{
					imgNode.setAttribute("src", img.getAttribute('data-bx-orig-src'));
					BX.unbind(img, 'load', onload);
					BX.unbind(img, 'error', onerror);
				}
			}

			BX.bind(img, 'load', onload);
			BX.bind(img, 'error', onerror);
		},

		HandleText: function(node)
		{
			var data = node.data;
			if (this.editor.pasteHandleMode && data.indexOf('EndFragment:') !== -1)
			{
				// Clean content inserted from OpenOffice in MacOs
				data = data.replace(/Version:\d\.\d(?:\s|\S)*?StartHTML:\d+(?:\s|\S)*?EndHTML:\d+(?:\s|\S)*?StartFragment:\d+(?:\s|\S)*?EndFragment:\d+(?:\s|\n|\t|\r)*/g, '');
			}

			return node.ownerDocument.createTextNode(data);
		},

		HandleAttributes: function(oldNode, newNode, rule, parseBx)
		{
			var
				attributes = {}, // fresh new set of attributes to set on newNode
				setClass = rule.set_class, // classes to set
				addClass = rule.add_class, // add classes based on existing attributes
				addCss = rule.add_css, // add classes based on existing attributes
				setAttributes = rule.set_attributes, // attributes to set on the current node
				checkAttributes = rule.check_attributes, // check/convert values of attributes
				clearAttributes = rule.clear_attributes, // clean all unknown attributes
				allowedClasses = this.editor.GetParseRules().classes,
				i = 0, newName, skipAttributes = {},
				st,
				classes = [],
				newClasses = [],
				newUniqueClasses = [],
				oldClasses = [],
				classesLength,
				newClassesLength,
				currentClass,
				newClass,
				attribute,
				attributeName,
				newAttributeValue,
				handler;

			if (checkAttributes)
			{
				for (attributeName in checkAttributes)
				{
					handler = this.GetCheckAttributeHandler(checkAttributes[attributeName]);
					if (!handler)
						continue;

					newAttributeValue = handler(this.GetAttributeEx(oldNode, attributeName));
					if (typeof(newAttributeValue) === "string" && newAttributeValue !== '')
						attributes[attributeName] = newAttributeValue;
				}
			}

			var cleanAttribute = oldNode.getAttribute('data-bx-clean-attribute');
			if (cleanAttribute)
			{
				oldNode.removeAttribute(cleanAttribute);
				oldNode.removeAttribute('data-bx-clean-attribute');
			}

			if (!clearAttributes)
			{
				for (i = 0; i < oldNode.attributes.length; i++)
				{
					attribute = oldNode.attributes[i];
					if (parseBx)
					{
						if (attribute.name.substr(0, 15) == 'data-bx-app-ex-')
						{
							newName = attribute.name.substr(15);
							attributes[newName] = oldNode.getAttribute(attribute.name);
							skipAttributes[newName] = true;
						}

						if (skipAttributes[attribute.name])
						{
							continue;
						}
					}

					// clear bitrix attributes
					if (attribute.name.substr(0, 8) == 'data-bx-'
						&& attribute.name != 'data-bx-noindex'
						&& this.bParseBx)
					{
						continue;
					}
					attributes[attribute.name] = this.GetAttributeEx(oldNode, attribute.name);
				}
			}

			if (setClass)
				classes.push(setClass);

			if (addCss)
			{
				for (st in addCss)
				{
					if (addCss.hasOwnProperty(st))
						newNode.style[st] = addCss[st];
				}
			}

			/*
			// TODO:
			if (addClass)
			{
				var addClassMethods = {
					align_img: (function() {
						var mapping = {
							left: "wysiwyg-float-left",
							right: "wysiwyg-float-right"
						};
						return function(attributeValue) {
							return mapping[String(attributeValue).toLowerCase()];
						};
					})(),

					align_text: (function() {
						var mapping = {
							left: "wysiwyg-text-align-left",
							right: "wysiwyg-text-align-right",
							center: "wysiwyg-text-align-center",
							justify: "wysiwyg-text-align-justify"
						};
						return function(attributeValue) {
							return mapping[String(attributeValue).toLowerCase()];
						};
					})(),

					clear_br: (function() {
						var mapping = {
							left: "wysiwyg-clear-left",
							right: "wysiwyg-clear-right",
							both: "wysiwyg-clear-both",
							all: "wysiwyg-clear-both"
						};
						return function(attributeValue) {
							return mapping[String(attributeValue).toLowerCase()];
						};
					})(),

					size_font: (function() {
						var mapping = {
							"1": "wysiwyg-font-size-xx-small",
							"2": "wysiwyg-font-size-small",
							"3": "wysiwyg-font-size-medium",
							"4": "wysiwyg-font-size-large",
							"5": "wysiwyg-font-size-x-large",
							"6": "wysiwyg-font-size-xx-large",
							"7": "wysiwyg-font-size-xx-large",
							"-": "wysiwyg-font-size-smaller",
							"+": "wysiwyg-font-size-larger"
						};
						return function(attributeValue) {
							return mapping[String(attributeValue).charAt(0)];
						};
					})()
				};

				for (attributeName in addClass)
				{
					handler = addClassMethods[addClass[attributeName]];
					if (!handler)
						continue;
					newClass = handler(this.GetAttributeEx(oldNode, attributeName));
					if (typeof(newClass) === "string")
						classes.push(newClass);
				}
			}
			*/

			// add old classes last
			oldClasses = oldNode.getAttribute("class");
			if (oldClasses)
				classes = classes.concat(oldClasses.split(this.WHITE_SPACE_REG_EXP));

			classesLength = classes.length;
			for (; i<classesLength; i++)
			{
				currentClass = classes[i];
				if (allowedClasses[currentClass])
					newClasses.push(currentClass);
			}

			if (newUniqueClasses.length)
				attributes["class"] = newUniqueClasses.join(" ");

			// set attributes on newNode
			for (attributeName in attributes)
			{
				// Setting attributes can cause a js error in IE under certain circumstances
				// eg. on a <img> under https when it's new attribute value is non-https
				// TODO: Investigate this further and check for smarter handling
				try {
					newNode.setAttribute(attributeName, attributes[attributeName]);
				} catch(e) {}
			}

			// IE8 sometimes loses the width/height attributes when those are set before the "src"
			// so we make sure to set them again
			if (attributes.src)
			{
				if (typeof(attributes.width) !== "undefined")
					newNode.setAttribute("width", attributes.width);
				if (typeof(attributes.height) !== "undefined")
					newNode.setAttribute("height", attributes.height);
			}
		},

		ConvertAttributeValueToCss: function(attribute, css, value)
		{
			if (attribute == 'size' && css == 'fontSize') // fontsize
			{
				var fontSizeMap = {
					1: '9px',
					2: '13px',
					3: '16px',
					4: '18px',
					5: '24px',
					6: '32px',
					7: '48px'
				};
				if (fontSizeMap[value])
				{
					value = fontSizeMap[value];
				}
				else if (value < 1)
				{
					value = false;
				}
				else if (value > 7)
				{
					value = fontSizeMap[7];
				}
			}
			return value;
		},

		GetAttributeEx: function(node, attributeName)
		{
			attributeName = attributeName.toLowerCase();
			var
				res,
				nodeName = node.nodeName;

			if (nodeName == "IMG" && attributeName == "src" && this.IsLoadedImage(node) === true)
			{
				res = node.getAttribute('src');
			}
			else if (!this.editor.util.CheckGetAttributeTruth() && "outerHTML" in node)
			{
				var
					outerHTML = node.outerHTML.toLowerCase(),
					hasAttribute = outerHTML.indexOf(" " + attributeName + "=") != -1;

				res = hasAttribute ? node.getAttribute(attributeName) : null;
			}
			else
			{
				res = node.getAttribute(attributeName);
			}

			return res;
		},

		IsLoadedImage: function(node)
		{
			try
			{
				return node.complete && !node.mozMatchesSelector(":-moz-broken");
			}
			catch(e)
			{
				if (node.complete && node.readyState === "complete")
					return true;
			}
			return false;
		},

		GetCheckAttributeHandler: function(attrName)
		{
			var methods = this.GetCheckAttributeHandlers();
			return methods[attrName];
		},

		GetCheckAttributeHandlers: function()
		{
			return {
				url: function(attributeValue)
				{
					return attributeValue;
//					if (!attributeValue || !attributeValue.match(/^https?:\/\//i))
//						return null;
//					return attributeValue.replace(/^https?:\/\//i, function(match){return match.toLowerCase();});
				},

				alt: function(attributeValue)
				{
					if (!attributeValue)
					{
						return "";
					}
					return attributeValue.replace(/[^ a-z0-9_\-]/gi, "");
				},

				numbers: function(attributeValue)
				{
					attributeValue = (attributeValue || "").replace(/\D/g, "");
					return attributeValue || null;
				}
			};
		},

		HandleBitrixNode: function(node)
		{
			return node;
		},

		RegexpContentParse: function(content, parseBx)
		{
			// parse color inside style attributes RGB ==> HEX
			// TODO: it will cause wrong replace if rgba will be not inside style attribute...
			if (content.indexOf('rgb') !== -1)
			{
				content = content.replace(/rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+))?\)/ig, function(str, h1, h2, h3, h4)
				{
					function hex(x)
					{
						return ("0" + parseInt(x).toString(16)).slice(-2);
					}
					return "#" + hex(h1) + hex(h2) + hex(h3);
				});
			}

			if (parseBx && content.indexOf('data-bx-noindex') !== -1)
			{
				content = content.replace(/(<a[^<]*?)data\-bx\-noindex="Y"([\s\S]*?>[\s\S]*?\/a>)/ig, function(s, s1, s2)
				{
					return '<!--noindex-->' + s1 + s2 + '<!--\/noindex-->';
				});
			}

			if (parseBx)
			{
				content = content.replace(/\uFEFF/ig, '');
			}
			else
			{
				content = content.replace(/\uFEFF+/ig, this.editor.INVISIBLE_SPACE);
			}

			if (parseBx && content.indexOf('#BXAPP') !== -1)
			{
				var _this = this;
				content = content.replace(/#BXAPP(\d+)#/g, function(str, ind)
				{
					ind = parseInt(ind, 10);
					return _this.editor.phpParser.AdvancedPhpGetFragmentByIndex(ind, true);
				});
			}

			return content;
		},

		IsAnchor: function(n)
		{
			return n.nodeName == 'A' && !n.href;
		},

		IsPrintBreak: function(n)
		{
			return n.style.pageBreakAfter == 'always';
		},

		GetPrintBreakSurrogate: function(node)
		{
			var
				doc = this.editor.GetIframeDoc(),
				id = this.editor.SetBxTag(false, {tag: 'printbreak', params: {innerHTML: BX.util.trim(node.innerHTML)}, name: BX.message('BXEdPrintBreakName'), title: BX.message('BXEdPrintBreakTitle')});

			return BX.create('IMG', {props: {src: this.editor.EMPTY_IMAGE_SRC, id: id,className: "bxhtmled-printbreak", title: BX.message('BXEdPrintBreakTitle')}}, doc);
		},

		CheckBlockNode: function(node)
		{
			return this.editor.phpParser.IsSurrogate(node) ||
				(node.nodeType == 1 &&
					(
						node.style.display == 'block' || node.style.display == 'inline-block' ||
						node.nodeName == 'BLOCKQUOTE' || node.nodeName == 'DIV'
					)
				);
		},

		// For chrome only
		HandleFirstLetterNode: function(node)
		{
			this.firstNodeCheck = true;
			node.className = this.FIRST_LETTER_CLASS_CHROME;
			var
				createSpan = true,
				doc = this.editor.GetIframeDoc(),
				textContent, firstSpanContent,
				flTextNode = this._GetFlTextNode(node),
				flSpan = this._GetFlSpan(node);

			if (flTextNode)
			{
				textContent = BX.util.trim(this.editor.util.GetTextContent(flTextNode));

				if (flSpan)
				{
					this._FLCleanNodesBeforeFirstSpan(flSpan);
					firstSpanContent = BX.util.trim(this.editor.util.GetTextContent(flSpan));

					if (textContent.substr(0, 1) == firstSpanContent && firstSpanContent.length == 1)
					{
						createSpan = false;
					}
					else if (textContent == firstSpanContent && firstSpanContent.length > 1)
					{
						createSpan = false;
						this.editor.SetCursorNode();
						this.editor.util.InsertAfter(doc.createTextNode(textContent.substr(1)), flSpan);
						this.editor.RestoreCursor();
						flSpan.innerHTML = textContent.substr(0, 1);
					}
					this._FLCleanNodesBeforeFirstSpan(flSpan);
				}

				if (createSpan)
				{
					if (flSpan)
					{
						this.editor.SetCursorNode();
						this.editor.util.ReplaceWithOwnChildren(flSpan);
						this.editor.RestoreCursor();
					}

					flSpan = BX.create('SPAN', {props: {className: this.FIRST_LETTER_SPAN}, text: textContent.substr(0, 1)}, node.ownerDocument);
					this.editor.util.SetTextContent(flTextNode, textContent.substr(1));
					flTextNode.parentNode.insertBefore(flSpan, flTextNode);
					this._FLCleanNodesBeforeFirstSpan(flSpan);
				}

				this._FLCleanBogusSpans(node);
			}
		},

		HandleFirstLetterNodeBack: function(node)
		{
			this.firstNodeCheck = true;
			node.className = this.FIRST_LETTER_CLASS;
			var flSpan = this._GetFlSpan(node);
			if (flSpan)
			{
				this.editor.util.ReplaceWithOwnChildren(flSpan);
			}
		},

		_GetFlSpan: function(node)
		{
			return BX.findChild(node, {className: this.FIRST_LETTER_SPAN}, 1);
		},

		_GetFlTextNode: function(node)
		{
			if (node.innerHTML == '' || !node.firstChild)
				return null;

			if (node.firstChild && node.firstChild.nodeType == 3 && node.firstChild.nodeValue && BX.util.trim(node.firstChild.nodeValue) !== '')
				return node.firstChild;

			var
				iter = 0,
				_this = this,
				nodeI = node;

			while(iter++ <= 100)
			{
				nodeI = BX.findChild(nodeI, function(n){return (BX.util.trim(_this.editor.util.GetTextContent(n)) !== '');}, false);
				if (nodeI.nodeType == 3 && nodeI.nodeValue && BX.util.trim(nodeI.nodeValue) !== '')
				{
					return nodeI;
				}
			}

			return null;
		},

		FirstLetterCheckNodes: function(content, contentTextarea, hardCheck)
		{
			var doc = this.editor.GetIframeDoc(), i;
			if (this.firstNodeCheck || content.indexOf(this.FIRST_LETTER_CLASS) !== -1 || hardCheck === true)
			{
				var
					html,
					nodes1 = doc.querySelectorAll('.' + this.FIRST_LETTER_CLASS),
					nodes2 = doc.querySelectorAll('.' + this.FIRST_LETTER_CLASS_CHROME);

				for (i = 0; i < nodes2.length; i++)
				{
					html = BX.util.trim(nodes2[i].innerHTML);
					if (html == '' || html == '<br>')
					{
						BX.remove(nodes2[i]);
						continue;
					}
					this.HandleFirstLetterNode(nodes2[i]);
				}

				for (i = 0; i < nodes1.length; i++)
				{
					this.HandleFirstLetterNode(nodes1[i]);
				}

				this.firstNodeCheck = !!(nodes1.length || nodes2.length);
			}

			if (!this.firstNodeCheck && content.indexOf(this.FIRST_LETTER_SPAN) !== -1)
			{
				var spans = doc.querySelectorAll('.' + this.FIRST_LETTER_SPAN);
				for (i = 0; i < spans.length; i++)
				{
					this.editor.util.ReplaceWithOwnChildren(spans[i]);
				}
			}
		},

		_FLCleanNodesBeforeFirstSpan: function(span)
		{
			while (span.previousSibling)
			{
				BX.remove(span.previousSibling);
			}
		},

		_FLCleanBogusSpans: function(node)
		{
			var i, spans = node.getElementsByTagName('SPAN');
			for (i = spans.length - 1; i >= 0; i--)
			{
				if (!spans[i].className && spans[i].style.lineHeight && spans[i].style.fontSize)
					this.editor.util.ReplaceWithOwnChildren(spans[i]);
			}
		},

		FirstLetterBackspaceHandler: function(range)
		{
			if (range.collapsed && range.startOffset == 0)
			{
				var flSpan = range.startContainer.previousSibling;
				if (flSpan && flSpan.className.indexOf(this.FIRST_LETTER_SPAN) !== -1)
				{
					this.editor.selection.SetAfter(flSpan.lastChild);
				}
			}
		}
	};


	function BXEditorPhpParser(editor)
	{
		this.PHP_PATTERN = '#BXPHP_IND#';
		this.editor = editor;

		this.allowed = {
			php: this.editor.allowPhp || this.editor.lpa,
			javascript: true,
			style: true,
			htmlcomment: true,
			iframe: true,
			video: true,
			audio: true,
			'object': true
		};

		this.bUseAPP = true; // APP - AdvancedPHPParser
		this.APPConfig =
		{
			arTags_before : ['tbody','thead','tfoot','tr','td','th'],
			arTags_after : ['tbody','thead','tfoot','tr','td','th'],
			arTags :
			{
				'a' : ['href','title','class','style'],
				'img' : ['src','alt','class','style','width','height'],
				'input' : ['id','name','value']
			}
		};

		this.customParsers = [];
		this.arScripts = {}; // object which contains all php codes with indexes
		this.arJavascripts = {}; // object which contains all javascripts codes with indexes
		this.arHtmlComments = {}; // object which contains all html comments with indexes
		this.arIframes = {}; // object which contains all iframes with indexes
		this.arVideos = {}; // object which contains all iframes with emeded videos
		this.arAudio = {};
		this.arStyles = {}; // object which contains all <style> tags with indexes
		this.arObjects = {}; // object which contains all <object> tags with indexes
		this.surrClass = 'bxhtmled-surrogate';

		this.surrogateTags = {
			component: 1,
			php: 1,
			javascript: 1,
			style: 1,
			htmlcomment: 1,
			anchor: 1,
			iframe: 1,
			video: 1,
			audio: 1,
			'object': 1
		};

		BX.addCustomEvent(this.editor, "OnIframeMouseDown", BX.proxy(this.OnSurrogateMousedown, this));
		//BX.addCustomEvent(this.editor, "OnIframeClick", BX.proxy(this.OnSurrogateClick, this));
		BX.addCustomEvent(this.editor, "OnIframeDblClick", BX.proxy(this.OnSurrogateDblClick, this));
		BX.addCustomEvent(this.editor, "OnIframeKeydown", BX.proxy(this.OnSurrogateKeydown, this));
		//BX.addCustomEvent(this.editor, "OnIframeKeyup", BX.proxy(this.OnSurrogateKeyup, this));
		BX.addCustomEvent(this.editor, "OnAfterCommandExec", BX.proxy(this.RenewSurrogates, this));
	}
	//BX.extend(BXEditorPhpParser, BXEditorParser);

	BXEditorPhpParser.prototype = {
		ParsePhp: function(content)
		{
			var _this = this;
			//1. All fragments of the php code we replace by special str - #BXPHP_IND#
			if (this.IsAllowed('php'))
			{
				content = this.ReplacePhpBySymCode(content);
			}
			else
			{
				content = this.CleanPhp(content);
			}

			// Custom parse
			content = this.CustomContentParse(content);

			// Javascript
			content = this.ReplaceJavascriptBySymCode(content);
			// Html comments
			content = this.ReplaceHtmlCommentsBySymCode(content);
			// Iframe & Video
			content = this.ReplaceIframeBySymCode(content);
			// Audio
			content = this.ReplaceAudioBySymCode(content);
			// Style
			content = this.ReplaceStyleBySymCode(content);
			// Object && embed
			content = this.ReplaceObjectBySymCode(content);
			// <Break>
			content = this.ParseBreak(content);

			//2. We trying to resolve html tags with PHP code inside
			content = this.AdvancedPhpParse(content);

			//3. We replace all #BXPHP_IND# and other sym codes by visual custom elements
			content = this.ParseSymCode(content);

			// 4. LPA
			if (this.editor.lpa)
			{
				content = content.replace(/#PHP(\d+)#/g, function(str)
				{
					return  _this.GetSurrogateHTML("php_protected", BX.message('BXEdPhpCode') + " *", BX.message('BXEdPhpCodeProtected'), {value : str});
				});

				content = content.replace(/#BXAPP(\d+)#/g, function(str, appInd)
				{
					appInd = parseInt(appInd);
					return _this.editor.phpParser.AdvancedPhpGetFragmentByIndex(appInd, false);
				});
			}

			return content;
		},

		// Example:
		// <?...?> => #BXPHP0#
		ReplacePhpBySymCode: function(content, cleanPhp)
		{
			var
				arScripts = [],
				p = 0, i,
				bSlashed,
				bInString, ch, posnext, ti, quote_ch, mm = 0;

			cleanPhp = cleanPhp === true;
			while((p = content.indexOf("<?", p)) >= 0)
			{
				mm = 0;
				i = p + 1;
				bSlashed = false;
				bInString = false;
				while(i < content.length - 1)
				{
					i++;
					ch = content.substr(i, 1);

					if(!bInString)
					{
						//if it's not comment
						if(ch == "/" && i + 1 < content.length)
						{
							//find end of php fragment php
							posnext = content.indexOf("?>", i);
							if(posnext == -1)
							{
								//if it's no close tag - so script is unfinished
								p = content.length;
								break;
							}
							posnext += 2;

							ti = 0;
							if(content.substr(i + 1, 1)=="*" && (ti = content.indexOf("*/", i + 2))>=0)
							{
								ti += 2;
							}
							else if(content.substr(i + 1, 1)=="/" && (ti = content.indexOf("\n", i + 2))>=0)
							{
								ti += 1;
							}

							if(ti>0)
							{
								//find begin - "i" and end - "ti" of comment
								// check: what is coming sooner: "END of COMMENT" or "END of SCRIPT"
								if(ti > posnext && content.substr(i + 1, 1) != "*")
								{
									//if script is finished - CUT THE SCRIPT
									arScripts.push([p, posnext, content.substr(p, posnext - p)]);
									p = posnext;
									break;
								}
								else
								{
									i = ti - 1; //End of comment come sooner
								}
							}
							continue;
						}
						if(ch == "?" && i + 1 < content.length && content.substr(i + 1, 1) == ">")
						{
							i = i + 2;
							arScripts.push([p, i, content.substr(p, i - p)]);
							p = i + 1;
							break;
						}
					}

					if(bInString && ch == "\\")
					{
						bSlashed = true;
						continue;
					}

					if(ch == "\"" || ch == "'")
					{
						if(bInString)
						{
							if(!bSlashed && quote_ch == ch)
								bInString = false;
						}
						else
						{
							bInString = true;
							quote_ch = ch;
						}
					}

					bSlashed = false;
				}

				if(i >= content.length)
					break;

				p = i;
			}

			this.arScripts = {};
			if(arScripts.length > 0)
			{
				var
					newstr = "",
					plast = 0,
					arScript;

				if (cleanPhp)
				{
					for(i = 0; i < arScripts.length; i++)
					{
						arScript = arScripts[i];
						newstr += content.substr(plast, arScript[0] - plast);
						plast = arScript[1];
					}
				}
				else
				{
					for(i = 0; i < arScripts.length; i++)
					{
						arScript = arScripts[i];
						newstr += content.substr(plast, arScript[0] - plast) + this.SavePhpCode(arScript[2], i);
						plast = arScript[1];
					}
				}

				content = newstr + content.substr(plast);
			}

			return content;
		},

		CleanPhp: function(content)
		{
			return this.ReplacePhpBySymCode(content, true);
		},

		// Example: <script>...</script> => #BXJAVASCRIPT_1#
		ReplaceJavascriptBySymCode: function(content)
		{
			this.arJavascripts = {};
			var
				_this = this,
				index = 0;

			content = content.replace(/<script[\s\S]*?\/script>/gi, function(s)
				{
					_this.arJavascripts[index] = s;
					var code = _this.GetPattern(index, false, 'javascript');
					index++;
					return code;
				}
			);
			return content;
		},

		// Example: <!-- --> => #BXHTMLCOMMENT_1#
		ReplaceHtmlCommentsBySymCode: function(content)
		{
			this.arHtmlComments = {};
			var
				_this = this,
				index = 0;

			content = content.replace(/(<!--noindex-->)(?:[\s|\n|\r|\t]*?)<a([\s\S]*?)\/a>(?:[\s|\n|\r|\t]*?)(<!--\/noindex-->)/ig, function(s, s1, s2, s3)
				{
					return '<a data-bx-noindex="Y"' + s2 + '/a>';
				}
			);

			content = content.replace(/<!--[\s\S]*?-->/ig, function(s)
				{
					_this.arHtmlComments[index] = s;
					return _this.GetPattern(index++, false, 'html_comment');
				}
			);
			return content;
		},

		// Example: <iframe src="...."></iframe> => #BXIFRAME_0#
		// Also looking for embeded video
		ReplaceIframeBySymCode: function(content)
		{
			this.arIframes = {};
			var
				_this = this,
				index = 0;
			content = content.replace(/<iframe([\s\S]*?)\/iframe>/gi, function(s, s1)
				{
					var video = _this.CheckForVideo(s1);
					if (video)
					{
						_this.arVideos[index] = {
							html: s,
							provider: video.provider || false,
							src:  video.src || false
						};
						return _this.GetPattern(index++, false, 'video');
					}
					else
					{
						_this.arIframes[index] = s;
						return _this.GetPattern(index++, false, 'iframe');
					}
				}
			);
			return content;
		},

		// Example: <style type="css/text"></style> => #BXSTYLE_0#
		ReplaceStyleBySymCode: function(content)
		{
			this.arStyles = {};
			var
				_this = this,
				index = 0;

			content = content.replace(/<style[\s\S]*?\/style>/gi, function(s)
				{
					_this.arStyles[index] = s;
					return _this.GetPattern(index++, false, 'style');
				}
			);

			return content;
		},

		// Example: <audio controls=""><source src="/sound.mp3" type="audio/mpeg"> => #BXAUDIO_0#
		ReplaceAudioBySymCode: function(content)
		{
			this.arAudio = {};
			var
				_this = this,
				index = 0;

			content = content.replace(/<audio[\s\S]*?\/audio>/gi, function(s)
				{
					_this.arAudio[index] = s;
					return _this.GetPattern(index++, false, 'audio');
				}
			);
			return content;
		},

		ReplaceObjectBySymCode: function(content)
		{
			this.arObjects = {};
			var
				_this = this,
				index = 0;

			content = content.replace(/<object[\s\S]*?\/object>/gi, function(s)
				{
					_this.arObjects[index] = s;
					return _this.GetPattern(index++, false, 'object');
				}
			);

			content = content.replace(/<embed[\s\S]*?(?:\/embed)?>/gi, function(s)
				{
					_this.arObjects[index] = s;
					return _this.GetPattern(index++, false, 'object');
				}
			);
			return content;
		},

		CheckForVideo: function(str)
		{
			var videoRe = new RegExp('(?:src)\\s*=\\s*("|\')([\\s\\S]*?((?:youtube.com)|(?:youtu.be)|(?:rutube.ru)|(?:vimeo.com)|(?:vk.com)|(?:' + location.host + '))[\\s\\S]*?)(\\1)', 'ig');

			var res = videoRe.exec(str);
			if (res)
			{
				return {
					src: res[2],
					provider: this.GetVideoProviderName(res[3], str)
				};
			}
			else
			{
				return false;
			}
		},

		GetVideoProviderName: function(host, url)
		{
			var name = '';
			if(!BX.type.isNotEmptyString(url))
			{
				url = '';
			}
			switch (host)
			{
				case 'youtube.com':
				case 'youtu.be':
					name = 'YouTube';
					break;
				case 'rutube.ru':
					name = 'Rutube';
					break;
				case 'vimeo.com':
					name = 'Vimeo';
					break;
				case 'vk.com':
					name = 'Vk';
					break;
				case location.host:
					var providerRe = /((?:provider))=([\S]+)(?:&*)/ig;
					res = providerRe.exec(url);
					if(res)
					{
						name = res[2];
					}
					break;
			}
			return name;
		},

		SavePhpCode: function(code, index)
		{
			this.arScripts[index] = code;
			return this.GetPhpPattern(index, false);
		},

		GetPhpPattern: function(ind, bRegexp)
		{
			if (bRegexp)
				return new RegExp('#BXPHP_' + ind + '#', 'ig');
			else
				return '#BXPHP_' + ind + '#';
		},

		GetPattern: function(ind, bRegexp, entity)
		{
			var code;

			switch (entity)
			{
				case 'php':
					code = '#BXPHP_';
					break;
				case 'javascript':
					code = '#BXJAVASCRIPT_';
					break;
				case 'html_comment':
					code = '#BXHTMLCOMMENT_';
					break;
				case 'iframe':
					code = '#BXIFRAME_';
					break;
				case 'style':
					code = '#BXSTYLE_';
					break;
				case 'video':
					code = '#BXVIDEO_';
					break;
				case 'audio':
					code = '#BXAUDIO_';
					break;
				case 'object':
					code = '#BXOBJECT_';
					break;
				default:
					return '';
			}

			return bRegexp ? new RegExp(code + ind + '#', 'ig') : code + ind + '#';
		},

		// Example:
		// #BXPHP0# => <img ... />
		ParseSymCode: function(content)
		{
			var _this = this;

			content = content.replace(/#BX(PHP|JAVASCRIPT|HTMLCOMMENT|IFRAME|STYLE|VIDEO|AUDIO|OBJECT)_(\d+)#/g, function(str, type, ind)
			{
				var res = '';
				if (_this.IsAllowed(type.toLowerCase()))
				{
					switch (type)
					{
						case 'PHP':
							res = _this.GetPhpCodeHTML(_this.arScripts[ind]);
							break;
						case 'JAVASCRIPT':
							res = _this.GetJavascriptCodeHTML(_this.arJavascripts[ind]);
							break;
						case 'HTMLCOMMENT':
							res = _this.GetHtmlCommentHTML(_this.arHtmlComments[ind]);
							break;
						case 'IFRAME':
							res = _this.GetIframeHTML(_this.arIframes[ind]);
							break;
						case 'STYLE':
							res = _this.GetStyleHTML(_this.arStyles[ind]);
							break;
						case 'VIDEO':
							res = _this.GetVideoHTML(_this.arVideos[ind]);
							break;
						case 'AUDIO':
							res = _this.GetAudioHTML(_this.arAudio[ind]);
							break;
						case 'OBJECT':
							res = _this.GetObjectHTML(_this.arObjects[ind]);
							break;
					}
				}
				return res || str;
			});

			return content;
		},

		GetPhpCodeHTML: function(code)
		{
			if (typeof code !== 'string')
				return null;

			var
				result = '',
				component = this.editor.components.IsComponent(code);

			if (component !== false) // It's Bitrix Component
			{
				var
					cData = this.editor.components.GetComponentData(component.name),
					name = cData.title || component.name,
					title = (cData.params && cData.params.DESCRIPTION) ? cData.params.DESCRIPTION : title;

				if (cData.className)
				{
					component.className = cData.className || '';
				}
				result = this.GetSurrogateHTML('component', name, title, component);
			}
			else // ordinary PHP code
			{
				if (this.editor.allowPhp)
				{
					result = this.GetSurrogateHTML("php", BX.message('BXEdPhpCode'), BX.message('BXEdPhpCode') + ": " + this.GetShortTitle(code, 200), {value : code});
				}
				else
				{
					// TODO: add warning for here (access denied or smth )
					result = '';
				}
			}

			return result;
		},

		GetJavascriptCodeHTML: function(code)
		{
			if (typeof code !== 'string')
				return null;
			return this.GetSurrogateHTML("javascript", "Javascript", "Javascript: " + this.GetShortTitle(code, 200), {value : code});
		},

		GetHtmlCommentHTML: function(code)
		{
			if (typeof code !== 'string')
				return null;
			return this.GetSurrogateHTML("htmlcomment", BX.message('BXEdHtmlComment'), BX.message('BXEdHtmlComment') + ": " + this.GetShortTitle(code), {value : code});
		},

		GetIframeHTML: function(code)
		{
			if (typeof code !== 'string')
				return null;
			return this.GetSurrogateHTML("iframe", BX.message('BXEdIframe'), BX.message('BXEdIframe') + ": " + this.GetShortTitle(code), {value : code});
		},

		GetStyleHTML: function(code)
		{
			if (typeof code !== 'string')
				return null;
			return this.GetSurrogateHTML("style", BX.message('BXEdStyle'), BX.message('BXEdStyle') + ": " + this.GetShortTitle(code), {value : code});
		},

		GetVideoHTML: function(videoParams)
		{
			var
				tag = "video",
				params = videoParams.params || this.FetchVideoIframeParams(videoParams.html, videoParams.provider);

			params.value = videoParams.html;

			var
				id = this.editor.SetBxTag(false, {tag: tag, name: params.title, params: params}),
				surrogateId = this.editor.SetBxTag(false, {tag: "surrogate_dd", params: {origParams: params, origId: id}});

			this.editor.SetBxTag({id: id},
				{
					tag: tag,
					name: params.title,
					params: params,
					title: params.title,
					surrogateId: surrogateId
				}
			);

			var result = '<span id="' + id + '" title="' + params.title + '"  class="' + this.surrClass + ' bxhtmled-video-surrogate' + '" ' +
				'style="min-width:' + params.width + 'px; max-width:' + params.width + 'px; min-height:' + params.height + 'px; max-height:' + params.height + 'px"' +
				'>' +
				'<img title="' + params.title + '" id="'+ surrogateId +'" class="bxhtmled-surrogate-dd" src="' + this.editor.util.GetEmptyImage() + '"/>' +
				'<span class="bxhtmled-surrogate-inner"><span class="bxhtmled-video-icon"></span><span class="bxhtmled-comp-lable" spellcheck=false>' + params.title + '</span></span>' +
				'</span>';

			return result;
		},

		GetAudioHTML: function(code)
		{
			if (typeof code !== 'string')
				return null;
			var
				title = "Audio",
				params = this.FetchVideoIframeParams(code);

			if (params && params.src)
			{
				title += ': ' + this.GetShortTitle(BX.util.htmlspecialchars(params.src));
			}

			return this.GetSurrogateHTML("audio", title, "Audio: " + this.GetShortTitle(code), {value : code});
		},

		GetObjectHTML: function(code)
		{
			return this.GetSurrogateHTML("object", BX.message('BXEdObjectEmbed'),  BX.message('BXEdObjectEmbed') + ": " + this.GetShortTitle(code), {value : code});
		},

		FetchVideoIframeParams: function(html, provider)
		{
			var
				attrRe = /((?:src)|(?:title)|(?:width)|(?:height))\s*=\s*("|')([\s\S]*?)(\2)/ig,
				res = {
					src: '',
					width: 180,
					height: 100,
					title: provider ? BX.message('BXEdVideoTitleProvider').replace('#PROVIDER_NAME#', provider) : BX.message('BXEdVideoTitle'),
					origTitle : ''
				};

			html.replace(attrRe, function(s, attrName, q, attrValue)
			{
				attrName = attrName.toLowerCase();
				if (attrName == 'width' || attrName == 'height')
				{
					attrValue = parseInt(attrValue, 10);
					if (attrValue && !isNaN(attrValue))
					{
						res[attrName] = attrValue;
					}
				}
				else if (attrName == 'title')// title
				{
					res.origTitle = BX.util.htmlspecialcharsback(attrValue);
					res.title += ': ' + attrValue;
				}
				else
				{
					res[attrName] = attrValue;
				}
				return s;
			});

			return res;
		},

		GetSurrogateHTML: function(tag, name, title, params)
		{
			if (title)
			{
				title = BX.util.htmlspecialchars(title);
				title = title.replace('"', '\"');
			}

			if (!params)
			{
				params = {};
			}

			var
				id = this.editor.SetBxTag(false, {tag: tag, name: name, params: params}),
				surrogateId = this.editor.SetBxTag(false, {tag: "surrogate_dd", params: {origParams: params, origId: id}});

			this.editor.SetBxTag({id: id}, {tag: tag, name: name, params: params, title: title, surrogateId: surrogateId});

			if (!this.surrogateTags.tag)
			{
				this.surrogateTags.tag = 1;
			}

			var result = '<span id="' + id + '" title="' + (title || name) + '"  class="' + this.surrClass + (params.className ? ' ' + params.className : '') + '">' +
				this.GetSurrogateInner(surrogateId, title, name) +
				'</span>';

			return result;
		},

		GetSurrogateNode: function(tag, name, title, params)
		{
			var
				doc = this.editor.GetIframeDoc(),
				id = this.editor.SetBxTag(false, {tag: tag, name: name, params: params, title: title}),
				surrogateId = this.editor.SetBxTag(false, {tag: "surrogate_dd", params: {origParams: params, origId: id}});

			if (!params)
				params = {};

			this.editor.SetBxTag({id: id}, {
				tag: tag,
				name: name,
				params: params,
				title: title,
				surrogateId: surrogateId
			});

			if (!this.surrogateTags.tag)
			{
				this.surrogateTags.tag = 1;
			}

			return BX.create('SPAN', {props: {
				id: id,
				title: title || name,
				className: this.surrClass + (params.className ? ' ' + params.className : '')
			},
				html: this.GetSurrogateInner(surrogateId, title, name)
			}, doc);
		},

		GetSurrogateInner: function(surrogateId, title, name)
		{
			return '<img title="' + (title || name) + '" id="'+ surrogateId +'" class="bxhtmled-surrogate-dd" src="' + this.editor.util.GetEmptyImage() + '"/>' +
				'<span class="bxhtmled-surrogate-inner"><span class="bxhtmled-right-side-item-icon"></span><span class="bxhtmled-comp-lable" unselectable="on" spellcheck=false>' + BX.util.htmlspecialchars(name) + '</span></span>';
		},

		GetShortTitle: function(str, trim)
		{
			if (str.length > 100)
				str = str.substr(0, 100) + '...';
			return str;
		},

		_GetUnParsedContent: function(content)
		{
			var _this = this;
			content = content.replace(/#BX(PHP|JAVASCRIPT|HTMLCOMMENT|IFRAME|STYLE|VIDEO|AUDIO|OBJECT)_(\d+)#/g, function(str, type, ind)
			{
				var res;
				switch (type)
				{
					case 'PHP':
						res = _this.arScripts[ind];
						break;
					case 'JAVASCRIPT':
						res = _this.arJavascripts[ind];
						break;
					case 'HTMLCOMMENT':
						res = _this.arHtmlComments[ind];
						break;
					case 'IFRAME':
						res = _this.arIframes[ind];
						break;
					case 'STYLE':
						res = _this.arStyles[ind];
						break;
					case 'VIDEO':
						res = _this.arVideos[ind].html;
						break;
					case 'AUDIO':
						res = _this.arAudio[ind];
						break;
					case 'OBJECT':
						res = _this.arObjects[ind].html;
						break;
				}
				return res;
			});

			return content;
		},

		IsSurrogate: function(node)
		{
			return node && BX.hasClass(node, this.surrClass);
		},

		TrimPhpBrackets: function(str)
		{
			if (str.substr(0, 2) != "<?")
				return str;

			if(str.substr(0, 5).toLowerCase()=="<?php")
				str = str.substr(5);
			else
				str = str.substr(2);

			str = str.substr(0, str.length-2);
			return str;
		},

		TrimQuotes: function(str, qoute)
		{
			var f_ch, l_ch;
			str = str.trim();
			if (qoute == undefined)
			{
				f_ch = str.substr(0, 1);
				l_ch = str.substr(0, 1);
				if ((f_ch == '"' && l_ch == '"') || (f_ch == '\'' && l_ch == '\''))
					str = str.substring(1, str.length - 1);
			}
			else
			{
				if (!qoute.length)
					return str;
				f_ch = str.substr(0, 1);
				l_ch = str.substr(0, 1);
				qoute = qoute.substr(0, 1);
				if (f_ch == qoute && l_ch == qoute)
					str = str.substring(1, str.length - 1);
			}
			return str;
		},

		CleanCode: function(str)
		{
			var
				bSlashed = false,
				bInString = false,
				new_str = "",
				i=-1, ch, ti, quote_ch;

			while(i < str.length - 1)
			{
				i++;
				ch = str.substr(i, 1);
				if(!bInString)
				{
					if(ch == "/" && i + 1 < str.length)
					{
						ti = 0;
						if(str.substr(i+1, 1) == "*" && ((ti = str.indexOf("*/", i + 2)) >= 0))
							ti += 2;
						else if(str.substr(i + 1, 1) == "/" && ((ti = str.indexOf("\n", i + 2)) >= 0))
							ti += 1;

						if(ti > 0)
						{
							if(i > ti)
								alert('iti=' + i + '=' + ti);
							i = ti;
						}

						continue;
					}

					if(ch == " " || ch == "\r" || ch == "\n" || ch == "\t")
						continue;
				}

				if(bInString && ch == "\\")
				{
					bSlashed = true;
					new_str += ch;
					continue;
				}

				if(ch == "\"" || ch == "'")
				{
					if(bInString)
					{
						if(!bSlashed && quote_ch == ch)
							bInString = false;
					}
					else
					{
						bInString = true;
						quote_ch = ch;
					}
				}
				bSlashed = false;
				new_str += ch;
			}
			return new_str;
		},

		ParseFunction: function(str)
		{
			var
				pos = str.indexOf("("),
				lastPos = str.lastIndexOf(")");

			if(pos >= 0 && lastPos >= 0 && pos<lastPos)
				return {name:str.substr(0, pos),params:str.substring(pos+1,lastPos)};

			return false;
		},

		ParseParameters: function(str)
		{
			str = this.CleanCode(str);
			var
				prevAr = this.GetParams(str),
				tq, j, l = prevAr.length;

			for (j = 0; j < l; j++)
			{
				if (prevAr[j].substr(0, 6).toLowerCase()=='array('
				|| prevAr[j].substr(0, 1).toLowerCase()=='[')
				{
					prevAr[j] = this.GetArray(prevAr[j]);
				}
				else
				{
					tq = this.TrimQuotes(prevAr[j]);
					if (this.IsNum(tq) || prevAr[j] != tq)
						prevAr[j] = tq;
					else
						prevAr[j] = this.WrapPhpBrackets(prevAr[j]);
				}
			}
			return prevAr;
		},

		GetArray: function(str)
		{
			var
				php7ArrayStyle = str.substr(0, 1).toLowerCase() == '[',
				resAr = {};

			if (str.substr(0, 6).toLowerCase() != 'array(' && !php7ArrayStyle)
			{
				return str;
			}

			str = str.substring(php7ArrayStyle ? 1 : 6, str.length - 1);
			var
				tempAr = this.GetParams(str),
				propKey, propValue, p, trimedPropValue,
				y;

			for (y = 0; y < tempAr.length; y++)
			{
				if (tempAr[y].substr(0, 6).toLowerCase() == 'array('
				|| tempAr[y].substr(0, 1).toLowerCase() == '[')
				{
					resAr[y] = this.GetArray(tempAr[y]);
					continue;
				}

				p = tempAr[y].indexOf("=>");
				if (p == -1)
				{
					if (tempAr[y] == this.TrimQuotes(tempAr[y]))
						resAr[y] = this.WrapPhpBrackets(tempAr[y]);
					else
						resAr[y] = this.TrimQuotes(tempAr[y]);
				}
				else
				{
					propKey = this.TrimQuotes(tempAr[y].substr(0, p));
					propValue = tempAr[y].substr(p + 2);
					trimedPropValue = this.TrimQuotes(propValue);

					if (propValue == trimedPropValue)
					{
						propValue = this.WrapPhpBrackets(propValue);
						if (propValue.substr(0, 6).toLowerCase()=='array('
							|| propValue.substr(0, 1).toLowerCase()=='[')
						{
							propValue = this.GetArray(propValue);
						}
					}
					else
					{
						propValue = this.TrimQuotes(propValue);
					}

					resAr[propKey] = propValue;
				}
			}
			return resAr;
		},

		WrapPhpBrackets: function(str)
		{
			str = str.trim();
			var
				f_ch = str.substr(0, 1),
				l_ch = str.substr(0, 1);

			if ((f_ch == '"' && l_ch == '"') || (f_ch == '\'' && l_ch == '\''))
				return str;

			return "={" + str + "}";
		},

		GetParams: function(params)
		{
			var
				paramsList = [],
				bracket = 0,
				sk = 0, ch, sl, q1 = 1, q2 = 1, i,
				param_tmp = "";

			for(i = 0; i < params.length; i++)
			{
				ch = params.substr(i, 1);
				if (ch == "\"" && q2 == 1 && !sl)
				{
					q1 *= -1;
				}
				else if (ch == "'" && q1 == 1 && !sl)
				{
					q2 *=-1;
				}
				else if(ch == "\\" && !sl)
				{
					sl = true;
					param_tmp += ch;
					continue;
				}

				if (sl)
					sl = false;

				if (q2 == -1 || q1 == -1)
				{
					param_tmp += ch;
					continue;
				}

				if(ch == "[")
				{
					bracket++;
				}
				else if(ch == "]")
				{
					bracket--;
				}
				else if(ch == "(")
				{
					sk++;
				}
				else if(ch == ")")
				{
					sk--;
				}
				else if(ch == "," && bracket == 0 && sk == 0)
				{
					paramsList.push(param_tmp);
					param_tmp = "";
					continue;
				}

				if(sk < 0 || bracket < 0)
				{
					break;
				}

				param_tmp += ch;
			}

			if(param_tmp != "")
			{
				paramsList.push(param_tmp);
			}

			return paramsList;
		},

		IsNum: function(val)
		{
			var _val = val;
			val = parseFloat(_val);
			if (isNaN(val))
				val = parseInt(_val);
			if (!isNaN(val))
				return _val == val;
			return false;
		},

		ParseBxNodes: function(content)
		{
			var
				i,
				//skipBxNodeIds = [],
				bxNodes = this.editor.parser.convertedBxNodes,
				l = bxNodes.length;

			for(i = 0; i < l; i++)
			{
				if (bxNodes[i].tag == 'surrogate_dd')
				{
					content = content.replace('~' + bxNodes[i].params.origId + '~', '');
				}
			}

			this._skipNodeIndex = {}; //_skipNodeIndex - used in Chrome to prevent double parsing of surrogates
			this._skipNodeList = [];
			var _this = this;

			content = content.replace(/~(bxid\d{1,9})~/ig, function(s, bxid)
			{
				if (!_this._skipNodeIndex[bxid])
				{
					var bxTag = _this.editor.GetBxTag(bxid);
					if (bxTag && bxTag.tag)
					{
						var node = _this.GetBxNode(bxTag.tag);
						if (node)
						{
							return node.Parse(bxTag.params, bxid);
						}
					}
				}
				return '';
			});

			return content;
		},

		// Describe all available surrogates here
		GetBxNodeList: function()
		{
			var _this = this;
			this.arBxNodes = {
				component: {
					Parse: function(params, bxid)
					{
						return _this.editor.components.GetSource(params, bxid);
					}
				},
				component_icon: {
					Parse: function(params)
					{
						return _this.editor.components.GetOnDropHtml(params);
					}
				},
				surrogate_dd: {
					Parse: function(params)
					{
						if (BX.browser.IsFirefox() || !params || !params.origId)
						{
							return '';
						}

						var bxTag = _this.editor.GetBxTag(params.origId);
						if (bxTag)
						{
							_this._skipNodeIndex[params.origId] = true;
							_this._skipNodeList.push(params.origId);

							var origNode = _this.GetBxNode(bxTag.tag);
							if (origNode)
							{
								return origNode.Parse(bxTag.params);
							}
						}

						return '#parse surrogate_dd#';
					}
				},
				php: {
					Parse: function(params)
					{
						return _this._GetUnParsedContent(params.value);
					}
				},
				php_protected: {
					Parse: function(params)
					{
						return params.value;
					}
				},
				javascript: {
					Parse: function(params)
					{
						return _this._GetUnParsedContent(params.value);
					}
				},
				htmlcomment: {
					Parse: function(params)
					{
						return _this._GetUnParsedContent(params.value);
					}
				},
				iframe: {
					Parse: function(params)
					{
						return _this._GetUnParsedContent(params.value);
					}
				},
				style: {
					Parse: function(params)
					{
						return _this._GetUnParsedContent(params.value);
					}
				},
				video: {
					Parse: function(params)
					{
						return _this._GetUnParsedContent(params.value);
					}
				},
				audio: {
					Parse: function(params)
					{
						return _this._GetUnParsedContent(params.value);
					}
				},
				object: {
					Parse: function(params)
					{
						return _this._GetUnParsedContent(params.value);
					}
				},
				anchor: {
					Parse: function(params)
					{
						var strAtr = '';
						if (params.attributes)
						{
							for (var k in params.attributes)
							{
								if (params.attributes.hasOwnProperty(k))
								{
									strAtr += k + '="' + params.attributes[k] + '" ';
								}
							}
						}
						return '<a ' + strAtr + (params.name ? 'name="' + params.name + '"' : '') + '>' + params.html + '</a>';
					}
				},
				pagebreak: {
					Parse: function(params)
					{
						return '<BREAK />';
					}
				},
				printbreak: {
					Parse: function(params)
					{
						return '<div style="page-break-after: always">' + params.innerHTML + '</div>';
					}
				}
			};

			this.editor.On("OnGetBxNodeList");

			return this.arBxNodes;
		},

		AddBxNode: function(key, node)
		{
			if (this.arBxNodes == undefined)
			{
				var _this = this;
				BX.addCustomEvent(this.editor, "OnGetBxNodeList", function(){
					_this.arBxNodes[key] = node;
				});
			}
			else
			{
				this.arBxNodes[key] = node;
			}
		},

		GetBxNode: function(tag)
		{
			if (!this.arBxNodes)
			{
				this.arBxNodes = this.GetBxNodeList();
			}

			return this.arBxNodes[tag] || null;
		},

		OnSurrogateMousedown: function(e, target, bxTag)
		{
			var _this = this;

			// User clicked to surrogate icon
			if (bxTag.tag == 'surrogate_dd')
			{
				BX.bind(target, 'dragstart', function(e){_this.OnSurrogateDragStart(e, this)});
				BX.bind(target, 'dragend', function(e){_this.OnSurrogateDragEnd(e, this, bxTag)});
			}
			else
			{
				setTimeout(function()
				{
					var node = _this.CheckParentSurrogate(_this.editor.selection.GetSelectedNode());
					if(node)
					{
						_this.editor.selection.SetAfter(node);
						if (!node.nextSibling || node.nextSibling.nodeType != 3)
						{
							var invisText = _this.editor.util.GetInvisibleTextNode();
							_this.editor.selection.InsertNode(invisText);
							_this.editor.selection.SetAfter(invisText);
						}
					}
				}, 0);
			}
		},

		OnSurrogateDragEnd: function(e, target, bxTag)
		{
			if (!document.querySelectorAll)
				return;

			var
				doc = this.editor.GetIframeDoc(),
				i, surr, surBxTag,
				usedSurrs = {},
				surrs = doc.querySelectorAll('.bxhtmled-surrogate'),
				surrs_dd = doc.querySelectorAll('.bxhtmled-surrogate-dd'),
				l = surrs.length;

			for (i = 0; i < surrs_dd.length; i++)
			{
				if (surrs_dd[i] && surrs_dd[i].id == bxTag.id)
				{
					BX.remove(surrs_dd[i]);
				}
			}

			for (i = 0; i < l; i++)
			{
				surr = surrs[i];
				if (usedSurrs[surr.id])
				{
					if (surr.getAttribute('data-bx-paste-flag') == 'Y' || !usedSurrs[surr.id].getAttribute('data-bx-paste-flag'))
						BX.remove(surr);
					else if (usedSurrs[surr.id].getAttribute('data-bx-paste-flag'))
						BX.remove(usedSurrs[surr.id]);
				}
				else
				{
					usedSurrs[surr.id] = surr;
					surBxTag = this.editor.GetBxTag(surr.id);
					surr.innerHTML = this.GetSurrogateInner(surBxTag.surrogateId, surBxTag.title, surBxTag.name);
				}
			}
		},

		OnSurrogateDragStart: function(e, target)
		{
			// We need to append it to body to prevent loading picture in Firefox
			if (BX.browser.IsFirefox())
			{
				this.editor.GetIframeDoc().body.appendChild(target);
			}
		},

		CheckParentSurrogate: function(n)
		{
			if (!n)
			{
				return false;
			}

			if (this.IsSurrogate(n))
			{
				return n;
			}

			var
				_this = this,
				iter = 0,
				parentSur = BX.findParent(n, function(node)
				{
					return (iter++ > 4) || _this.IsSurrogate(node);
				}, this.editor.GetIframeDoc().body);

			return this.IsSurrogate(parentSur) ? parentSur : false;
		},

		CheckSurrogateDd: function(n)
		{
			return n && n.nodeType == 1 && this.editor.GetBxTag(n).tag == 'surrogate_dd';
		},

		OnSurrogateClick: function(e, target)
		{
			var bxTag = this.editor.GetBxTag(target);
			// User clicked to component icon
			if (bxTag && bxTag.tag == 'surrogate_dd')
			{
				var origTag = this.editor.GetBxTag(bxTag.params.origId);
				this.editor.On("OnSurrogateClick", [bxTag, origTag, target, e]);
			}
		},

		OnSurrogateDblClick: function(e, target)
		{
			var bxTag = this.editor.GetBxTag(target);
			// User clicked to component icon

			if (bxTag && bxTag.tag == 'surrogate_dd')
			{
				var origTag = this.editor.GetBxTag(bxTag.params.origId);
				this.editor.On("OnSurrogateDblClick", [bxTag, origTag, target, e]);
			}
		},

		OnSurrogateKeyup: function(e, keyCode, command, target)
		{
			var
				sur, bxTag,
				range = this.editor.selection.GetRange();

			if (range)
			{
				// Collapsed selection
				if (range.collapsed)
				{
				}
				else
				{
				}
			}
		},

		OnSurrogateKeydown: function(e, keyCode, command, target)
		{
			var
				sur,
				codes = this.editor.KEY_CODES,
				range = this.editor.selection.GetRange(),
				invisText,
				bxTag, surNode,
				node = target;

			if (!range || !range.getNodes)
				return;

			if (!range.collapsed)
			{
				if (keyCode === codes['backspace'] || keyCode === codes['delete'])
				{
					var
						i,
						nodes = range.getNodes([3]);

					for (i = 0; i < nodes.length; i++)
					{
						sur = this.editor.util.CheckSurrogateNode(nodes[i]);
						if (sur)
						{
							bxTag = this.editor.GetBxTag(sur);
							if (this.surrogateTags[bxTag.tag])
							{
								this.RemoveSurrogate(sur, bxTag);
							}
						}
					}
				}
			}

			if (keyCode === codes['delete'] && range.collapsed)
			{
				invisText = this.editor.util.GetInvisibleTextNode();
				this.editor.selection.InsertNode(invisText);
				this.editor.selection.SetAfter(invisText);
				var nodeNextToCarret = invisText.nextSibling;
				if (nodeNextToCarret)
				{
					if (nodeNextToCarret && nodeNextToCarret.nodeName == 'BR')
					{
						nodeNextToCarret = nodeNextToCarret.nextSibling;
					}
					if (nodeNextToCarret && nodeNextToCarret.nodeType == 3 && (nodeNextToCarret.nodeValue == '\n' || this.editor.util.IsEmptyNode(nodeNextToCarret)))
					{
						nodeNextToCarret = nodeNextToCarret.nextSibling;
					}

					if (nodeNextToCarret)
					{
						BX.remove(invisText);
						bxTag = this.editor.GetBxTag(nodeNextToCarret);

						if (this.surrogateTags[bxTag.tag])
						{
							this.RemoveSurrogate(nodeNextToCarret, bxTag);
							return BX.PreventDefault(e);
						}
					}
				}

			}
			else if (keyCode === codes['backspace'] && range.collapsed)
			{
				invisText = this.editor.util.GetInvisibleTextNode();
				this.editor.selection.InsertNode(invisText);
				this.editor.selection.SetAfter(invisText);
				var nodeBeforeCarret = this.editor.util.GetPreviousNotEmptySibling(invisText);
				if (nodeBeforeCarret && this.editor.phpParser.IsSurrogate(nodeBeforeCarret))
				{
					BX.remove(nodeBeforeCarret);
					if (invisText)
						BX.remove(invisText);
					return BX.PreventDefault(e);
				}
				else
				{
					if (invisText)
						BX.remove(invisText);
				}
			}

			if (range.startContainer &&
				range.startContainer == range.endContainer &&
				range.startContainer.nodeName !== 'BODY')
			{
				node = range.startContainer;
				surNode = this.editor.util.CheckSurrogateNode(node);

				if (surNode)
				{
					bxTag = this.editor.GetBxTag(surNode.id);
					if (keyCode === codes['backspace'] || keyCode === codes['delete'])
					{
						this.RemoveSurrogate(surNode, bxTag);
						BX.PreventDefault(e);
					}
					else if (keyCode === codes['left'] || keyCode === codes['up'])
					{
						var prevToSur = surNode.previousSibling;
						if (prevToSur && prevToSur.nodeType == 3 && this.editor.util.IsEmptyNode(prevToSur))
							this.editor.selection._MoveCursorBeforeNode(prevToSur);
						else
							this.editor.selection._MoveCursorBeforeNode(surNode);

						return BX.PreventDefault(e);
					}
					else if (keyCode === codes['right'] || keyCode === codes['down'])
					{
						var nextToSur = surNode.nextSibling;
						if (nextToSur && nextToSur.nodeType == 3 && this.editor.util.IsEmptyNode(nextToSur))
							this.editor.selection._MoveCursorAfterNode(nextToSur);
						else
							this.editor.selection._MoveCursorAfterNode(surNode);

						return BX.PreventDefault(e);
					}
					else if (keyCode === codes.shift || keyCode === codes.ctrl || keyCode === codes.alt || keyCode === codes.cmd || keyCode === codes.cmdRight)
					{
						return BX.PreventDefault(e);
					}
					else
					{
						this.editor.selection._MoveCursorAfterNode(surNode);
					}
				}
			}
		},

		RemoveSurrogate: function(node, bxTag)
		{
			this.editor.undoManager.Transact();
			BX.remove(node);
			this.editor.On("OnSurrogateRemove", [node, bxTag]);
		},

		CheckHiddenSurrogateDrag: function()
		{
			var dd, i;
			for (i = 0; i < this.hiddenDd.length; i++)
			{
				dd = this.editor.GetIframeElement(this.hiddenDd[i]);
				if (dd)
				{
					dd.style.visibility = '';
				}
			}
			this.hiddenDd = [];
		},

		GetAllSurrogates: function(bGetAll)
		{
			if (!document.querySelectorAll)
				return [];

			bGetAll = bGetAll === true;
			var
				doc = this.editor.GetIframeDoc(),
				res = [], i, surr, bxTag,
				surrs = doc.querySelectorAll(".bxhtmled-surrogate");

			for (i = 0; i < surrs.length; i++)
			{
				surr = surrs[i];
				bxTag = this.editor.GetBxTag(surr.id);
				if (bxTag.tag || bGetAll)
				{
					res.push({
						node : surr,
						bxTag : bxTag
					});
				}
			}

			return res;
		},

		RenewSurrogates: function()
		{
			var
				bCheck = true,
				i, idInd = {}, id,
				surrs = this.GetAllSurrogates(true);

			for (i = 0; i < surrs.length; i++)
			{
				if (!surrs[i].bxTag.tag)
				{
					BX.remove(surrs[i].node);
					continue;
				}

				id = surrs[i].bxTag.surrogateId;
				if (!idInd[id] || !bCheck)
				{
					idInd[id] = id;
					surrs[i].node.innerHTML = this.GetSurrogateInner(surrs[i].bxTag.surrogateId, surrs[i].bxTag.title, surrs[i].bxTag.name);
				}
				else
				{
					BX.remove(surrs[i].node);
				}
			}
		},

		RedrawSurrogates: function()
		{
			var i, surrs = this.GetAllSurrogates();

			for (i = 0; i < surrs.length; i++)
			{
				if (surrs[i].node)
				{
					BX.addClass(surrs[i].node, 'bxhtmled-surrogate-tmp');
				}
			}

			setTimeout(function(){
				for (i = 0; i < surrs.length; i++)
				{
					if (surrs[i].node)
					{
						BX.removeClass(surrs[i].node, 'bxhtmled-surrogate-tmp');
					}
				}
			}, 0);
		},

		IsAllowed: function(id)
		{
			return this.allowed[id];
		},


		AdvancedPhpParse: function(content)
		{
			if (this.bUseAPP)
			{
				this.arAPPFragments = [];
				//content = this.AdvancedPhpParseBetweenTableTags(content);
				content = this.AdvancedPhpParseInAttributes(content);
			}
			return content;
		},

		AdvancedPhpParseBetweenTableTags: function(str)
		{
			var _this = this;
			function replacePHP_before(str, b1, b2, b3, b4)
			{
				_this.arAPPFragments.push(JS_addslashes(b1));
				return b2 + b3 + ' data-bx-php-before=\"#BXAPP' + (_this.arAPPFragments.length - 1) + '#\" ' + b4;
			};

			function replacePHP_after(str, b1, b2, b3, b4)
			{
				_this.arAPPFragments.push(JS_addslashes(b4));
				return b1+'>'+b3+'<'+b2+' style="display:none;" data-bx-php-after=\"#BXAPP'+(_this.arAPPFragments.length-1)+'#\"></'+b2+'>';
			};

			var
				arTags_before = _this.APPConfig.arTags_before,
				arTags_after = _this.APPConfig.arTags_after,
				tagName,
				i,
				re;

			// PHP fragments before tags
			for (i = 0; i < arTags_before.length; i++)
			{
				tagName = arTags_before[i];
				if (_this.limit_php_access)
					re = new RegExp('#(PHP(?:\\d{4}))#(\\s*)(<'+tagName+'[^>]*?)(>)',"ig");
				else
					re = new RegExp('<\\?(.*?)\\?>(\\s*)(<'+tagName+'[^>]*?)(>)',"ig");
				str = str.replace(re, replacePHP_before);
			}
			// PHP fragments after tags
			for (i = 0,l = arTags_after.length; i<l; i++)
			{
				tagName = arTags_after[i];
				if (_this.limit_php_access)
					re = new RegExp('(</('+tagName+')[^>]*?)>(\\s*)#(PHP(?:\\d{4}))#',"ig");
				else
					re = new RegExp('(</('+tagName+')[^>]*?)>(\\s*)<\\?(.*?)\\?>',"ig");
				str = str.replace(re, replacePHP_after);
			}
			return str;
		},

		AdvancedPhpParseInAttributes: function(str)
		{
			var
				_this = this,
				arTags = this.APPConfig.arTags,
				tagName, atrName, i, re;

			function replacePhpInAttributes(str, b1, b2, b3, b4, b5, b6)
			{
				var appInd, atrValue;

				if (b4.match(/#PHP\d+#/g))
				{
					_this.arAPPFragments.push(b4);
					appInd = _this.arAPPFragments.length - 1;
					atrValue = '#BXAPP' + appInd + '#';
					return b1 + b2 + '="' + atrValue + '"' + b5;
				}

				if (b4.indexOf('#BXPHP_') === -1)
				{
					return str;
				}

				_this.arAPPFragments.push(b4);
				appInd = _this.arAPPFragments.length - 1;
				atrValue = _this.AdvancedPhpGetFragmentByIndex(appInd, true);

				return b1 + b2 + '="' + atrValue + '"' + ' data-bx-app-ex-' + b2 + '=\"#BXAPP' + appInd + '#\"' + b5;
			}

			for (tagName in arTags)
			{
				if (arTags.hasOwnProperty(tagName))
				{
					for (i = 0; i < arTags[tagName].length; i++)
					{
						atrName = arTags[tagName][i];
						re = new RegExp('(<' + tagName + '(?:[^>](?:\\?>)*?)*?)(' + atrName + ')\\s*=\\s*((?:"|\')?)([\\s\\S]*?)\\3((?:[^>](?:\\?>)*?)*?>)', "ig");
						str = str.replace(re, replacePhpInAttributes);
					}
				}
			}

			return str;
		},

		AdvancedPhpUnParse: function(content)
		{
			return content;
		},

		AdvancedPhpGetFragmentByCode: function(code, handleSiteTemplate)
		{
			var appInd = code.substr(6); // #BXAPP***#
			appInd = parseInt(appInd.substr(0, appInd.length - 1), 10);
			return this.AdvancedPhpGetFragmentByIndex(appInd, handleSiteTemplate);
		},

		AdvancedPhpGetFragmentByIndex: function(appInd, handleSiteTemplate)
		{
			var
				_this = this,
				appStr = this.arAPPFragments[appInd];

			appStr = appStr.replace(/#BXPHP_(\d+)#/g, function(str, ind)
			{
				var res = _this.arScripts[parseInt(ind, 10)];

				if (handleSiteTemplate)
				{
					var stp = _this.GetSiteTemplatePath();
					if(stp)
					{
						res = res.replace(/<\?=\s*SITE_TEMPLATE_PATH;?\s*\?>/i, stp);
						res = res.replace(/<\?\s*echo\s*SITE_TEMPLATE_PATH;?\s*\?>/i, stp);
					}
				}
				return res;
			});

			return appStr;
		},

		ParseBreak: function(content)
		{
			var _this = this;
			content = content.replace(/<break\s*\/*>/gi, function(s)
				{
					return _this.GetSurrogateHTML("pagebreak", BX.message('BXEdPageBreakSur'), BX.message('BXEdPageBreakSurTitle'));
				}
			);
			return content;
		},

		GetSiteTemplatePath: function()
		{
			return this.editor.GetTemplateParams().SITE_TEMPLATE_PATH;
		},

		CustomContentParse: function(content)
		{
			for (var i = 0; i < this.customParsers.length; i++)
			{
				if (typeof this.customParsers[i] == 'function')
				{
					content = this.customParsers[i](content);
				}
			}

			return content;
		},

		AddCustomParser: function(parser)
		{
			if (typeof parser == 'function')
				this.customParsers.push(parser);
		}
	};

	function BXEditorBbCodeParser(editor)
	{
		this.editor = editor;
		this.parseAlign = true;
	}

	BXEditorBbCodeParser.prototype = {
		Unparse: function(content)  // HTML - > Bbcode
		{
			var el = this.editor.parser.GetAsDomElement(content, this.editor.GetIframeDoc());
			el.setAttribute('data-bx-parent-node', 'Y');
			content = this.GetNodeHtml(el, true);
			content = content.replace(/#BR#/ig, "\n");
			content = content.replace(/&nbsp;/ig, " ");
			content = content.replace(/\uFEFF/ig, '');
			return content;
		},

		Parse: function(content) // // BBCode -> HTML
		{
			var _this = this, i, l;

			content = content.replace(/</ig, "&lt;");
			content = content.replace(/>/ig, "&gt;");

			function secureAtr(str)
			{
				if(!str.replace)
					return str;
				return str.replace(/("|<|>)/g, '');
			}

			// [CODE] == > #BX_CODE1#
			var arCodes = [];
			content = content.replace(/\[code\]((?:\s|\S)*?)\[\/code\]/ig, function(str, code)
			{
				arCodes.push('<pre class="bxhtmled-code">' + code + '</pre>');
				return '#BX_CODE' + (arCodes.length - 1) + '#';
			});

			var parserName, specialParser;
			for (parserName in this.editor.parser.specialParsers)
			{
				if (this.editor.parser.specialParsers.hasOwnProperty(parserName))
				{
					specialParser = this.editor.parser.specialParsers[parserName];
					if (specialParser && specialParser.Parse)
					{
						content = specialParser.Parse(parserName, content, this.editor);
					}
				}
			}

			// * * * Handle Smiles  * * *
			if (this.editor.sortedSmiles)
			{
				var
					arUrls = [],
					arTags = [],
					smile;

				content = content.replace(/\[(?:\s|\S)*?\]/ig, function(str)
				{
					arTags.push(str);
					return '#BX_TMP_TAG' + (arTags.length - 1) + '#';
				});

				content = content.replace(/(?:https?|ftp):\/\//gi, function(str)
				{
					arUrls.push(str);
					return '#BX_TMP_URL' + (arUrls.length - 1) + '#';
				});

				l = this.editor.sortedSmiles.length;
				var smHtml, symRe = "\\s.,;:!?\\#\\-\\*\\|\\[\\]\\(\\)\\{\\}<>&\\n\\t\\r", css;
				for (i = 0; i < l; i++)
				{
					smile = this.editor.sortedSmiles[i];
					if (smile.path && smile.code)
					{
						css = '';
						if (smile.width)
							css += 'width:' + parseInt(smile.width) + 'px;';
						if (smile.height)
							css += 'height:' + parseInt(smile.height) + 'px;';
						if (css !== '')
							css = 'style="' + css + '"';
						smHtml = '<img id="' + _this.editor.SetBxTag(false, {tag: "smile", params: smile}) + '" src="' + smile.path + '" title="' + (smile.name || smile.code) + '" ' + css + '/>';
						content = content.replace(
							new RegExp('([' + symRe + '])' + BX.util.preg_quote(smile.code) + '([' + symRe + '])', 'ig'),
							"$1" + smHtml + "$2"
						);
						content = content.replace(
							new RegExp('([' + symRe + '])' + BX.util.preg_quote(smile.code) + '$', 'ig'),
							"$1" + smHtml
						);
						content = content.replace(
							new RegExp('^' + BX.util.preg_quote(smile.code) + '([' + symRe + '])', 'ig'),
							smHtml + "$1"
						);
						content = content.replace(
							new RegExp('^' + BX.util.preg_quote(smile.code) + '$', 'ig'),
							smHtml
						);
					}
				}

				// Set urls back
				if (arUrls.length > 0)
				{
					content = content.replace(/#BX_TMP_URL(\d+)#/ig, function(s, num){return arUrls[num] || s;});
				}

				// Set tags back
				if (arTags.length > 0)
				{
					content = content.replace(/#BX_TMP_TAG(\d+)#/ig, function(s, num){return arTags[num] || s;});
				}
			}

			// * * * Handle Smiles  END * * *

			// Quote
			//content = content.replace(/\n?\[quote\]/ig, '<blockquote class="bxhtmled-quote">');
			content = content.replace(/\[quote\]/ig, '<blockquote class="bxhtmled-quote">');
			content = content.replace(/\[\/quote\]\n?/ig, '</blockquote>');

			// Table
			content = content.replace(/[\r\n\s\t]?\[table\][\r\n\s\t]*?\[tr\]/ig, '<table border="1">[TR]');
			content = content.replace(/\[tr\][\r\n\s\t]*?\[td\]/ig, '[TR][TD]');
			content = content.replace(/\[tr\][\r\n\s\t]*?\[th\]/ig, '[TR][TH]');
			content = content.replace(/\[\/td\][\r\n\s\t]*?\[td\]/ig, '[/TD][TD]');
			content = content.replace(/\[\/tr\][\r\n\s\t]*?\[tr\]/ig, '[/TR][TR]');
			content = content.replace(/\[\/td\][\r\n\s\t]*?\[\/tr\]/ig, '[/TD][/TR]');
			content = content.replace(/\[\/th\][\r\n\s\t]*?\[\/tr\]/ig, '[/TH][/TR]');
			content = content.replace(/\[\/tr\][\r\n\s\t]*?\[\/table\][\r\n\s\t]?/ig, '[/TR][/TABLE]');

			// List
			content = content.replace(/[\r\n\s\t]*?\[\/list\]/ig, '[/LIST]');
			content = content.replace(/[\r\n\s\t]*?\[\*\]?/ig, '[*]');

			// Paragraph
			content = content.replace(/\[p\]/ig, '<p>');
			content = content.replace(/\[\/p\]\n?/ig, '</p>');

			var
				arSimpleTags = [
					'b','u', 'i', ['s', 'del'], // B, U, I, S
					'table', 'tr', 'td', 'th'//, // Table
				],
				bbTag, tag;

			l = arSimpleTags.length;

			for (i = 0; i < l; i++)
			{
				if (typeof arSimpleTags[i] == 'object')
				{
					bbTag = arSimpleTags[i][0];
					tag = arSimpleTags[i][1];
				}
				else
				{
					bbTag = tag = arSimpleTags[i];
				}

				content = content.replace(new RegExp('\\[(\\/?)' + bbTag + '\\]', 'ig'), "<$1" + tag + ">");
			}

			// Link
			content = content.replace(/\[url\]((?:\s|\S)*?)\[\/url\]/ig, function(str, href)
			{
				return '<a href="' + secureAtr(href) + '">' + href + '</a>';
			});
			content = content.replace(/\[url\s*=\s*((?:\s|\S)*?)\]((?:\s|\S)*?)\[\/url\]/ig, function(str, href, html)
			{
				return '<a href="' + secureAtr(href) + '">' + html + '</a>';
			});

			// Img
			content = content.replace(/\[img((?:\s|\S)*?)\]((?:\s|\S)*?)\[\/img\]/ig,
				function(str, params, src)
				{
					params = _this.FetchImageParams(params);
					src = secureAtr(src);
					var size = "";
					if (params.width)
						size += 'width:' + parseInt(params.width) + 'px;';
					if (params.height)
						size += 'height:' + parseInt(params.height) + 'px;';
					if (size !== '')
						size = 'style="' + size + '"';
					return '<img  src="' + src + '"' + size + '/>';
				}
			);

			// Font color
			i = 0;
			while (content.toLowerCase().indexOf('[color=') != -1 && content.toLowerCase().indexOf('[/color]') != -1 && i++ < 20)
			{
				content = content.replace(/\[color=((?:\s|\S)*?)\]((?:\s|\S)*?)\[\/color\]/ig, function(s, value, cont){ return '<span style="color:' + secureAtr(value) + '">' + cont + '</span>' });
			}

			// List
			i = 0;
			while (content.toLowerCase().indexOf('[list=') != -1 && content.toLowerCase().indexOf('[/list]') != -1 && i++ < 20)
			{
				content = content.replace(/\[list=1\]((?:\s|\S)*?)\[\/list\]/ig, "<ol>$1</ol>");
			}

			i = 0;
			while (content.toLowerCase().indexOf('[list') != -1 && content.toLowerCase().indexOf('[/list]') != -1 && i++ < 20)
			{
				content = content.replace(/\[list\]((?:\s|\S)*?)\[\/list\]/ig, "<ul>$1</ul>");
			}
			content = content.replace(/\[\*\]/ig, "<li>");

			// Font
			i = 0;
			while (content.toLowerCase().indexOf('[font=') != -1 && content.toLowerCase().indexOf('[/font]') != -1 && i++ < 20)
			{

				content = content.replace(/\[font=((?:\s|\S)*?)\]((?:\s|\S)*?)\[\/font\]/ig, function(s, value, cont){ return '<span style="font-family:' + secureAtr(value) + '">' + cont + '</span>' });
			}

			// Font size
			i = 0;
			while (content.toLowerCase().indexOf('[size=') != -1 && content.toLowerCase().indexOf('[/size]') != -1 && i++ < 20)
			{
				content = content.replace(/\[size=((?:\s|\S)*?)\]((?:\s|\S)*?)\[\/size\]/ig, function(s, value, cont){ return '<span style="font-size:' + secureAtr(value) + '">' + cont + '</span>' });
			}

			if (this.parseAlign)
			{
				content = content.replace(/\[(center|left|right|justify)\]/ig, function(s, value){return '<div style="text-align:' + secureAtr(value) + '">';});
				content = content.replace(/\[\/(center|left|right|justify)\]/ig, "</div>");
			}

			// VIDEO
			if (content.toLowerCase().indexOf('[/video]') != -1)
			{
				content = content.replace(/\[video((?:\s|\S)*?)\]((?:\s|\S)*?)\[\/video\]/ig, function(s, params, src)
				{
					return _this.GetVideoSourse(src, _this.FetchVideoParams(params.trim(params)), s);
				});
			}

			// Replace \n => <br/>
			content = content.replace(/\n/ig, "<br />");

			// Replace encoded "[", "]" by [ and ]
			content = content.replace(/&#91;/ig, "[");
			content = content.replace(/&#93;/ig, "]");

			// Replace back CODE content without modifications
			// #BX_CODE1# ==> <pre>...</pre>
			if (arCodes.length > 0)
			{
				content = content.replace(/#BX_CODE(\d+)#/ig, function(s, num){return arCodes[num] || s;});
			}

			return content;
		},

		GetNodeHtml: function(node, onlyChild)
		{
			var
				oNode = {
					node: node
				},
				res = '';

			if (!onlyChild)
			{
				if(node.nodeType == 3)
				{
					var text = BX.util.htmlspecialchars(node.nodeValue);
					if (!text.match(/[^\n]+/ig) && node.previousSibling && node.nextSibling
						&& this.editor.util.IsBlockNode(node.previousSibling)
						&& this.editor.util.IsBlockNode(node.nextSibling))
					{
						return "\n";
					}

					// Mantis: 54329
					if (BX.browser.IsChrome() && this.editor.pasteHandleMode && node.nextSibling && node.nextSibling.nodeName == 'P')
					{
						text = text.replace(/\n+/ig, "\n");
					}

					// List of tags inside of which \n will be cleaned in textNodes
					if (node.parentNode && !node.parentNode.getAttribute('data-bx-parent-node') &&
						BX.util.in_array(node.parentNode.nodeName, ['P', 'DIV', 'SPAN', 'TD', 'TH', 'B', 'STRONG', 'I', 'EM', 'U', 'DEL', 'S','STRIKE', 'BLOCKQUOTE']))
					{
						text = text.replace(/\n/ig, " ");
					}

					if (BX.browser.IsMac() || BX.browser.IsFirefox())
					{
						text = text.replace(/\n/ig, " ");
					}

					text = text.replace(/\[/ig, "&#91;");
					text = text.replace(/\]/ig, "&#93;");
					return text;
				}

				if (node.nodeType == 1 && node.nodeName == 'P')
				{
					var html = BX.util.trim(node.innerHTML);
					html = html.replace(/[\n\r\s]/ig, "").toLowerCase();
					if(html == '<br>')
					{
						node.innerHTML = '';
					}
				}

				var bbRes = this.UnParseNodeBB(oNode);
				if (bbRes !== false)
				{
					return bbRes;
				}

				if (oNode.bbOnlyChild)
					onlyChild = true;

				// Left part
				if (!onlyChild)
				{
					if (oNode.breakLineBefore)
					{
						res += "\n";
					}
					if(node.nodeType == 1 && !oNode.hide)
					{
						res += "[" + oNode.bbTag;
						if (oNode.bbValue)
						{
							res += '=' + oNode.bbValue;
						}
						res += "]";
					}
				}
			}

			if (oNode.checkNodeAgain)
			{
				res += this.GetNodeHtml(node);
			}
			else
			{
				var
					i, child,
					innerContent = '';

				// Handle childs
				for (i = 0; i < node.childNodes.length; i++)
				{
					child = node.childNodes[i];
					innerContent += this.GetNodeHtml(child);
				}
				res += innerContent;
			}

			// Right part
			if (!onlyChild)
			{
				if (oNode.breakLineAfter)
					res += "\n";

				if (innerContent == '' && this.IsPairNode(oNode.bbTag)
					&& node.nodeName !== 'P'
					&& node.nodeName !== 'TD'
					&& node.nodeName !== 'TR'
					&& node.nodeName !== 'TH')
				{
					return '';
				}

				if(node.nodeType == 1 && (node.childNodes.length > 0 || this.IsPairNode(oNode.bbTag)) && !oNode.hide && !oNode.hideRight)
				{
					res += "[/" + oNode.bbTag + "]";
				}

				if (BX.browser.IsFirefox() && this.editor.util.IsBlockNode(node)
					&& BX.util.trim(node.innerHTML.replace(/\uFEFF/ig, '')).toLowerCase() == '<br>')
				{
					return '\n';
				}

				// mantis: #54244 & #100442
				if (oNode.breakLineAfterEnd || node.nodeType == 1 && this.editor.util.IsBlockNode(node) && this.editor.util.IsBlockNode(this.editor.util.GetNextSibling(node)))
				{
					res += "\n";
				}
			}

			return res;
		},

		UnParseNodeBB: function (oNode) // WYSIWYG -> BBCode
		{
			var
				bxTag,
				isTableTag, isAlign,
				nodeName = oNode.node.nodeName.toUpperCase();

			oNode.checkNodeAgain = false;
			if (nodeName == "BR")
			{
				return "#BR#";
			}

			if (oNode.node && oNode.node.id)
			{
				bxTag = this.editor.GetBxTag(oNode.node.id);

				if (bxTag.tag)
				{
					var parser = this.editor.parser.specialParsers[bxTag.tag];
					if (parser && parser.UnParse)
					{
						return parser.UnParse(bxTag, oNode, this.editor);
					}
					else if (bxTag.tag == 'video')
					{
						return bxTag.params.value;
					}
					else if (bxTag.tag == 'smile')
					{
						return bxTag.params.code;
					}
					else
					{
						return '';
					}
				}
			}

			if (nodeName == "SCRIPT")
			{
				return '';
			}

			if (nodeName == "IFRAME" && oNode.node.src)
			{
				var
					src = oNode.node.src.replace(/https?:\/\//ig, '//'),
					video = this.editor.phpParser.CheckForVideo('src="' + src + '"');
				if (video)
				{
					var
						width = parseInt(oNode.node.width),
						height = parseInt(oNode.node.height);

					return '[VIDEO TYPE=' + video.provider.toUpperCase() +
						' WIDTH=' + width +
						' HEIGHT=' + height + ']' +
						src +
						'[/VIDEO]';
				}
			}

			//[CODE] Handle code tag
			if (nodeName == "PRE" && BX.hasClass(oNode.node, 'bxhtmled-code'))
			{
				return "[CODE]" + this.GetCodeContent(oNode.node) + "[/CODE]";
			}

			// Image
			if (nodeName == "IMG")
			{
				var size = '';

				if (oNode.node.style.width)
					size += ' WIDTH=' + parseInt(oNode.node.style.width);
				else if (oNode.node.width)
					size += ' WIDTH=' + parseInt(oNode.node.width);

				if (oNode.node.style.height)
					size += ' HEIGHT=' + parseInt(oNode.node.style.height);
				else if (oNode.node.height)
					size += ' HEIGHT=' + parseInt(oNode.node.height);

				return "[IMG" + size + "]" + oNode.node.src + "[/IMG]";
			}

			oNode.hide = false;
			oNode.bbTag = nodeName;
			isTableTag = BX.util.in_array(nodeName, this.editor.TABLE_TAGS);
			isAlign = this.parseAlign && (oNode.node.style.textAlign || oNode.node.align) && !isTableTag;

			if(nodeName == 'STRONG' || nodeName == 'B')
			{
				oNode.bbTag = 'B';
			}
			else if(nodeName == 'EM' || nodeName == 'I')
			{
				oNode.bbTag = 'I';
			}
			else if(nodeName == 'DEL' || nodeName == 'S')
			{
				oNode.bbTag = 'S';
			}
			// List
			else if((nodeName == 'OL' || nodeName == 'UL'))
			{
				oNode.bbTag = 'LIST';
				oNode.breakLineAfter = true;
				oNode.bbValue = nodeName == 'OL' ? '1' : '';
			}
			else if(nodeName == 'LI')
			{
				if (oNode.node.lastChild && oNode.node.lastChild.nodeName == 'BR')
				{
					oNode.node.removeChild(oNode.node.lastChild);
				}
				oNode.bbTag = '*';
				oNode.breakLineBefore = true;
				oNode.hideRight = true;
			}
			else if(nodeName == 'A')
			{
				oNode.bbTag = 'URL';
				oNode.bbValue = this.editor.parser.GetAttributeEx(oNode.node, 'href');
				oNode.bbValue = oNode.bbValue.replace(/\[/ig, "&#91;").replace(/\]/ig, "&#93;");
				if (oNode.bbValue === '')
				{
					oNode.bbOnlyChild = true;
				}
			}
			// Color
			else if(oNode.node.style.color && !isTableTag)
			{
				oNode.bbTag = 'COLOR';
				oNode.bbValue = this.editor.util.RgbToHex(oNode.node.style.color);
				oNode.node.style.color = '';
				if (oNode.node.style.cssText != '')
				{
					oNode.checkNodeAgain = true;
				}
			}
			// Font family
			else if(oNode.node.style.fontFamily && !isTableTag)
			{
				oNode.bbTag = 'FONT';
				oNode.bbValue = oNode.node.style.fontFamily;
				oNode.node.style.fontFamily = '';
				if (oNode.node.style.cssText != '')
				{
					oNode.checkNodeAgain = true;
				}
			}
			// Font size
			else if(oNode.node.style.fontSize && !isTableTag)
			{
				oNode.bbTag = 'SIZE';
				oNode.bbValue = oNode.node.style.fontSize;
				oNode.node.style.fontSize = '';
				if (oNode.node.style.cssText != '')
				{
					oNode.checkNodeAgain = true;
				}
			}
			else if(nodeName == 'BLOCKQUOTE' && oNode.node.className == 'bxhtmled-quote' && !oNode.node.getAttribute('data-bx-skip-check'))
			{
				oNode.bbTag = 'QUOTE';
				//oNode.breakLineBefore = true;
				oNode.breakLineAfterEnd = true;

				if (isAlign)
				{
					oNode.checkNodeAgain = true;
					oNode.node.setAttribute('data-bx-skip-check', 'Y');
				}
			}
			else if(isAlign)
			{
				var align = oNode.node.style.textAlign || oNode.node.align;
				if (BX.util.in_array(align, ['left', 'right', 'center', 'justify']))
				{
					oNode.hide = false;
					oNode.bbTag = align.toUpperCase();
				}
				else
				{
					oNode.hide = !BX.util.in_array(nodeName, this.editor.BBCODE_TAGS);
				}
			}
			else if(!BX.util.in_array(nodeName, this.editor.BBCODE_TAGS)) //'p', 'u', 'div', 'table', 'tr', 'img', 'td', 'a'
			{
				oNode.hide = true;
			}

			return false;
		},

		IsPairNode: function(text)
		{
			text = text.toUpperCase();
			return !(text.substr(0, 1) == 'H' || text == 'BR' || text == 'IMG' || text == 'INPUT');
		},

		GetCodeContent: function(node) // WYSIWYG -> BBCode
		{
			if (!node || this.editor.util.IsEmptyNode(node))
				return '';

			var
				i,
				res = '';

			for (i = 0; i < node.childNodes.length; i++)
			{
				if (node.childNodes[i].nodeType == 3)
					res += node.childNodes[i].data;
				else if (node.childNodes[i].nodeType == 1 && node.childNodes[i].nodeName == "BR")
					res += "#BR#";
				else
					res += this.GetCodeContent(node.childNodes[i]);
			}

			if (BX.browser.IsIE())
				res = res.replace(/\r/ig, "#BR#");
			else
				res = res.replace(/\n/ig, "#BR#");

			res = res.replace(/\[/ig, "&#91;");
			res = res.replace(/\]/ig, "&#93;");

			return res;
		},

		GetVideoSourse: function(src, params, source, title)
		{
			title = title || BX.message.BXEdVideoTitle;
			return this.editor.phpParser.GetVideoHTML({
				params: {
					width: params.width,
					height: params.height,
					title: title,
					origTitle: '',
					provider: params.type
				},
				html: source
			});
		},

		FetchVideoParams: function(str)
		{
			str = BX.util.trim(str);
			var
				atr = str.split(' '),
				i, name, val, atr_,
				res = {
					width: 180,
					height: 100,
					type: false
				};

			for (i = 0; i < atr.length; i++)
			{
				atr_ = atr[i].split('=');
				name = atr_[0].toLowerCase();
				val = atr_[1];
				if (name == 'width' || name == 'height')
				{
					val = parseInt(val, 10);
					if (val && !isNaN(val))
					{
						res[name] = Math.max(val, 100);
					}
				}
				else if (name == 'type')
				{
					val = val.toUpperCase();
					if (val == 'YOUTUBE' || val == 'RUTUBE' || val == 'VIMEO')
					{
						res[name] = val;
					}
				}
			}

			return res;
		},

		FetchImageParams: function(str)
		{
			str = BX.util.trim(str);
			var
				atr = str.split(' '),
				i, name, val, atr_,
				res = {};

			for (i = 0; i < atr.length; i++)
			{
				atr_ = atr[i].split('=');
				name = atr_[0].toLowerCase();
				val = atr_[1];
				if (name == 'width' || name == 'height')
				{
					val = parseInt(val, 10);
					if (val && !isNaN(val))
					{
						res[name] = val;
					}
				}
			}

			return res;
		}
	};

	function BXCodeFormatter(editor)
	{
		this.editor = editor;

		var
			ownLine = ['area', 'hr', 'i?frame', 'link', 'meta', 'noscript', 'style', 'table', 'tbody', 'thead', 'tfoot'],
			contOwnLine = ['li', 'dt', 'dd', 'h[1-6]', 'option', 'script'];

		this.reBefore = new RegExp('^<(/?' + ownLine.join('|/?') + '|' + contOwnLine.join('|') + ')[ >]', 'i');
		this.reAfter = new RegExp('^<(br|/?' + ownLine.join('|/?') + '|/' + contOwnLine.join('|/') + ')[ >]');

		var newLevel = ['blockquote', 'div', 'dl', 'fieldset', 'form', 'frameset', 'map', 'ol', 'p', 'pre', 'select', 'td', 'th', 'tr', 'ul'];
		this.reLevel = new RegExp('^</?(' + newLevel.join('|') + ')[ >]');

		this.lastCode = null;
		this.lastResult = null;
	}

	BXCodeFormatter.prototype = {
		Format: function(code)
		{
			if (code != this.lastCode)
			{
				this.lastCode = code;
				this.lastResult = this.DoFormat(code);
			}
			return this.lastResult;
		},

		DoFormat: function(code)
		{
			code += ' ';
			this.level = 0;

			var
				_this = this,
				i, t,
				point = 0,
				start = null,
				end = null,
				tag = '',
				result = '',
				index = 0,
				cont = '';

			this.pieces = {};
			code = code.replace(/<pre[\s\S]*?\/pre>/gi, function(s)
				{
					_this.pieces[index] = s;
					var str = '#BX_CODE_PIECE_' + index + '#';
					index++;
					return str;
				}
			);

			for (i = 0; i < code.length; i++)
			{
				point = i;
				//if no more tags ==> exit
				if (code.substr(i).indexOf('<') == -1)
				{
					result += code.substr(i);
					result = result.replace(/\n\s*\n/g, '\n');  //blank lines
					result = result.replace(/^[\s\n]*/, ''); //leading space
					result = result.replace(/[\s\n]*$/, ''); //trailing space

					if (result.indexOf('<!--noindex-->') !== -1)
					{
						result = result.replace(/(<!--noindex-->)(?:[\s|\n|\r|\t]*?)(<a[\s\S]*?\/a>)(?:[\s|\n|\r|\t]*?)(<!--\/noindex-->)(?:[\n|\r|\t]*)/ig, "$1$2$3");
					}

					result = result.replace(/#BX_CODE_PIECE_(\d+)#/g, function(s, ind){return (ind && _this.pieces[ind]) ? _this.pieces[ind] : s;});

					return result;
				}

				while (point < code.length && code.charAt(point) !== '<')
				{
					point++;
				}

				if (i != point)
				{
					cont = code.substr(i, point - i);
					if (cont.match(/^\s+$/))
					{
						cont = cont.replace(/\s+/g, ' ');
						result += cont;
					}
					else
					{
						if (result.charAt(result.length - 1) == '\n')
						{
							result += this.GetTabs();
						}
						else if (cont.charAt(0) == '\n')
						{
							result += '\n' + this.GetTabs();
							cont = cont.replace(/^\s+/, '');
						}
						cont = cont.replace(/\n/g, ' ');
						cont = cont.replace(/\n+/g, '');
						cont = cont.replace(/\s+/g, ' ');
						result += cont;
					}

					if (cont.match(/\n/))
					{
						result += '\n' + this.GetTabs();
					}
				}
				start = point;

				//find the end of the tag
				while (point < code.length && code.charAt(point) != '>')
				{
					point++;
				}

				tag = code.substr(start, point - start);
				i = point;

				//if this is a special tag, deal with it
				if (tag.substr(1, 3) === '!--')
				{
					if (!tag.match(/--$/))
					{
						while (code.substr(point, 3) !== '-->')
						{
							point++;
						}
						point += 2;
						tag = code.substr(start, point - start);
						i = point;
					}
					if (result.charAt(result.length - 1) !== '\n')
					{
						result += '\n';
					}

					result += this.GetTabs();
					result += tag + '>\n';
				}
				else if (tag[1] === '!')
				{
					result = this.PutTag(tag + '>', result);
				}
				else if (tag[1] == '?')
				{
					result += tag + '>\n';
				}
				else if (t = tag.match(/^<(script|style)/i))
				{
					t[1] = t[1].toLowerCase();
					result = this.PutTag(this.CleanTag(tag), result);
					//end = String(code.substr(i + 1)).indexOf('</' + t[1]);
					end = String(code.substr(i + 1)).toLowerCase().indexOf('</' + t[1]);

					if (end)
					{
						cont = code.substr(i + 1, end);
						i += end;
						result += cont;
					}
				}
				else
				{
					result = this.PutTag(this.CleanTag(tag), result);
				}
			}

			code = code.replace(/#BX_CODE_PIECE_(\d+)#/g, function(s, ind){return (ind && _this.pieces[ind]) ? _this.pieces[ind] : s;});

			return code;
		},

		GetTabs: function()
		{
			var s = '', j;
			for (j = 0; j < this.level; j++)
			{
				s += '\t';
			}
			return s;
		},

		CleanTag: function(tag)
		{
			var
				m,
				partRe = /\s*([^= ]+)(?:=((['"']).*?\3|[^ ]+))?/,
				result = '',
				suffix = '';

			tag = tag.replace(/\n/g, ' '); //remove newlines
			tag = tag.replace(/[\s]{2,}/g, ' '); //collapse whitespace
			tag = tag.replace(/^\s+|\s+$/g, ' '); //collapse whitespace

			if (tag.match(/\/$/))
			{
				suffix = '/';
				tag = tag.replace(/\/+$/, '');
			}

			while (m = partRe.exec(tag))
			{
				if (m[2])
					result += m[1] + '=' + m[2];
				else if (m[1])
					result += m[1];
				result += ' ';

				tag = tag.substr(m[0].length);
			}

			return result.replace(/\s*$/, '') + suffix + '>';
		},

		PutTag: function(tag, res)
		{
			var nl = tag.match(this.reLevel);

			if (tag.match(this.reBefore) || nl)
			{
				res = res.replace(/\s*$/, '');
				res += "\n";
			}

			if (nl && tag.charAt(1) == '/')
			{
				this.level--;
			}

			if (res.charAt(res.length-1) == '\n')
			{
				res += this.GetTabs();
			}

			if (nl && '/' != tag.charAt(1))
			{
				this.level++;
			}

			res += tag;
			if (tag.match(this.reAfter) || tag.match(this.reLevel))
			{
				res = res.replace(/ *$/, '');
				res += "\n";
			}

			return res;
		}
	};

	function __run()
	{
		window.BXHtmlEditor.BXCodeFormatter = BXCodeFormatter;
		window.BXHtmlEditor.BXEditorParser = BXEditorParser;
		window.BXHtmlEditor.BXEditorPhpParser = BXEditorPhpParser;
		window.BXHtmlEditor.BXEditorBbCodeParser = BXEditorBbCodeParser;
	}

	if (window.BXHtmlEditor)
	{
		__run();
	}
	else
	{
		BX.addCustomEvent(window, "OnBXHtmlEditorInit", __run);
	}
})();