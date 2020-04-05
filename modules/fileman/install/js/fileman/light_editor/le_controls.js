function LHEButton(oBut, pLEditor)
{
	if (!oBut.name)
		oBut.name = oBut.id;

	if (!oBut.title)
		oBut.title = oBut.name;
	this.disabled = false;

	this.pLEditor = pLEditor;

	this.oBut = oBut;
	if (this.oBut && typeof this.oBut.OnBeforeCreate == 'function')
		this.oBut = this.oBut.OnBeforeCreate(this.pLEditor, this.oBut);

	if(this.oBut)
		this.Create();
}


LHEButton.prototype = {
	Create: function ()
	{
		var _this = this;
		this.pCont = BX.create("DIV", {props: {className: 'lhe-button-cont'}});

		this.pWnd = this.pCont.appendChild(BX.create("IMG", {props: {src: this.oBut.src || this.pLEditor.oneGif, title: this.oBut.title, className: "lhe-button lhe-button-normal", id: "lhe_btn_" + this.oBut.id.toLowerCase()}}));

		if (this.oBut.disableOnCodeView)
			BX.addCustomEvent(this.pLEditor, "OnChangeView", BX.proxy(this.OnChangeView, this));

		if (this.oBut.width)
		{
			this.pCont.style.width = parseInt(this.oBut.width) + 5 + "px";
			this.pWnd.style.width = parseInt(this.oBut.width) + "px";
		}

		this.pWnd.onmouseover = function(e){_this.OnMouseOver(e, this)};
		this.pWnd.onmouseout = function(e){_this.OnMouseOut(e, this)};
		this.pWnd.onmousedown = function(e){_this.OnClick(e, this);};
	},

	OnMouseOver: function (e, pEl)
	{
		if(this.disabled)
			return;
		pEl.className = 'lhe-button lhe-button-over';
	},

	OnMouseOut: function (e, pEl)
	{
		if(this.disabled)
			return;

		if(this.checked)
			pEl.className = 'lhe-button lhe-button-checked';
		else
			pEl.className = 'lhe-button lhe-button-normal';
	},

	OnClick: function (e, pEl)
	{
		if(this.disabled)
			return false;

		var res = false;
		if (this.pLEditor.sEditorMode == 'code' && this.pLEditor.bBBCode && typeof this.oBut.bbHandler == 'function')
		{
			res = this.oBut.bbHandler(this) !== false;
		}
		else
		{
			if(typeof this.oBut.handler == 'function')
				res = this.oBut.handler(this) !== false;

			if(this.pLEditor.sEditorMode != 'code' && !res && this.oBut.cmd)
				res = this.pLEditor.executeCommand(this.oBut.cmd);

			this.pLEditor.SetFocus();
			BX.defer(this.pLEditor.SetFocus, this.pLEditor)();
		}

		return res;
	},

	Check: function (bFlag)
	{
		if(bFlag == this.checked || this.disabled)
			return;

		this.checked = bFlag;
		if(this.checked)
			BX.addClass(this.pWnd, 'lhe-button-checked');
		else
			BX.removeClass(this.pWnd, 'lhe-button-checked');
	},

	Disable: function (bFlag)
	{
		if(bFlag == this.disabled)
			return false;
		this.disabled = bFlag;
		if(bFlag)
			BX.addClass(this.pWnd, 'lhe-button-disabled');
		else
			BX.removeClass(this.pWnd, 'lhe-button-disabled');
	},

	OnChangeView: function()
	{
		if (this.oBut.disableOnCodeView)
			this.Disable(this.pLEditor.sEditorMode == 'code');
	}
}

// Dialog
function LHEDialog(arParams, pLEditor)
{
	this.pSel = arParams.obj || false;
	this.pLEditor = pLEditor;
	this.id = arParams.id;
	this.arParams = arParams;
	this.Create();
};

