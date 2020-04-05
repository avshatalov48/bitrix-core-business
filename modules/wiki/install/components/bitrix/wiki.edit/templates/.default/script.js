
var BXWikiEditor = function(params)
{
	this.element = params.element || null;
	this.elementId = params.elementId;
	this.wikiTextHtmlInit = params.wikiTextHtmlInit;
	this.editUrl = params.editUrl;
	this.htmlEditorId = params.editorId;
	this.htmlEditor = null;
	this.charset = params.charset;
	this.maxImageWidth = params.maxImageWidth;
	this.myAgent = navigator.userAgent.toLowerCase();
	this.myVersion = parseInt(navigator.appVersion);
	this.wikitags = [];
	this.arWikiImg = {};
	this.arWikiCodeStorage = [];
	this.rng = null;
	this.sel = null;
	this.selLength = null;
	this.selStart = null;
	this.selEnd = null;
	this.oPrevRange = null;
	this.currentMode = 'text';

	this.is_ie = ((this.myAgent.indexOf("msie") != -1) && (this.myAgent.indexOf("opera") == -1));
	this.is_nav = ((this.myAgent.indexOf('mozilla')!=-1) && (this.myAgent.indexOf('spoofer')==-1)
		&& (this.myAgent.indexOf('compatible') == -1) && (this.myAgent.indexOf('opera')==-1)
		&& (this.myAgent.indexOf('webtv')==-1) && (this.myAgent.indexOf('hotjava')==-1));
	this.is_opera = (this.myAgent.indexOf("opera") != -1);
	this.is_win = ((this.myAgent.indexOf("win")!=-1) || (this.myAgent.indexOf("16bit") != -1));
	this.is_mac = (this.myAgent.indexOf("mac")!=-1);

	if (!window.phpVars)  // For anonymus  users
		window.phpVars = {};
};

BXWikiEditor.prototype.init = function()
{
	if(!this.element)
	{
		this.element = document.getElementById(this.elementId);
	}

	var htmlEditor = this.getHtmlEditor(this.htmlEditorId);
	if(htmlEditor)
	{
		this.htmlEditor = htmlEditor;
	}

	if(this.element)
	{
		this.bindEventHandlers();
		if(this.wikiTextHtmlInit)
			setTimeout(function(){this.showEditField('html', 'N')}.bind(this), 100);
		else
			this.showEditField('text', 'N');
	}
};

BXWikiEditor.prototype.getHtmlEditor = function(editorId)
{
	if(window['JCLightHTMLEditor'] && window['JCLightHTMLEditor'].items[editorId])
		return window['JCLightHTMLEditor'].items[editorId];
	else
		return null;
};

BXWikiEditor.prototype.bindEventHandlers = function()
{
	BX.addCustomEvent(window, 'LHE_OnInit', function(editor)
	{
		if(editor.id === this.htmlEditorId)
		{
			this.htmlEditor = editor;
		}
	}.bind(this));
	var handlers =
	{
		'wiki-wcode-bold': this.wiki_bold.bind(this),
		'wiki-wcode-italic': this.wiki_italic.bind(this),
		'wiki-wcode-wheader': this.wiki_header.bind(this),
		'wiki-wcode-category': this.ShowCategoryInsert.bind(this),
		'wiki-wcode-url': this.ShowInsertLink.bind(this, false),
		'wiki-wcode-signature': this.wiki_signature.bind(this),
		'wiki-wcode-line': this.wiki_line.bind(this),
		'wiki-wcode-ignore': this.wiki_nowiki.bind(this),
		'wiki-wcode-external-url': this.ShowInsertLink.bind(this,true),
		'wiki-wcode-img': this.ShowImageInsert.bind(this),
		'wiki-wcode-img-upload': this.ShowImageUpload.bind(this),
		'wiki-wcode-code': this.WikiInsertCode.bind(this)
	};

	for(var buttonClass in handlers)
	{
		var button = this.element.querySelector('a.' + buttonClass);
		if(button && handlers.hasOwnProperty(buttonClass))
		{
			button.addEventListener('click', handlers[buttonClass]);
		}
	}

	var switchToTextElement = this.element.querySelector('#wki-text-text');
	if(switchToTextElement)
		switchToTextElement.addEventListener('click', this.showEditField.bind(this, 'text', 'Y'));

	var switchToHtmlElement = this.element.querySelector('#wki-text-html');
	if(switchToHtmlElement)
		switchToHtmlElement.addEventListener('click', this.showEditField.bind(this, 'html', 'Y'));

	var messageElement = this.element.querySelector('#MESSAGE');
	if(messageElement)
		messageElement.addEventListener('keydown',this.check_ctrl_enter);
};

