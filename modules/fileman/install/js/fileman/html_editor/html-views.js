/**
 * Bitrix HTML Editor 3.0
 * Date: 24.04.13
 * Time: 4:23
 *
 * Views class
 */
(function()
{

function BXEditorView(editor, element, container)
{
	this.editor = editor;
	this.element = element;
	this.container = container;
	this.config = editor.config || {};
	this.isShown = null;
	this.bbCode = editor.bbCode;
	BX.addCustomEvent(this.editor, "OnClickBefore", BX.proxy(this.OnClick, this));
}

BXEditorView.prototype = {
	Focus: function()
	{
		if (!document.querySelector || this.element.ownerDocument.querySelector(":focus") === this.element)
			return;

		try{this.element.focus();}catch(e){}
	},

	Hide: function()
	{
		this.isShown = false;
		this.container.style.display = "none";
	},

	Show: function()
	{
		this.isShown = true;
		this.container.style.display = "";
	},

	Disable: function()
	{
		this.element.setAttribute("disabled", "disabled");
	},

	Enable: function()
	{
		this.element.removeAttribute("disabled");
	},

	OnClick: function(params)
	{

	},

	IsShown: function()
	{
		return !!this.isShown;
	}
};

function BXEditorTextareaView(parent, textareaElement, container)
{
	// Call parrent constructor
	BXEditorIframeView.superclass.constructor.apply(this, arguments);
	this.name = "textarea";
	this.InitEventHandlers();

	if (!this.element.value && this.editor.config.content)
		this.SetValue(this.editor.config.content, false);
}

BX.extend(BXEditorTextareaView, BXEditorView);

BXEditorTextareaView.prototype.Clear = function()
{
	this.element.value = "";
};

BXEditorTextareaView.prototype.GetValue = function(bParse)
{
	var value = this.IsEmpty() ? "" : this.element.value;

	if (bParse)
	{
		value = this.parent.parse(value);
	}

	return value;
};

BXEditorTextareaView.prototype.SetValue = function(html, bParse, bFormat)
{
	if (bParse)
	{
		html = this.editor.Parse(html, true, bFormat);
	}
	this.editor.dom.pValueInput.value = this.element.value = html;
};


BXEditorTextareaView.prototype.SaveValue = function()
{
	if (this.editor.inited)
	{
		this.editor.dom.pValueInput.value = this.element.value;
	}
};

BXEditorTextareaView.prototype.HasPlaceholderSet = function()
{
	var
		placeholderText = this.element.getAttribute("placeholder") || null,
		value = this.element.value;
	return !value || (value === placeholderText);
};

BXEditorTextareaView.prototype.IsEmpty = function()
{
	var value = BX.util.trim(this.element.value);
	return value === '' || this.HasPlaceholderSet();
};

BXEditorTextareaView.prototype.InitEventHandlers = function()
{
	var _this = this;
	BX.bind(this.element, "focus", function()
	{
		_this.editor.On("OnTextareaFocus");
		_this.isFocused = true;
	});

	BX.bind(this.element, "blur", function()
	{
		_this.editor.On("OnTextareaBlur");
		_this.isFocused = false;
	});

	BX.bind(this.element, "keydown", function(e)
	{
		_this.editor.textareaKeyDownPreventDefault = false;

		// Handle Ctrl+Enter
		if ((e.ctrlKey || e.metaKey) && !e.altKey && e.keyCode === _this.editor.KEY_CODES["enter"])
		{
			_this.editor.On('OnCtrlEnter', [e, _this.editor.GetViewMode()]);
			return BX.PreventDefault(e);
		}
		_this.editor.On('OnTextareaKeydown', [e]);

		if (_this.editor.textareaKeyDownPreventDefault)
			return BX.PreventDefault(e);
	});

	BX.bind(this.element, "keyup", function(e)
	{
		_this.editor.On('OnTextareaKeyup', [e]);
	});
};

BXEditorTextareaView.prototype.IsFocused = function()
{
	return this.isFocused;
};

BXEditorTextareaView.prototype.ScrollToSelectedText = function(searchText)
{
// http://blog.blupixelit.eu/scroll-textarea-to-selected-word-using-javascript-jquery/
//	var parola_cercata = "parola"; // the searched word
//	var posi = jQuery('#my_textarea').val().indexOf(parola_cercata); // take the position of the word in the text
//	if (posi != -1) {
//		var target = document.getElementById("my_textarea");
//		// select the textarea and the word
//		target.focus();
//		if (target.setSelectionRange)
//			target.setSelectionRange(posi, posi+parola_cercata.length);
//		else {
//			var r = target.createTextRange();
//			r.collapse(true);
//			r.moveEnd('character',  posi+parola_cercata);
//			r.moveStart('character', posi);
//			r.select();
//		}
//		var objDiv = document.getElementById("my_textarea");
//		var sh = objDiv.scrollHeight; //height in pixel of the textarea (n_rows*line_height)
//		var line_ht = jQuery('#my_textarea').css('line-height').replace('px',''); //height in pixel of each row
//		var n_lines = sh/line_ht; // the total amount of lines
//		var char_in_line = jQuery('#insert_textarea').val().length / n_lines; // amount of chars for each line
//		var height = Math.floor(posi/char_in_line); // amount of lines in the textarea
//		jQuery('#my_textarea').scrollTop(height*line_ht); // scroll to the selected line
//	} else {
//		alert('parola '+parola_cercata+' non trovata'); // alert word not found
//	}
};

BXEditorTextareaView.prototype.SelectText = function(searchText)
{
	var
		value = this.element.value,
	 	ind = value.indexOf(searchText);

	if(ind != -1)
	{
		this.element.focus();
		this.element.setSelectionRange(ind, ind + searchText.length);
	}
};

BXEditorTextareaView.prototype.GetTextSelection = function()
{
	var res = false;
	if (this.element.selectionStart != undefined)
	{
		res = this.element.value.substr(this.element.selectionStart, this.element.selectionEnd - this.element.selectionStart);
	}
	else if (document.selection && document.selection.createRange)
	{
		res = document.selection.createRange().text;
	}
	else if (window.getSelection)
	{
		res = window.getSelection();
		res = res.toString();
	}

	return res;
};

BXEditorTextareaView.prototype.WrapWith = function (tagBegin, tagEnd, postText)
{
	if (!tagBegin)
		tagBegin = "";
	if (!tagEnd)
		tagEnd = "";
	if (!postText)
		postText = "";

	if (tagBegin.length <= 0 && tagEnd.length <= 0 && postText.length <= 0)
		return true;

	var
		bReplaceText = !!postText,
		selectedText = this.GetTextSelection(),
		mode = (selectedText ? 'select' : (bReplaceText ? 'after' : 'in'));

	//if (!this.bTextareaFocus)
	//	this.pTextarea.focus(); // BUG IN IE

	if (bReplaceText)
	{
		postText = tagBegin + postText + tagEnd;
	}
	else if (selectedText)
	{
		postText = tagBegin + selectedText + tagEnd;
	}
	else
	{
		postText = tagBegin + tagEnd;
	}

	if (this.element.selectionStart != undefined)
	{
		var
			currentScroll = this.element.scrollTop,
			start = this.element.selectionStart,
			end = this.element.selectionEnd;

		this.element.value = this.element.value.substr(0, start) + postText + this.element.value.substr(end);

		if (mode == 'select')
		{
			this.element.selectionStart = start;
			this.element.selectionEnd = start + postText.length;
		}
		else if (mode == 'in')
		{
			this.element.selectionStart = this.element.selectionEnd = start + tagBegin.length;
		}
		else
		{
			this.element.selectionStart = this.element.selectionEnd = start + postText.length;
		}
		this.element.scrollTop = currentScroll;
	}
	else if (document.selection && document.selection.createRange)
	{
		var sel = document.selection.createRange();
		var selection_copy = sel.duplicate();
		postText = postText.replace(/\r?\n/g, '\n');
		sel.text = postText;
		sel.setEndPoint('StartToStart', selection_copy);
		sel.setEndPoint('EndToEnd', selection_copy);

		if (mode == 'select')
		{
			sel.collapse(true);
			postText = postText.replace(/\r\n/g, '1');
			sel.moveEnd('character', postText.length);
		}
		else if (mode == 'in')
		{
			sel.collapse(false);
			sel.moveEnd('character', tagBegin.length);
			sel.collapse(false);
		}
		else
		{
			sel.collapse(false);
			sel.moveEnd('character', postText.length);
			sel.collapse(false);
		}
		sel.select();
	}
	else
	{
		// failed - just stuff it at the end of the message
		this.element.value += postText;
	}
	return true;
};


BXEditorTextareaView.prototype.GetCursorPosition = function()
{
	return this.element.selectionStart;
};


function BXEditorIframeView(editor, textarea, container)
{
	// Call parrent constructor
	BXEditorIframeView.superclass.constructor.apply(this, arguments);
	this.name = "wysiwyg";
	this.caretNode = "<br>";
}

BX.extend(BXEditorIframeView, BXEditorView);

BXEditorIframeView.prototype.OnCreateIframe = function()
{
	this.document = this.editor.sandbox.GetDocument();
	this.element = this.document.body;
	this.editor.document = this.document;
	this.textarea = this.editor.dom.textarea;
	this.isFocused = false;
	this.InitEventHandlers();

	// Check and init external range library
	window.rangy.init();

	this.Enable();
};

BXEditorIframeView.prototype.Clear = function()
{
	//this.element.innerHTML = BX.browser.IsFirefox() ? this.caretNode : "";
	this.element.innerHTML = this.caretNode;
};

BXEditorIframeView.prototype.GetValue = function(bParse, bFormat)
{
	this.iframeValue = this.IsEmpty() ? "" : this.editor.GetInnerHtml(this.element);
	this.editor.On('OnIframeBeforeGetValue', [this.iframeValue]);
	if (bParse)
	{
		this.iframeValue = this.editor.Parse(this.iframeValue, false, bFormat);
	}
	return this.iframeValue;
};

BXEditorIframeView.prototype.SetValue = function(html, bParse)
{
	if (bParse)
	{
		html = this.editor.Parse(html);
	}
	this.element.innerHTML = html;
	// Check last child - if it's block node in the end - add <br> tag there
	this.CheckContentLastChild(this.element);
	this.editor.On('OnIframeSetValue', [html]);
};

BXEditorIframeView.prototype.Show = function()
{
	this.isShown = true;
	this.container.style.display = "";
	this.ReInit();
};

BXEditorIframeView.prototype.ReInit = function()
{
	// Firefox needs this, otherwise contentEditable becomes uneditable
	this.Disable();
	this.Enable();

	this.editor.On('OnIframeReInit');
};

BXEditorIframeView.prototype.Hide = function()
{
	this.isShown = false;
	this.container.style.display = "none";
};

BXEditorIframeView.prototype.Disable = function()
{
	this.element.removeAttribute("contentEditable");
};

BXEditorIframeView.prototype.Enable = function()
{
	this.element.setAttribute("contentEditable", "true");
};

BXEditorIframeView.prototype.Focus = function(setToEnd)
{
	if (BX.browser.IsIE() && this.HasPlaceholderSet())
	{
		this.Clear();
	}

	if (!document.querySelector
		|| this.element.ownerDocument.querySelector(":focus") !== this.element
		|| !this.IsFocused())
	{
		if (BX.browser.IsIOS())
		{
			var _this = this;
			if (this.focusTimeout)
				clearTimeout(this.focusTimeout);

			this.focusTimeout = setTimeout(function()
			{
				var
					orScrollTop = document.documentElement.scrollTop || document.body.scrollTop,
					orScrollLeft = document.documentElement.scrollLeft || document.body.scrollLeft;
					BX.focus(_this.element);
					window.scrollTo(orScrollLeft, orScrollTop);
			}, 200);
		}
		else
		{
			BX.focus(this.element);
		}
	}

	if (setToEnd && this.element.lastChild)
	{
		if (this.element.lastChild.nodeName === "BR")
		{
			this.editor.selection.SetBefore(this.element.lastChild);
		}
		else
		{
			this.editor.selection.SetAfter(this.element.lastChild);
		}
	}
};

BXEditorIframeView.prototype.SetFocusedFlag = function(isFocused)
{
	this.isFocused = isFocused;
};

BXEditorIframeView.prototype.IsFocused = function()
{
	return this.isFocused;
};

BXEditorIframeView.prototype.GetTextContent = function(clearInvisibleSpace)
{
	var txt = this.editor.util.GetTextContent(this.element);
	return clearInvisibleSpace === true ? txt.replace(/\uFEFF/ig, '') : txt;
};

BXEditorIframeView.prototype.HasPlaceholderSet = function()
{
	return this.textarea && this.GetTextContent() == this.textarea.getAttribute("placeholder");
};

BXEditorIframeView.prototype.IsEmpty = function(clearInvisibleSpace)
{
	if (!document.querySelector)
		return false;

	var
		innerHTML = this.element.innerHTML,
		elementsWithVisualValue = "blockquote, ul, ol, img, embed, object, table, iframe, svg, video, audio, button, input, select, textarea";

	return innerHTML === "" ||
		innerHTML === this.caretNode ||
		this.HasPlaceholderSet() ||
		(this.GetTextContent(clearInvisibleSpace) === "" && !this.element.querySelector(elementsWithVisualValue));
};

BXEditorIframeView.prototype._initObjectResizing = function()
{
	var properties = ["width", "height"],
		propertiesLength = properties.length,
		element = this.element;

	this.commands.exec("enableObjectResizing", this.config.allowObjectResizing);

	if (this.config.allowObjectResizing) {
		// IE sets inline styles after resizing objects
		// The following lines make sure _this the width/height css properties
		// are copied over to the width/height attributes
		if (browser.supportsEvent("resizeend")) {
			dom.observe(element, "resizeend", function(event) {
				var target = event.target || event.srcElement,
					style = target.style,
					i = 0,
					property;
				for(; i<propertiesLength; i++) {
					property = properties[i];
					if (style[property]) {
						target.setAttribute(property, parseInt(style[property], 10));
						style[property] = "";
					}
				}
				// After resizing IE sometimes forgets to remove the old resize handles
				redraw(element);
			});
		}
	} else {
		if (browser.supportsEvent("resizestart")) {
			dom.observe(element, "resizestart", function(event) { event.preventDefault(); });
		}
	}
};

/**
 * With "setActive" IE offers a smart way of focusing elements without scrolling them into view:
 * http://msdn.microsoft.com/en-us/library/ms536738(v=vs.85).aspx
 *
 * Other browsers need a more hacky way: (pssst don't tell my mama)
 * In order to prevent the element being scrolled into view when focusing it, we simply
 * move it out of the scrollable area, focus it, and reset it's position
 */

var focusWithoutScrolling = function(element)
{
	if (element.setActive) {
		// Following line could cause a js error when the textarea is invisible
		// See https://github.com/xing/wysihtml5/issues/9
		try { element.setActive(); } catch(e) {}
	} else {
		var elementStyle = element.style,
			originalScrollTop = doc.documentElement.scrollTop || doc.body.scrollTop,
			originalScrollLeft = doc.documentElement.scrollLeft || doc.body.scrollLeft,
			originalStyles = {
				position: elementStyle.position,
				top: elementStyle.top,
				left: elementStyle.left,
				WebkitUserSelect: elementStyle.WebkitUserSelect
			};

		dom.setStyles({
			position: "absolute",
			top: "-99999px",
			left: "-99999px",
			// Don't ask why but temporarily setting -webkit-user-select to none makes the whole thing performing smoother
			WebkitUserSelect: "none"
		}).on(element);

		element.focus();

		dom.setStyles(originalStyles).on(element);

		if (win.scrollTo) {
			// Some browser extensions unset this method to prevent annoyances
			// "Better PopUp Blocker" for Chrome http://code.google.com/p/betterpopupblocker/source/browse/trunk/blockStart.js#100
			// Issue: http://code.google.com/p/betterpopupblocker/issues/detail?id=1
			win.scrollTo(originalScrollLeft, originalScrollTop);
		}
	}
};


/**
 * Taking care of events
 * - Simulating 'change' event on contentEditable element
 * - Handling drag & drop logic
 * - Catch paste events
 * - Dispatch proprietary newword:composer event
 * - Keyboard shortcuts
 */
	BXEditorIframeView.prototype.InitEventHandlers = function()
	{
		var
			_this = this,
			editor = this.editor,
			value = this.GetValue(),
			element = this.element,
			iframeWindow = this.editor.sandbox.GetWindow(),
			_element = !BX.browser.IsOpera() ? element : this.editor.sandbox.GetWindow();

		if (this._eventsInitedObject && this._eventsInitedObject === _element)
			return;

		this._eventsInitedObject = _element;

		BX.bind(_element, "focus", function()
		{
			editor.On("OnIframeFocus");
			_this.isFocused = true;
			if (value !== _this.GetValue())
				BX.onCustomEvent(editor, "OnIframeChange");
		});

		BX.bind(_element, "blur", function()
		{
			editor.On("OnIframeBlur");
			_this.isFocused = false;
			setTimeout(function(){value = _this.GetValue();}, 0);
		});

		BX.bind(_element, "contextmenu", function(e)
		{
			if(e && !e.ctrlKey && !e.shiftKey && (BX.getEventButton(e) & BX.MSRIGHT))
			{
				editor.On("OnIframeContextMenu", [e, e.target || e.srcElement, _this.contMenuRangeCollapsed]);
			}
		});

		BX.bind(_element, "mousedown", function(e)
		{
			var
				range = editor.selection.GetRange(),
				target = e.target || e.srcElement,
				bxTag = editor.GetBxTag(target);

			//mantis: 71174
			_this.contMenuRangeCollapsed = range && range.collapsed;
			if (editor.synchro.IsSyncOn())
			{
				editor.synchro.StopSync();
			}

			if (BX.browser.IsIE10() || BX.browser.IsIE11())
			{
				editor.phpParser.RedrawSurrogates();
			}

			if (target.nodeName == 'BODY' || !editor.phpParser.CheckParentSurrogate(target))
			{
				setTimeout(function()
				{
					range = editor.selection.GetRange();
					if (range && range.collapsed && range.startContainer && range.startContainer == range.endContainer)
					{
						var surr = editor.phpParser.CheckParentSurrogate(range.startContainer);
						if (surr)
						{
							editor.selection.SetInvisibleTextAfterNode(surr);
							editor.selection.SetInvisibleTextBeforeNode(surr);
						}
					}
				}, 10);
			}

			editor.action.actions.quote.checkSpaceAfterQuotes(target);

			editor.selection.SaveRange(false);
			setTimeout(function(){editor.selection.SaveRange(false);}, 10);
			editor.On("OnIframeMouseDown", [e, target, bxTag]);
		});

		BX.bind(_element, "touchend", function(){_this.Focus();});
		BX.bind(_element, "touchstart", function(){_this.Focus();});

		BX.bind(_element, "click", function(e)
		{
			var
				target = e.target || e.srcElement;
			editor.On("OnIframeClick", [e, target]);
		});

		BX.bind(_element, "dblclick", function(e)
		{
			var
				target = e.target || e.srcElement;
			editor.On("OnIframeDblClick", [e, target]);
		});

		BX.bind(_element, "mouseup", function(e)
		{
			var target = e.target || e.srcElement;
			if (!editor.synchro.IsSyncOn())
			{
				editor.synchro.StartSync();
			}

			editor.On("OnIframeMouseUp", [e, target]);
		});

		// Mantis: 90137
		//if (BX.browser.IsIOS())
		//{
		//	// When on iPad/iPhone/IPod after clicking outside of editor, the editor loses focus
		//	// but the UI still acts as if the editor has focus (blinking caret and onscreen keyboard visible)
		//	// We prevent _this by focusing a temporary input element which immediately loses focus
		//	BX.bind(iframeWindow, "blur", function()
		//	{
		//		var
		//			orScrollTop = document.documentElement.scrollTop || document.body.scrollTop,
		//			orScrollLeft = document.documentElement.scrollLeft || document.body.scrollLeft,
		//			input = BX.create('INPUT', {
		//				props:{type: 'text', value: ''}
		//			}, iframeWindow.ownerDocument);
		//
		//		try
		//		{
		//			editor.selection.InsertNode(input);
		//		}
		//		catch(e)
		//		{
		//			iframeWindow.appendChild(input);
		//		}
		//
		//		BX.focus(input);
		//		BX.remove(input);
		//		window.scrollTo(orScrollLeft, orScrollTop);
		//	});
		//}

		// --------- Drag & Drop events  ---------
		BX.bind(element, "dragover", function(){editor.On("OnIframeDragOver", arguments);});
		BX.bind(element, "dragenter", function(){editor.On("OnIframeDragEnter", arguments);});
		BX.bind(element, "dragleave", function(){editor.On("OnIframeDragLeave", arguments);});
		BX.bind(element, "dragexit", function(){editor.On("OnIframeDragExit", arguments);});
		BX.bind(element, "drop", function(){editor.On("OnIframeDrop", arguments);});

		// Chrome & Safari & Firefox only fire the ondrop/ondragend/... events when the ondragover event is cancelled
		//if (BX.browser.IsChrome() || BX.browser.IsFirefox())
		// TODO: Truobles with firefox during selections http://jabber.bx/view.php?id=49370
		if (BX.browser.IsFirefox())
		{
			BX.bind(element, "dragover", function(e)
			{
				e.preventDefault();
			});
			BX.bind(element, "dragenter", function(e)
			{
				e.preventDefault();
			});
		}

		BX.bind(element, 'drop', BX.delegate(this.OnPasteHandler, this));
		BX.bind(element, 'paste', BX.delegate(this.OnPasteHandler, this));

		BX.bind(element, "keyup", function(e)
		{
			var
				keyCode = e.keyCode,
				target = editor.selection.GetSelectedNode(true);

			_this.SetFocusedFlag(true);
			if (keyCode === editor.KEY_CODES['space'] || keyCode === editor.KEY_CODES['enter'])
			{
				if (keyCode === editor.KEY_CODES['enter'])
				{
					_this.OnEnterHandlerKeyUp(e, keyCode, target);
				}
				editor.On("OnIframeNewWord");
			}
			else
			{
				_this.OnKeyUpArrowsHandler(e, keyCode);
			}

			editor.selection.SaveRange();
			editor.On('OnIframeKeyup', [e, keyCode, target]);

			// Mantis:#67998
			if (keyCode === editor.KEY_CODES['backspace'] && BX.browser.IsChrome()
				&& target && target.nodeType == '3' && target.nextSibling && target.nextSibling.nodeType == '3')
			{
				_this.editor.selection.ExecuteAndRestoreSimple(function()
				{
					_this.editor.util.SetTextContent(target, _this.editor.util.GetTextContent(target) + _this.editor.util.GetTextContent(target.nextSibling));
					target.nextSibling.parentNode.removeChild(target.nextSibling);
				});
			}

			if (!editor.util.FirstLetterSupported() && _this.editor.parser.firstNodeCheck)
			{
				_this.editor.parser.FirstLetterCheckNodes('', '', true);
			}

			//mantis:91555, mantis:93629
			if (BX.browser.IsChrome())
			{
				if (_this.stopBugusScrollTimeout)
					clearTimeout(_this.stopBugusScrollTimeout);
				_this.stopBugusScrollTimeout = setTimeout(function(){_this.stopBugusScroll = false;}, 200);
			}
		});

		BX.bind(element, "mousedown", function(e)
		{
			var target = e.target || e.srcElement;
			if (!editor.util.CheckImageSelectSupport() && target.nodeName === 'IMG')
			{
				editor.selection.SelectNode(target);
			}

			// Handle mousedown for "code" element in IE
			if (!editor.util.CheckPreCursorSupport() && target.nodeName === 'PRE')
			{
				var selectedNode = editor.selection.GetSelectedNode(true);
				if (selectedNode && selectedNode != target)
				{
					_this.FocusPreElement(target, true);
				}
			}
		});

		BX.bind(element, "keydown", BX.proxy(this.KeyDown, this));

		// Workaround for chrome bug with bugus scrolling to the top of the page (mantis:91555, mantis:93629)
		if (BX.browser.IsChrome())
		{
			BX.bind(window, "scroll", BX.proxy(function(e)
			{
				if (_this.stopBugusScroll)
				{
					if ((!_this.savedScroll || !_this.savedScroll.scrollTop) && _this.lastSavedScroll)
						_this.savedScroll = _this.lastSavedScroll;

					if (_this.savedScroll && !_this.lastSavedScroll)
						_this.lastSavedScroll = _this.savedScroll;
					_this._RestoreScrollTop();
				}
			}, this));
		}

		// Show urls and srcs in tooltip when hovering links or images
		var nodeTitles = {
			IMG: BX.message.SrcTitle + ": ",
			A: BX.message.UrlTitle + ": "
		};

		BX.bind(element, "mouseover", function(e)
		{
			var
				target = e.target || e.srcElement,
				value = (target.getAttribute("href") || target.getAttribute("src")),
				nodeName = target.nodeName;

			if (nodeTitles[nodeName]
					&& !target.hasAttribute("title")
					&& value
					&& value.indexOf('data:image/') === -1
			)
			{
				target.setAttribute("title", nodeTitles[nodeName] + value);
				target.setAttribute("data-bx-clean-attribute", "title");
			}
		});

		this.editor.InitClipboardHandler();
	};

	BXEditorIframeView.prototype.KeyDown = function(e)
	{
		var
			_this = this,
			keyCode = e.keyCode,
			KEY_CODES = this.editor.KEY_CODES;

		this.SetFocusedFlag(true);
		this.editor.iframeKeyDownPreventDefault = false;

		// Workaround for chrome bug with bugus scrollint to the top of the page (mantis:91555, mantis:93629)
		if (BX.browser.IsChrome())
		{
			this.stopBugusScroll = true;
			this.savedScroll = BX.GetWindowScrollPos(document);
		}

		var
			command = this.editor.SHORTCUTS[keyCode],
			selectedNode = this.editor.selection.GetSelectedNode(true),
			range = this.editor.selection.GetRange(),
			body = this.document.body,
			parent;

		if ((BX.browser.IsIE() || BX.browser.IsIE10() || BX.browser.IsIE11()) &&
			!BX.util.in_array(keyCode, [16, 17, 18, 20, 65, 144, 37, 38, 39, 40]))
		{
			if (selectedNode && selectedNode.nodeName == "BODY"
				||
				range.startContainer && range.startContainer.nodeName == "BODY"
				||
				(range.startContainer == body.firstChild &&
				range.endContainer == body.lastChild &&
				range.startOffset == 0 &&
				range.endOffset == body.lastChild.length))
			{
				BX.addCustomEvent(this.editor, "OnIframeKeyup", BX.proxy(this._IEBodyClearHandler, this));
			}
		}

		// Last symbol in iframe and new paragraph in IE
		if ((BX.browser.IsIE() || BX.browser.IsIE10() || BX.browser.IsIE11()) &&
			keyCode == KEY_CODES['backspace'])
		{
			BX.addCustomEvent(this.editor, "OnIframeKeyup", BX.proxy(this._IEBodyClearHandlerEx, this));
		}

		this.isUserTyping = true;
		if (this.typingTimeout)
		{
			this.typingTimeout = clearTimeout(this.typingTimeout);
		}
		this.typingTimeout = setTimeout(function()
		{
			_this.isUserTyping = false;
		}, 1000);

		this.editor.synchro.StartSync(200);

		this.editor.On('OnIframeKeydown', [e, keyCode, command, selectedNode]);

		if (this.editor.iframeKeyDownPreventDefault)
			return BX.PreventDefault(e);

		// Handle  Shortcuts
		if ((e.ctrlKey || e.metaKey) && !e.altKey && command)
		{
			this.editor.action.Exec(command);
			return BX.PreventDefault(e);
		}

		// Bug mantis: #59759 workaround *Chrome only
		// Begin
		if (
			keyCode === KEY_CODES['backspace'] &&
			range.startOffset == 0 &&
			range.startContainer.nodeType == 3 &&
			range.startContainer.parentNode.firstChild == range.startContainer &&
			range.startContainer.parentNode &&
			range.startContainer.parentNode.nodeName == 'BLOCKQUOTE' &&
			range.startContainer.parentNode.className
		)
		{
			range.startContainer.parentNode.className = '';
		}

		if (
			keyCode === KEY_CODES['delete'] &&
				range.collapsed &&
				range.endContainer.nodeType == 3 &&
				range.endOffset == range.endContainer.length
			)
		{
			var next = this.editor.util.GetNextNotEmptySibling(range.endContainer);
			if (next)
			{
				if(next.nodeName == 'BR')
				{
					next = this.editor.util.GetNextNotEmptySibling(next);
				}

				if (next && next.nodeName == 'BLOCKQUOTE' && next.className)
				{
					next.className = '';
				}
			}
		}
		// END: Bug mantis: #59759

		// Clear link with image
		if (selectedNode && selectedNode.nodeName === "IMG" &&
			(keyCode === KEY_CODES['backspace'] || keyCode === KEY_CODES['delete']))
		{
			parent = selectedNode.parentNode;
			parent.removeChild(selectedNode); // delete image

			// Parent - is LINK, and it's hasn't got any other childs
			if (parent.nodeName === "A" && !parent.firstChild)
			{
				parent.parentNode.removeChild(parent);
			}

			setTimeout(function(){_this.editor.util.Refresh(_this.element);}, 0);
			BX.PreventDefault(e);
		}

		if (range.collapsed && this.OnKeyDownArrowsHandler(e, keyCode, range) === false)
		{
			return false;
		}

		// Handle Ctrl+Enter
		if ((e.ctrlKey || e.metaKey) && !e.altKey && keyCode === KEY_CODES["enter"])
		{
			if (this.IsFocused())
				this.editor.On("OnIframeBlur");

			this.editor.On('OnCtrlEnter', [e, this.editor.GetViewMode()]);
			return BX.PreventDefault(e);
		}

		// Firefox's bug it remove first node for customized lists
		if (BX.browser.IsFirefox() && selectedNode && (keyCode === KEY_CODES["delete"] || keyCode === KEY_CODES["backspace"]))
		{
			var li = selectedNode.nodeName == 'LI' ? selectedNode : BX.findParent(selectedNode, {tag: 'LI'}, body);
			if (li && li.firstChild && li.firstChild.nodeName == 'I')
			{
				var ul = BX.findParent(li, {tag: 'UL'}, body);
				if (ul)
				{
					var customBullitClass = this.editor.action.actions.insertUnorderedList.getCustomBullitClass(ul);
					if (customBullitClass)
					{
						setTimeout(function()
						{
							if (ul && li && li.innerHTML !== '')
							{
								_this.editor.action.actions.insertUnorderedList.checkCustomBullitList(ul, customBullitClass);
							}
						}, 0);
					}
				}
			}
		}

		if (!this.editor.util.FirstLetterSupported()  &&
			_this.editor.parser.firstNodeCheck &&
			keyCode === this.editor.KEY_CODES['backspace'])
		{
			_this.editor.parser.FirstLetterBackspaceHandler(range);
		}

		// Handle "Enter"
		if (!e.shiftKey && (keyCode === KEY_CODES["enter"] || keyCode === KEY_CODES["backspace"]))
		{
			return this.OnEnterHandler(e, keyCode, selectedNode, range);
		}

		if (keyCode === KEY_CODES["pageUp"] || keyCode === KEY_CODES["pageDown"])
		{
			this.savedScroll = BX.GetWindowScrollPos(document);
			BX.addCustomEvent(this.editor, "OnIframeKeyup", BX.proxy(this._RestoreScrollTop, this));
			setTimeout(BX.proxy(this._RestoreScrollTop, this), 0);
		}
	};

	BXEditorIframeView.prototype._RestoreScrollTop = function(e)
	{
		if (this.savedScroll)
		{
			window.scrollTo(this.savedScroll.scrollLeft, this.savedScroll.scrollTop);
			this.savedScroll = null;
		}
		BX.removeCustomEvent(this.editor, "OnIframeKeyup", BX.proxy(this._RestoreScrollTop, this));
	};

	BXEditorIframeView.prototype._IEBodyClearHandler = function(e)
	{
		var
			p = this.document.body.firstChild;

		if (e.keyCode == this.editor.KEY_CODES['enter'] && p.nodeName == "P" && p != this.document.body.lastChild)
		{
			if (p.innerHTML && p.innerHTML.toLowerCase() == '<br>')
			{
				var newPar = p.nextSibling;
				this.editor.util.ReplaceWithOwnChildren(p);
				p = newPar;
			}
		}

		if (p && p.nodeName == "P" && p == this.document.body.lastChild)
		{
			this.editor.util.ReplaceWithOwnChildren(p);
		}
		BX.removeCustomEvent(this.editor, "OnIframeKeyup", BX.proxy(this._IEBodyClearHandler, this));
	};

	BXEditorIframeView.prototype._IEBodyClearHandlerEx = function(e)
	{
		var p = this.document.body.firstChild;

		if (e.keyCode == this.editor.KEY_CODES['backspace'] &&
			p && p.nodeName == "P" && p == this.document.body.lastChild &&
			(this.editor.util.IsEmptyNode(p, true, true) || p.innerHTML && p.innerHTML.toLowerCase() == '<br>'))
		{
			this.editor.util.ReplaceWithOwnChildren(p);
		}

		BX.removeCustomEvent(this.editor, "OnIframeKeyup", BX.proxy(this._IEBodyClearHandlerEx, this));
	};

	BXEditorIframeView.prototype.OnEnterHandler = function(e, keyCode, selectedNode, range)
	{
		// TODO: check it again later maybe chrome will fix it
		// mantis: 55872. Chrome 38 rendering bug workaround
		if (BX.browser.IsChrome())
		{
			this.document.body.style.minHeight = (parseInt(this.document.body.style.minHeight) + 1) + 'px';
			// mantis: 60033
			this.document.body.style.minHeight = (parseInt(this.document.body.style.minHeight) - 1) + 'px';
		}

		// Check selectedNode
		if (!selectedNode)
		{
			return;
		}

		var _this = this;
		function unwrap(node)
		{
			if (node)
			{
				if (node.nodeName !== "P" && node.nodeName !== "DIV")
				{
					node = BX.findParent(node, function(n)
					{
						return n.nodeName === "P" || n.nodeName === "DIV";
					}, _this.document.body);
				}

				var emptyNode = _this.editor.util.GetInvisibleTextNode();
				if (node)
				{
					node.parentNode.insertBefore(emptyNode, node);
					_this.editor.util.ReplaceWithOwnChildren(node);
					_this.editor.selection.SelectNode(emptyNode);
				}
			}
		}

		var
			list, br, blockElement,
			blockTags  = ["LI", "P", "H1", "H2", "H3", "H4", "H5", "H6"],
			listTags  = ["UL", "OL", "MENU"];

		if (BX.util.in_array(selectedNode.nodeName, blockTags))
		{
			blockElement = selectedNode;
		}
		else
		{
			blockElement = BX.findParent(selectedNode, function(n)
			{
				return BX.util.in_array(n.nodeName, blockTags);
			}, this.document.body);
		}

		if (blockElement)
		{
			if (blockElement.nodeName === "LI")
			{
				if (keyCode === _this.editor.KEY_CODES["enter"] && blockElement && blockElement.parentNode)
				{
					var bullitClass = _this.editor.action.actions.insertUnorderedList.getCustomBullitClass(blockElement.parentNode);
				}

				// Some browsers create <p> elements after leaving a list
				// check after keydown of backspace and return whether a <p> got inserted and unwrap it
				setTimeout(function()
				{
					var node = _this.editor.selection.GetSelectedNode(true);
					if (node)
					{
						list = BX.findParent(node, function(n)
						{
							return BX.util.in_array(n.nodeName, listTags);
						}, _this.document.body);

						// Check if it's list with custom styled bullits - we have to check it all items have same style
						if (keyCode === _this.editor.KEY_CODES["enter"] && blockElement && blockElement.parentNode)
						{
							_this.editor.action.actions.insertUnorderedList.checkCustomBullitList(blockElement.parentNode, bullitClass, true);
						}

						// mantis: 82028, 83178
						if (list && BX.browser.IsChrome() && keyCode === _this.editor.KEY_CODES["enter"])
						{
							var i, li = list.getElementsByTagName('LI');

							for (i = 0; i < li.length; i++)
							{
								//&65279; === \uFEFF - invisible space
								li[i].innerHTML = li[i].innerHTML.replace(/\uFEFF/ig, '');
							}
						}

						if (!list)
						{
							unwrap(node);
						}
					}
				}, 0);
			}
			else if (blockElement.nodeName.match(/H[1-6]/) && keyCode === this.editor.KEY_CODES["enter"])
			{
				setTimeout(function()
				{
					unwrap(_this.editor.selection.GetSelectedNode());
				}, 0);
			}

			return true;
		}

		if (keyCode === this.editor.KEY_CODES["enter"] && !BX.browser.IsFirefox() && this.editor.action.IsSupported('insertLineBreak'))
		{
			if (BX.browser.IsIE10() || BX.browser.IsIE11())
			{
				this.editor.action.Exec('insertHTML', '<br>' + this.editor.INVISIBLE_SPACE);
			}
			else if(BX.browser.IsChrome())
			{
				this.editor.action.Exec('insertLineBreak');

				// Bug in Chrome - when you press enter but it put carret on the prev string
				// Chrome 43.0.2357 in Mac puts visible space instead of invisible
				if (BX.browser.IsMac())
				{
					var tmpId = "bx-editor-temp-" + Math.round(Math.random() * 1000000);
					this.editor.action.Exec('insertHTML', '<span id="' + tmpId + '">' + this.editor.INVISIBLE_SPACE + '</span>');
					var tmpElement = this.editor.GetIframeElement(tmpId);
					if (tmpElement)
						BX.remove(tmpElement);
				}
				else
				{
					this.editor.action.Exec('insertHTML', this.editor.INVISIBLE_SPACE);
				}
			}
			else
			{
				this.editor.action.Exec('insertLineBreak');
			}
			return BX.PreventDefault(e);
		}

		if ((BX.browser.IsChrome() || BX.browser.IsIE10() || BX.browser.IsIE11()) && keyCode == this.editor.KEY_CODES['backspace'] && range.collapsed)
		{
			var checkNode = BX.create('SPAN', false, this.document);
			this.editor.selection.InsertNode(checkNode);
			var prev = checkNode.previousSibling;
			if (prev && prev.nodeType == 3 && this.editor.util.IsEmptyNode(prev, false, false))
			{
				BX.remove(prev);
			}
			this.editor.selection.SetBefore(checkNode);
			BX.remove(checkNode);
		}
	};

	BXEditorIframeView.prototype.OnEnterHandlerKeyUp = function(e, keyCode, node)
	{
		// Clean class of all block nodes when they created after Enter pressing
		// All new Ps and DIVs should be without classNames
		if (node)
		{
			var _this = this;
			if (!BX.util.in_array(node.nodeName, this.editor.GetBlockTags()))
			{
				node = BX.findParent(node, function(n)
				{
					return BX.util.in_array(n.nodeName, _this.editor.GetBlockTags());
				}, this.document.body);
			}

			if (node && BX.util.in_array(node.nodeName, this.editor.GetBlockTags()))
			{
				var html = BX.util.trim(node.innerHTML).toLowerCase();
				if (this.editor.util.IsEmptyNode(node, true, true) || html == '' || html == '<br>')
				{
					node.removeAttribute("class");
				}
			}
		}
	};

	BXEditorIframeView.prototype.OnKeyDownArrowsHandler = function(e, keyCode, range)
	{
		var
			node, parentNode, nextNode, prevNode,
			KC = this.editor.KEY_CODES;

		this.keyDownRange = range;

		if (keyCode === KC['right'] || keyCode === KC['down'])
		{
			node = range.endContainer;
			nextNode = node ? node.nextSibling : false;
			parentNode = node ? node.parentNode : false;

			if (
				node.nodeType == 3 && node.length == range.endOffset
				&& parentNode && parentNode.nodeName !== 'BODY'
				&& (!nextNode || (nextNode && nextNode.nodeName == 'BR' && !nextNode.nextSibling))
				&& (this.editor.util.IsBlockElement(parentNode) || this.editor.util.IsBlockNode(parentNode))
				)
			{
				this.editor.selection.SetInvisibleTextAfterNode(parentNode, true);
				return BX.PreventDefault(e);
			}
			else if(
					node.nodeType == 3 && this.editor.util.IsEmptyNode(node)
					&& nextNode
					&& (this.editor.util.IsBlockElement(nextNode) || this.editor.util.IsBlockNode(nextNode))
				)
			{
				BX.remove(node);
				if (nextNode.firstChild)
				{
					this.editor.selection.SetBefore(nextNode.firstChild);
				}
				else
				{
					this.editor.selection.SetAfter(nextNode);
				}
				return BX.PreventDefault(e);
			}
			else if (
					node.nodeType == 3 && node.length == range.endOffset
					&& parentNode && parentNode.nodeName !== 'BODY'
					&& nextNode && nextNode.nodeName == 'BR'
					&& !nextNode.nextSibling
					&& (this.editor.util.IsBlockElement(parentNode) || this.editor.util.IsBlockNode(parentNode))
			)
			{
				this.editor.selection.SetInvisibleTextAfterNode(parentNode, true);
				return BX.PreventDefault(e);
			}

		}
		else if (keyCode === KC['left'] || keyCode === KC['up'])
		{
			node = range.startContainer;
			parentNode = node ? node.parentNode : false;
			prevNode = node ? node.previousSibling : false;

			if (
				node.nodeType == 3 && range.endOffset === 0
				&& parentNode && parentNode.nodeName !== 'BODY'
				&& !prevNode
				&& (this.editor.util.IsBlockElement(parentNode) || this.editor.util.IsBlockNode(parentNode))
				)
			{
				this.editor.selection.SetInvisibleTextBeforeNode(parentNode);
				return BX.PreventDefault(e);
			}
			else if(
				node.nodeType == 3 && this.editor.util.IsEmptyNode(node)
					&& prevNode
					&& (this.editor.util.IsBlockElement(prevNode) || this.editor.util.IsBlockNode(prevNode))
				)
			{
				BX.remove(node);
				if (prevNode.lastChild)
				{
					this.editor.selection.SetAfter(prevNode.lastChild);
				}
				else
				{
					this.editor.selection.SetBefore(prevNode);
				}
				return BX.PreventDefault(e);
			}
		}

		return true;
	};

	BXEditorIframeView.prototype.OnKeyUpArrowsHandler = function(e, keyCode)
	{
		var
			_this = this,
			pre, prevToSur, nextToSur,
			keyDownNode, keyDownPre,
			range = this.editor.selection.GetRange(),
			node, parentNode, nextNode, prevNode, isEmpty, isSur, sameLastRange,
			startCont, endCont, startIsSur, endIsSur,
			KC = this.editor.KEY_CODES;

		// Arrows right or down
		if (keyCode === KC['right'] || keyCode === KC['down'])
		{
			this.editor.selection.GetStructuralTags();
			// Moving cursor by arrows (right & down)
			if (range.collapsed)
			{
				node = range.endContainer;

				isEmpty = this.editor.util.IsEmptyNode(node);
				// We check if last range was the same - it means that cursor doesn't
				// moved when user tried to move it
				sameLastRange = this.editor.selection.CheckLastRange(range);
				nextNode = node.nextSibling;

				if (!this.editor.util.CheckPreCursorSupport())
				{
					if (node.nodeName === 'PRE')
					{
						pre = node;
					}
					else if (node.nodeType == 3)
					{
						pre = BX.findParent(node, {tag: 'PRE'}, this.element);
					}

					if(pre)
					{
						if (this.keyDownRange)
						{
							keyDownNode = this.keyDownRange.endContainer;
							keyDownPre = keyDownNode == pre ? pre : BX.findParent(keyDownNode, function(n){return n == pre;}, this.element);
						}

						_this.FocusPreElement(pre, false, keyDownPre ? null : 'start');
					}
				}

				// If cursor in the invisible node - we take next node
				if (node.nodeType == 3 && isEmpty && nextNode)
				{
					node = nextNode;
					isEmpty = this.editor.util.IsEmptyNode(node);
				}

				isSur = this.editor.util.CheckSurrogateNode(node);

				// It's surrogate
				if (isSur)
				{
					nextToSur = node.nextSibling;
					if (nextToSur && nextToSur.nodeType == 3 && this.editor.util.IsEmptyNode(nextToSur))
						this.editor.selection._MoveCursorAfterNode(nextToSur);
					else
						this.editor.selection._MoveCursorAfterNode(node);

					BX.PreventDefault(e);
				}
				// If it's element
				else if (node.nodeType == 1 && node.nodeName != "BODY" && !isEmpty)
				{
					if (sameLastRange)
					{
						this.editor.selection._MoveCursorAfterNode(node);
						BX.PreventDefault(e);
					}
				}
				else if (sameLastRange && node.nodeType == 3 && /*node.length == range.endOffset &&*/ !isEmpty)
				{
					parentNode = node.parentNode;
					if (parentNode && node === parentNode.lastChild && parentNode.nodeName != "BODY")
					{
						this.editor.selection._MoveCursorAfterNode(parentNode);
					}
				}
				else if (node.nodeType == 3 && node.parentNode)
				{
					parentNode = node.parentNode;
					prevNode = parentNode.previousSibling;

					// It's empty invisible node before block element which was put there by us.
					// So we should remove it.
					if (
						(this.editor.util.IsBlockElement(parentNode) || this.editor.util.IsBlockNode(parentNode))
						&& prevNode && prevNode.nodeType == 3 && this.editor.util.IsEmptyNode(prevNode)
						)
					{
						BX.remove(prevNode);
					}
				}
			}
			else // Selection Shift + Right & Shift + down
			{
				startCont = range.startContainer;
				endCont = range.endContainer;
				startIsSur = this.editor.util.CheckSurrogateNode(startCont);
				endIsSur = this.editor.util.CheckSurrogateNode(endCont);

				if (startIsSur)
				{
					prevToSur = startCont.previousSibling;
					if (prevToSur && prevToSur.nodeType == 3 && this.editor.util.IsEmptyNode(prevToSur))
						range.setStartBefore(prevToSur);
					else
						range.setStartBefore(startCont);

					this.editor.selection.SetSelection(range);
				}

				if (endIsSur)
				{
					nextToSur = endCont.nextSibling;
					if (nextToSur && nextToSur.nodeType == 3 && this.editor.util.IsEmptyNode(nextToSur))
						range.setEndAfter(nextToSur);
					else
						range.setEndAfter(endCont);

					this.editor.selection.SetSelection(range);
				}
			}
		}
		// Arrows left or up
		else if (keyCode === KC['left'] || keyCode === KC['up'])
		{
			this.editor.selection.GetStructuralTags();

			// Moving cursor by arrows (left & up)
			if (range.collapsed)
			{
				node = range.startContainer;
				isEmpty = this.editor.util.IsEmptyNode(node);
				// We check if last range was the same - it means that cursor doesn't
				// moved when user tried to move it
				sameLastRange = this.editor.selection.CheckLastRange(range);

				// If cursor in the invisible node - we take next node
				if (node.nodeType == 3 && isEmpty && node.previousSibling)
				{
					node = node.previousSibling;
					isEmpty = this.editor.util.IsEmptyNode(node);
				}

				if (!this.editor.util.CheckPreCursorSupport())
				{
					if (node.nodeName === 'PRE')
					{
						pre = node;
					}
					else if (node.nodeType == 3)
					{
						pre = BX.findParent(node, {tag: 'PRE'}, this.element);
					}

					if(pre)
					{
						if (this.keyDownRange)
						{
							keyDownNode = this.keyDownRange.startContainer;
							keyDownPre = keyDownNode == pre ? pre : BX.findParent(keyDownNode, function(n){return n == pre;}, this.element);
						}
						_this.FocusPreElement(pre, false, keyDownPre ? null : 'end');
					}
				}

				isSur = this.editor.util.CheckSurrogateNode(node);
				// It's surrogate
				if (isSur)
				{
					prevToSur = node.previousSibling;
					if (prevToSur && prevToSur.nodeType == 3 && this.editor.util.IsEmptyNode(prevToSur))
						this.editor.selection._MoveCursorBeforeNode(prevToSur);
					else
						this.editor.selection._MoveCursorBeforeNode(node);

					BX.PreventDefault(e);
				}
				// If it's element
				else if (node.nodeType == 1 && node.nodeName != "BODY" && !isEmpty)
				{
					if (sameLastRange)
					{
						this.editor.selection._MoveCursorBeforeNode(node);
						BX.PreventDefault(e);
					}
				}
				//else if (sameLastRange && node.nodeType == 3 && range.startOffset == 0 && !isEmpty)
				else if (sameLastRange && node.nodeType == 3 && !isEmpty)
				{
					parentNode = node.parentNode;
					if (parentNode && node === parentNode.firstChild && parentNode.nodeName != "BODY")
					{
						this.editor.selection._MoveCursorBeforeNode(parentNode);
					}
				}
				else if (node.nodeType == 3 && node.parentNode)
				{
					parentNode = node.parentNode;
					prevNode = parentNode.nextSibling;

					// It's empty invisible node after block element which was put there by us.
					// So we should remove it.
					if (
						(this.editor.util.IsBlockElement(parentNode) || this.editor.util.IsBlockNode(parentNode))
							&& prevNode && prevNode.nodeType == 3 && this.editor.util.IsEmptyNode(prevNode)
						)
					{
						BX.remove(prevNode);
					}
				}

			}
			else // Selection Shift + left & Shift + up
			{
				startCont = range.startContainer;
				endCont = range.endContainer;
				startIsSur = this.editor.util.CheckSurrogateNode(startCont);
				endIsSur = this.editor.util.CheckSurrogateNode(endCont);

				if (startIsSur)
				{
					prevToSur = startCont.previousSibling;
					if (prevToSur && prevToSur.nodeType == 3 && this.editor.util.IsEmptyNode(prevToSur))
						range.setStartBefore(prevToSur);
					else
						range.setStartBefore(startCont);
					this.editor.selection.SetSelection(range);
				}

				if (endIsSur)
				{
					nextToSur = endCont.nextSibling;
					if (nextToSur && nextToSur.nodeType == 3 && this.editor.util.IsEmptyNode(nextToSur))
						range.setEndAfter(nextToSur);
					else
						range.setEndAfter(endCont);
					this.SetSelection(range);
				}
			}
		}

		this.keyDownRange = null;
	};

	BXEditorIframeView.prototype.FocusPreElement = function(preNode, timeout, mode)
	{
		var _this = this;

		if (this._focusPreElementTimeout)
			this._focusPreElementTimeout = clearTimeout(this._focusPreElementTimeout);

		if (timeout)
		{
			this._focusPreElementTimeout = setTimeout(function(){
				_this.FocusPreElement(preNode, false, mode);
			}, 100);
			return;
		}
		BX.focus(preNode);
		if (mode == 'end' && preNode.lastChild)
		{
			this.editor.selection.SetAfter(preNode.lastChild);
		}
		else if (mode == 'start' && preNode.firstChild)
		{
			this.editor.selection.SetBefore(preNode.firstChild);
		}
	};

	BXEditorIframeView.prototype.OnPasteHandler = function(e)
	{
		if (!this.editor.skipPasteHandler)
		{
			this.editor.skipPasteHandler = true;
			this.editor.pasteNodeIndex = {};
			var
				originalScrollTop = document.documentElement.scrollTop || document.body.scrollTop,
				originalScrollLeft = document.documentElement.scrollLeft || document.body.scrollLeft,
				_this = this,
				arNodes = [],
				curNode, i, node, qnodes;

			function markGoodNode(n)
			{
				if (n && n.setAttribute)
				{
					var randValue = Math.round(Math.random() * 1000000);
					_this.editor.pasteNodeIndex[randValue] = true;
					n.setAttribute('data-bx-paste-flag', randValue);
				}
			}

			curNode = this.document.body;
			if (curNode)
			{
				qnodes = curNode.querySelectorAll("*");
				for (i = 0; i < qnodes.length; i++)
				{
					if (qnodes[i].nodeType == 1 && qnodes[i].nodeName != 'BODY' && qnodes[i].nodeName != 'HEAD')
					{
						arNodes.push(qnodes[i]);
					}
				}

				for (i = 0; i < curNode.parentNode.childNodes.length; i++)
				{
					node = curNode.parentNode.childNodes[i];
					if (node.nodeType == 1 && node.nodeName != 'BODY' && node.nodeName != 'HEAD')
					{
						arNodes.push(node);
					}
				}
			}

			for (i = 0; i < arNodes.length; i++)
			{
				markGoodNode(arNodes[i]);
			}

			var sync = this.editor.synchro.IsSyncOn();
			if (sync)
			{
				this.editor.synchro.StopSync();
			}

			if (this.editor.iframeView.pasteHandlerTimeout)
			{
				clearTimeout(this.editor.iframeView.pasteHandlerTimeout);
			}

			this.pasteHandlerTimeout = setTimeout(function()
			{
				_this.editor.SetCursorNode();

				_this.editor.pasteHandleMode = true;
				_this.editor.bbParseContentMode = true;

				_this.editor.synchro.lastIframeValue = false;

				// Paste control: show menu after pasting content
				// to let user select weather insert rich content or plain text
				if (!_this.editor.skipPasteControl)
				{
					_this.editor.pasteControl.SaveIframeContent(_this.GetValue());
					_this.editor.pasteControl.CheckAndShow();
				}

				_this.editor.synchro.FromIframeToTextarea(true, true);

				_this.editor.pasteHandleMode = false;
				_this.editor.bbParseContentMode = false;

				_this.editor.synchro.lastTextareaValue = false;
				_this.editor.synchro.FromTextareaToIframe(true);

				_this.editor.RestoreCursor();

				_this.editor.On("OnIframePaste");
				_this.editor.On("OnIframeNewWord");
				_this.editor.skipPasteHandler = false;

				if (sync)
				{
					_this.editor.synchro.StartSync();
				}

				if (window.scrollTo)
				{
					window.scrollTo(originalScrollLeft, originalScrollTop);
				}
			}, 10);
		}
	};

	BXEditorIframeView.prototype.InitAutoLinking = function()
	{
		var
			_this = this,
			editor = this.editor,
			nativeAutolinkCanBeDisabled = editor.action.IsSupportedByBrowser("autoUrlDetect"),
			nativeAutoLink = BX.browser.IsIE() || BX.browser.IsIE9() || BX.browser.IsIE10();

		if (nativeAutolinkCanBeDisabled)
			editor.action.Exec("autoUrlDetect", false);

		if (editor.config.autoLink === false)
			return;

		// Init Autolink system
		var
			ignorableParents = {"CODE" : 1, "PRE" : 1, "A" : 1, "SCRIPT" : 1, "HEAD" : 1, "TITLE" : 1, "STYLE" : 1},
			urlRegExp = /(((?:https?|ftp):\/\/|www\.)[^\s'"<]{3,500})/gi,
			emailRegExp = /[\.a-z0-9_\-]+@[\.a-z0-9_\-]+\.[\.a-z0-9_\-]+/gi,
			MAX_LENGTH = 100,
			BRACKETS = {
				")": "(",
				"]": "[",
				"}": "{"
			};
		this.editor.autolinkUrlRegExp = urlRegExp;
		this.editor.autolinkEmailRegExp = emailRegExp;

		function autoLinkHandler()
		{
			try
			{
				if (checkAutoLink())
				{
					editor.selection.ExecuteAndRestore(function(startContainer, endContainer)
					{
						if (endContainer && endContainer.parentNode)
							autoLink(endContainer.parentNode);
					});
				}
			}
			catch(e){}
		}

		function checkAutoLink()
		{
			var
				node, nodeValue,
				result = false,
				doc = editor.GetIframeDoc(),
				walker = doc.createTreeWalker(
				doc.body,
				NodeFilter.SHOW_TEXT,
				null,
				false
			);

			while(node = walker.nextNode())
			{
				nodeValue = node.nodeValue || '';
				if ((nodeValue.match(emailRegExp) || nodeValue.match(urlRegExp)) &&
					node.parentNode && node.parentNode.nodeName != 'A')
				{
					result = true;
					break;
				}
			}

			return result;
		}

		function autoLink(element)
		{
			if (element && !ignorableParents[element.nodeName])
			{
				var ignorableParent = BX.findParent(element, function(node)
				{
					return !!ignorableParents[node.nodeName];
				}, element.ownerDocument.body);

				if (ignorableParent)
					return element;

				if (element === element.ownerDocument.documentElement)
					element = element.ownerDocument.body;

				return parseNode(element);
			}
		}

		function convertUrlToLink(str)
		{
			str = BX.util.htmlspecialchars(str);
			return str.replace(urlRegExp, function(match, url)
			{
				var
					punctuation = (url.match(/([^\w\u0430-\u0456\u0451\/\-#](,?))$/i) || [])[1] || "",
					opening = BRACKETS[punctuation];

				url = url.replace(/([^\w\u0430-\u0456\u0451\/\-#](,?))$/i, "");

				if (url.split(opening).length > url.split(punctuation).length)
				{
					url = url + punctuation;
					punctuation = "";
				}
				var
					realUrl = url,
					displayUrl = BX.util.htmlspecialchars(url);

				if (url.length > MAX_LENGTH)
					displayUrl = displayUrl.substr(0, MAX_LENGTH) + "...";

				if (realUrl.substr(0, 4) === "www.")
					realUrl = "http://" + realUrl;

				BX.onCustomEvent(_this.editor, 'OnAfterUrlConvert', [realUrl]);
				return '<a href="' + realUrl + '">' + displayUrl + '</a>' + punctuation;
			});
		}

		function convertEmailToLink(str)
		{
			str = BX.util.htmlspecialchars(str);
			return str.replace(emailRegExp, function(email)
			{
				var
					punctuation = (email.match(/([^\w\/\-](,?))$/i) || [])[1] || "",
					opening = BRACKETS[punctuation];
				email = email.replace(/([^\w\/\-](,?))$/i, "");
				if (email.split(opening).length > email.split(punctuation).length)
				{
					email = email + punctuation;
					punctuation = "";
				}

				var realUrl = "mailto:" + email;

				return '<a href="' + realUrl + '">' + email + '</a>' + punctuation;
			});
		}

		function getTmpDiv(doc)
		{
			var tmp = doc._bx_autolink_temp_div;
			if (!tmp)
				tmp = doc._bx_autolink_temp_div = doc.createElement("div");
			return tmp;
		}

		function parseNode(element)
		{
			var res, parentNode, tmpDiv;
			if (element && !ignorableParents[element.nodeName])
			{
				// Replaces the content of the text node by link
				if (element.nodeType === 3 &&
					element.data.match(urlRegExp) && element.parentNode)
				{
					parentNode = element.parentNode;
					tmpDiv = getTmpDiv(parentNode.ownerDocument);
					tmpDiv.innerHTML = "<span></span>" + convertUrlToLink(element.data);
					tmpDiv.removeChild(tmpDiv.firstChild);

					while (tmpDiv.firstChild)
						parentNode.insertBefore(tmpDiv.firstChild, element);

					parentNode.removeChild(element);
				}
				else if (element.nodeType === 3 &&
					element.data.match(emailRegExp) && element.parentNode)
				{
					parentNode = element.parentNode;
					tmpDiv = getTmpDiv(parentNode.ownerDocument);
					tmpDiv.innerHTML = "<span></span>" + convertEmailToLink(element.data);
					tmpDiv.removeChild(tmpDiv.firstChild);

					while (tmpDiv.firstChild)
						parentNode.insertBefore(tmpDiv.firstChild, element);

					parentNode.removeChild(element);
				}
				else if (element.nodeType === 1)
				{
					var
						childNodes = element.childNodes,
						i;

					for (i = 0; i < childNodes.length; i++)
						parseNode(childNodes[i]);

					res = element;
				}
			}
			return res;
		}

		if (!nativeAutoLink || (nativeAutoLink && nativeAutolinkCanBeDisabled))
		{
			BX.addCustomEvent(editor, "OnIframeNewWord", function()
			{
				if (editor.autolinkTimeout)
					editor.autolinkTimeout = clearTimeout(editor.autolinkTimeout);

				editor.autolinkTimeout = setTimeout(autoLinkHandler, 500);
			});

			BX.addCustomEvent(editor, "OnSubmit", function()
			{
				try
				{
					autoLink(editor.GetIframeDoc().body);
				}
				catch(e){}
			});
		}

		var
			links = editor.sandbox.GetDocument().getElementsByTagName("a"),
			getTextContent  = function(element)
			{
				var textContent = BX.util.trim(editor.util.GetTextContent(element));
				if (textContent.substr(0, 4) === "www.")
					textContent = "http://" + textContent;
				return textContent;
			};

		BX.addCustomEvent(editor, "OnIframeKeydown", function(e, keyCode, command, selectedNode)
		{
			if (links.length > 0 && selectedNode)
			{
				var link = BX.findParent(selectedNode, {tag: 'A'}, selectedNode.ownerDocument.body);
				if (link)
				{
					var textContent = getTextContent(link);
					setTimeout(function()
					{
						var newTextContent = getTextContent(link);
						if (newTextContent === textContent)
							return;

						// Only set href when new href looks like a valid url
						if (newTextContent.match(urlRegExp))
							link.setAttribute("href", newTextContent);
					}, 0);
				}
			}
		});
	};

	BXEditorIframeView.prototype.IsUserTypingNow = function(e)
	{
		return this.isFocused && this.isShown && this.isUserTyping;
	};

	BXEditorIframeView.prototype.CheckContentLastChild = function(element)
	{
		if (!element)
		{
			element = this.element;
		}

		var lastChild = element.lastChild;
		if (lastChild && (this.editor.util.IsEmptyNode(lastChild, true) && this.editor.util.IsBlockNode(lastChild.previousSibling) || this.editor.phpParser.IsSurrogate(lastChild)))
		{
			element.appendChild(BX.create('BR', {}, element.ownerDocument));
			element.appendChild(this.editor.util.GetInvisibleTextNode());
		}
	};

/**
 * Class _this takes care that the value of the composer and the textarea is always in sync
 */
	function BXEditorViewsSynchro(editor, textareaView, iframeView)
	{
		this.INTERVAL = 500;

		this.editor = editor;
		this.textareaView = textareaView;
		this.iframeView = iframeView;
		this.lastFocused = 'wysiwyg';

		this.InitEventHandlers();
	}

	/**
	 * Sync html from composer to textarea
	 * Takes care of placeholders
	 * @param {Boolean} bParseHtml Whether the html should be sanitized before inserting it into the textarea
	 */
	BXEditorViewsSynchro.prototype =
	{
		FromIframeToTextarea: function(bParseHtml, bFormat)
		{
			var value;
			if (this.editor.bbCode)
			{
				value = this.iframeView.GetValue(this.editor.bbParseContentMode, false);

				value = BX.util.trim(value);
				if (value !== this.lastIframeValue)
				{
					var bbCodes = this.editor.bbParser.Unparse(value);
					this.textareaView.SetValue(bbCodes, false, bFormat || this.editor.bbParseContentMode);

					if (typeof this.lastSavedIframeValue !== 'undefined' && this.lastSavedIframeValue != value)
					{
						this.editor.On("OnContentChanged", [bbCodes, value]);
					}
					this.lastSavedIframeValue = value;
					this.lastIframeValue = value;
				}
			}
			else
			{
				value = this.iframeView.GetValue();
				value = BX.util.trim(value);
				if (value !== this.lastIframeValue)
				{
					this.textareaView.SetValue(value, true, bFormat);
					if (typeof this.lastSavedIframeValue !== 'undefined' && this.lastSavedIframeValue != value)
					{
						this.editor.On("OnContentChanged", [this.textareaView.GetValue() || '', value || '']);
					}
					this.lastSavedIframeValue = value;
					this.lastIframeValue = value;
				}
			}
		},

		/**
		* Sync value of textarea to composer
		* Takes care of placeholders
		* @param {Boolean} bParseHtml Whether the html should be sanitized before inserting it into the composer
		*/
		FromTextareaToIframe: function(bParseHtml)
		{
			var value = this.textareaView.GetValue();

			if (value !== this.lastTextareaValue)
			{
				if (value)
				{
					if (this.editor.bbCode)
					{
						var htmlFromBbCode = this.editor.bbParser.Parse(value);
						// INVISIBLE_CURSOR
						htmlFromBbCode = htmlFromBbCode.replace(/\u2060/ig, '<span id="bx-cursor-node"> </span>');
						this.iframeView.SetValue(htmlFromBbCode, bParseHtml);
					}
					else
					{
						// INVISIBLE_CURSOR
						value = value.replace(/\u2060/ig, '<span id="bx-cursor-node"> </span>');
						this.iframeView.SetValue(value, bParseHtml);
					}
				}
				else
				{
					this.iframeView.Clear();
				}
				this.lastTextareaValue = value;
				this.editor.On("OnContentChanged", [value || '', this.iframeView.GetValue() || '']);
			}
		},

		FullSyncFromIframe: function()
		{
			this.lastIframeValue = false;
			this.FromIframeToTextarea(true, true);
			this.lastTextareaValue = false;
			this.FromTextareaToIframe(true);
		},

		Sync: function()
		{
			var bParseHtml = true;
			var view = this.editor.currentViewName;

			if (view === "split")
			{
				if (this.GetSplitMode() === "code")
				{
					this.FromTextareaToIframe(bParseHtml);
				}
				else // wysiwyg
				{
					this.FromIframeToTextarea(bParseHtml);
				}
			}
			else if (view === "code")
			{
				this.FromTextareaToIframe(bParseHtml);
			}
			else // wysiwyg
			{
				this.FromIframeToTextarea(bParseHtml);
			}
		},

		GetSplitMode: function()
		{
			var mode = false;
			if (this.editor.currentViewName == "split")
			{
				if (this.editor.iframeView.IsFocused())
				{
					mode = "wysiwyg";
				}
				else if(this.editor.textareaView.IsFocused())
				{
					mode = "code";
				}
				else
				{
					mode = this.lastFocused;
				}
			}
			return mode;
		},

		InitEventHandlers: function()
		{
			var _this = this;
			BX.addCustomEvent(this.editor, "OnTextareaFocus", function()
			{
				_this.lastFocused = 'code';
				_this.StartSync();
			});
			BX.addCustomEvent(this.editor, "OnIframeFocus", function()
			{
				_this.lastFocused = 'wysiwyg';
				_this.StartSync();
			});

			BX.addCustomEvent(this.editor, "OnTextareaBlur", BX.delegate(this.StopSync, this));
			BX.addCustomEvent(this.editor, "OnIframeBlur", BX.delegate(this.StopSync, this));
		},

		StartSync: function(delay)
		{
			var _this = this;

			if (this.interval)
			{
				this.interval = clearTimeout(this.interval);
			}

			this.delay = delay || this.INTERVAL; // it can reduce or increase initial timeout
			function sync()
			{
				// set delay to normal value
				_this.delay = _this.INTERVAL;
				_this.Sync();
				_this.interval = setTimeout(sync, _this.delay);
			}
			this.interval = setTimeout(sync, _this.delay);
		},

		StopSync: function()
		{
			if (this.interval)
			{
				this.interval = clearTimeout(this.interval);
			}
		},

		IsSyncOn: function()
		{
			return !!this.interval;
		},

		OnIframeMousedown: function(e, target, bxTag)
		{
		},

		IsFocusedOnTextarea: function()
		{
			return this.editor.currentViewName === "code" || this.editor.currentViewName === "split" && this.GetSplitMode() === "code";
		}
	}

	// global interface
	window.BXEditorTextareaView = BXEditorTextareaView;
	window.BXEditorIframeView = BXEditorIframeView;
	window.BXEditorViewsSynchro = BXEditorViewsSynchro;
})();