LHEDialog.prototype = {
	Create: function()
	{
		if (!window.LHEDailogs[this.id] || typeof window.LHEDailogs[this.id] != 'function')
			return;

		var oDialog = window.LHEDailogs[this.id](this);
		if (!oDialog)
			return;

		this.prevTextSelection = "";
		if (this.pLEditor.sEditorMode == 'code')
			this.prevTextSelection = this.pLEditor.GetTextSelection();

		this.pLEditor.SaveSelectionRange();

		if (BX.browser.IsIE() && !this.arParams.bCM && this.pLEditor.sEditorMode != 'code')
		{
			if (this.pLEditor.GetSelectedText(this.pLEditor.oPrevRange) == '')
			{
				this.pLEditor.InsertHTML('<img id="bx_lhe_temp_bogus_node" src="' + this.pLEditor.oneGif + '" _moz_editor_bogus_node="on" style="border: 0px !important;"/>');
				this.pLEditor.oPrevRange = this.pLEditor.GetSelectionRange();
			}
		}

		var arDConfig = {
			title : oDialog.title || this.name || '',
			width: oDialog.width || 500,
			height: 200,
			resizable: false
		};

		if (oDialog.height)
			arDConfig.height = oDialog.height;

		if (oDialog.resizable)
		{
			arDConfig.resizable = true;
			arDConfig.min_width = oDialog.min_width;
			arDConfig.min_height = oDialog.min_height;
			arDConfig.resize_id = oDialog.resize_id;
		}

		window.obLHEDialog = new BX.CDialog(arDConfig);

		var _this = this;
		BX.addCustomEvent(obLHEDialog, 'onWindowUnRegister', function()
		{
			_this.pLEditor.bPopup = false;
			if (obLHEDialog.DIV && obLHEDialog.DIV.parentNode)
				obLHEDialog.DIV.parentNode.removeChild(window.obLHEDialog.DIV);

			if (_this.arParams.bEnterClose !== false)
				BX.unbind(window, "keydown", BX.proxy(_this.OnKeyPress, _this));
		});

		if (this.arParams.bEnterClose !== false)
			BX.bind(window, "keydown", BX.proxy(this.OnKeyPress, this));

		this.pLEditor.bPopup = true;
		obLHEDialog.Show();
		obLHEDialog.SetContent(oDialog.innerHTML);

		if (oDialog.OnLoad && typeof oDialog.OnLoad == 'function')
			oDialog.OnLoad();

		obLHEDialog.oDialog = oDialog;
		obLHEDialog.SetButtons([
			new BX.CWindowButton(
				{
					title: BX.message.DialogSave,
					action: function()
					{
						var res = true;
						if (oDialog.OnSave && typeof oDialog.OnSave == 'function')
						{
							_this.pLEditor.RestoreSelectionRange();
							res = oDialog.OnSave();
						}
						if (res !== false)
							window.obLHEDialog.Close();
					}
				}),
			obLHEDialog.btnCancel
		]);
		BX.addClass(obLHEDialog.PARTS.CONTENT, "lhe-dialog");

		obLHEDialog.adjustSizeEx();
		// Hack for Opera
		setTimeout(function(){obLHEDialog.Move(1, 1);}, 100);
	},

	OnKeyPress: function(e)
	{
		if(!e)
			e = window.event
		if (e.keyCode == 13)
			obLHEDialog.PARAMS.buttons[0].emulate();
	},

	Close: function(floatDiv)
	{
		this.RemoveOverlay();
		if (!floatDiv)
			floatDiv = this.floatDiv;
		if (!floatDiv || !floatDiv.parentNode)
			return;

		this.pLEditor.bDialogOpened = false;
		jsFloatDiv.Close(floatDiv);
		floatDiv.parentNode.removeChild(floatDiv);
		if (window.jsPopup)
			jsPopup.AllowClose();
	},

	CreateOverlay: function()
	{
		var ws = BX.GetWindowScrollSize();
		this.overlay = document.body.appendChild(BX.create("DIV", {props: {id: this.overlay_id, className: "lhe-overlay"}, style: {zIndex: this.zIndex - 5, width: ws.scrollWidth + "px", height: ws.scrollHeight + "px"}}));
		this.overlay.ondrag = BX.False;
		this.overlay.onselectstart = BX.False;
	},

	RemoveOverlay: function()
	{
		if (this.overlay && this.overlay.parentNode)
			this.overlay.parentNode.removeChild(this.overlay);
	}
}