BXWikiEditor.prototype.wiki_bold = function()
{
	this.simpletag("'''");
};

BXWikiEditor.prototype.wiki_italic = function()
{
	this.simpletag("''");
};

BXWikiEditor.prototype.wiki_header = function()
{
	this.simpletag("==");
};

BXWikiEditor.prototype.wiki_line = function()
{
	this.doInsert('----', '', false);
};

BXWikiEditor.prototype.wiki_signature = function()
{
	if(this.htmlEditor && this.currentMode === 'html')
	{
		this.htmlEditor.InsertHTML('--~~~~');
	}
	else
	{
		this.doInsert('--~~~~', '', false);
	}
};

BXWikiEditor.prototype.wiki_nowiki = function()
{
	this.simpletag("<NOWIKI>");
};

// Insert simple tags: B, I, U, CODE, QUOTE
BXWikiEditor.prototype.simpletag = function(thetag)
{
	if (this.doInsert(thetag, this.checkTag(thetag), true))
	{
		// Change the button status
		this.wikitags.push(thetag);
		this.cstat();
	}
};

BXWikiEditor.prototype.showDialog = function(params, buttons)
{
	var bxd = new BX.CDialog(params);
	bxd.ClearButtons();
	bxd.SetButtons(buttons);
	bxd.adjustSizeEx();
	BX.addCustomEvent(bxd, 'onWindowUnRegister', function()
	{
		if (bxd.DIV && bxd.DIV.parentNode)
			bxd.DIV.parentNode.removeChild(bxd.DIV);
	});
	bxd.Show();
};


BXWikiEditor.prototype.ShowImageUpload = function()
{
	var wikiEditor = this;
	var params = {
		title: BX.message('WIKI_IMAGE_UPLOAD'),
		content: this.getTemplate('template-image-upload'),
		height: 150,
		width: 400,
		min_height: 150,
		min_width: 400
	};

	var _BTN = [
		{
			'title': BX.message('WIKI_SAVE'),
			'id': 'wk_upload',
			'action': function() {
				this.disableUntilError();
				BX.ajax.submitAjax(
					document.forms['load_form'],
					{
						url: this.editUrl,
						method: 'POST',
						dataType: 'json',
						onsuccess: function(data) {
							if(data.hasOwnProperty('ERROR_MESSAGE'))
							{
								BX.WindowManager.Get().ShowError(data['ERROR_MESSAGE']);
								return;
							}
							if(!data.hasOwnProperty('IMAGE'))
							{
								return;
							}

							var image = {
								id: parseInt(data['IMAGE']['ID']),
								name: data['IMAGE']['ORIGINAL_NAME'],
								html: data['IMAGE']['FILE_SHOW']
							};
							var imgTable = document.getElementById('wiki-post-image');
							var newImageElement = document.createElement('div');
							newImageElement.classList.add('wiki-post-image-item');
							newImageElement.innerHTML = wikiEditor.render(wikiEditor.getTemplate('template-image-item'), image);
							imgTable.appendChild(newImageElement);
							wikiEditor.arWikiImg[image.id] = image.name;
							wikiEditor.doInsert('[File:' + image.name + ']', '', false, image.id);

							BX.closeWait();
							BX.WindowManager.Get().Close();
						},
						onerror: function() {
							BX.closeWait();
						}
					});
			}
		},
		/*BX.CAdminDialog.btnSave,*/
		BX.CDialog.btnCancel
	];

	this.getSelectedText();
	this.showDialog(params, _BTN);
};

