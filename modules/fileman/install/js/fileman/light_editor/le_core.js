function JCLightHTMLEditor(arConfig) {this.Init(arConfig);}

JCLightHTMLEditor.items = {};

JCLightHTMLEditor.prototype = {
Init: function(arConfig)
{
	this.id = arConfig.id;
	JCLightHTMLEditor.items[this.id] = this;

	var _this = this;
	this.arConfig = arConfig;
	this.bxTags = {};
	this.bFocused = false;
	arConfig.timeoutCount = arConfig.timeoutCount || 0;

	this.bPopup = false;
	this.buttonsIndex = {};
	this.parseAlign = true;
	this.parseTable = true;
	this.lastCursorId = 'bxed-last-cursor';
	this.bHandleOnPaste = this.arConfig.bHandleOnPaste !== false;

	this.arBBTags = ['p', 'u', 'div', 'table', 'tr', 'td', 'th', 'img', 'a', 'center', 'left', 'right', 'justify'];
	this._turnOffCssCount = 0;

	if (this.arConfig.arBBTags)
		this.arBBTags = this.arBBTags.concat(this.arConfig.arBBTags);

	this.arConfig.width = this.arConfig.width ? parseInt(this.arConfig.width) + (this.arConfig.width.indexOf('%') == -1 ? "px" : '%') : "100%";
	this.arConfig.height = this.arConfig.height ? parseInt(this.arConfig.height) + (this.arConfig.height.indexOf('%') == -1 ? "px" : '%') : "100%";
	this.SetConstants();
	this.sEditorMode = 'html';
	this.toolbarLineCount = 1;

	this.CACHE = {};
	this.arVideos = {};

	// Set content from config;
	this.content = this.arConfig.content;
	this.oSpecialParsers = {};
	BX.onCustomEvent(window, 'LHE_OnBeforeParsersInit', [this]);

	this.oSpecialParsers.cursor = {
		Parse: function(sName, sContent, pLEditor)
		{
			return sContent.replace(/#BXCURSOR#/ig, '<span id="' + pLEditor.lastCursorId + '"></span>');
		},
		UnParse: function(bxTag, pNode, pLEditor)
		{
			return '#BXCURSOR#';
		}
	};

	if (arConfig.parsers)
	{
		for (var p in arConfig.parsers)
		{
			if (arConfig.parsers[p])
				this.oSpecialParsers[p] = arConfig.parsers[p];
		}
	}

	this.bDialogOpened = false;

	// Sceleton
	this.pFrame = BX('bxlhe_frame_' + this.id);
	if (!this.pFrame)
	{
		if (arConfig.timeoutCount < 100)
		{
			setTimeout(function()
			{
				arConfig.timeoutCount++;
				_this.Init(arConfig);
			}, 1);
		}
		return;
	}

	this.pFrame.style.display = "block";

	this.pFrame.style.width = this.arConfig.width;
	this.pFrame.style.height = this.arConfig.height;

	this.pFrameTable = this.pFrame.firstChild;
	this.pButtonsCell = this.pFrameTable.rows[0].cells[0];
	this.pButtonsCont = this.pButtonsCell.firstChild;
	this.pEditCont = this.pFrameTable.rows[1].cells[0];

	if (this.arConfig.height.indexOf('%') == -1)
	{
		var h = parseInt(this.arConfig.height) - this.toolbarLineCount * 27;
		if (h > 0)
			this.pEditCont.style.height = h + 'px';
	}

	// iFrame
	this.CreateFrame();

	// Textarea
	this.pSourceDiv = this.pEditCont.appendChild(BX.create("DIV", {props: {className: 'lha-source-div' }}));
	this.pTextarea = this.pSourceDiv.appendChild(BX.create("TEXTAREA", {props: {className: 'lha-textarea', rows: 25, id: this.arConfig.inputId}}));
	this.pHiddenInput = this.pFrame.appendChild(BX.create("INPUT", {props: {type: 'hidden', name: this.arConfig.inputName}}));

	this.pTextarea.onfocus = function(){_this.bTextareaFocus = true;};
	this.pTextarea.onblur = function(){_this.bTextareaFocus = false;};

	this.pTextarea.style.fontFamily = this.arConfig.fontFamily;
	this.pTextarea.style.fontSize = this.arConfig.fontSize;
	this.pTextarea.style.fontSize = this.arConfig.lineHeight;

	if (this.pHiddenInput.form)
	{
		BX.bind(this.pHiddenInput.form, 'submit', function(){
			try{
				_this.SaveContent();
				_this.pHiddenInput.value = _this.pTextarea.value = _this.pHiddenInput.value.replace(/#BXCURSOR#/ig, '');
			}
			catch(e){}
		});
	}

	// Sort smiles
	if (this.arConfig.arSmiles && this.arConfig.arSmiles.length > 0)
	{
		this.sortedSmiles = [];
		var i, l, smile, j, k, arCodes;
		for (i = 0, l = this.arConfig.arSmiles.length; i < l; i++)
		{
			smile = this.arConfig.arSmiles[i];
			if (!smile['codes'] || smile['codes'] == smile['code'])
			{
				this.sortedSmiles.push(smile);
			}
			else if(smile['codes'].length > 0)
			{
				arCodes = smile['codes'].split(' ');
				for(j = 0, k = arCodes.length; j < k; j++)
					this.sortedSmiles.push({name: smile.name, path: smile.path, code: arCodes[j]});
			}
		}

		//this.sortedSmiles = BX.clone(this.arConfig.arSmiles);
		this.sortedSmiles = this.sortedSmiles.sort(function(a, b){return b.code.length - a.code.length;});
	}

	if (!this.arConfig.bBBCode && this.arConfig.bConvertContentFromBBCodes)
		this.arConfig.bBBCode = true;

	this.bBBCode = this.arConfig.bBBCode;
	if (this.bBBCode)
	{
		if (this.InitBBCode && typeof this.InitBBCode == 'function')
			this.InitBBCode();
	}

	this.bBBParseImageSize = this.arConfig.bBBParseImageSize;

	if (this.arConfig.bResizable)
	{
		if (this.arConfig.bManualResize)
		{
			this.pResizer = BX('bxlhe_resize_' + this.id);
			/*this.pResizer.style.width = this.arConfig.width;*/
			this.pResizer.title = BX.message.ResizerTitle;

			if (!this.arConfig.minHeight || parseInt(this.arConfig.minHeight) <= 0)
				this.arConfig.minHeight = 100;
			if (!this.arConfig.maxHeight || parseInt(this.arConfig.maxHeight) <= 0)
				this.arConfig.maxHeight = 2000;

			this.pResizer.unselectable = "on";
			this.pResizer.ondragstart = function (e){return BX.PreventDefault(e);};
			this.pResizer.onmousedown = function(){_this.InitResizer(); return false;};
		}

		if (this.arConfig.bAutoResize)
		{
			BX.bind(this.pTextarea, 'keydown', BX.proxy(this.AutoResize, this));
			BX.addCustomEvent(this, 'onShow', BX.proxy(this.AutoResize, this));
		}
	}

	// Add buttons
	this.AddButtons();

	// Check if ALIGN tags allowed
	this.parseAlign = !this.arConfig.bBBCode || !!(this.buttonsIndex['Justify'] || this.buttonsIndex['JustifyLeft']);
	this.parseTable = !this.arConfig.bBBCode || !!this.buttonsIndex['Table'];

	if (!this.parseAlign || !this.parseTable)
	{
		var arBBTags = [];
		for (var k in this.arBBTags)
		{
			// Align tags
			if (!this.parseAlign && (
				this.arBBTags[k] == 'center' || this.arBBTags[k] ==  'left' ||
				this.arBBTags[k] ==  'right' || this.arBBTags[k] == 'justify'
			))
				continue;

			// Table tags
			if (!this.parseTable && (
				this.arBBTags[k] == 'table' || this.arBBTags[k] ==  'tr' ||
					this.arBBTags[k] ==  'td' || this.arBBTags[k] == 'th'
				))
				continue;

			arBBTags.push(this.arBBTags[k]);
		}
		this.arBBTags = arBBTags;
	}

	this.SetContent(this.content);
	this.SetEditorContent(this.content);
	this.oTransOverlay = new LHETransOverlay({}, this);
	// TODO: Fix it
	//this.oContextMenu = new LHEContextMenu({zIndex: 1000}, this);

	BX.onCustomEvent(window, 'LHE_OnInit', [this, false]);

	// Init events
	BX.bind(this.pEditorDocument, 'click', BX.proxy(this.OnClick, this));
	BX.bind(this.pEditorDocument, 'mousedown', BX.proxy(this.OnMousedown, this));
	//BX.bind(this.pEditorDocument, 'contextmenu', BX.proxy(this.OnContextMenu, this));

	if (this.arConfig.bSaveOnBlur)
		BX.bind(document, "mousedown", BX.proxy(this.OnDocMousedown, this));

	if (this.arConfig.ctrlEnterHandler && typeof window[this.arConfig.ctrlEnterHandler] == 'function')
		this.ctrlEnterHandler = window[this.arConfig.ctrlEnterHandler];

	// Android < 4.x
	if (BX.browser.IsAndroid() && /Android\s[1-3].[0-9]/i.test(navigator.userAgent))
	{
		this.arConfig.bSetDefaultCodeView = true;
	}

	if (this.arConfig.bSetDefaultCodeView)
	{
		if (this.sourseBut)
			this.sourseBut.oBut.handler(this.sourseBut);
		else
			this.SetView('code');
	}

	BX.ready(function(){
		if (_this.pFrame.offsetWidth == 0 && _this.pFrame.offsetWidth == 0)
		{
			_this.onShowInterval = setInterval(function(){
				if (_this.pFrame.offsetWidth != 0 && _this.pFrame.offsetWidth != 0)
				{
					BX.onCustomEvent(_this, 'onShow');
					clearInterval(_this.onShowInterval);
				}
			}, 500);
		}
		else
		{
			BX.onCustomEvent(_this, 'onShow');
		}
	});

	this.adjustBodyInterval = 1000;
	this._AdjustBodyWidth();
	BX.removeClass(this.pButtonsCont, "lhe-stat-toolbar-cont-preload"); /**/
},

CreateFrame: function()
{
	if (this.iFrame && this.iFrame.parentNode)
	{
		this.pEditCont.removeChild(this.iFrame);
		this.iFrame = null;
	}

	this.iFrame = this.pEditCont.appendChild(BX.create("IFRAME", {props: { id: 'LHE_iframe_' + this.id, className: 'lha-iframe', src: "javascript:void(0)", frameborder: 0}}));

	if (this.iFrame.contentDocument && !BX.browser.IsIE())
		this.pEditorDocument = this.iFrame.contentDocument;
	else
		this.pEditorDocument = this.iFrame.contentWindow.document;
	this.pEditorWindow = this.iFrame.contentWindow;
},

ReInit: function(content)
{
	if (typeof content == 'undefined')
		content = '';
	this.SetContent(content);
	this.CreateFrame();
	this.SetEditorContent(this.content);
	this.SetFocus();

	BX.onCustomEvent(window, 'LHE_OnInit', [this, true]);
},

SetConstants: function()
{
	//this.reBlockElements = /^(BR|TITLE|TABLE|SCRIPT|TR|TBODY|P|DIV|H1|H2|H3|H4|H5|H6|ADDRESS|PRE|OL|UL|LI)$/i;
	this.reBlockElements = /^(TITLE|TABLE|SCRIPT|TR|TBODY|H1|H2|H3|H4|H5|H6|ADDRESS|PRE|OL|UL|LI)$/i;
	this.oneGif = this.arConfig.oneGif;
	this.imagePath = this.arConfig.imagePath;

	if (!this.arConfig.fontFamily)
	{
		const editorContainer = BX('bxlhe_frame_' + this.id);
		const styleContainer = editorContainer ? editorContainer : document.body;
		const primaryFont = BX.Dom.style(styleContainer, '--ui-font-family-primary');
		const fallbackFont = BX.Dom.style(styleContainer, '--ui-font-family-helvetica');

		const currentFont = BX.Type.isStringFilled(primaryFont) ? primaryFont : fallbackFont;
		if (BX.Type.isStringFilled(currentFont))
		{
			this.arConfig.fontFamily = currentFont;
		}
	}

	if (!this.arConfig.fontSize)
		this.arConfig.fontSize = '14px';
	if (!this.arConfig.lineHeight)
		this.arConfig.lineHeight = '16px';

	this.arColors = [
		'#FF0000', '#FFFF00', '#00FF00', '#00FFFF', '#0000FF', '#FF00FF', '#FFFFFF', '#EBEBEB', '#E1E1E1', '#D7D7D7', '#CCCCCC', '#C2C2C2', '#B7B7B7', '#ACACAC', '#A0A0A0', '#959595',
		'#EE1D24', '#FFF100', '#00A650', '#00AEEF', '#2F3192', '#ED008C', '#898989', '#7D7D7D', '#707070', '#626262', '#555', '#464646', '#363636', '#262626', '#111', '#000000',
		'#F7977A', '#FBAD82', '#FDC68C', '#FFF799', '#C6DF9C', '#A4D49D', '#81CA9D', '#7BCDC9', '#6CCFF7', '#7CA6D8', '#8293CA', '#8881BE', '#A286BD', '#BC8CBF', '#F49BC1', '#F5999D',
		'#F16C4D', '#F68E54', '#FBAF5A', '#FFF467', '#ACD372', '#7DC473', '#39B778', '#16BCB4', '#00BFF3', '#438CCB', '#5573B7', '#5E5CA7', '#855FA8', '#A763A9', '#EF6EA8', '#F16D7E',
		'#EE1D24', '#F16522', '#F7941D', '#FFF100', '#8FC63D', '#37B44A', '#00A650', '#00A99E', '#00AEEF', '#0072BC', '#0054A5', '#2F3192', '#652C91', '#91278F', '#ED008C', '#EE105A',
		'#9D0A0F', '#A1410D', '#A36209', '#ABA000', '#588528', '#197B30', '#007236', '#00736A', '#0076A4', '#004A80', '#003370', '#1D1363', '#450E61', '#62055F', '#9E005C', '#9D0039',
		'#790000', '#7B3000', '#7C4900', '#827A00', '#3E6617', '#045F20', '#005824', '#005951', '#005B7E', '#003562', '#002056', '#0C004B', '#30004A', '#4B0048', '#7A0045', '#7A0026'
	];

	this.systemCSS = "img.bxed-anchor{background-image: url(" + this.imagePath + "lhe_iconkit.gif)!important; background-position: -260px 0!important; height: 20px!important; width: 20px!important;}\n" +
		"body{font-family:" + this.arConfig.fontFamily + "; font-size: " + this.arConfig.fontSize + "; line-height:" + this.arConfig.lineHeight + "; color: #151515;}\n" +
		"p{padding:0!important; margin: 0!important;}\n" +
		"span.bxed-noscript{color: #0000a0!important; padding: 2px!important; font-style:italic!important; font-size: 90%!important;}\n" +
		"span.bxed-noindex{color: #004000!important; padding: 2px!important; font-style:italic!important; font-size: 90%!important;}\n" +
		"img.bxed-flash{border: 1px solid #B6B6B8!important; background: url(" + this.imagePath + "flash.gif) #E2DFDA center center no-repeat !important;}\n" +
		"table{border: 1px solid #B6B6B8!important; border-collapse: collapse;}\n" +
		"table td{border: 1px solid #B6B6B8!important; padding: 2px 5px;}\n" +
		"img.bxed-video{border: 1px solid #B6B6B8!important; background-color: #E2DFDA!important; background-image: url(" + this.imagePath + "video.gif); background-position: center center!important; background-repeat:no-repeat!important;}\n" +
		"img.bxed-hr{padding: 2px!important; width: 100%!important; height: 2px!important;}\n";

	if (this.arConfig.documentCSS)
		this.systemCSS += "\n" + this.arConfig.documentCSS;

	this.tabNbsp = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"; // &nbsp; x 6
	this.tabNbspRe1 = new RegExp(String.fromCharCode(160) + String.fromCharCode(160) + String.fromCharCode(160) + String.fromCharCode(160) + String.fromCharCode(160) + String.fromCharCode(160), 'ig'); //
	this.tabNbspRe2 = new RegExp(String.fromCharCode(160) + String.fromCharCode(160) + String.fromCharCode(160) + String.fromCharCode(160) + String.fromCharCode(160) + ' ', 'ig'); //
},

OnMousedown: function(e)
{
	if (!e)
		e = window.event;
	this.bFocused = true;
},

OnClick: function(e)
{
	this.bFocused = true;
	this.CheckBr();
},

OnDblClick: function(e)
{
	return;
},

OnContextMenu: function(e, pElement)
{
	return;
	var
		_this = this,
		oFramePos,
		x, y;
	if (!e) e = this.pEditorWindow.event;

	if(e.pageX || e.pageY)
	{
		x = e.pageX - this.pEditorDocument.body.scrollLeft;
		y = e.pageY - this.pEditorDocument.body.scrollTop;
	}
	else if(e.clientX || e.clientY)
	{
		x = e.clientX;
		y = e.clientY;
	}

	oFramePos = this.CACHE['frame_pos'];
	if (!oFramePos)
		this.CACHE['frame_pos'] = oFramePos = BX.pos(this.pEditCont);

	x += oFramePos.left;
	y += oFramePos.top;

	var targ;
	if (e.target)
		targ = e.target;
	else if (e.srcElement)
		targ = e.srcElement;
	if (targ.nodeType == 3) // defeat Safari bug
		targ = targ.parentNode;

	if (!targ || !targ.nodeName)
		return;
	var res = this.oContextMenu.Show({oPos: {left : x, top : y}, pElement: targ});

	return BX.PreventDefault(e);
},

OnKeyDown: function(e)
{
	if(!e)
		e = window.event;
	BX.onCustomEvent(this, 'OnDocumentKeyDown', [e]);

	var key = e.which || e.keyCode;
	if (e.ctrlKey && !e.shiftKey && !e.altKey)
	{
		// if (!BX.browser.IsIE() && !BX.browser.IsOpera())
		// {
		switch (key)
		{
			case 66 : // B
			case 98 : // b
				this.executeCommand('Bold');
				return BX.PreventDefault(e);
			case 105 : // i
			case 73 : // I
				this.executeCommand('Italic');
				return BX.PreventDefault(e);
			case 117 : // u
			case 85 : // U
				this.executeCommand('Underline');
				return BX.PreventDefault(e);
			case 81 : // Q - quote
				if (this.quoteBut)
				{
					this.quoteBut.oBut.handler(this.quoteBut);
					return BX.PreventDefault(e);
				}
		}
		//}
	}

	if (this.bHandleOnPaste
		&&
		(
			(e.ctrlKey && !e.shiftKey && !e.altKey && e.keyCode == 86) /* Ctrl+V */
				||
				(!e.ctrlKey && e.shiftKey && !e.altKey && e.keyCode == 45) /*Shift+Ins*/
				||
				(e.metaKey && !e.shiftKey && !e.altKey && e.keyCode == 86) /* Cmd+V */
			)
		)
	{
		this.OnPaste();
	}

	// Shift +Del - Deleting code fragment in WYSIWYG
	if (this.bCodeBut && e.shiftKey && e.keyCode == 46 /* Del*/)
	{
		var pSel = this.GetSelectionObject();
		if (pSel)
		{
			if (pSel.className == 'lhe-code')
			{
				pSel.parentNode.removeChild(pSel);
				return BX.PreventDefault(e);
			}
			else if(pSel.parentNode)
			{
				var pCode = BX.findParent(pSel, {className: 'lhe-code'});
				if (pCode)
				{
					pCode.parentNode.removeChild(pCode);
					return BX.PreventDefault(e);
				}
			}
		}
	}

	// Tab
	if (key == 9 && this.arConfig.bReplaceTabToNbsp)
	{
		this.InsertHTML(this.tabNbsp);
		return BX.PreventDefault(e);
	}

	if (this.bCodeBut && e.keyCode == 13)
	{
		if (BX.browser.IsIE() || BX.browser.IsSafari() || BX.browser.IsChrome())
		{
			var pElement = this.GetSelectionObject();
			if (pElement)
			{
				var bFind = false;
				if (pElement && pElement.nodeName && pElement.nodeName.toLowerCase() == 'pre')
					bFind = true;

				if (!bFind)
					bFind = !!BX.findParent(pElement, {tagName: 'pre'});

				if (bFind)
				{
					if (BX.browser.IsIE())
						this.InsertHTML("<br/><img src=\"" + this.oneGif + "\" height=\"20\" width=\"1\"/>");
					else if (BX.browser.IsSafari() || BX.browser.IsChrome())
						this.InsertHTML(" \r\n");

					return BX.PreventDefault(e);
				}
			}
		}
	}

	// Ctrl + Enter
	if ((e.keyCode == 13 || e.keyCode == 10) && e.ctrlKey && this.ctrlEnterHandler)
	{
		this.SaveContent();
		this.ctrlEnterHandler();
	}

	if (this.arConfig.bAutoResize && this.arConfig.bResizable)
	{
		if (this._resizeTimeout)
		{
			clearTimeout(this._resizeTimeout);
			this._resizeTimeout = null;
		}

		this._resizeTimeout = setTimeout(BX.proxy(this.AutoResize, this), 200);
	}

	if (this._CheckBrTimeout)
	{
		clearTimeout(this._CheckBrTimeout);
		this._CheckBrTimeout = null;
	}

	this._CheckBrTimeout = setTimeout(BX.proxy(this.CheckBr, this), 1000);
},

OnDocMousedown: function(e)
{
	if (this.bFocused)
	{
		if (!e)
			e = window.event;

		var pEl;
		if (e.target)
			pEl = e.target;
		else if (e.srcElement)
			pEl = e.srcElement;
		if (pEl.nodeType == 3)
			pEl = pEl.parentNode;

		if (!this.bPopup && !BX.findParent(pEl, {className: 'bxlhe-frame'}))
		{
			this.SaveContent();
			this.bFocused = false;
		}
	}
},

SetView: function(sType)
{
	if (this.sEditorMode == sType)
		return;

	this.SaveContent();
	if (sType == 'code')
	{
		this.iFrame.style.display = "none";
		this.pSourceDiv.style.display = "block";
		this.SetCodeEditorContent(this.GetContent());
	}
	else
	{
		this.iFrame.style.display = "block";
		this.pSourceDiv.style.display = "none";
		this.SetEditorContent(this.GetContent());
		this.CheckBr();
	}
	this.sEditorMode = sType;
	BX.onCustomEvent(this, "OnChangeView");
},

SaveContent: function()
{
	var sContent = this.sEditorMode == 'code' ? this.GetCodeEditorContent() : this.GetEditorContent();
	if (this.bBBCode)
		sContent = this.OptimizeBB(sContent);

	this.SetContent(sContent);

	BX.onCustomEvent(this, 'OnSaveContent', [sContent]);
},

SetContent: function(sContent)
{
	this.pHiddenInput.value = this.pTextarea.value = this.content = sContent;
},

GetContent: function()
{
	return this.content.toString();
},

SetEditorContent: function(sContent)
{
	if (this.pEditorDocument)
	{
		sContent = this.ParseContent(sContent);

		if (this.pEditorDocument.designMode)
		{
			try{
				this.pEditorDocument.designMode = 'off';
			}catch(e){alert('SetEditorContent: designMode=\'off\'');}
		}

		this.pEditorDocument.open();
		this.pEditorDocument.write('<html><head></head><body>' + sContent + '</body></html>');
		this.pEditorDocument.close();

		this.pEditorDocument.body.style.padding = "8px";
		this.pEditorDocument.body.style.margin = "0";
		this.pEditorDocument.body.style.borderWidth = "0";

		this.pEditorDocument.body.style.fontFamily = this.arConfig.fontFamily;
		this.pEditorDocument.body.style.fontSize = this.arConfig.fontSize;
		this.pEditorDocument.body.style.lineHeight = this.arConfig.lineHeight;

		// Set events
		BX.bind(this.pEditorDocument, 'keydown', BX.proxy(this.OnKeyDown, this));

		if(BX.browser.IsIE())
		{
			if (this.bHandleOnPaste)
				BX.bind(this.pEditorDocument.body, 'paste', BX.proxy(this.OnPaste, this));
			this.pEditorDocument.body.contentEditable = true;
		}
		else if (this.pEditorDocument.designMode)
		{
			this.pEditorDocument.designMode = "on";
			this._TurnOffStyleWithCSS(true);
		}

		if (this.arConfig.bConvertContentFromBBCodes)
			this.ShutdownBBCode();
	}
},

_TurnOffStyleWithCSS: function(bTimeout)
{
	try{
		this._turnOffCssCount++;
		if (this._turnOffCssCount < 5 && bTimeout !== false)
			bTimeout = true;

		this.pEditorDocument.execCommand("styleWithCSS", false, false);
		try{this.pEditorDocument.execCommand("useCSS", false, true);}catch(e){}
	}
	catch(e)
	{
		if (bTimeout === true)
			setTimeout(BX.proxy(this._TurnOffStyleWithCSS, this), 500);
	}
},

_AdjustBodyWidth: function()
{
	if (!BX.browser.IsChrome())
	{
		if (this.pEditorDocument && this.pEditorDocument.body)
		{
			var html = this.pEditorDocument.body.innerHTML;
			if (html != this.lastEditedBodyHtml)
			{
				this.adjustBodyInterval = 500;
				var _this = this;
				this.pEditorDocument.body.style.width = null;
				this.lastEditedBodyHtml = html;
				setTimeout(function(){
					var scrollWidth = BX.GetWindowScrollSize(_this.pEditorDocument).scrollWidth - 16;
					if (scrollWidth > 0)
						_this.pEditorDocument.body.style.width = scrollWidth + 'px';
				}, 50);
			}
			else
			{
				this.adjustBodyInterval = 5000;
			}
		}

		setTimeout(BX.proxy(this._AdjustBodyWidth, this), this.adjustBodyInterval)
	}
},

GetEditorContent: function()
{
	var sContent = this.UnParseContent();
	return sContent;
},

SetCodeEditorContent: function(sContent)
{
	this.pHiddenInput.value = this.pTextarea.value = sContent;
},

GetCodeEditorContent: function()
{
	return this.pTextarea.value;
},

OptimizeHTML: function(str)
{
	var
		iter = 0,
		bReplasing = true,
		arTags = ['b', 'em', 'font', 'h\\d', 'i', 'li', 'ol', 'p', 'small', 'span', 'strong', 'u', 'ul'],
		replaceEmptyTags = function(){i--; bReplasing = true; return ' ';},
		re, tagName, i, l;

	while(iter++ < 20 && bReplasing)
	{
		bReplasing = false;
		for (i = 0, l = arTags.length; i < l; i++)
		{
			tagName = arTags[i];
			re = new RegExp('<'+tagName+'[^>]*?>\\s*?</'+tagName+'>', 'ig');
			str = str.replace(re, replaceEmptyTags);

			re = new RegExp('<' + tagName + '\\s+?[^>]*?/>', 'ig');
			str = str.replace(re, replaceEmptyTags);

			// Replace <b>text1</b>    <b>text2</b> ===>>  <b>text1 text2</b>
			re = new RegExp('<((' + tagName + '+?)(?:\\s+?[^>]*?)?)>([\\s\\S]+?)<\\/\\2>\\s*?<\\1>([\\s\\S]+?)<\\/\\2>', 'ig');
			str = str.replace(re, function(str, b1, b2, b3, b4)
				{
					bReplasing = true;
					return '<' + b1 + '>' + b3 + ' ' + b4 + '</' + b2 + '>';
				}
			);
		}
	}
	return str;
},

_RecursiveDomWalker: function(pNode, pParentNode)
{
	var oNode =
	{
		arAttributes : {},
		arNodes : [],
		type : null,
		text : "",
		arStyle : {}
	};

	switch(pNode.nodeType)
	{
		case 9:
			oNode.type = 'document';
			break;
		case 1:
			if(pNode.tagName.length <= 0 || pNode.tagName.substring(0, 1) == "/")
				return;

			oNode.text = pNode.tagName.toLowerCase();
			if (oNode.text == 'script')
				break;

			oNode.type = 'element';
			var
				attr = pNode.attributes,
				j, l = attr.length;

			if (pNode.nodeName.toLowerCase() == 'a' && pNode.innerHTML == '' && (this.bBBCode || !pNode.getAttribute("name")))
				return;

			for(j = 0; j < l; j++)
			{
				if(attr[j].specified || (oNode.text == "input" && attr[j].nodeName.toLowerCase()=="value"))
				{
					var attrName = attr[j].nodeName.toLowerCase();

					if(attrName == "style")
					{
						oNode.arAttributes[attrName] = pNode.style.cssText;
						oNode.arStyle = pNode.style;

						if(oNode.arStyle.display == 'none')
						{
							oNode.type = 'text';
							oNode.text = '';
							break;
						}

						if(oNode.arStyle.textAlign && (oNode.text == 'div' || oNode.text == 'p' || oNode.text == 'span'))
						{
							var align = oNode.arStyle.textAlign;
							BX.util.in_array(oNode.arStyle.textAlign, ['left', 'right', 'center', 'justify'])
							{
								oNode.arStyle = {};
								oNode.text = 'span';
								oNode.arAttributes['style'] = 'text-align:' + align + ';display:block;';
								oNode.arStyle.textAlign = align;
								oNode.arStyle.display = 'block';
							}
						}
					}
					else if(attrName=="src" || attrName=="href"  || attrName=="width"  || attrName=="height")
					{
						oNode.arAttributes[attrName] = pNode.getAttribute(attrName, 2);
					}
					else if(!this.bBBCode && attrName == 'align' && BX.util.in_array(attr[j].nodeValue, ['left', 'right', 'center', 'justify']))
					{
						oNode.text = 'span';
						oNode.arAttributes['style'] = 'text-align:' + attr[j].nodeValue + ';display:block;';
						oNode.arStyle.textAlign = attr[j].nodeValue;
						oNode.arStyle.display = 'block';
					}
					else
					{
						oNode.arAttributes[attrName] = attr[j].nodeValue;
					}
				}
			}
			break;
		case 3:
			oNode.type = 'text';
			var res = pNode.nodeValue;

			if (this.arConfig.bReplaceTabToNbsp)
			{
				res = res.replace(this.tabNbspRe1, "\t");
				res = res.replace(this.tabNbspRe2, "\t");
			}

			if(!pParentNode || (pParentNode.text != 'pre' && pParentNode.arAttributes['class'] != 'lhe-code'))
			{
				res = res.replace(/\n+/g, ' ');
				res = res.replace(/ +/g, ' ');
			}

			oNode.text = res;
			break;
	}

	if (oNode.type != 'text')
	{
		var
			arChilds = pNode.childNodes,
			i, l = arChilds.length;

		for(i = 0; i < l; i++)
			oNode.arNodes.push(this._RecursiveDomWalker(arChilds[i], oNode));
	}

	return oNode;
},

_RecursiveGetHTML: function(pNode)
{
	if (!pNode || typeof pNode != 'object' || !pNode.arAttributes)
		return "";

	var ob, res = "", id = pNode.arAttributes["id"];

	if (pNode.text == 'img' && !id) // Images pasted by Ctrl+V
		id = this.SetBxTag(false, {tag: 'img', params: {src: pNode.arAttributes["src"]}});

	if (id)
	{
		var bxTag = this.GetBxTag(id);
		if(bxTag.tag)
		{
			var parser = this.oSpecialParsers[bxTag.tag];
			if (parser && parser.UnParse)
				return parser.UnParse(bxTag, pNode, this);
			else if (bxTag.params && bxTag.params.value)
				return '\n' + bxTag.params.value + '\n';
			else
				return '';
		}
	}

	if (pNode.arAttributes["_moz_editor_bogus_node"])
		return '';

	if (this.bBBCode)
	{
		var bbRes = this.UnParseNodeBB(pNode);
		if (bbRes !== false)
			return bbRes;
	}

	bFormatted = true;

	if (pNode.text.toLowerCase() != 'body')
		res = this.GetNodeHTMLLeft(pNode);

	var bNewLine = false;

	var sIndent = '';
	if (typeof pNode.bFormatted != 'undefined')
		bFormatted = !!pNode.bFormatted;

	if (bFormatted && pNode.type != 'text')
	{
		if (this.reBlockElements.test(pNode.text) && !(pNode.oParent && pNode.oParent.text && pNode.oParent.text.toLowerCase() == 'pre'))
		{
			for (var j = 0; j < pNode.iLevel - 3; j++)
				sIndent += "  ";
			bNewLine = true;
			res = "\r\n" + sIndent + res;
		}
	}

	for (var i = 0; i < pNode.arNodes.length; i++)
		res += this._RecursiveGetHTML(pNode.arNodes[i]);

	if (pNode.text.toLowerCase() != 'body')
		res += this.GetNodeHTMLRight(pNode);

	if (bNewLine)
		res += "\r\n" + (sIndent == '' ? '' : sIndent.substr(2));

	return res;
},

// Redeclared in BBCode mode
GetNodeHTMLLeft: function(pNode)
{
	if(pNode.type == 'text')
		return BX.util.htmlspecialchars(pNode.text);

	var atrVal, attrName, res;

	if(pNode.type == 'element')
	{
		res = "<" + pNode.text;

		for(attrName in pNode.arAttributes)
		{
			atrVal = pNode.arAttributes[attrName];
			if(attrName.substring(0,4).toLowerCase() == '_moz')
				continue;

			if(pNode.text.toUpperCase()=='BR' && attrName.toLowerCase() == 'type' && atrVal == '_moz')
				continue;

			if(attrName == 'style')
			{
				if (atrVal.length > 0 && atrVal.indexOf('-moz') != -1) // Kill -moz* styles from firefox
					atrVal = BX.util.trim(atrVal.replace(/-moz.*?;/ig, ''));

				if (pNode.text == 'td') // Kill border-image: none; styles from firefox for <td>
					atrVal = BX.util.trim(atrVal.replace(/border-image:\s*none;/ig, ''));

				if(atrVal.length <= 0)
					continue;
			}

			res += ' ' + attrName + '="' + (pNode.bDontUseSpecialchars ? atrVal : BX.util.htmlspecialchars(atrVal)) + '"';
		}

		if(pNode.arNodes.length <= 0 && !this.IsPairNode(pNode.text))
			return res + " />";
		return res + ">";
	}
	return "";
},

// Redeclared in BBCode mode
GetNodeHTMLRight: function(pNode)
{
	if(pNode.type == 'element' && (pNode.arNodes.length>0 || this.IsPairNode(pNode.text)))
		return "</" + pNode.text + ">";
	return "";
},

IsPairNode: function(text)
{
	if(text.substr(0, 1) == 'h' || text == 'br' || text == 'img' || text == 'input')
		return false;
	return true;
},

executeCommand: function(commandName, sValue)
{
	this.SetFocus();
	//try{
	var res = this.pEditorWindow.document.execCommand(commandName, false, sValue);
	//}catch(e){};
	this.SetFocus();
	//this.OnEvent("OnSelectionChange");
	//this.OnChange("executeCommand", commandName);

	if (this.arConfig.bAutoResize && this.arConfig.bResizable)
		this.AutoResize();

	return res;
},

queryCommand: function(commandName)
{
	var sValue = '';
	if (!this.pEditorDocument.queryCommandEnabled || !this.pEditorDocument.queryCommandValue)
		return null;

	if(!this.pEditorDocument.queryCommandEnabled(commandName))
		return null;

	return this.pEditorDocument.queryCommandValue(commandName);
},

SetFocus: function()
{
	if (this.sEditorMode != 'html')
		return;

	BX.focus(this.pEditorWindow.focus ? this.pEditorWindow : this.pEditorDocument.body);
	this.bFocused = true;
},

SetFocusToEnd: function()
{
	this.CheckBr();
	var ss = BX.GetWindowScrollSize(this.pEditorDocument);
	this.pEditorWindow.scrollTo(0, ss.scrollHeight);

	this.SetFocus();
	this.SelectElement(this.pEditorDocument.body.lastChild);
},

SetCursorFF: function()
{
	if (this.sEditorMode != 'code' && !BX.browser.IsIE())
	{
		var _this = this;
		try{
			this.iFrame.blur();
			this.iFrame.focus();

			setTimeout(function(){
				_this.iFrame.blur();
				_this.iFrame.focus();
			}, 600);

			setTimeout(function(){
				_this.iFrame.blur();
				_this.iFrame.focus();
			}, 1000);
		}catch(e){}
	}
},

CheckBr: function()
{
	if (this.CheckBrTimeout)
	{
		clearTimeout(this.CheckBrTimeout);
		this.CheckBrTimeout = false;
	}

	var _this = this;
	this.CheckBrTimeout = setTimeout(function()
	{
		var lastChild = _this.pEditorDocument.body.lastChild;
		if (lastChild && lastChild.nodeType == 1)
		{
			var nn = lastChild.nodeName.toUpperCase();
			var reBlockElements = /^(TITLE|TABLE|SCRIPT|DIV|H1|H2|H3|H4|H5|H6|ADDRESS|PRE|OL|UL|LI|BLOCKQUOTE|FORM|CENTER|)$/i;
			if (reBlockElements.test(nn))
				_this.pEditorDocument.body.appendChild(_this.pEditorDocument.createElement("BR"));
		}
	}, 200);
},

ParseContent: function(sContent, bJustParse) // HTML -> WYSIWYG
{
	var _this = this;
	var arCodes = [];
	sContent = sContent.replace(/\[code\]((?:\s|\S)*?)\[\/code\]/ig, function(str, code)
	{
		var strId = '';
		if (!_this.bBBCode)
			strId = " id=\"" + _this.SetBxTag(false, {tag: "code"}) + "\" ";

		arCodes.push('<pre ' + strId + 'class="lhe-code" title="' + BX.message.CodeDel + '">' + BX.util.htmlspecialchars(code) + '</pre>');
		return '#BX_CODE' + (arCodes.length - 1) + '#';
	});

	if (!bJustParse)
		BX.onCustomEvent(this, 'OnParseContent');

	if (this.arConfig.bBBCode)
		sContent = this.ParseBB(sContent);

	sContent = sContent.replace(/(<td[^>]*>)\s*(<\/td>)/ig, "$1<br _moz_editor_bogus_node=\"on\">$2");

	if (this.arConfig.bReplaceTabToNbsp)
		sContent = sContent.replace(/\t/ig, this.tabNbsp);

	if (!BX.browser.IsIE())
	{
		sContent = sContent.replace(/<hr[^>]*>/ig, function(sContent)
			{
				return '<img class="bxed-hr" src="' + _this.imagePath + 'break_page.gif" id="' + _this.SetBxTag(false, {tag: "hr", params: {value : sContent}}) + '"/>';
			}
		);
	}

	for (var p in this.oSpecialParsers)
	{
		if (this.oSpecialParsers[p] && this.oSpecialParsers[p].Parse)
			sContent = this.oSpecialParsers[p].Parse(p, sContent, this);
	}

	if (!bJustParse)
		setTimeout(function(){
			_this.AppendCSS(_this.systemCSS);
			// Hack for chrome: we have to unset font family
			// because than user paste text - chrome wraps it with [FONT=.....
			setTimeout(function(){
				_this.pEditorDocument.body.style.fontFamily = '';
				_this.pEditorDocument.body.style.fontSize = '';
			}, 1);
		}, 300);

	if (arCodes.length > 0) // Replace back CODE content without modifications
		sContent = sContent.replace(/#BX_CODE(\d+)#/ig, function(s, num){return arCodes[num] || s;});

	if (this.bBBCode)
	{
		sContent = sContent.replace(/&amp;#91;/ig, "[");
		sContent = sContent.replace(/&amp;#93;/ig, "]");
	}

	sContent = BX.util.trim(sContent);

	// Add <br> in the end of the message if text not ends with <br>
	if (this.arConfig.bBBCode && !sContent.match(/(<br[^>]*>)$/ig))
		sContent += '<br/>';

	return sContent;
},

UnParseContent: function() // WYSIWYG - > html
{
	BX.onCustomEvent(this, 'OnUnParseContent');

	var sContent = this._RecursiveGetHTML(this._RecursiveDomWalker(this.pEditorDocument.body, false));

	if (this.bBBCode)
	{
		if (!BX.browser.IsIE())
			sContent = sContent.replace(/\r/ig, '');
		sContent = sContent.replace(/\n/ig, '');
	}

	var arDivRules = [
		['#BR#(#TAG_BEGIN#)', "$1"], // 111<br><div>... => 111<>
		['(#TAG_BEGIN#)(?:#BR#)*?(#TAG_END#)', "$1$2"], // [DIV]#BR#[/DIV]  ==> [DIV][/DIV]
		['(#TAG_BEGIN#)([\\s\\S]*?)#TAG_END#(?:\\n|\\r|\\s)*?#TAG_BEGIN#([\\s\\S]*?)(#TAG_END#)', function(str, s1, s2,s3,s4){return s1 + s2 + '#BR#' + s3 + s4;}, true],
		['^#TAG_BEGIN#', ""], //kill [DIV] in the begining of the text
		['([\\s\\S]*?(\\[\\/\\w+\\])*?)#TAG_BEGIN#([\\s\\S]*?)#TAG_END#([\\s\\S]*?)', function(str, s1, s2,s3,s4)
		{
			if (s2 && s2.toLowerCase && s2.toLowerCase() == '[/list]')
				return s1 + s3 + '#BR#' + s4;
			return s1 + '#BR#' + s3 + '#BR#' + s4;
		}, true], // [/list][DIV]wwww[/div]wwww => [/list]wwww#BR#wwwww, text[DIV]wwww[/div]wwww => text#BR#www#BR#
		['#TAG_END#', "#BR#"] // [/DIV] ==> \n
	];

	var re, i, l = arDivRules.length, str;
	if (this.bBBCode)
	{
		//
		if (BX.browser.IsOpera())
			sContent = sContent.replace(/(?:#BR#)*?\[\/P\]/ig, "[/P]"); // #BR#[/P]  ==> [/P] for opera

		for (i = 0; i < l; i++)
		{
			re = arDivRules[i][0];
			re = re.replace(/#TAG_BEGIN#/g, '\\[P\\]');
			re = re.replace(/#TAG_END#/g, '\\[\\/P\\]');
			re = re.replace(/\\\\/ig, '\\\\');
			re = new RegExp(re, 'igm');
			if (arDivRules[i][2] === true)
				while(true)
				{
					str = sContent.replace(re, arDivRules[i][1]);
					if (str == sContent)
						break;
					else
						sContent = str;
				}
			else
				sContent = sContent.replace(re, arDivRules[i][1]);
		}
		sContent = sContent.replace(/^((?:\s|\S)*?)(?:\n|\r|\s)+$/ig, "$1\n\n"); //kill multiple \n in the end

		// Handle  [DIV] tags from safari, chrome
		for (i = 0; i < l; i++)
		{
			re = arDivRules[i][0];
			re = re.replace(/#TAG_BEGIN#/g, '\\[DIV\\]');
			re = re.replace(/#TAG_END#/g, '\\[\\/DIV\\]');
			re = re.replace(/\\\\/ig, '\\\\');

			if (arDivRules[i][2] === true)
				while(true)
				{
					str = sContent.replace(new RegExp(re, 'igm'), arDivRules[i][1]);
					if (str == sContent)
						break;
					else
						sContent = str;
				}
			else
				sContent = sContent.replace(new RegExp(re, 'igm'), arDivRules[i][1]);
		}

		sContent = sContent.replace(/#BR#/ig, "\n");
		sContent = sContent.replace(/\[DIV]/ig, "");
		sContent = BX.util.htmlspecialcharsback(sContent);
	}

	this.__sContent = sContent;
	BX.onCustomEvent(this, 'OnUnParseContentAfter');
	sContent = this.__sContent;
	return sContent;
},

InitResizer: function()
{
	this.oTransOverlay.Show();

	var
		_this = this,
		coreContPos = BX.pos(this.pFrame),
		newHeight = false;

	var MouseMove = function(e)
	{
		e = e || window.event;
		BX.fixEventPageY(e);
		newHeight = e.pageY - coreContPos.top;

		// New height
		if (newHeight < _this.arConfig.minHeight)
		{
			newHeight = _this.arConfig.minHeight;
			document.body.style.cursor = "not-allowed";
		}
		else if (newHeight > _this.arConfig.maxHeight)
		{
			newHeight = _this.arConfig.maxHeight;
			document.body.style.cursor = "not-allowed";
		}
		else
		{
			document.body.style.cursor = "n-resize";
		}

		_this.pFrame.style.height = newHeight + "px";
		_this.ResizeFrame(newHeight);
	};

	var MouseUp = function(e)
	{
		if (_this.arConfig.autoResizeSaveSize)
			BX.userOptions.save('fileman', 'LHESize_' + _this.id, 'height', newHeight);
		_this.arConfig.height = newHeight;

		document.body.style.cursor = "";
		if (_this.oTransOverlay && _this.oTransOverlay.bShowed)
			_this.oTransOverlay.Hide();

		BX.unbind(document, "mousemove", MouseMove);
		BX.unbind(document, "mouseup", MouseUp);
	};

	BX.bind(document, "mousemove", MouseMove);
	BX.bind(document, "mouseup", MouseUp);
},

AutoResize: function()
{
	var
		heightOffset = parseInt(this.arConfig.autoResizeOffset || 80),
		maxHeight = parseInt(this.arConfig.autoResizeMaxHeight || 0),
		minHeight = parseInt(this.arConfig.autoResizeMinHeight || 50),
		newHeight,
		_this = this;

	if (this.autoResizeTimeout)
		clearTimeout(this.autoResizeTimeout);

	this.autoResizeTimeout = setTimeout(function()
	{
		if (_this.sEditorMode == 'html')
		{
			//newHeight = _this.pEditorDocument.body.offsetHeight + heightOffset;
			newHeight = _this.pEditorDocument.body.offsetHeight;
			var
				body = _this.pEditorDocument.body,
				node = body.lastChild,
				offsetTop = false, i;

			while (true)
			{
				if (!node)
					break;
				if (node.offsetTop)
				{
					offsetTop = node.offsetTop + (node.offsetHeight || 0);
					newHeight = offsetTop + heightOffset;
					break;
				}
				else
				{
					node = node.previousSibling;
				}
			}

			var oEdSize = BX.GetWindowSize(_this.pEditorDocument);
			if (oEdSize.scrollHeight - oEdSize.innerHeight > 5)
				newHeight = Math.max(oEdSize.scrollHeight + heightOffset, newHeight);
		}
		else
		{
			newHeight = (_this.pTextarea.value.split("\n").length /* rows count*/ + 5) * 17;
		}

		if (newHeight > parseInt(_this.arConfig.height))
		{
			if (BX.browser.IsIOS())
				maxHeight = Infinity;
			else if (!maxHeight || maxHeight < 10)
				maxHeight = Math.round(BX.GetWindowInnerSize().innerHeight * 0.9); // 90% from screen height

			newHeight = Math.min(newHeight, maxHeight);
			newHeight = Math.max(newHeight, minHeight);

			_this.SmoothResizeFrame(newHeight);
		}
	}, 300);
},

MousePos: function (e)
{
	if(window.event)
		e = window.event;

	if(e.pageX || e.pageY)
	{
		e.realX = e.pageX;
		e.realY = e.pageY;
	}
	else if(e.clientX || e.clientY)
	{
		e.realX = e.clientX + (document.documentElement.scrollLeft || document.body.scrollLeft) - document.documentElement.clientLeft;
		e.realY = e.clientY + (document.documentElement.scrollTop || document.body.scrollTop) - document.documentElement.clientTop;
	}
	return e;
},

SmoothResizeFrame: function(height)
{
	var
		_this = this,
		curHeight = parseInt(this.pFrame.offsetHeight),
		count = 0,
		bRise = height > curHeight,
		timeInt = BX.browser.IsIE() ? 50 : 50,
		dy = 5;

	if (!bRise)
		return;

	if (this.smoothResizeInterval)
		clearInterval(this.smoothResizeInterval);

	this.smoothResizeInterval = setInterval(function()
		{
			if (bRise)
			{
				curHeight += Math.round(dy * count);
				if (curHeight > height)
				{
					clearInterval(_this.smoothResizeInterval);
					if (curHeight > height)
						curHeight = height;
				}
			}
			else
			{
				curHeight -= Math.round(dy * count);
				if (curHeight < height)
				{
					curHeight = height;
					clearInterval(_this.smoothResizeInterval);
				}
			}

			_this.pFrame.style.height = curHeight + "px";
			_this.ResizeFrame(curHeight);
			count++;
		},
		timeInt
	);
},

ResizeFrame: function(newHeight)
{
	var
		deltaWidth = 7,
		resizeHeight = this.arConfig.bManualResize ? 3 : 0, // resize row
		height = newHeight || parseInt(this.pFrame.offsetHeight),
		width = this.pFrame.offsetWidth;

	this.pFrameTable.style.height = height + 'px';
	var contHeight = height - this.buttonsHeight - resizeHeight;

	if (contHeight > 0)
	{
		this.pEditCont.style.height = contHeight + 'px';
		this.pTextarea.style.height = contHeight + 'px';
	}

	this.pTextarea.style.width = (width > deltaWidth) ? (width - deltaWidth) + 'px' : 'auto';
	this.pButtonsCell.style.height = this.buttonsHeight + 'px';

	/*if (this.arConfig.bResizable)
	 this.pResizer.parentNode.style.height = resizeHeight + 'px';*/
},

AddButtons: function()
{
	var
		i, l, butId, grInd, arButtons,
		toolbarConfig = this.arConfig.toolbarConfig;
	this.buttonsCount = 0;

	if(!toolbarConfig)
		toolbarConfig = [
			//'Source',
			'Bold', 'Italic', 'Underline', 'Strike', 'RemoveFormat', 'InsertHR',
			'Anchor',
			'CreateLink', 'DeleteLink', 'Image', //'SpecialChar',
			'Justify',
			'InsertOrderedList', 'InsertUnorderedList', 'Outdent', 'Indent',
			'BackColor', 'ForeColor',
			'Video',
			'StyleList', 'HeaderList',
			'FontList', 'FontSizeList',
			'Table'
			//smiles:['SmileList']
		];

	if (oBXLEditorUtils.oTune && oBXLEditorUtils.oTune[this.id])
	{
		var
			ripButtons = oBXLEditorUtils.oTune[this.id].ripButtons,
			addButtons = oBXLEditorUtils.oTune[this.id].buttons;

		if (ripButtons)
		{
			i = 0;
			while(i < toolbarConfig.length)
			{
				if (ripButtons[toolbarConfig[i]])
					toolbarConfig = BX.util.deleteFromArray(toolbarConfig, i);
				else
					i++;
			}
		}

		if (addButtons)
		{
			for (var j = 0, n = addButtons.length; j < n; j++)
			{
				if (addButtons[j].ind == -1 || addButtons[j].ind >= toolbarConfig.length)
					toolbarConfig.push(addButtons[j].but.id);
				else
					toolbarConfig = BX.util.insertIntoArray(toolbarConfig, addButtons[j].ind, addButtons[j].but.id);
			}
		}
	}

	var
		begWidth = 0,
		endWidth = 0, // 4
		curLineWidth = begWidth, pCont,
		butContWidth = parseInt(this.pButtonsCont.offsetWidth);

	this.ToolbarStartLine(true);
	for(i in toolbarConfig)
	{
		butId = toolbarConfig[i];
		if (typeof butId != 'string' || !toolbarConfig.hasOwnProperty(i))
			continue;

		if (butId == '=|=')
		{
			this.ToolbarNewLine();
			curLineWidth = begWidth;
		}
		else if (LHEButtons[butId])
		{
			if (this.bBBCode && LHEButtons[butId].bBBHide)
				continue;

			this.buttonsIndex[butId] = i;
			pCont = this.AddButton(LHEButtons[butId], butId);
			if (pCont)
			{
				curLineWidth += parseInt(pCont.style.width) || 23;
				if (curLineWidth + endWidth > butContWidth && butContWidth > 0)
				{
					butContWidth = parseInt(this.pButtonsCont.offsetWidth); // Doublecheck
					if (curLineWidth + endWidth > butContWidth && butContWidth > 0)
					{
						this.ToolbarNewLine();
						this.pButtonsCont.appendChild(pCont);
						curLineWidth = begWidth;
					}
				}
			}
		}
	}
	this.ToolbarEndLine();

	if (typeof this.arConfig.controlButtonsHeight == 'undefined')
		this.buttonsHeight = this.toolbarLineCount * 27;
	else
		this.buttonsHeight = parseInt(this.arConfig.controlButtonsHeight || 0);

	this.arConfig.minHeight += this.buttonsHeight;
	this.arConfig.maxHeight += this.buttonsHeight;

	BX.addCustomEvent(this, 'onShow', BX.proxy(this.ResizeFrame, this));
},

AddButton: function(oBut, buttonId)
{
	if (oBut.parser && oBut.parser.obj)
		this.oSpecialParsers[oBut.parser.name] = oBut.parser.obj;

	this.buttonsCount++;
	var result;
	if (!oBut.type || !oBut.type == 'button')
	{
		if (buttonId == 'Code')
			this.bCodeBut = true;

		var pButton = new window.LHEButton(oBut, this);
		if (pButton && pButton.oBut)
		{
			if (buttonId == 'Source')
				this.sourseBut = pButton;
			else if(buttonId == 'Quote')
				this.quoteBut = pButton;

			result = this.pButtonsCont.appendChild(pButton.pCont);
		}
	}
	else if (oBut.type == 'Colorpicker')
	{
		var pColorpicker = new window.LHEColorPicker(oBut, this);
		result =  this.pButtonsCont.appendChild(pColorpicker.pCont);
	}
	else if (oBut.type == 'List')
	{
		var pList = new window.LHEList(oBut, this);
		result =  this.pButtonsCont.appendChild(pList.pCont);
	}

	if (oBut.parsers)
	{
		for(var i = 0, cnt = oBut.parsers.length; i < cnt; i++)
			if (oBut.parsers[i] && oBut.parsers[i].obj)
				this.oSpecialParsers[oBut.parsers[i].name] = oBut.parsers[i].obj;
	}

	return result;
},

AddParser: function(parser)
{
	if (parser && parser.name && typeof parser.obj == 'object')
		this.oSpecialParsers[parser.name] = parser.obj;
},

ToolbarStartLine: function(bFirst)
{
	// Hack for IE 7
	if (!bFirst && BX.browser.IsIE())
		this.pButtonsCont.appendChild(BX.create("IMG", {props: {src: this.oneGif, className: "lhe-line-ie"}}));

	this.pButtonsCont.appendChild(BX.create("DIV", {props: {className: 'lhe-line-begin'}}));
},

ToolbarEndLine: function()
{
	this.pButtonsCont.appendChild(BX.create("DIV", {props: {className: 'lhe-line-end'}}));
},

ToolbarNewLine: function()
{
	this.toolbarLineCount++;
	this.ToolbarEndLine();
	this.ToolbarStartLine();
},

OpenDialog: function(arParams)
{
	var oDialog = new window.LHEDialog(arParams, this);
},

GetSelectionObject: function()
{
	var oSelection, oRange, root;
	if(this.pEditorDocument.selection) // IE
	{
		oSelection = this.pEditorDocument.selection;
		oRange = oSelection.createRange();

		if(oSelection.type=="Control")
			return oRange.commonParentElement();

		return oRange.parentElement();
	}
	else // FF
	{
		oSelection = this.pEditorWindow.getSelection();
		if(!oSelection)
			return false;

		var container, i, rangeCount = oSelection.rangeCount, obj;
		for(var i = 0; i < rangeCount; i++)
		{
			oRange = oSelection.getRangeAt(i);
			container = oRange.startContainer;
			if(container.nodeType != 3)
			{
				if(container.nodeType == 1 && container.childNodes.length <= 0)
					obj = container;
				else
					obj = container.childNodes[oRange.startOffset];
			}
			else
			{
				temp = oRange.commonAncestorContainer;
				while(temp && temp.nodeType == 3)
					temp = temp.parentNode;
				obj = temp;
			}
			root = (i == 0) ? obj : BXFindParentElement(root, obj);
		}
		return root;
	}
},

GetSelectionObjects: function()
{
	var oSelection;
	if(this.pEditorDocument.selection) // IE
	{
		oSelection = this.pEditorDocument.selection;
		var s = oSelection.createRange();

		if(oSelection.type=="Control")
			return s.commonParentElement();

		return s.parentElement();
	}
	else // FF
	{
		oSelection = this.pEditorWindow.getSelection();
		if(!oSelection)
			return false;
		var oRange;
		var container, temp;
		var res = [];
		for(var i = 0; i < oSelection.rangeCount; i++)
		{
			oRange = oSelection.getRangeAt(i);
			container = oRange.startContainer;
			if(container.nodeType != 3)
			{
				if(container.nodeType == 1 && container.childNodes.length <= 0)
					res[res.length] = container;
				else
					res[res.length] = container.childNodes[oRange.startOffset];
			}
			else
			{
				temp = oRange.commonAncestorContainer;
				while(temp && temp.nodeType == 3)
					temp = temp.parentNode;
				res[res.length] = temp;
			}
		}
		if(res.length > 1)
			return res;
		return res[0];
	}
},

GetSelectionRange: function(doc, win)
{
	try{
		var
			oDoc = doc || this.pEditorDocument,
			oWin = win || this.pEditorWindow,
			oRange,
			oSel = this.GetSelection(oDoc, oWin);

		if (oSel)
		{
			if (oDoc.createRange)
			{
				if (oSel.getRangeAt)
					oRange = oSel.getRangeAt(0);
				else
				{
					oRange = document.createRange();
					oRange.setStart(oSel.anchorNode, oSel.anchorOffset);
					oRange.setEnd(oSel.focusNode, oSel.focusOffset);
				}
			}
			else
				oRange = oSel.createRange();
		}
		else
		{
			oRange = false;
		}

	} catch(e) {oRange = false;}

	return oRange;
},

SelectRange: function(oRange, doc, win)
{
	try{ // IE9 sometimes generete JS error
		if (!oRange)
			return;

		var
			oDoc = doc || this.pEditorDocument,
			oWin = win || this.pEditorWindow;

		this.ClearSelection(oDoc, oWin);
		if (oDoc.createRange) // FF
		{
			var oSel = oWin.getSelection();
			oSel.removeAllRanges();
			oSel.addRange(oRange);
		}
		else //IE
		{
			oRange.select();
		}

	}catch(e){}
},

SelectElement: function(pElement)
{
	try{
		var
			oRange,
			oDoc = this.pEditorDocument,
			oWin = this.pEditorWindow;

		if(oWin.getSelection)
		{
			var oSel = oWin.getSelection();
			oSel.selectAllChildren(pElement);
			oRange = oSel.getRangeAt(0);
			if (oRange.selectNode)
				oRange.selectNode(pElement);
		}
		else
		{
			oDoc.selection.empty();
			oRange = oDoc.selection.createRange();
			oRange.moveToElementText(pElement);
			oRange.select();
		}
		return oRange;
	}catch(e){}
},

GetSelectedText: function(oRange)
{
	// Get selected text
	var selectedText = '';
	if (oRange.startContainer && oRange.endContainer) // DOM Model
	{
		if (oRange.startContainer == oRange.endContainer && (oRange.endContainer.nodeType == 3 || oRange.endContainer.nodeType == 1))
			selectedText = oRange.startContainer.textContent.substring(oRange.startOffset, oRange.endOffset);
	}
	else // IE
	{
		if (oRange.text == oRange.htmlText)
			selectedText = oRange.text;
	}
	return selectedText || '';
},

ClearSelection: function(doc, win)
{
	var
		oDoc = doc || this.pEditorDocument,
		oWin = win || this.pEditorWindow;

	if (oWin.getSelection)
		oWin.getSelection().removeAllRanges();
	else
		oDoc.selection.empty();
},

GetSelection: function(oDoc, oWin)
{
	if (!oDoc)
		oDoc = document;
	if (!oWin)
		oWin = window;

	var oSel = false;
	if (oWin.getSelection)
		oSel = oWin.getSelection();
	else if (oDoc.getSelection)
		oSel = oDoc.getSelection();
	else if (oDoc.selection)
		oSel = oDoc.selection;
	return oSel;
},

InsertHTML: function(sContent)
{
	try{ // Don't clear "try catch"... Some times browsers generetes failures
		this.SetFocus();

		if(BX.browser.IsIE())
		{
			var oRng = this.pEditorDocument.selection.createRange();
			if (oRng.pasteHTML)
			{
				oRng.pasteHTML(sContent);
				oRng.collapse(false);
				oRng.select();
			}
		}
		else if(BX.browser.IsIE11())
		{
			this.PasteHtmlAtCaret(sContent);
		}
		else
		{
			this.pEditorWindow.document.execCommand('insertHTML', false, sContent);
		}
	}catch(e){}

	if (this.arConfig.bAutoResize && this.arConfig.bResizable)
		this.AutoResize();
},

PasteHtmlAtCaret: function(html, selectPastedContent)
{
	var
		win = this.pEditorWindow,
		doc = this.pEditorDocument,
		sel, range;

	if (win.getSelection)
	{
		// IE9 and non-IE
		sel = win.getSelection();
		if (sel.getRangeAt && sel.rangeCount)
		{
			range = sel.getRangeAt(0);
			range.deleteContents();

			// Range.createContextualFragment() would be useful here but is
			// only relatively recently standardized and is not supported in
			// some browsers (IE9, for one)
			var el = doc.createElement("div");
			el.innerHTML = html;
			var frag = doc.createDocumentFragment(), node, lastNode;
			while ((node = el.firstChild))
				lastNode = frag.appendChild(node);

			var firstNode = frag.firstChild;
			range.insertNode(frag);

			// Preserve the selection
			if (lastNode)
			{
				range = range.cloneRange();
				range.setStartAfter(lastNode);
				if (selectPastedContent)
					range.setStartBefore(firstNode);
				else
					range.collapse(true);

				sel.removeAllRanges();
				sel.addRange(range);
			}
		}
	}
	else if ((sel = doc.selection) && sel.type != "Control")
	{
		// IE < 9
		var originalRange = sel.createRange();
		originalRange.collapse(true);
		sel.createRange().pasteHTML(html);
		if (selectPastedContent)
		{
			range = sel.createRange();
			range.setEndPoint("StartToStart", originalRange);
			range.select();
		}
	}
},

AppendCSS: function(styles)
{
	styles = BX.util.trim(styles);
	if (styles.length <= 0)
		return false;

	var
		pDoc = this.pEditorDocument,
		pHeads = pDoc.getElementsByTagName("HEAD");

	if(pHeads.length != 1)
		return false;

	if(BX.browser.IsIE())
	{
		setTimeout(function()
		{
			try{
				if (pDoc.styleSheets.length == 0)
					pHeads[0].appendChild(pDoc.createElement("STYLE"));
				pDoc.styleSheets[0].cssText += styles;
			}catch(e){}
		}, 100);
	}
	else
	{
		try{
			var xStyle = pDoc.createElement("STYLE");
			pHeads[0].appendChild(xStyle);
			xStyle.appendChild(pDoc.createTextNode(styles));
		}catch(e){}
	}
	return true;
},

SetBxTag: function(pElement, params)
{
	var id;
	if (params.id || pElement && pElement.id)
		id = params.id || pElement.id;

	if (!id)
		id = 'bxid_' + Math.round(Math.random() * 1000000);
	else if (this.bxTags[id] && !params.tag)
		params.tag = this.bxTags[id].tag;

	params.id = id;
	if (pElement)
		pElement.id = params.id;

	this.bxTags[params.id] = params;
	return params.id;
},

GetBxTag: function(id)
{
	if (id)
	{
		if (typeof id != "string" && id.id)
			id = id.id;

		if (id && id.length > 0 && this.bxTags[id] && this.bxTags[id].tag)
		{
			this.bxTags[id].tag = this.bxTags[id].tag.toLowerCase();
			return this.bxTags[id];
		}
	}

	return {tag: false};
},

GetAttributesList: function(str)
{
	str = str + " ";

	var arParams = {}, arPHP = [], bPhp = false, _this = this;
	// 1. Replace PHP by #BXPHP#
	str = str.replace(/<\?.*?\?>/ig, function(s)
	{
		arPHP.push(s);
		return "#BXPHP" + (arPHP.length - 1) + "#";
	});

	// 2.0 Parse params - without quotes
	str = str.replace(/([^\w]??)(\w+?)=([^\s\'"]+?)(\s)/ig, function(s, b0, b1, b2, b3)
	{
		b2 = b2.replace(/#BXPHP(\d+)#/ig, function(s, num){return arPHP[num] || s;});
		arParams[b1.toLowerCase()] = BX.util.htmlspecialcharsback(b2);
		return b0;
	});

	// 2.1 Parse params
	str = str.replace(/([^\w]??)(\w+?)\s*=\s*("|\')([^\3]*?)\3/ig, function(s, b0, b1, b2, b3)
	{
		// 3. Replace PHP back
		b3 = b3.replace(/#BXPHP(\d+)#/ig, function(s, num){return arPHP[num] || s;});
		arParams[b1.toLowerCase()] = BX.util.htmlspecialcharsback(b3);
		return b0;
	});

	return arParams;
},

RidOfNode: function (pNode, bHard)
{
	if (!pNode || pNode.nodeType != 1)
		return;

	var i, nodeName = pNode.tagName.toLowerCase(),
		nodes = ['span', 'strike', 'del', 'font', 'code', 'div'];

	if (BX.util.in_array(nodeName, nodes)) // Check node names
	{
		if (bHard !== true)
		{
			for (i = pNode.attributes.length - 1; i >= 0; i--)
			{
				if (BX.util.trim(pNode.getAttribute(pNode.attributes[i].nodeName.toLowerCase())) != "")
					return false; // Node have attributes, so we cant get rid of it without loosing info
			}
		}

		var arNodes = pNode.childNodes;
		while(arNodes.length > 0)
			pNode.parentNode.insertBefore(arNodes[0], pNode);

		pNode.parentNode.removeChild(pNode);
		//this.OnEvent("OnSelectionChange");
		return true;
	}

	return false;
},

WrapSelectionWith: function (tagName, arAttributes)
{
	this.SetFocus();
	var oRange, oSelection;

	if (!tagName)
		tagName = 'SPAN';

	var sTag = 'FONT', i, pEl, arTags, arRes = [];

	try{this.pEditorDocument.execCommand("styleWithCSS", false, false);}catch(e){}
	this.executeCommand("FontName", "bitrixtemp");

	arTags = this.pEditorDocument.getElementsByTagName(sTag);

	for(i = arTags.length - 1; i >= 0; i--)
	{
		if (arTags[i].getAttribute('face') != 'bitrixtemp')
			continue;

		pEl = BX.create(tagName, arAttributes, this.pEditorDocument);
		arRes.push(pEl);

		while(arTags[i].firstChild)
			pEl.appendChild(arTags[i].firstChild);

		arTags[i].parentNode.insertBefore(pEl, arTags[i]);
		arTags[i].parentNode.removeChild(arTags[i]);
	}

	if (this.arConfig.bAutoResize && this.arConfig.bResizable)
		this.AutoResize();

	return arRes;
},

SaveSelectionRange: function()
{
	if (this.sEditorMode == 'code')
		this.oPrevRangeText = this.GetSelectionRange(document, window);
	else
		this.oPrevRange = this.GetSelectionRange();
},

RestoreSelectionRange: function()
{
	if (this.sEditorMode == 'code')
		this.IESetCarretPos(this.oPrevRangeText);
	else if(this.oPrevRange)
		this.SelectRange(this.oPrevRange);
},

focus: function(el, bSelect)
{
	setTimeout(function()
	{
		try{
			el.focus();
			if(bSelect)
				el.select();
		}catch(e){}
	}, 100);
},

// Methods below used in BB-mode
// Earlier was in bb.js
InitBBCode: function()
{
	this.stack = [];
	var _this = this;
	this.pTextarea.onkeydown = BX.proxy(this.OnKeyDownBB, this);

	// Backup parser functions
	this._GetNodeHTMLLeft = this.GetNodeHTMLLeft;
	this._GetNodeHTMLRight = this.GetNodeHTMLRight;

	this.GetNodeHTMLLeft = this.GetNodeHTMLLeftBB;
	this.GetNodeHTMLRight = this.GetNodeHTMLRightBB;
},

ShutdownBBCode: function()
{
	this.bBBCode = false;
	this.arConfig.bBBCode = false;

	this.pTextarea.onkeydown = null;

	// Restore parser functions
	this.GetNodeHTMLLeft = this._GetNodeHTMLLeft;
	this.GetNodeHTMLRight = this._GetNodeHTMLRight;

	this.arConfig.bConvertContentFromBBCodes = false;
},

FormatBB: function(params)
{
	var
		pBut = params.pBut,
		value = params.value,
		tag = params.tag.toUpperCase(),
		tag_end = tag;

	if (tag == 'FONT' || tag == 'COLOR' || tag == 'SIZE')
		tag += "=" + value;

	if ((!BX.util.in_array(tag, this.stack) || this.GetTextSelection()) && !(tag == 'FONT' && value == 'none'))
	{
		if (!this.WrapWith("[" + tag + "]", "[/" + tag_end + "]"))
		{
			this.stack.push(tag);

			if (pBut && pBut.Check)
				pBut.Check(true);
		}
	}
	else
	{
		var res = false;
		while (res = this.stack.pop())
		{
			this.WrapWith("[/" + res + "]", "");
			if (pBut && pBut.Check)
				pBut.Check(false);

			if (res == tag)
				break;
		}
	}
},

GetTextSelection: function()
{
	var res = false;
	if (typeof this.pTextarea.selectionStart != 'undefined')
	{
		res = this.pTextarea.value.substr(this.pTextarea.selectionStart, this.pTextarea.selectionEnd - this.pTextarea.selectionStart);
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
},

IESetCarretPos: function(oRange)
{
	if (!oRange || !BX.browser.IsIE() || oRange.text.length != 0 /* text selected*/)
		return;

	oRange.moveStart('character', - this.pTextarea.value.length);
	var pos = oRange.text.length;

	var range = this.pTextarea.createTextRange();
	range.collapse(true);
	range.moveEnd('character', pos);
	range.moveStart('character', pos);
	range.select();
},

WrapWith: function (tagBegin, tagEnd, postText)
{
	if (!tagBegin)
		tagBegin = "";
	if (!tagEnd)
		tagEnd = ""

	if (!postText)
		postText = "";

	if (tagBegin.length <= 0 && tagEnd.length <= 0 && postText.length <= 0)
		return true;

	var bReplaceText = !!postText;
	var sSelectionText = this.GetTextSelection();

	if (!this.bTextareaFocus)
		this.pTextarea.focus(); // BUG IN IE

	var isSelect = (sSelectionText ? 'select' : bReplaceText ? 'after' : 'in');

	if (bReplaceText)
		postText = tagBegin + postText + tagEnd;
	else if (sSelectionText)
		postText = tagBegin + sSelectionText + tagEnd;
	else
		postText = tagBegin + tagEnd;

	if (typeof this.pTextarea.selectionStart != 'undefined')
	{
		var
			currentScroll = this.pTextarea.scrollTop,
			start = this.pTextarea.selectionStart,
			end = this.pTextarea.selectionEnd;

		this.pTextarea.value = this.pTextarea.value.substr(0, start) + postText + this.pTextarea.value.substr(end);

		if (isSelect == 'select')
		{
			this.pTextarea.selectionStart = start;
			this.pTextarea.selectionEnd = start + postText.length;
		}
		else if (isSelect == 'in')
		{
			this.pTextarea.selectionStart = this.pTextarea.selectionEnd = start + tagBegin.length;
		}
		else
		{
			this.pTextarea.selectionStart = this.pTextarea.selectionEnd = start + postText.length;
		}
		this.pTextarea.scrollTop = currentScroll;
	}
	else if (document.selection && document.selection.createRange)
	{
		var sel = document.selection.createRange();
		var selection_copy = sel.duplicate();
		postText = postText.replace(/\r?\n/g, '\n');
		sel.text = postText;
		sel.setEndPoint('StartToStart', selection_copy);
		sel.setEndPoint('EndToEnd', selection_copy);

		if (isSelect == 'select')
		{
			sel.collapse(true);
			postText = postText.replace(/\r\n/g, '1');
			sel.moveEnd('character', postText.length);
		}
		else if (isSelect == 'in')
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
		this.pTextarea.value += postText;
	}
	return true;
},

ParseBB: function (sContent)  // BBCode -> WYSIWYG
{
	sContent = BX.util.htmlspecialchars(sContent);

	// Table
	sContent = sContent.replace(/[\r\n\s\t]?\[table\][\r\n\s\t]*?\[tr\]/ig, '[TABLE][TR]');
	sContent = sContent.replace(/\[tr\][\r\n\s\t]*?\[td\]/ig, '[TR][TD]');
	sContent = sContent.replace(/\[tr\][\r\n\s\t]*?\[th\]/ig, '[TR][TH]');
	sContent = sContent.replace(/\[\/td\][\r\n\s\t]*?\[td\]/ig, '[/TD][TD]');
	sContent = sContent.replace(/\[\/tr\][\r\n\s\t]*?\[tr\]/ig, '[/TR][TR]');
	sContent = sContent.replace(/\[\/td\][\r\n\s\t]*?\[\/tr\]/ig, '[/TD][/TR]');
	sContent = sContent.replace(/\[\/th\][\r\n\s\t]*?\[\/tr\]/ig, '[/TH][/TR]');
	sContent = sContent.replace(/\[\/tr\][\r\n\s\t]*?\[\/table\][\r\n\s\t]?/ig, '[/TR][/TABLE]');

	// List
	sContent = sContent.replace(/[\r\n\s\t]*?\[\/list\]/ig, '[/LIST]');
	sContent = sContent.replace(/[\r\n\s\t]*?\[\*\]?/ig, '[*]');

	var
		arSimpleTags = [
			'b','u', 'i', ['s', 'del'], // B, U, I, S
			'table', 'tr', 'td', 'th'//, // Table
		],
		bbTag, tag, i, l = arSimpleTags.length, re;

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

		sContent = sContent.replace(new RegExp('\\[(\\/?)' + bbTag + '\\]', 'ig'), "<$1" + tag + ">");
	}

	// Link
	sContent = sContent.replace(/\[url\]((?:\s|\S)*?)\[\/url\]/ig, "<a href=\"$1\">$1</a>");
	sContent = sContent.replace(/\[url\s*=\s*((?:[^\[\]]*?(?:\[[^\]]+?\])*[^\[\]]*?)*)\s*\]((?:\s|\S)*?)\[\/url\]/ig, "<a href=\"$1\">$2</a>");

	// Img
	var _this = this;
	sContent = sContent.replace(/\[img(?:\s*?width=(\d+)\s*?height=(\d+))?\]((?:\s|\S)*?)\[\/img\]/ig,
		function(str, w, h, src)
		{
			var strSize = "";
			w = parseInt(w);
			h = parseInt(h);

			if (w && h && _this.bBBParseImageSize)
				strSize = ' width="' + w + '" height="' + h + '"';

			return '<img  src="' + src + '"' + strSize + '/>';
		}
	);

	// Font color
	i = 0;
	while (sContent.toLowerCase().indexOf('[color=') != -1 && sContent.toLowerCase().indexOf('[/color]') != -1 && i++ < 20)
		sContent = sContent.replace(/\[color=((?:\s|\S)*?)\]((?:\s|\S)*?)\[\/color\]/ig, "<font color=\"$1\">$2</font>");

	// List
	i = 0;
	while (sContent.toLowerCase().indexOf('[list=') != -1 && sContent.toLowerCase().indexOf('[/list]') != -1 && i++ < 20)
		sContent = sContent.replace(/\[list=1\]((?:\s|\S)*?)\[\/list\]/ig, "<ol>$1</ol>");

	i = 0;
	while (sContent.toLowerCase().indexOf('[list') != -1 && sContent.toLowerCase().indexOf('[/list]') != -1 && i++ < 20)
		sContent = sContent.replace(/\[list\]((?:\s|\S)*?)\[\/list\]/ig, "<ul>$1</ul>");

	sContent = sContent.replace(/\[\*\]/ig, "<li>");

	// Font
	i = 0;
	while (sContent.toLowerCase().indexOf('[font=') != -1 && sContent.toLowerCase().indexOf('[/font]') != -1 && i++ < 20)
		sContent = sContent.replace(/\[font=((?:\s|\S)*?)\]((?:\s|\S)*?)\[\/font\]/ig, "<font face=\"$1\">$2</font>");

	// Font size
	i = 0;
	while (sContent.toLowerCase().indexOf('[size=') != -1 && sContent.toLowerCase().indexOf('[/size]') != -1 && i++ < 20)
		sContent = sContent.replace(/\[size=((?:\s|\S)*?)\]((?:\s|\S)*?)\[\/size\]/ig, "<font size=\"$1\">$2</font>");

	// Replace \n => <br/>
	sContent = sContent.replace(/\n/ig, "<br />");

	return sContent;
},

UnParseNodeBB: function (pNode) // WYSIWYG -> BBCode
{
	if (pNode.text == "br")
		return "#BR#";

	if (pNode.type == 'text')
		return false;

	//[CODE] Handle code tag
	if (pNode.text == "pre" && pNode.arAttributes['class'] == 'lhe-code')
		return "[CODE]" + this.RecGetCodeContent(pNode) + "[/CODE]";

	pNode.bbHide = true;
	if (pNode.text == 'font' && pNode.arAttributes.color)
	{
		pNode.bbHide = false;
		pNode.text = 'color';
		pNode.bbValue = pNode.arAttributes.color;
	}
	else if (pNode.text == 'font' && pNode.arAttributes.size)
	{
		pNode.bbHide = false;
		pNode.text = 'size';
		pNode.bbValue = pNode.arAttributes.size;
	}
	else if (pNode.text == 'font' && pNode.arAttributes.face)
	{
		pNode.bbHide = false;
		pNode.text = 'font';
		pNode.bbValue = pNode.arAttributes.face;
	}
	else if(pNode.text == 'del')
	{
		pNode.bbHide = false;
		pNode.text = 's';
	}
	else if(pNode.text == 'strong' || pNode.text == 'b')
	{
		pNode.bbHide = false;
		pNode.text = 'b';
	}
	else if(pNode.text == 'em' || pNode.text == 'i')
	{
		pNode.bbHide = false;
		pNode.text = 'i';
	}
	else if(pNode.text == 'blockquote')
	{
		pNode.bbHide = false;
		pNode.text = 'quote';
	}
	else if(pNode.text == 'ol')
	{
		pNode.bbHide = false;
		pNode.text = 'list';
		pNode.bbBreakLineRight = true;
		pNode.bbValue = '1';
	}
	else if(pNode.text == 'ul')
	{
		pNode.bbHide = false;
		pNode.text = 'list';
		pNode.bbBreakLineRight = true;
	}
	else if(pNode.text == 'li')
	{
		pNode.bbHide = false;
		pNode.text = '*';
		pNode.bbBreakLine = true;
		pNode.bbHideRight = true;
	}
	else if(pNode.text == 'a')
	{
		pNode.bbHide = false;
		pNode.text = 'url';
		pNode.bbValue = pNode.arAttributes.href;
	}
	else if(this.parseAlign
		&&
		(pNode.arAttributes.align || pNode.arStyle.textAlign)
		&&
		!(BX.util.in_array(pNode.text.toLowerCase(), ['table', 'tr', 'td', 'th']))
		)
	{
		var align = pNode.arStyle.textAlign || pNode.arAttributes.align;
		if (BX.util.in_array(align, ['left', 'right', 'center', 'justify']))
		{
			pNode.bbHide = false;
			pNode.text = align;
		}
		else
		{
			pNode.bbHide = !BX.util.in_array(pNode.text, this.arBBTags);
		}
	}
	else if(BX.util.in_array(pNode.text, this.arBBTags)) //'p', 'u', 'div', 'table', 'tr', 'img', 'td', 'a'
	{
		pNode.bbHide = false;
	}

	return false;
},

RecGetCodeContent: function(pNode) // WYSIWYG -> BBCode
{
	if (!pNode || !pNode.arNodes || !pNode.arNodes.length)
		return '';

	var res = '';
	for (var i = 0; i < pNode.arNodes.length; i++)
	{
		if (pNode.arNodes[i].type == 'text')
			res += pNode.arNodes[i].text;
		else if (pNode.arNodes[i].type == 'element' && pNode.arNodes[i].text == "br")
			res += (this.bBBCode ? "#BR#" : "\n");
		else if (pNode.arNodes[i].arNodes)
			res += this.RecGetCodeContent(pNode.arNodes[i]);
	}

	if (this.bBBCode)
	{
		if (BX.browser.IsIE())
			res = res.replace(/\r/ig, "#BR#");
		else
			res = res.replace(/\n/ig, "#BR#");
	}
	else if (BX.browser.IsIE())
	{
		res = res.replace(/\n/ig, "\r\n");
	}

	return res;
},

GetNodeHTMLLeftBB: function (pNode)
{
	if(pNode.type == 'text')
	{
		var text = BX.util.htmlspecialchars(pNode.text);
		text = text.replace(/\[/ig, "&#91;");
		text = text.replace(/\]/ig, "&#93;");
		return text;
	}

	var res = "";
	if (pNode.bbBreakLine)
		res += "\n";

	if(pNode.type == 'element' && !pNode.bbHide)
	{
		res += "[" + pNode.text.toUpperCase();
		if (pNode.bbValue)
			res += '=' + pNode.bbValue;
		res += "]";
	}

	return res;
},

GetNodeHTMLRightBB: function (pNode)
{
	var res = "";
	if (pNode.bbBreakLineRight)
		res += "\n";

	if(pNode.type == 'element' && (pNode.arNodes.length > 0 || this.IsPairNode(pNode.text)) && !pNode.bbHide && !pNode.bbHideRight)
		res += "[/" + pNode.text.toUpperCase() + "]";

	return res;
},

OptimizeBB: function (str)
{
	// TODO: kill links without text and names
	// TODO: Kill multiple line ends
	var
		iter = 0,
		bReplasing = true,
		arTags = ['b', 'i', 'u', 's', 'color', 'font', 'size', 'quote'],
		replaceEmptyTags = function(){i--; bReplasing = true; return ' ';},
		re, tagName, i, l;

	while(iter++ < 20 && bReplasing)
	{
		bReplasing = false;
		for (i = 0, l = arTags.length; i < l; i++)
		{
			tagName = arTags[i];
			// Replace empties: [b][/b]  ==> ""
			re = new RegExp('\\[' + tagName + '[^\\]]*?\\]\\s*?\\[/' + tagName + '\\]', 'ig');
			str = str.replace(re, replaceEmptyTags);

			if (tagName !== 'quote')
			{
				re = new RegExp('\\[((' + tagName + '+?)(?:\\s+?[^\\]]*?)?)\\]([\\s\\S]+?)\\[\\/\\2\\](\\s*?)\\[\\1\\]([\\s\\S]+?)\\[\\/\\2\\]', 'ig');
				str = str.replace(re, function(str, b1, b2, b3, spacer, b4)
					{
						if (spacer.indexOf("\n") != -1)
							return str;
						bReplasing = true;
						return '[' + b1 + ']' + b3 + ' ' + b4 + '[/' + b2 + ']';
					}
				);

				//Replace [b]1 [b]2[/b] 3[/b] ===>>  [b]1 2 3[/b]
				// re = new RegExp('(\\[' + tagName + '(?:\\s+?[^\\]]*?)?\\])([\\s\\S]+?)\\1([\\s\\S]+?)(\\[\\/' + tagName + '\\])([\\s\\S]+?)\\4', 'ig');
				// str = str.replace(re, function(str, b1, b2, b3, b4, b5)
				// {
				// bReplasing = true;
				// return b1 + b2 + b3 + b5 + b4;
				// }
				// );
			}
		}
	}
	//
	str = str.replace(/[\r\n\s\t]*?\[\/list\]/ig, "\n[/LIST]");
	str = str.replace(/[\r\n\s\t]*?\[\/list\]/ig, "\n[/LIST]");

	// Cut "\n" in the end of the message (only for BB)
	str = str.replace(/\n*$/ig, '');

	return str;
},

RemoveFormatBB: function()
{
	var str = this.GetTextSelection();
	if (str)
	{
		var
			it = 0,
			arTags = ['b', 'i', 'u', 's', 'color', 'font', 'size'],
			i, l = arTags.length;

		//[b]123[/b]  ==> 123
		while (it < 30)
		{
			str1 = str;
			for (i = 0; i < l; i++)
				str = str.replace(new RegExp('\\[(' + arTags[i] + ')[^\\]]*?\\]([\\s\\S]*?)\\[/\\1\\]', 'ig'), "$2");

			if (str == str1)
				break;
			it++;
		}

		this.WrapWith('', '', str);
	}
},

OnKeyDownBB: function(e)
{
	if(!e) e = window.event;

	var key = e.which || e.keyCode;
	if (e.ctrlKey && !e.shiftKey && !e.altKey)
	{
		switch (key)
		{
			case 66 : // B
			case 98 : // b
				this.FormatBB({tag: 'B'});
				return BX.PreventDefault(e);
			case 105 : // i
			case 73 : // I
				this.FormatBB({tag: 'I'});
				return BX.PreventDefault(e);
			case 117 : // u
			case 85 : // U
				this.FormatBB({tag: 'U'});
				return BX.PreventDefault(e);
			case 81 : // Q - quote
				this.FormatBB({tag: 'QUOTE'});
				return BX.PreventDefault(e);
		}
	}

	// Tab
	if (key == 9)
	{
		this.WrapWith('', '', "\t");
		return BX.PreventDefault(e);
	}

	// Ctrl + Enter
	if ((e.keyCode == 13 || e.keyCode == 10) && e.ctrlKey && this.ctrlEnterHandler)
	{
		this.SaveContent();
		this.ctrlEnterHandler();
	}
},

GetCutHTML: function(e)
{
	if (this.curCutId)
	{
		var pCut = this.pEditorDocument.getElementById(this.curCutId);
		if (pCut)
		{
			pCut.parentNode.insertBefore(BX.create("BR", {}, this.pEditorDocument), pCut);
			pCut.parentNode.removeChild(pCut);
		}
	}

	this.curCutId = this.SetBxTag(false, {tag: "cut"});
	return '<img src="' + this.oneGif+ '" class="bxed-cut" id="' + this.curCutId + '" title="' + BX.message.CutTitle + '"/>';
},

OnPaste: function()
{
	if (this.bOnPasteProcessing)
		return;

	this.bOnPasteProcessing = true;
	var _this = this;
	var scrollTop = this.pEditorDocument.body.scrollTop;
	setTimeout(function(){
		_this.bOnPasteProcessing = false;
		_this.InsertHTML('<span style="visibility: hidden;" id="' + _this.SetBxTag(false, {tag: "cursor"}) + '" ></span>');

		_this.SaveContent();
		setTimeout(function()
		{
			var content = _this.GetContent();

			if (/<\w[^>]*(( class="?MsoNormal"?)|(="mso-))/gi.test(content))
				content = _this.CleanWordText(content);

			_this.SetEditorContent(content);

			setTimeout(function()
			{
				try{
					var pCursor = _this.pEditorDocument.getElementById(_this.lastCursorId);
					if (pCursor && pCursor.parentNode)
					{
						var newScrollTop = pCursor.offsetTop - 30;
						if (newScrollTop > 0)
						{
							if (scrollTop > 0 && scrollTop + parseInt(_this.pFrame.offsetHeight) > newScrollTop)
								_this.pEditorDocument.body.scrollTop = scrollTop;
							else
								_this.pEditorDocument.body.scrollTop = newScrollTop;
						}

						_this.SelectElement(pCursor);
						pCursor.parentNode.removeChild(pCursor);
						_this.SetFocus();
					}
				}catch(e){}
			}, 100);

		}, 100);
	}, 100);
},

CleanWordText: function(text)
{
	text = text.replace(/<(P|B|U|I|STRIKE)>&nbsp;<\/\1>/g, ' ');
	text = text.replace(/<o:p>([\s\S]*?)<\/o:p>/ig, "$1");
	//text = text.replace(/<o:p>[\s\S]*?<\/o:p>/ig, "&nbsp;");

	text = text.replace(/<span[^>]*display:\s*?none[^>]*>([\s\S]*?)<\/span>/gi, ''); // Hide spans with display none

	text = text.replace(/<!--\[[\s\S]*?\]-->/ig, ""); //<!--[.....]--> <!--[if gte mso 9]>...<![endif]-->
	text = text.replace(/<!\[[\s\S]*?\]>/ig, ""); //	<! [if !vml]>
	text = text.replace(/<\\?\?xml[^>]*>/ig, ""); //<xml...>, </xml...>

	text = text.replace(/<o:p>\s*<\/o:p>/ig, "");

	text = text.replace(/<\/?[a-z1-9]+:[^>]*>/gi, "");	//<o:p...>, </o:p>
	text = text.replace(/<([a-z1-9]+[^>]*) class=([^ |>]*)(.*?>)/gi, "<$1$3");
	text = text.replace(/<([a-z1-9]+[^>]*) [a-z]+:[a-z]+=([^ |>]*)(.*?>)/gi, "<$1$3"); //	xmlns:v="urn:schemas-microsoft-com:vml"

	text = text.replace(/&nbsp;/ig, ' ');
	text = text.replace(/\s+?/gi, ' ');

	// Remove mso-xxx styles.
	text = text.replace(/\s*mso-[^:]+:[^;"]+;?/gi, "");

	// Remove margin styles.
	text = text.replace(/\s*margin: 0cm 0cm 0pt\s*;/gi, "");
	text = text.replace(/\s*margin: 0cm 0cm 0pt\s*"/gi, "\"");

	text = text.replace(/\s*TEXT-INDENT: 0cm\s*;/gi, "");
	text = text.replace(/\s*TEXT-INDENT: 0cm\s*"/gi, "\"");


	text = text.replace(/\s*TEXT-ALIGN: [^\s;]+;?"/gi, "\"");
	text = text.replace(/\s*PAGE-BREAK-BEFORE: [^\s;]+;?"/gi, "\"");
	text = text.replace(/\s*FONT-VARIANT: [^\s;]+;?"/gi, "\"");
	text = text.replace(/\s*tab-stops:[^;"]*;?/gi, "");
	text = text.replace(/\s*tab-stops:[^"]*/gi, "");

	text = text.replace(/<FONT[^>]*>([\s\S]*?)<\/FONT>/gi, '$1');
	text = text.replace(/\s*face="[^"]*"/gi, "");
	text = text.replace(/\s*face=[^ >]*/gi, "");
	text = text.replace(/\s*FONT-FAMILY:[^;"]*;?/gi, "");

	// Remove Class attributes
	text = text.replace(/<(\w[^>]*) class=([^ |>]*)([^>]*)/gi, "<$1$3");

	// Remove styles.
	text = text.replace(/<(\w[^>]*) style="([^\"]*)"([^>]*)/gi, "<$1$3");

	// Remove empty styles.
	text = text.replace(/\s*style="\s*"/gi, '');

	// Remove Lang attributes
	text = text.replace(/<(\w[^>]*) lang=([^ |>]*)([^>]*)/gi, "<$1$3");

	var iter = 0;
	while (text.toLowerCase().indexOf('<span') != -1 && text.toLowerCase().indexOf('</span>') != -1 && iter++ < 20)
		text = text.replace(/<span[^>]*?>([\s\S]*?)<\/span>/gi, '$1');

	var
		_text,
		i, tag, arFormatTags = ['b', 'strong', 'i', 'u', 'font', 'span', 'strike'];

	while (true)
	{
		_text = text;
		for (i in arFormatTags)
		{
			tag = arFormatTags[i];
			text = text.replace(new RegExp('<' + tag + '[^>]*?>(\\s*?)<\\/' + tag + '>', 'gi'), '$1');
			text = text.replace(new RegExp('<\\/' + tag + '[^>]*?>(\\s*?)<' + tag + '>', 'gi'), '$1');
		}

		if (_text == text)
			break;
	}

	// Remove empty tags
	text = text.replace(/<(?:[^\s>]+)[^>]*>([\s\n\t\r]*)<\/\1>/g, "$1");
	text = text.replace(/<(?:[^\s>]+)[^>]*>(\s*)<\/\1>/g, "$1");
	text = text.replace(/<(?:[^\s>]+)[^>]*>(\s*)<\/\1>/g, "$1");

	//text = text.replace(/<\/?xml[^>]*>/gi, "");	//<xml...>, </xml...>
	text = text.replace(/<xml[^>]*?(?:>\s*?<\/xml)?(?:\/?)?>/ig, '');
	text = text.replace(/<meta[^>]*?(?:>\s*?<\/meta)?(?:\/?)?>/ig, '');
	text = text.replace(/<link[^>]*?(?:>\s*?<\/link)?(?:\/?)?>/ig, '');
	text = text.replace(/<style[\s\S]*?<\/style>/ig, '');

	text = text.replace(/<table([\s\S]*?)>/gi, "<table>");
	text = text.replace(/<tr([\s\S]*?)>/gi, "<tr>");
	text = text.replace(/(<td[\s\S]*?)width=("|')[\s\S]*?\2([\s\S]*?>)/gi, "$1$3");
	text = text.replace(/(<td[\s\S]*?)height=("|')[\s\S]*?\2([\s\S]*?>)/gi, "$1$3");
	text = text.replace(/(<td[\s\S]*?)style=("|')[\s\S]*?\2([\s\S]*?>)/gi, "$1$3");
	text = text.replace(/(<td[\s\S]*?)valign=("|')[\s\S]*?\2([\s\S]*?>)/gi, "$1$3");
	text = text.replace(/(<td[\s\S]*?)nowrap=("|')[\s\S]*?\2([\s\S]*?>)/gi, "$1$3");
	text = text.replace(/(<td[\s\S]*?)nowrap([\s\S]*?>)/gi, "$1$3");

	text = text.replace(/(<col[\s\S]*?)width=("|')[\s\S]*?\2([\s\S]*?>)/gi, "$1$3");
	text = text.replace(/(<col[\s\S]*?)style=("|')[\s\S]*?\2([\s\S]*?>)/gi, "$1$3");

	// For Opera (12.10+) only when in text we have reference links.
	if (BX.browser.IsOpera())
		text = text.replace(/REF\s+?_Ref\d+?[\s\S]*?MERGEFORMAT\s([\s\S]*?)\s[\s\S]*?<\/xml>/gi, " $1 ");

	return text;
}
};

BXLEditorUtils = function()
{
	this.oTune = {};
	this.setCurrentEditorId('default');
};
BXLEditorUtils.prototype = {
	setCurrentEditorId: function(id)
	{
		this.curId = id;
	},

	prepare : function()
	{
		if (!this.oTune[this.curId])
			this.oTune[this.curId] =
			{
				buttons: [],
				ripButtons: {}
			};
	},

	addButton : function(pBut, ind)
	{
		if (!pBut || !pBut.id)
			return false;
		if (typeof ind == 'undefined')
			ind = -1;

		this.prepare();
		this.oTune[this.curId].buttons.push({but: pBut, ind: ind});

		return true;
	},

	removeButton: function(id)
	{
		this.prepare();
		this.oTune[this.curId].ripButtons[id] = true;
	}
};
oBXLEditorUtils = new BXLEditorUtils();

function BXFindParentElement(pElement1, pElement2)
{
	var p, arr1 = [], arr2 = [];
	while((pElement1 = pElement1.parentNode) != null)
		arr1[arr1.length] = pElement1;
	while((pElement2 = pElement2.parentNode) != null)
		arr2[arr2.length] = pElement2;

	var min, diff1 = 0, diff2 = 0;
	if(arr1.length<arr2.length)
	{
		min = arr1.length;
		diff2 = arr2.length - min;
	}
	else
	{
		min = arr2.length;
		diff1 = arr1.length - min;
	}

	for(var i=0; i<min-1; i++)
	{
		if(BXElementEqual(arr1[i+diff1], arr2[i+diff2]))
			return arr1[i+diff1];
	}
	return arr1[0];
}

window.BXFindParentByTagName = function (pElement, tagName)
{
	tagName = tagName.toUpperCase();
	while(pElement && (pElement.nodeType !=1 || pElement.tagName.toUpperCase() != tagName))
		pElement = pElement.parentNode;
	return pElement;
}


function SetAttr(pEl, attr, val)
{
	if(attr=='className' && !BX.browser.IsIE())
		attr = 'class';

	if(val.length <= 0)
		pEl.removeAttribute(attr);
	else
		pEl.setAttribute(attr, val);
}

function BXCutNode(pNode)
{
	while(pNode.childNodes.length > 0)
		pNode.parentNode.insertBefore(pNode.childNodes[0], pNode);

	pNode.parentNode.removeChild(pNode);
}