// List
function LHEList(oBut, pLEditor)
{
	if (!oBut.name)
		oBut.name = oBut.id;
	if (!oBut.title)
		oBut.title = oBut.name;
	this.disabled = false;
	this.zIndex = 5000;

	this.pLEditor = pLEditor;
	this.oBut = oBut;
	this.Create();
	this.bRunOnOpen = false;
	if (this.oBut && typeof this.oBut.OnBeforeCreate == 'function')
		this.oBut = this.oBut.OnBeforeCreate(this.pLEditor, this.oBut);

	if (this.oBut)
	{
		if (oBut.OnCreate && typeof oBut.OnCreate == 'function')
			this.bRunOnOpen = true;

		if (this.oBut.disableOnCodeView)
			BX.addCustomEvent(this.pLEditor, "OnChangeView", BX.proxy(this.OnChangeView, this));
	}
	else
	{
		BX.defer(function(){BX.remove(this.pCont);}, this)();
	}
}

LHEList.prototype = {
	Create: function ()
	{
		var _this = this;

		this.pWnd = BX.create("IMG", {props: {src: this.pLEditor.oneGif, title: this.oBut.title, className: "lhe-button lhe-button-normal", id: "lhe_btn_" + this.oBut.id.toLowerCase()}});

		this.pWnd.onmouseover = function(e){_this.OnMouseOver(e, this)};
		this.pWnd.onmouseout = function(e){_this.OnMouseOut(e, this)};
		this.pWnd.onmousedown = function(e){_this.OnClick(e, this)};

		this.pCont = BX.create("DIV", {props: {className: 'lhe-button-cont'}});
		this.pCont.appendChild(this.pWnd);

		this.pValuesCont = BX.create("DIV", {props: {className: "lhe-list-val-cont"}, style: {zIndex: this.zIndex}});

		if (this.oBut && typeof this.oBut.OnAfterCreate == 'function')
			this.oBut.OnAfterCreate(this.pLEditor, this);
	},

	OnChangeView: function()
	{
		if (this.oBut.disableOnCodeView)
			this.Disable(this.pLEditor.sEditorMode == 'code');
	},

	Disable: function (bFlag)
	{
		if(bFlag == this.disabled)
			return false;
		this.disabled = bFlag;
		if(bFlag)
			BX.addClass(this.pWnd, 'lhe-button-disabled');
		else
			BX.removeClass(this.pWnd, 'lhe-button-disabled');
	},

	OnMouseOver: function (e, pEl)
	{
		if(this.disabled)
			return;
		BX.addClass(pEl, 'lhe-button-over');
	},

	OnMouseOut: function (e, pEl)
	{
		if(this.disabled)
			return;

		BX.removeClass(pEl, 'lhe-button-over');
		if(this.checked)
			BX.addClass(pEl, 'lhe-button-checked');

		// if(this.checked)
		// pEl.className = 'lhe-button lhe-button-checked';
		// else
		// pEl.className = 'lhe-button lhe-button-normal';
	},

	OnKeyPress: function(e)
	{
		if(!e) e = window.event
		if(e.keyCode == 27)
			this.Close();
	},

	OnClick: function (e, pEl)
	{
		this.pLEditor.SaveSelectionRange();

		if(this.disabled)
			return false;

		if (this.bOpened)
			return this.Close();

		this.Open();
	},

	Close: function ()
	{
		this.pValuesCont.style.display = 'none';
		this.pLEditor.oTransOverlay.Hide();

		BX.unbind(window, "keypress", BX.proxy(this.OnKeyPress, this));
		BX.unbind(document, 'mousedown', BX.proxy(this.CheckClose, this));

		this.bOpened = false;
	},

	CheckClose: function(e)
	{
		if (!this.bOpened)
			return BX.unbind(document, 'mousedown', BX.proxy(this.CheckClose, this));

		var pEl;
		if (e.target)
			pEl = e.target;
		else if (e.srcElement)
			pEl = e.srcElement;
		if (pEl.nodeType == 3)
			pEl = pEl.parentNode;

		if (!BX.findParent(pEl, {className: 'lhe-colpick-cont'}))
			this.Close();
	},

	Open: function ()
	{
		if (this.bRunOnOpen)
		{
			if (this.oBut.OnCreate && typeof this.oBut.OnCreate == 'function')
				this.oBut.OnCreate(this);
			this.bRunOnOpen = false;
		}

		document.body.appendChild(this.pValuesCont);

		this.pValuesCont.style.display = 'block';
		var
			pOverlay = this.pLEditor.oTransOverlay.Show(),
			pos = BX.align(BX.pos(this.pWnd), parseInt(this.pValuesCont.offsetWidth) || 150, parseInt(this.pValuesCont.offsetHeight) || 200),
			_this = this;

		BX.bind(window, "keypress", BX.proxy(this.OnKeyPress, this));
		pOverlay.onclick = function(){_this.Close()};

		this.pLEditor.oPrevRange = this.pLEditor.GetSelectionRange();
		if (this.oBut.OnOpen && typeof this.oBut.OnOpen == 'function')
			this.oBut.OnOpen(this);

		this.pValuesCont.style.top = pos.top + 'px';
		this.pValuesCont.style.left = pos.left + 'px';
		this.bOpened = true;

		setTimeout(function()
		{
			BX.bind(document, 'mousedown', BX.proxy(_this.CheckClose, _this));
		},100);
	},

	SelectItem: function(bSelect)
	{
		var pItem = this.arItems[this.pSelectedItemId || 0].pWnd;
		if (bSelect)
		{
			pItem.style.border = '1px solid #4B4B6F';
			pItem.style.backgroundColor = '#FFC678';
		}
		else
		{
			pItem.style.border = '';
			pItem.style.backgroundColor = '';
		}
	}
}