BXWikiEditor.prototype.ShowImageInsert = function()
{
	var wikiEditor = this;
	this.getSelectedText();
	var params = {
		title: BX.message('WIKI_INSERT_IMAGE'),
		content: this.getTemplate('template-insert-image'),
		height: 150,
		width: 400,
		min_height: 150,
		min_width: 400
	};
	var _BTN = [
		{
			'title': BX.message('WIKI_BUTTON_INSERT'),
			'id': 'wk_insert',
			'action': function () {
				wikiEditor.wiki_tag_image(BX('image_url').value);
				this.parentWindow.Close();
			}
		},
		BX.CDialog.btnCancel
	];
	this.showDialog(params, _BTN);
};

BXWikiEditor.prototype.ShowCategoryInsert = function()
{
	var wikiEditor = this;
	this.getSelectedText();
	var params = {
		title: BX.message('WIKI_INSERT_CATEGORY'),
		content: this.getTemplate('template-insert-category'),
		height: 120,
		width: 400,
		min_height: 120,
		min_width: 400
	};
	var bxd = new BX.CDialog(params);
	var _BTN = [
		{
			'title': BX.message('WIKI_BUTTON_INSERT'),
			'id': 'wk_insert',
			'action': function () {
				wikiEditor.wiki_tag_category(BX('category_name').value);
				this.parentWindow.Close();
			}
		},
		BX.CDialog.btnCancel
	];
	this.showDialog(params, _BTN);
};

BXWikiEditor.prototype.ShowInsertLink = function(external)
{
	var wikiEditor = this;
	var template = (external ? this.getTemplate('template-insert-external-link') : this.getTemplate('template-insert-internal-link'));
	var selectedText = this.getSelectedText();
	var data = {
		linkName: (selectedText ? selectedText : '')
	};
	var content = this.render(template, data);
	var params = {
		title: (external ? BX.message('WIKI_INSERT_EXTERANL_HYPERLINK') : BX.message('WIKI_INSERT_HYPERLINK')),
		content: content,
		height: 120,
		width: 400,
		min_height: 120,
		min_width: 400
	};

	var _BTN = [
		{
			'title': BX.message('WIKI_BUTTON_INSERT'),
			'id': 'wk_insert',
			'action': function () {
				if (external)
					wikiEditor.wiki_tag_url_external(BX('bx_url_type').value,BX('link_url').value, BX('link_name').value);
				else
					wikiEditor.wiki_tag_url(BX('link_url').value, BX('link_name').value);
				this.parentWindow.Close();
			}
		},
		BX.CDialog.btnCancel
	];
	this.showDialog(params, _BTN);
};

//Insert tag {{{ code }}}
BXWikiEditor.prototype.WikiInsertCode = function()
{
	var text = this.getSelectedText();

	if(!text)
		text=" ";

	if(this.doInsert("{{{"+text+"}}}","" , false, null, true))
	{
		// Change the button status
		this.wikitags.push("{{{");
		this.cstat();
	}
};

// Insert url tag
BXWikiEditor.prototype.wiki_tag_url = function(URL, TEXT)
{
	var text;
	if (TEXT &&  !URL)
		URL = TEXT;
	text = '[['+ URL + (TEXT && TEXT != '' ? '|' + TEXT : '') + ']]';

	if(this.htmlEditor && this.currentMode === 'html')
	{
		this.htmlEditor.SelectRange(this.oPrevRange);
		this.htmlEditor.InsertHTML(text);
	}
	else
	{
		this.doInsert(text, "", false, null, true);
	}
};

BXWikiEditor.prototype.wiki_tag_url_external = function(prefix, URL, TEXT)
{
	var text = '';
	if (!URL)
		return ;

	if(this.htmlEditor && this.currentMode === 'html')
	{
		text = '<a href="'+prefix+URL+'" >'+TEXT+'</a>';
		this.htmlEditor.SelectRange(this.oPrevRange);
		this.htmlEditor.InsertHTML(text);
	}
	else
	{
		text = '[' +prefix+ URL + (TEXT && TEXT != '' ? ' ' + TEXT : '') + ']';
		this.doInsert(text, "", false, null, true);
	}
};

// Insert image tag
BXWikiEditor.prototype.wiki_tag_image = function(URL)
{
	if (URL)
	{
		if(this.htmlEditor && this.currentMode === 'html')
		{
			var _str = '<img ' +
					'id="' + this.htmlEditor.SetBxTag(false, {'tag': 'wiki_img', 'params': {'id' : URL, 'file_name' : URL}}) + '" ' +
					'src="' + URL + '">';

			this.htmlEditor.SelectRange(this.oPrevRange);
			this.htmlEditor.InsertHTML(_str);
		}
		else
		{
			this.doInsert("[" + BX.message('FILE_NAME') + ":" + URL + "]", "", false, null, true);
		}
	}
};

//Insert image tag
BXWikiEditor.prototype.wiki_tag_category = function(TEXT)
{
	if (TEXT)
	{
		if(this.htmlEditor && this.currentMode === 'html')
		{
			var _str = "[[" + BX.message('CATEGORY_NAME') + ":" + TEXT + "]]<br />";

			this.htmlEditor.SelectRange(this.oPrevRange);
			this.htmlEditor.InsertHTML(_str);
		}
		else
		{
			this.doInsert("[[" + BX.message('CATEGORY_NAME') + ":" + TEXT + "]]\n", "", false, null, true);
		}
	}
};

// Close all tags
BXWikiEditor.prototype.closeall = function()
{
	while(this.wikitags.length > 0)
	{
		var tagRemove = this.wikitags.pop();
		document.getElementById("MESSAGE").value += this.checkTag(tagRemove);
	}

	this.wikitags = [];
	this.cstat();
};

// Show statistic
BXWikiEditor.prototype.cstat = function()
{
	document.getElementById("MESSAGE").focus();
};

BXWikiEditor.prototype.getSelectedText = function()
{
	var selectedText = false;

	if(this.htmlEditor && this.currentMode === 'html')
	{
		this.oPrevRange = this.htmlEditor.GetSelectionRange();

		if (this.oPrevRange.startContainer && this.oPrevRange.endContainer) // DOM Model
		{
			if (this.oPrevRange.startContainer == this.oPrevRange.endContainer &&	(this.oPrevRange.endContainer.nodeType == 3 || this.oPrevRange.endContainer.nodeType == 1))
					selectedText = this.oPrevRange.startContainer.textContent.substring(this.oPrevRange.startOffset, this.oPrevRange.endOffset) || '';
		}
		else // IE
		{
			if (this.oPrevRange.text == this.oPrevRange.htmlText)
				selectedText = this.oPrevRange.text || '';
			else if (this.oPrevRange.htmlText)
				selectedText = this.oPrevRange.htmlText;
		}

		if (!selectedText)
		{
			if(this.is_ie)
				selectedText = this.oPrevRange.text || '';
			else
				selectedText = this.oPrevRange || '';
		}
	}
	else
	{
		var textarea = document.getElementById("MESSAGE");
		var currentScroll = textarea.scrollTop;

		if (this.is_ie)
		{
			textarea.focus();
			this.sel = document.selection;
			this.rng = this.sel.createRange();
			var stored_rng = this.rng.duplicate();
			stored_rng.moveToElementText(textarea);
			stored_rng.setEndPoint('EndToEnd', this.rng);
			this.selStart = stored_rng.text.length - this.rng.text.length;
			this.selEnd = this.selStart + this.rng.text.length;
			this.rng.collapse();
			if ((this.sel.type == "Text" || this.sel.type == "None") && this.rng.text.length > 0)
				selectedText = this.rng.text;
		}
		else
		{
			this.selLength = textarea.textLength;
			this.selStart = textarea.selectionStart;
			this.selEnd = textarea.selectionEnd;
			if(textarea.selectionEnd > textarea.selectionStart)
				selectedText = (textarea.value).substring(textarea.selectionStart, textarea.selectionEnd);
		}
	}
	return selectedText;
};