function LHETransOverlay(arParams, pLEditor)
{
	this.pLEditor = pLEditor;
	this.id = 'lhe_trans_overlay';
	this.zIndex = arParams.zIndex || 100;
}

LHETransOverlay.prototype =
{
	Create: function ()
	{
		this.bCreated = true;
		this.bShowed = false;
		var ws = BX.GetWindowScrollSize();
		this.pWnd = document.body.appendChild(BX.create("DIV", {props: {id: this.id, className: "lhe-trans-overlay"}, style: {zIndex: this.zIndex, width: ws.scrollWidth + "px", height: ws.scrollHeight + "px"}}));

		this.pWnd.ondrag = BX.False;
		this.pWnd.onselectstart = BX.False;
	},

	Show: function(arParams)
	{
		if (!this.bCreated)
			this.Create();
		this.bShowed = true;
		this.pLEditor.bPopup = true;

		var ws = BX.GetWindowScrollSize();

		this.pWnd.style.display = 'block';
		this.pWnd.style.width = ws.scrollWidth + "px";
		this.pWnd.style.height = ws.scrollHeight + "px";

		if (!arParams)
			arParams = {};

		if (arParams.zIndex)
			this.pWnd.style.zIndex = arParams.zIndex;

		BX.bind(window, "resize", BX.proxy(this.Resize, this));
		return this.pWnd;
	},

	Hide: function ()
	{
		var _this = this;
		setTimeout(function(){_this.pLEditor.bPopup = false;}, 50);
		if (!this.bShowed)
			return;
		this.bShowed = false;
		this.pWnd.style.display = 'none';
		BX.unbind(window, "resize", BX.proxy(this.Resize, this));
		this.pWnd.onclick = null;
	},

	Resize: function ()
	{
		if (this.bCreated)
			this.pWnd.style.width = BX.GetWindowScrollSize().scrollWidth + "px";
	}
}