BXWikiEditor.prototype.mozillaWr = function(textarea, open, close, replace)
{
	if (!this.selLength) {
		this.selLength = textarea.textLength;
	}
	if (!this.selStart)
		this.selStart = textarea.selectionStart;
	if (!this.selEnd)
		this.selEnd = textarea.selectionEnd;

	if (this.selEnd == 1 || this.selEnd == 2)
		this.selEnd = this.selLength;

	var s1 = (textarea.value).substring(0,this.selStart);
	var s2 = (textarea.value).substring(this.selStart, this.selEnd);
	var s3 = (textarea.value).substring(this.selEnd, this.selLength);

	if (replace === true)
		textarea.value = s1 + open + close + s3;
	else
		textarea.value = s1 + open + s2 + close + s3;

	textarea.selectionEnd = 0;
	textarea.selectionStart = this.selEnd + open.length + close.length;
	textarea.setSelectionRange(this.selStart, this.selEnd);
	this.selLength = null;
	this.selStart = null;
	this.selEnd = null;
};

BXWikiEditor.prototype.checkTag = function(tag)
{
	var bracketEnd = '';
	var bracketStart = '';

	if (tag.substr(0, 1) == '[' || tag.substr(0, 1) == '<')
	{
		bracketStart = tag.substr(0, 1) + '/';
		tag = tag.substring(1,tag.length);
		bracketEnd = tag.substr(tag.length, 1);
	}
	return bracketStart + tag + bracketEnd;
};

BXWikiEditor.prototype.doInsert = function(ibTag, ibClsTag, isSingle, imgID, replace)
{
	if(imgID > 0 && this.htmlEditor && this.currentMode === 'html')
	{
			var __image = document.getElementById(imgID);
			var imageSrc = __image.src;
			var _img_width = null;
			var _img_height = null;
			var _img_style = '';

			if (!__image.naturalWidth) {
				var lgi = new Image();
				lgi.src = imageSrc;
				_img_width = lgi.width;
				_img_height = lgi.height;
			}
			else
			{
				_img_width = __image.naturalWidth;
				_img_height = __image.naturalHeight;
			}

			if (_img_width > this.maxImageWidth)
				_img_style += 'width: ' + this.maxImageWidth;

			var file_name  = this.arWikiImg[imgID];
			var _str = '<img id="' + this.htmlEditor.SetBxTag(false, {'tag': 'wiki_img', 'params': {'id' : imgID, 'file_name' : file_name}}) + '"' +
					'src="'+imageSrc+'" style="'+_img_style+'">';

			this.htmlEditor.InsertHTML(_str);
			return true;
	}

	var isClose = false;
	var textarea = document.getElementById("MESSAGE");
	var currentScroll = textarea.scrollTop;

	if (isSingle)
		isClose = true;

	if (this.is_ie)
	{
		textarea.focus();
		if (!this.sel)
			this.sel = document.selection;
		if (!this.rng)
			this.rng = this.sel.createRange();



		this.rng.collapse();
		if ((this.sel.type == "Text" || this.sel.type == "None") && this.rng != null)
		{
			if (ibClsTag != "" && this.rng.text.length > 0)
			{
				ibTag += this.rng.text + ibClsTag;
				isClose = false;
			}
			else if (ibClsTag != "")
			{
				ibTag += this.rng.text + ' ' + ibClsTag;
				isClose = false;
			}

			this.rng.text = ibTag;
			var new_rng = textarea.createTextRange();
			new_rng.move("character", this.selStart);
			new_rng.select();
		}
		this.rng = null;
		this.sel = null;
	}
	else
	{
		if (this.is_nav && document.getElementById)
		{
			if (ibClsTag != "" && textarea.selectionEnd > textarea.selectionStart)
			{
				this.mozillaWr(textarea, ibTag, ibClsTag, replace);
				isClose = false;
			}
			else if (ibClsTag != "")
			{
				this.mozillaWr(textarea, ibTag + ' ', ibClsTag, false);
			}
			else
				this.mozillaWr(textarea, ibTag, '', replace);
		}
		else
			textarea.value += ibTag;
	}

	textarea.scrollTop = currentScroll;
	textarea.focus();
	return isClose;
};

BXWikiEditor.prototype.check_ctrl_enter = function(e)
{
	if((e.keyCode == 13 || e.keyCode == 10) && e.ctrlKey)
	{
		document.REPLIER.submit();
	}
};

BXWikiEditor.prototype.saveWikiCodes = function(text)
{
	this.arWikiCodeStorage = [];
	var retText = '';
	var _this = this;

	retText = text.replace(/({{{[\s\S]*?}}}|\[CODE\][\s\S]*?\[\/CODE\])/igm, function(code, offset, text)
	{
		var i = _this.arWikiCodeStorage.push(code) - 1;
		return "##CODE"+i+"##";
	});
	return retText;
};

BXWikiEditor.prototype.restoreWikiCodes = function(text)
{
	var _this = this;
	return text.replace(/##CODE(\d+)##/igm, function(code, digit, offset, text)
	{
		return (_this.arWikiCodeStorage[digit] ? _this.arWikiCodeStorage[digit] : "");
	});
};

BXWikiEditor.prototype.convTextForText = function(text)
{
	text = this.saveWikiCodes(text);
	text = text.replace(/<br(\s*\/)?>\s*(\r*\n)?/igm, "\n");
	text = this.restoreWikiCodes(text);
	text = text.replace(/\[CODE\]/igm, "{{{");
	text = text.replace(/\[\/CODE\]/igm, "}}}");
	return text;
};