function LHEColorPicker(oPar, pLEditor)
{
	if (!oPar.name)
		oPar.name = oPar.id;
	if (!oPar.title)
		oPar.title = oPar.name;
	this.disabled = false;
	this.bCreated = false;
	this.bOpened = false;
	this.zIndex = 5000;

	this.pLEditor = pLEditor;

	this.oPar = oPar;
	this.BeforeCreate();
}

LHEColorPicker.prototype = {
	BeforeCreate: function()
	{
		var _this = this;
		this.pWnd = BX.create("IMG", {props: {src: this.pLEditor.oneGif, title: this.oPar.title, className: "lhe-button lhe-button-normal", id: "lhe_btn_" + this.oPar.id.toLowerCase()}});

		this.pWnd.onmouseover = function(e){_this.OnMouseOver(e, this)};
		this.pWnd.onmouseout = function(e){_this.OnMouseOut(e, this)};
		this.pWnd.onmousedown = function(e){_this.OnClick(e, this)};
		this.pCont = BX.create("DIV", {props: {className: 'lhe-button-cont'}});
		this.pCont.appendChild(this.pWnd);

		if (this.oPar && typeof this.oPar.OnBeforeCreate == 'function')
			this.oPar = this.oPar.OnBeforeCreate(this.pLEditor, this.oPar);

		if (this.oPar.disableOnCodeView)
			BX.addCustomEvent(this.pLEditor, "OnChangeView", BX.proxy(this.OnChangeView, this));
	},

	Create: function ()
	{
		var _this = this;
		this.pColCont = document.body.appendChild(BX.create("DIV", {props: {className: "lhe-colpick-cont"}, style: {zIndex: this.zIndex}}));

		var
			arColors = this.pLEditor.arColors,
			row, cell, colorCell,
			tbl = BX.create("TABLE", {props: {className: 'lha-colpic-tbl'}}),
			i, l = arColors.length;

		row = tbl.insertRow(-1);
		cell = row.insertCell(-1);
		cell.colSpan = 8;
		var defBut = cell.appendChild(BX.create("SPAN", {props: {className: 'lha-colpic-def-but'}, text: BX.message.DefaultColor}));
		defBut.onmouseover = function()
		{
			this.className = 'lha-colpic-def-but lha-colpic-def-but-over';
			colorCell.style.backgroundColor = 'transparent';
		};
		defBut.onmouseout = function(){this.className = 'lha-colpic-def-but';};
		defBut.onmousedown = function(e){_this.Select(false);}

		colorCell = row.insertCell(-1);
		colorCell.colSpan = 8;
		colorCell.className = 'lha-color-inp-cell';
		colorCell.style.backgroundColor = arColors[38];

		for(i = 0; i < l; i++)
		{
			if (Math.round(i / 16) == i / 16) // new row
				row = tbl.insertRow(-1);

			cell = row.insertCell(-1);
			cell.innerHTML = '&nbsp;';
			cell.className = 'lha-col-cell';
			cell.style.backgroundColor = arColors[i];
			cell.id = 'lhe_color_id__' + i;

			cell.onmouseover = function (e)
			{
				this.className = 'lha-col-cell lha-col-cell-over';
				colorCell.style.backgroundColor = arColors[this.id.substring('lhe_color_id__'.length)];
			};
			cell.onmouseout = function (e){this.className = 'lha-col-cell';};
			cell.onmousedown = function (e)
			{
				var k = this.id.substring('lhe_color_id__'.length);
				_this.Select(arColors[k]);
			};
		}

		this.pColCont.appendChild(tbl);
		this.bCreated = true;
	},

	OnChangeView: function()
	{
		if (this.oPar.disableOnCodeView)
			this.Disable(this.pLEditor.sEditorMode == 'code');
	},

	Disable: function (bFlag)
	{
		if(bFlag == this.disabled)
			return false;
		this.disabled = bFlag;
		if(bFlag)
			BX.addClass(this.pWnd, 'lhe-button-disabled');
		else
			BX.removeClass(this.pWnd, 'lhe-button-disabled');
	},

	OnClick: function (e, pEl)
	{
		this.pLEditor.SaveSelectionRange();

		if(this.disabled)
			return false;

		if (!this.bCreated)
			this.Create();

		if (this.bOpened)
			return this.Close();

		this.Open();
	},

	Open: function ()
	{
		var
			pOverlay = this.pLEditor.oTransOverlay.Show(),
			pos = BX.align(BX.pos(this.pWnd), 325, 155),
			_this = this;

		this.pLEditor.oPrevRange = this.pLEditor.GetSelectionRange();

		BX.bind(window, "keypress", BX.proxy(this.OnKeyPress, this));
		pOverlay.onclick = function(){_this.Close()};

		this.pColCont.style.display = 'block';
		this.pColCont.style.top = pos.top + 'px';
		this.pColCont.style.left = pos.left + 'px';
		this.bOpened = true;

		setTimeout(function()
		{
			BX.bind(document, 'mousedown', BX.proxy(_this.CheckClose, _this));
		},100);
	},

	Close: function ()
	{
		this.pColCont.style.display = 'none';
		this.pLEditor.oTransOverlay.Hide();
		BX.unbind(window, "keypress", BX.proxy(this.OnKeyPress, this));
		BX.unbind(document, 'mousedown', BX.proxy(this.CheckClose, this));

		this.bOpened = false;
	},

	CheckClose: function(e)
	{
		if (!this.bOpened)
			return BX.unbind(document, 'mousedown', BX.proxy(this.CheckClose, this));

		var pEl;
		if (e.target)
			pEl = e.target;
		else if (e.srcElement)
			pEl = e.srcElement;
		if (pEl.nodeType == 3)
			pEl = pEl.parentNode;

		if (!BX.findParent(pEl, {className: 'lhe-colpick-cont'}))
			this.Close();
	},

	OnMouseOver: function (e, pEl)
	{
		if(this.disabled)
			return;
		pEl.className = 'lhe-button lhe-button-over';
	},

	OnMouseOut: function (e, pEl)
	{
		if(this.disabled)
			return;
		pEl.className = 'lhe-button lhe-button-normal';
	},

	OnKeyPress: function(e)
	{
		if(!e) e = window.event
		if(e.keyCode == 27)
			this.Close();
	},

	Select: function (color)
	{
		this.pLEditor.RestoreSelectionRange();

		if (this.oPar.OnSelect && typeof this.oPar.OnSelect == 'function')
			this.oPar.OnSelect(color, this);

		this.Close();
	}
};