BXWikiEditor.prototype.convTextForVisual = function(text)
{
	text = this.saveWikiCodes(text);
	text = text.replace(/(<\s*\/?(h\d|li|ul|ol|p|table|tbody|td|tr|hr|div|span)\s*>)\s*(\r*\n)/igm, "$1##NN##");
	text = text.replace(/(<\s*(ul)\s*>)\s*(\r*\n)/igm, "$1##NN##");
	text = text.replace(/\r*\n/igm, "<br />\n");
	text = text.replace(/##NN##/igm, "\n");
	text = this.restoreWikiCodes(text);
	text = text.replace(/{{{/igm, "[CODE]");
	text = text.replace(/}}}/igm, "[/CODE]");
	return text;
};

BXWikiEditor.prototype.htmlspecchWikiCodes = function(text)
{
	return text.replace(/({{{(\s|.)*}}}|\[CODE\](\s|.)*\[\/CODE\])/igm, function (code,p1,p2,p3,offset,text){
				return BX.util.htmlspecialchars(code);
				});
};

BXWikiEditor.prototype.htmlspecchWikiCodesBack = function(text)
{
	return text.replace(/({{{(\s|.)*}}}|\[CODE\](\s|.)*\[\/CODE\])/igm, function (code,p1,p2,p3,offset,text){
				return BX.util.htmlspecialcharsback(code);
				});
};

BXWikiEditor.prototype.insertSanitized = function(text, pLEditor)
{
	var _this = this;
	pLEditor.SetEditorContent("");
	var oDivIDHtml = document.getElementById("edit-post-html");
	var show = BX.showWait(oDivIDHtml);
	var textPrepared = this.saveWikiCodes(text);

	var requestParams = {
		'act': 'sanitize',
		'sessid': BX.bitrix_sessid(),
		'text': textPrepared
	};
	BX.ajax({
		url: '/bitrix/components/bitrix/wiki/component.ajax.php',
		method: 'POST',
		data: requestParams,
		onsuccess: function(result) {
			result = _this.restoreWikiCodes(result);
			result = _this.convTextForVisual(result);

			pLEditor.SetEditorContent(result);
			BX.closeWait(oDivIDHtml, show);
		}
	});
};

BXWikiEditor.prototype.setEditorContentAfterLoad = function(pLEditor)
{
	var content = document.getElementById("MESSAGE").value;
	this.insertSanitized(content,pLEditor);
};

BXWikiEditor.prototype.showEditField = function(type, change)
{
	var oDivIDHtml = document.getElementById("edit-post-html");
	var oDivIDText = document.getElementById("edit-post-text");

	if(type == "html")
	{
		oDivIDText.style.display = "none";
		if(oDivIDHtml)
			oDivIDHtml.style.display = "block";

		if(change == "Y")
		{
			if(this.htmlEditor)
			{
				var content = document.getElementById("MESSAGE").value;
				this.insertSanitized(content, this.htmlEditor);
			}
		}
		this.currentMode = 'html';
	}
	else
	{
		if(oDivIDHtml)
			oDivIDHtml.style.display = "none";
		oDivIDText.style.display = "block";
		if(change == "Y")
		{
			if(this.htmlEditor)
			{
				this.htmlEditor.SaveContent();
				var _content = this.htmlEditor.GetContent();
				_content = this.convTextForText(_content);
				_content = _content.replace(/(<\s*\/*\s*)(code)(\s*>)/gi, "$1nowiki$3" );
				document.getElementById("MESSAGE").value = _content;
			}
		}
		this.currentMode = 'text';
	}
	return false;
};

BXWikiEditor.prototype.wikiPostToFeedTogle = function()
{
	var ptf = document.getElementById("post_to_feed");

	if(!ptf)
		return false;

	if (ptf.value == "Y")
		ptf.value = "N";
	else
		ptf.value = "Y";

	this.wikiSetCheckboxPTF(ptf.value);

	return true;
};

BXWikiEditor.prototype.wikiSetCheckboxPTF = function(value)
{
	var cb = document.getElementById("cb_post_to_feed");

	if(!cb)
		return false;

	cb.checked = (value == "Y");
	return true;
};

BXWikiEditor.prototype.replaceLinkByInput = function(linkObj,inputDivId)
{
	if(!linkObj.parentNode)
		return false;

	var inputDiv = BX(inputDivId);

	if(!inputDiv)
		return false;

	BX.addClass(linkObj.parentNode,"wiki-post-div-hide");
	setTimeout(function() {BX.addClass(linkObj.parentNode,"wiki-post-div-nonedisplay");},300);

	setTimeout(function() {
		BX.removeClass(inputDiv,"wiki-post-div-nonedisplay");
		inputDiv.parentNode.style.height = "3em";
		BX.removeClass(inputDiv,"wiki-post-div-hide");
	},295);

	return false;
};

BXWikiEditor.prototype.getTemplate = function(templateName)
{
	var element = document.getElementById(templateName);
	var result = '';
	if(element)
	{
		result = element.innerHTML;
	}
	return result;
};

/**
 * Replaces placeholders in template with values from data.
 * @param template Template text.
 * @param data Data to replaces placeholders.
 */
BXWikiEditor.prototype.render = function(template, data)
{
	var result;
	if(typeof template !== 'string')
		return template;

	if(typeof data !== 'object')
		return template;

	result = template.replace(/#(\w+?)#/g, function(match, variable, offset)
	{
		if(data.hasOwnProperty(variable))
			return data[variable];
		else
			return match;
	});
	return result;
};