// CONTEXT MENU FOR EDITING AREA
function LHEContextMenu(arParams, pLEditor)
{
	this.zIndex = arParams.zIndex;
	this.pLEditor = pLEditor;
	this.Create();
}

LHEContextMenu.prototype = {
	Create: function()
	{
		this.pref = 'LHE_CM_' + this.pLEditor.id.toUpperCase()+'_';
		this.oDiv = document.body.appendChild(BX.create('DIV', {props: {className: 'lhe-cm', id: this.pref + '_cont'}, style: {zIndex: this.zIndex}, html: '<table><tr><td class="lhepopup"><table id="' + this.pref + '_cont_items"><tr><td></td></tr></table></td></tr></table>'}));

		// Part of logic of JCFloatDiv.Show()   Prevent bogus rerendering window in IE... And SpeedUp first context menu calling
		document.body.appendChild(BX.create('IFRAME', {props: {id: this.pref + '_frame', src: "javascript:void(0)"}, style: {position: 'absolute', zIndex: this.zIndex - 5, left: '-1000px', top: '-1000px', visibility: 'hidden'}}));
		this.menu = new PopupMenu(this.pref + '_cont');
	},

	Show: function(arParams)
	{
		if (!arParams.pElement || !this.FetchAndBuildItems(arParams.pElement))
			return;

		try{this.pLEditor.SelectElement(arParams.pElement);}catch(e){}
		this.pLEditor.oPrevRange = this.pLEditor.GetSelectionRange();
		this.oDiv.style.width = parseInt(this.oDiv.firstChild.offsetWidth) + 'px';

		var
			_this = this,
			w = parseInt(this.oDiv.offsetWidth),
			h = parseInt(this.oDiv.offsetHeight),
			pOverlay = this.pLEditor.oTransOverlay.Show();
		pOverlay.onclick = function(){_this.Close()};
		BX.bind(window, "keypress", BX.proxy(this.OnKeyPress, this));

		arParams.oPos.right = arParams.oPos.left + w;
		arParams.oPos.bottom = arParams.oPos.top;

		this.menu.PopupShow(arParams.oPos);
	},

	Close: function()
	{
		this.menu.PopupHide();
		this.pLEditor.oTransOverlay.Hide();
		BX.unbind(window, "keypress", BX.proxy(this.OnKeyPress, this));
	},

	FetchAndBuildItems: function(pElement)
	{
		var pElementTemp,
			i, k,
			arMenuItems = [],
			arUsed = {},
			strPath, strPath1,
			__bxtagname = false;
		this.arSelectedElement = {};

		//Adding elements
		while(pElement && (pElementTemp = pElement.parentNode) != null)
		{
			if(pElementTemp.nodeType == 1 && pElement.tagName && (strPath = pElement.tagName.toUpperCase()) && strPath != 'TBODY' && !arUsed[strPath])
			{
				strPath1 = strPath;
				if (pElement.getAttribute && (__bxtagname = pElement.getAttribute('__bxtagname')))
					strPath1 = __bxtagname.toUpperCase();

				arUsed[strPath] = pElement;
				if(LHEContMenu[strPath1])
				{
					this.arSelectedElement[strPath1] = pElement;
					if (arMenuItems.length > 0)
						arMenuItems.push('separator');
					for(i = 0, k = LHEContMenu[strPath1].length; i < k; i++)
						arMenuItems.push(LHEContMenu[strPath1][i]);
				}
			}
			else
			{
				pElement = pElementTemp;
				continue;
			}
		}

		if (arMenuItems.length == 0)
			return false;

		//Cleaning menu
		var contTbl = document.getElementById(this.pref + '_cont_items');
		while(contTbl.rows.length>0)
			contTbl.deleteRow(0);
		return this.BuildItems(arMenuItems, contTbl);
	},

	BuildItems: function(arMenuItems, contTbl, parentName)
	{
		var n = arMenuItems.length;
		var _this = this;
		var arSubMenu = {};
		this.subgroup_parent_id = '';
		this.current_opened_id = '';

		var _hide = function()
		{
			var cs = document.getElementById("__curent_submenu");
			if (!cs)
				return;
			_over(cs);
			_this.current_opened_id = '';
			_this.subgroup_parent_id = '';
			cs.style.display = "none";
			cs.id = "";
		};

		var _over = function(cs)
		{
			if (!cs)
				return;
			var t = cs.parentNode.nextSibling;
			t.parentNode.className = '';
		};

		var _refresh = function() {setTimeout(function() {_this.current_opened_id = '';_this.subgroup_parent_id = '';}, 400);}
		var i, row, cell, el_params, _atr, _innerHTML, oItem;

		//Creation menu elements
		for(var i = 0; i < n; i++)
		{
			oItem = arMenuItems[i];
			row = contTbl.insertRow(-1);
			cell = row.insertCell(-1);
			if(oItem == 'separator')
			{
				cell.innerHTML = '<div class="popupseparator"></div>';
			}
			else
			{
				if (oItem.isgroup)
				{
					var c = BX.browser.IsIE() ? 'arrow_ie' : 'arrow';
					cell.innerHTML =
						'<div id="_oSubMenuDiv_' + oItem.id + '" style="position: relative;"></div>'+
							'<table cellpadding="0" cellspacing="0" class="popupitem" id="'+oItem.id+'">'+
							'	<tr>'+
							'		<td class="gutter"></td>'+
							'		<td class="item">' + oItem.name + '</td>' +
							'		<td class="'+c+'"></td>'+
							'	</tr>'+
							'</table>';
					var oTable = cell.childNodes[1];
					var _LOCAL_CACHE = {};
					arSubMenu[oItem.id] = oItem.elements;

					oTable.onmouseover = function(e)
					{
						var pTbl = this;
						pTbl.className = 'popupitem popupitemover';
						_over(document.getElementById("__curent_submenu"));
						setTimeout(function()
						{
							//pTbl.parentNode.className = 'popup_open_cell';
							if (_this.current_opened_id && _this.current_opened_id == _this.subgroup_parent_id)
							{
								_refresh();
								return;
							}
							if (pTbl.className == 'popupitem')
								return;
							_hide();
							_this.current_opened_id = pTbl.id;

							var _oSubMenuDiv = document.getElementById("_oSubMenuDiv_" + pTbl.id);
							var left = parseInt(oTable.offsetWidth) + 1 + 'px';
							var oSubMenuDiv = BX.create('DIV', {props: {className : 'popupmenu'}, style: {position: 'absolute', zIndex: 1500, left: left, top: '-1px'}});

							_oSubMenuDiv.appendChild(oSubMenuDiv);
							oSubMenuDiv.onmouseover = function(){pTbl.parentNode.className = 'popup_open_cell';};

							var contTbl = oSubMenuDiv.appendChild(BX.create('TABLE', {props: {cellPadding:0, cellSpacing:0}}));
							_this.BuildItems(arSubMenu[pTbl.id], contTbl, pTbl.id);

							oSubMenuDiv.style.display = "block";
							oSubMenuDiv.id = "__curent_submenu";
						}, 400);
					};
					oTable.onmouseout = function(e){this.className = 'popupitem';};
					continue;
				}

				_innerHTML =
					'<table class="popupitem" id="lhe_cm__' + oItem.id + '"><tr>' +
						'	<td class="gutter"><div class="lhe-button" id="lhe_btn_' + oItem.id.toLowerCase()+'"></div></td>' +
						'	<td class="item">' + (oItem.name_edit || oItem.name) + '</td>' +
						'</tr></table>';
				cell.innerHTML = _innerHTML;

				var oTable = cell.firstChild;
				oTable.onmouseover = function(e){this.className='popupitem popupitemover';}
				oTable.onmouseout = function(e){this.className = 'popupitem';};
				oTable.onmousedown = function(e){_this.OnClick(this);};
			}
		}

		this.oDiv.style.width = contTbl.parentNode.offsetWidth;
		return true;
	},

	OnClick: function(pEl)
	{
		var oItem = LHEButtons[pEl.id.substring('lhe_cm__'.length)];
		if(!oItem || oItem.disabled)
			return false;
		this.pLEditor.RestoreSelectionRange();

		var res = false;

		if(oItem.handler)
			res = oItem.handler(this) !== false;

		if(!res && oItem.cmd)
		{
			this.pLEditor.executeCommand(oItem.cmd);
			this.pLEditor.SetFocus();
		}

		this.Close();
	},

	OnKeyPress: function(e)
	{
		if(!e) e = window.event

		if(e.keyCode == 27)
			this.Close();
	}
}