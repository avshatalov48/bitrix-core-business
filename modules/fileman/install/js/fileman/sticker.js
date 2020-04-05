function BXSticker(Params, Stickers, MESS)
{
	this.MESS = MESS;
	this.Stickers = Stickers || [];
	this.Params = Params;
	this.sessid_get = Params.sessid_get;
	this.bShowStickers = Params.bShowStickers;
	this.curEditorStickerInd = false;
	this.oneGifSrc = '/bitrix/images/1.gif';
	this.colorSchemes = [
		{name: 'bxst-yellow', color: '#FFFCB3', title: this.MESS.Yellow},
		{name: 'bxst-green', color: '#DBFCCD', title: this.MESS.Green},
		{name: 'bxst-blue', color: '#DCE7F7', title: this.MESS.Blue},
		{name: 'bxst-red', color: '#FCDFDF', title: this.MESS.Red},
		{name: 'bxst-purple', color: '#F6DAF8', title: this.MESS.Purple},
		{name: 'bxst-gray', color: '#F5F5F5', title: this.MESS.Gray}
	];

	this.curPageCount = this.Params.curPageCount;

	// Init hotkeys
	if (this.Params.useHotkeys)
		BX.bind(document, 'keyup', BX.proxy(this.OnKeyUp, this));

	// Object contains result from ajax requests
	window.__bxst_result = {};

	if (Params.bShowStickers)
		this.Init(Params);
}

BXSticker.prototype = {
	Init: function(Params)
	{
		this.oMarkerConfig = {
			attr: {
				title : true,
				src : true,
				href : true,
				alt : true,
				'class' : true,
				className : true,
				id : true,
				name : true,
				type : true,
				value : true
			},
			impAttr: {
				src : true,
				id : true,
				name : true,
				href : true
			}
		};

		this.Params.changeColorEffect = true;
		this.arStickers = [];
		this.posReg = {};
		this.bInited = true;
		this.access = this.Params.access;

		this._arSavedStickers = {};

		BX.bind(document, 'mousedown', BX.proxy(this.OnMousedown, this));
		var _this = this;
		BX.addCustomEvent('onMenuOpen', function(){
			var pEl = BX.findChild(BX('bxst-show-sticker-icon'), {className: 'icon'}, true);
			if (pEl)
			{
				if (_this.bShowStickers)
					BX.addClass(pEl, "checked");
				else
					BX.removeClass(pEl, "checked");
			}
			_this.UpdateStickersCount();
		});

		this.DisplayStickers(!!Params.bVisEffects);

		this.ShowEditor({ind: -1});
	},

	ShowAll: function(bShow, bAddStickers)
	{
		if (typeof bShow == 'undefined')
			bShow = !this.bShowStickers;

		var _this = this;
		var pEl = BX.findChild(BX('bxst-show-sticker-icon'), {className: 'icon'}, true);
		if (pEl)
		{
			if (bShow)
				BX.addClass(pEl, "checked");
			else
				BX.removeClass(pEl, "checked");
		}

		this.bShowStickers = bShow;
		window.__bxst_result.show = null;
		window.__bxst_result.stickers = null;

		this.Request(
			bShow ? 'show_stickers' : 'hide_stickers',
			{
				pageUrl : this.Params.pageUrl,
				b_inited : this.bInited ? "Y" : "N"
			},
			function(res)
			{
				if (_this.bInited)
					return;

				_this.bShowStickers = window.__bxst_result.show;
				if (window.__bxst_result.stickers)
				{
					_this.Stickers = window.__bxst_result.stickers;
					_this.Params.bVisEffects = true;
					if (!_this.bInited)
						_this.Init(_this.Params);

					if (bAddStickers)
						_this.AddSticker();
				}
			}
		);

		if (!bShow)
		{
			this.HideAll();
		}
		else if(bShow && this.bInited)
		{
			var oSt;
			for (var i = 0, l = this.arStickers.length; i < l; i++)
			{
				oSt = this.arStickers[i];
				oSt.pWin.Get().style.display = "block";
				oSt.pShadow.style.display = "block";

				//Hide marker if it exist
				if (oSt.pMarker)
					oSt.pMarker.style.display = "";
			}
		}
	},

	HideAll: function()
	{
		var oSt;
		for (var i = 0, l = this.arStickers.length; i < l; i++)
		{
			oSt = this.arStickers[i];
			oSt.pWin.Get().style.display = "none";
			oSt.pShadow.style.display = "none";

			//Hide marker if it exist
			if (oSt.pMarker)
				oSt.pMarker.style.display = "none";
		}
	},

	AddSticker: function(Sticker, bVisEffects, bShowEditor)
	{
		if (!this.bInited)
			return this.ShowAll(true, true);

		if(!this.bShowStickers && this.bInited)
			this.ShowAll(true, false);

		if (this.curEditorStickerInd !== false) // If we press add sticker hot key in the
		{
			var _this = this;
			this.SaveAndCloseEditor(this.curEditorStickerInd, true, true);
			return setTimeout(function(){_this.AddSticker(Sticker, bVisEffects, bShowEditor);}, 300);
		}

		var oSticker;
		if (Sticker)
		{
			oSticker = this.ConvertStickerObj(Sticker);
		}
		else
		{
			oSticker = {
				bNew: true,
				personal: false,
				colorInd: parseInt(this.Params.start_color),
				width: parseInt(this.Params.start_width),
				height: parseInt(this.Params.start_height),
				collapsed: false,
				completed: false,
				info: "&nbsp;"
			};
		}

		var ind = this.CreateWindow(oSticker, !!bVisEffects, bShowEditor);

		if (oSticker.bNew)
			this.SetMarker(ind, 'area');
	},

	CreateWindow: function(oSticker, bVisEffects, bShowEditor)
	{
		// Init common window object with basic functionality
		var pWin = new BX.CWindow(false, 'float');
		pWin.Show(true); // Show window
		pWin.Get().style.zIndex = pWin.zIndex = this.Params.zIndex;

		// Set resize limits
		pWin.SETTINGS.min_width = this.Params.min_width;
		pWin.SETTINGS.min_height = this.Params.min_height;
		BX.addClass(pWin.Get(), 'bx-sticker');
		pWin.DenyClose();

		var
			bReadonly = this.access == 'R',
			bNew = !!oSticker.bNew,
			_this = this,
			pTypeCont,
			ind = this.arStickers.length,// Index of element in arStickers array
			pHead = pWin.Get().appendChild(BX.create("DIV", {props: {className: 'bxst-header', id: 'bxst_head_' + ind}})),
			pIdsCont = pHead.appendChild(BX.create("DIV", {props: {className: 'bxst-id-cont bxst-title'}, html: oSticker.id > 0 ? '<a href="' + this.Params.pageUrl + "?show_sticker=" + oSticker.id + '"><span>' + oSticker.id + '</span></a>' : ''})),
			pCheckCont = pHead.appendChild(BX.create("DIV", {props: {className: 'bxst-check-cont'}})),
			pCheck = pCheckCont.appendChild(BX.create("INPUT", {props: {id: 'bxst_conplited_' + ind, name: 'bxst_conplited_' + ind, type: "checkbox", value: "Y", title: this.MESS.Complete}})),
			pCheckLabel = pCheckCont.appendChild(BX.create("LABEL", {attrs: {'for' : 'bxst_conplited_' + ind, title: this.MESS.Complete}, text: this.MESS.CompleteLabel})),
			pCollapsedTitle = pHead.appendChild(BX.create("DIV", {props: {id: 'bxst_col_title_' + ind, className: 'bxst-col-title-cont', title: this.MESS.UnCollapseTitle}})),
			pCloseBut = pHead.appendChild(BX.create("DIV", {props: {className: 'bxst-close bxst-but', title: this.MESS.Close}})).appendChild(BX.create("IMG", {props: {id: 'bxst_close_' + ind, src: this.oneGifSrc, className: 'bxst-sprite'}})),
			pCollapseBut = pHead.appendChild(BX.create("DIV", {props: {className: 'bxst-collapse bxst-but'}})).appendChild(BX.create("IMG", {props: {id: 'bxst_collapse_' + ind, src: this.oneGifSrc, className: 'bxst-sprite', title: this.MESS.Collapse}}));

		if (bNew || this.Params.curUserId == oSticker.authorId)
		{
			pTypeCont = pHead.appendChild(BX.create("DIV", {props: {id: 'bxst_type_' + ind, className: 'bxst-type-cont'}}));
			// Create type selector personal-public
			pTypeCont.appendChild(BX.create("DIV", {props: {className: 'bxst-type-l bxst-type-corn'}}));
			pTypeCont.appendChild(BX.create("DIV", {props: {className: 'bxst-type-c bxst-type-c-publ'}})).appendChild(BX.create("SPAN", {props: {}, text: this.MESS.Public}));
			pTypeCont.appendChild(BX.create("DIV", {props: {className: 'bxst-type-c  bxst-type-c-pers'}})).appendChild(BX.create("SPAN", {props: {}, text: this.MESS.Personal}));
			pTypeCont.appendChild(BX.create("DIV", {props: {className: 'bxst-type-r bxst-type-corn'}}));

			if (!bReadonly)
				pTypeCont.onclick = function(){if(!pWin.__stWasDragged){_this.SetType(parseInt(this.id.substr('bxst_type_'.length)), true);}};

			this.SetUnselectable([pTypeCont]);
		}

		var pBody = pWin.Get().appendChild(BX.create("DIV", {props: {id: 'bxst_body_' + ind, className: 'bxst-content'}}));
		var pContentArea = pBody.appendChild(BX.create("DIV", {props: {id: 'bxst_content_' + ind, className: 'bxst-content-area'}}));

		var
			pFoot = pWin.Get().appendChild(BX.create("DIV", {props: {className: 'bxst-footer'}})),
			pMarkerAreaBut = pFoot.appendChild(BX.create("DIV", {props: {className: 'bxst-marker-area-but'}})).appendChild(BX.create("IMG", {props: {id: 'bxst_marker_but0_' + ind, src: this.oneGifSrc, className: 'bxst-sprite', title: this.MESS.SetMarkerArea}})),
			pMarkerElementBut = pFoot.appendChild(BX.create("DIV", {props: {className: 'bxst-marker-elem-but'}})).appendChild(BX.create("IMG", {props: {id: 'bxst_marker_but1_' + ind, src: this.oneGifSrc, className: 'bxst-sprite', title: this.MESS.SetMarkerEl}})),
			pColorBut = pFoot.appendChild(BX.create("DIV", {props: {className: 'bxst-ctrl-txt bxst-color-but'}})).appendChild(BX.create("SPAN", {props: {id: 'bxst_color_' + ind}, text: this.MESS.Color})),
			pAddBut = pFoot.appendChild(BX.create("DIV", {props: {className: 'bxst-ctrl-txt bxst-add-but'}})).appendChild(BX.create("SPAN", {props: {id: 'bxst_add_but_' + ind}, text: this.MESS.Add})),

			pResizer = pFoot.appendChild(BX.create("DIV", {props: {className: 'bxst-resizer'}})).appendChild(BX.create("IMG", {props: {src: this.oneGifSrc, className: 'bxst-sprite'}}));

		var pInfo = pFoot.appendChild(BX.create("DIV", {props: {className: 'bxst-info-icon'}})).appendChild(BX.create("IMG", {props: {id: 'bxst_info_' + ind, src: this.oneGifSrc, className: 'bxst-sprite'}, style: {display: bNew ? 'none' : 'block'}}));
		var pHint = new BX.CHintSimple({parent: pInfo, hint: oSticker.info});

		if (bReadonly)
			BX.addClass(pWin.Get(), 'bx-sticker-readonly');

		// Adjust position to the center of the window.
		var windowSize = BX.GetWindowInnerSize();
		var windowScroll = BX.GetWindowScrollPos();

		if (bNew || oSticker.left <= 0 || oSticker.top <= 0)
		{
			oSticker.left = pWin.Get().style.left = parseInt(windowScroll.scrollLeft + windowSize.innerWidth / 2 - parseInt(pWin.Get().offsetWidth) / 2) + Math.round(oSticker.width / 2);
			oSticker.top = Math.max(parseInt(windowScroll.scrollTop + windowSize.innerHeight / 2 - parseInt(pWin.Get().offsetHeight) / 2), 0) - Math.round(oSticker.height / 2);
		}

		pWin.StickerInd = ind;

		if (bNew)
			pAddBut.style.display = 'none';

		// Create shadow
		pShadow = document.body.appendChild(BX.create("DIV", {props: {className: 'bxst-shadow'}, style: {zIndex: parseInt(pWin.Get().style.zIndex) - 5}}));

		this.RegisterSticker({
			obj: oSticker,
			pWin: pWin,
			pCheck: pCheck,
			pCloseBut: pCloseBut,
			pCollapseBut: pCollapseBut,
			pCollapsedTitle: pCollapsedTitle,
			pBody: pBody,
			pHead: pHead,
			pTypeCont: pTypeCont || false,
			pContentArea: pContentArea,
			pIdsCont: pIdsCont,
			pShadow: pShadow,
			bButPanelShowed: true,
			pMarkerAreaBut: pMarkerAreaBut,
			pMarkerElementBut: pMarkerElementBut,
			pColorBut: pColorBut,
			pAddBut: pAddBut,
			pInfo: pInfo,
			pHint: pHint,
			_over: !bNew && !bShowEditor,
			bButPanelShowed: !bNew && !bShowEditor
		});

		this.AdjustToSize(ind, oSticker.width, oSticker.height);
		this.SetColorScheme(ind, oSticker.colorInd, false);
		this.SetType(ind, false, oSticker.personal ? 'personal' : 'public');
		this.SetCompleted(ind, oSticker.completed, false);
		this.CollapseSticker(ind, false, oSticker.collapsed);

		pWin.SetDraggable(pHead);
		BX.addCustomEvent(pWin, 'onWindowDragStart', function(){this.__stWasDragged = true;});
		BX.addCustomEvent(pWin, 'onWindowDragFinished', function(){_this.OnDragEnd(this);});
		BX.addCustomEvent(pWin, 'onWindowDrag', function(){_this.OnDragDrop(this);});

		// Set and config resizer
		pWin.SetResize(pResizer);
		BX.addCustomEvent(pWin, 'onWindowResize', function(){_this.AdjustToSize(this.StickerInd);});
		BX.addCustomEvent(pWin, 'onWindowResizeStart', function(){_this.OnResizeStart(this);});
		BX.addCustomEvent(pWin, 'onWindowResizeFinished', function(){_this.OnResizeEnd(this);});

		// Control events
		pHead.ondblclick = function(){_this.CollapseSticker(parseInt(this.id.substr('bxst_head_'.length)), true);}
		pCollapseBut.onclick = function(){if(!pWin.__stWasDragged){_this.CollapseSticker(parseInt(this.id.substr('bxst_collapse_'.length)), true);}};

		if (!bReadonly)
		{
			// Control events
			pCloseBut.onclick = function(){if(!pWin.__stWasDragged){_this.CloseSticker(parseInt(this.id.substr('bxst_close_'.length)), true);}};
			//pTypeCont.onclick = function(){if(!pWin.__stWasDragged){_this.SetType(parseInt(this.id.substr('bxst_type_'.length)), true);}};
			pAddBut.onclick = function(){_this.AddToSticker(parseInt(this.id.substr('bxst_add_but_'.length)));};
			pCheck.onclick = function(){if(!pWin.__stWasDragged){_this.SetCompleted(parseInt(this.id.substr('bxst_conplited_'.length)), !!this.checked, true);}};
			pColorBut.onclick = function(){_this.ShowColorSelector(parseInt(this.id.substr('bxst_color_'.length)));};

			pMarkerAreaBut.onclick = function(){_this.SetMarker(parseInt(this.id.substr('bxst_marker_but0_'.length)), 'area');};
			pMarkerElementBut.onclick = function(){_this.SetMarker(parseInt(this.id.substr('bxst_marker_but1_'.length)),  'element');};
		}
		else
		{
			pCheck.disabled = true;
		}

		// Hide Buttons Panel instead of calling ShowButtonsPanel method
		if (!bNew && !bShowEditor && !oSticker.collapsed)
			pWin.Get().style.height = (oSticker.height - 24) + "px";

		if (bNew)
		{
			var pos = this.GetSuitablePosition(oSticker.left, oSticker.top);
			if (pos !== true)
			{
				oSticker.left = pos.left;
				oSticker.top = pos.top;
			}
		}
		else
		{
			pIdsCont.style.display = "block";
		}
		this.RegisterPosition(oSticker.left, oSticker.top);

		// Set start position
		pWin.Get().style.left = oSticker.left + 'px';
		pWin.Get().style.top = oSticker.top + 'px';

		this.AdjustShadow(ind);

		// Set unselectable elements
		this.SetUnselectable([pCloseBut, pCollapseBut, pColorBut, pMarkerAreaBut, pMarkerAreaBut, pResizer]);

		if (bNew || bShowEditor === true)
		{
			this.ShowEditor({ind: ind});

			if (bShowEditor)
			{
				this.OnDivMouseOver(ind, true);
				this.DisplayMarker(ind);
			}
		}
		else
		{
			pBody.style.overflow = 'auto';
			pContentArea.innerHTML = oSticker.html_content;
			//this.ShowButtonsPanel(ind, false, false);
			this.DisplayMarker(ind);

			if (oSticker.id == this.Params.focusOnSticker)
			{
				window.scrollTo(0, oSticker.top > 200 ? oSticker.top - 200 : 0);
				this.Hightlight(ind, true);
				this.BlinkRed(ind);
			}
		}


		if (!bReadonly)
		{
			pBody.onclick = function()
			{
				if (!this.id)
					return;
				var ind = parseInt(this.id.substr('bxst_body_'.length));
				if (_this.curEditorStickerInd !== ind)
					_this.ShowEditor({ind: ind});
			};
		}

		// Hide and show buttons panel
		pWin.Get().onmouseover = function(){_this.OnDivMouseOver(ind, true);};
		pWin.Get().onmouseout = function(){_this.OnDivMouseOver(ind, false);};

		return ind;
	},

	UpdateNewSticker: function(ind)
	{
		var oSt = this.arStickers[ind];
		oSt.pAddBut.style.display = 'block';
		oSt.pInfo.style.display = 'block';
		oSt.pIdsCont.style.display = "block";
		oSt.pIdsCont.innerHTML = '<a href="' + this.Params.pageUrl + "?show_sticker=" + oSt.obj.id + '"><span>' + oSt.obj.id + '</span></a>';

		if (ind === this.curEditorStickerInd && typeof window.oLHESticker == 'object')
		{
			setTimeout(function(){oLHESticker.SetFocusToEnd();}, 100);
			setTimeout(function(){oLHESticker.SetFocusToEnd();}, 500);
		}
	},

	RegisterPosition: function(l, t)
	{
		var
			d = 20,
			l1 = Math.round(l / d) * d,
			t1 = Math.round(t / d) * d;

		this.posReg[l1 + "_" + t1] = true;
	},

	GetSuitablePosition: function(l, t, bAdjust)
	{
		var
			d = 20,
			l1 = Math.round(l / d) * d,
			t1 = Math.round(t / d) * d;

		if (this.posReg[l1 + "_" + t1])
			return this.GetSuitablePosition(l + d, t + d, true);
		else if (bAdjust)
			return {left: l, top: t};

		return true;
	},

	RegisterSticker: function(oSt)
	{
		this.arStickers.push(oSt);
		return this.arStickers.length - 1;
	},

	AdjustToSize: function(ind, w, h)
	{
		var contHeight, oSt = this.arStickers[ind];
		if (typeof w == 'undefined' || typeof h == 'undefined')
		{
			w = parseInt(oSt.pWin.Get().style.width);
			h = parseInt(oSt.pWin.Get().style.height);
		}
		else
		{
			oSt.pWin.Get().style.width = w + "px";
			oSt.pWin.Get().style.height = h + "px";
		}

		if (BX.browser.IsIE() && !BX.browser.IsDoctype())
			contHeight = h - 19 /* header section */ - 27 /* footer section */ - 0;
		else
			contHeight = h - 19 /* header section */ - 24 /* footer section */ - 0;

		if (window.oLHESticker)
		{
			window.oLHESticker.pFrame.style.width = (w - 2)+ "px";
			window.oLHESticker.pFrame.style.height = (contHeight - 2) + "px";
			window.oLHESticker.ResizeFrame(contHeight - 2);
		}

		oSt.pCollapsedTitle.style.width = (w - 100) + "px";
		oSt.pBody.style.height = contHeight + "px";

		this.AdjustShadow(ind);
	},

	AdjustShadow: function(ind)
	{
		var oSt = this.arStickers[ind];

		if (oSt.obj.closed && oSt.pShadow.parentNode)
			return oSt.pShadow.parentNode.removeChild(oSt.pShadow);

		oSt.pShadow.style.top = (parseInt(oSt.pWin.Get().style.top) + 4) + "px";
		oSt.pShadow.style.left = (parseInt(oSt.pWin.Get().style.left) + 3) + "px";
		oSt.pShadow.style.width = oSt.pWin.Get().style.width;
		oSt.pShadow.style.height = oSt.pWin.Get().style.height;
	},

	AdjustEditorSizeAndPos: function(ind)
	{
		var oSt = this.arStickers[ind];
		this.pEditorCont.style.top = (parseInt(oSt.pWin.Get().style.top) + 20) + "px";
		this.pEditorCont.style.left = oSt.pWin.Get().style.left;
		this.pEditorCont.style.width = oSt.pWin.Get().style.width;
		this.pEditorCont.style.height = oSt.pBody.style.height;
		this.pEditorCont.style.zIndex = parseInt(oSt.pWin.Get().style.zIndex) + 10;
	},

	AdjustHintToCursor: function(pHint, e)
	{
		pHint.style.left = (e.realX + 30) + "px";
		pHint.style.top = (e.realY - 12) + "px";
	},

	AdjustScrollPosToCursor: function()
	{
	},

	AdjustStickerToArea: function(ind)
	{
		var
			x, y,
			size = BX.GetWindowInnerSize(document),
			scroll = BX.GetWindowScrollPos(document),
			oSt = this.arStickers[ind],
			deltaH = (oSt.obj.marker && oSt.obj.marker.adjust) ? 0 : 10;

		if (oSt.pMarker && oSt.obj.marker)
		{
			x = oSt.obj.marker.left + oSt.obj.marker.width - 60;
			y = oSt.obj.marker.top - oSt.obj.height + deltaH;

			if (x + oSt.obj.width > size.innerWidth)
				x = size.innerWidth - oSt.obj.width - 30;

			if (y < scroll.scrollTop + 50)
				y = oSt.obj.marker.top + oSt.obj.marker.height - deltaH;
		}

		this.MoveToPos(ind, {left: x, top: y});
		oSt.obj.top = y;
		oSt.obj.left = x;

		if (this.arStickers[ind].obj.id)
			this.SaveSticker(ind);
	},

	MoveToPos: function(ind, resPos)
	{
		var oSt = this.arStickers[ind];
		var
			startTop = parseInt(oSt.obj.top),
			startLeft = parseInt(oSt.obj.left),
			endTop = parseInt(resPos.top),
			endLeft = parseInt(resPos.left),
			curTop = parseInt(startTop),
			curLeft = parseInt(startLeft),

			_this = this,
			count = 0,
			bUp = startTop > endTop,
			bLeft = startLeft > endLeft,
			time = BX.browser.IsIE() ? 10 : 10,
			d = BX.browser.IsIE() ? 10 : 10,
			d1 = Math.ceil(Math.abs((startLeft - endLeft) / 50)),
			d2 = Math.ceil(Math.abs((startTop - endTop) / 50)),
			dx = bLeft ? -d1 : d1,
			dy = bUp ? -d2 : d2;

		var SetPos = function(t, l)
		{
			if (t !== false)
				oSt.pWin.Get().style.top = t + "px";
			if (l !== false)
				oSt.pWin.Get().style.left = l + "px";
			_this.AdjustShadow(ind);
		};

		var Interval = setInterval(function()
			{
				if (endTop != curTop && curTop !== false)
					curTop += Math.round(dy * count / 2);
				if (endLeft != curLeft && curLeft !== false)
					curLeft += Math.round(dx * count / 2);

				if (curTop !== false && (!bUp && curTop >= endTop || bUp && curTop <= endTop))
					curTop = endTop;

				if (curLeft !== false && (!bLeft && curLeft >= endLeft || bLeft && curLeft <= endLeft))
					curLeft = endLeft;

				SetPos(curTop, curLeft);

				if (curTop == endTop)
					curTop = false;

				if (curLeft == endLeft)
					curLeft = false;

				if (curTop === false && curLeft === false)
				{
					clearInterval(Interval);
					return _this.OnDragEnd(oSt.pWin);
				}
				count++;
			},
			time
		);
	},

	ChangeColor: function(ind, colorInd, bEffect, bFadeIn)
	{
		var oSt = this.arStickers[ind];
		if (!this.Params.changeColorEffect)
			bEffect = false;

		if (bEffect && bFadeIn === true)
		{
			this.Params.start_color = colorInd;
			return this.ShowColorOverlay(ind, colorInd, true);
		}
		else if((bEffect && bFadeIn === false) || !bEffect)
		{
			this.SetColorScheme(ind, colorInd, true);
			if (bEffect)
				return this.ShowColorOverlay(ind, colorInd, false);
		}
	},

	SetColorScheme: function(ind, colorInd, bSave)
	{
		// If we have editor
		if (ind === this.curEditorStickerInd && typeof window.oLHESticker == 'object')
		{
			if (window.oLHESticker.pEditorDocument && window.oLHESticker.pEditorDocument.body)
				window.oLHESticker.pEditorDocument.body.className = this.colorSchemes[colorInd].name;
		}

		this.arStickers[ind].obj.colorInd = colorInd;
		for (var i = 0, l = this.colorSchemes.length; i < l; i++)
		{
			if (i == colorInd)
				BX.addClass(this.arStickers[ind].pWin.Get(), this.colorSchemes[i].name);
			else
				BX.removeClass(this.arStickers[ind].pWin.Get(), this.colorSchemes[i].name);
		}

		if (this.arStickers[ind].pMarker)
			this.arStickers[ind].pMarker.className = 'bxst-sticker-marker ' + this.colorSchemes[colorInd].name;

		if (bSave && this.arStickers[ind].obj.id > 0)
		{
			var _this = this;
			if (this.arStickers[ind]._colTimeout)
			{
				clearTimeout(this.arStickers[ind]._colTimeout);
				this.arStickers[ind]._colTimeout = null;
			}

			// Save color with some delay for fast clicking colot controll
			_this.SaveSticker(ind);
		}
	},

	SetType: function(ind, bSave, type)
	{
		var
			oSt = this.arStickers[ind],
			bPersonal = (typeof type == 'undefined') ? !oSt.obj.personal : type == 'personal';

		if (!oSt.pTypeCont)
			return;

		if (bPersonal)
		{
			BX.addClass(oSt.pTypeCont, 'bxst-type-pers');
			BX.removeClass(oSt.pTypeCont, 'bxst-type-publ');
			oSt.pTypeCont.title = this.MESS.PersonalTitle;
		}
		else
		{
			BX.addClass(oSt.pTypeCont, 'bxst-type-publ');
			BX.removeClass(oSt.pTypeCont, 'bxst-type-pers');
			oSt.pTypeCont.title = this.MESS.PublicTitle;
		}
		oSt.obj.personal = bPersonal;

		if (oSt.obj.id && bSave) // Sticker already created - we change type and save it
			this.SaveSticker(ind);
	},

	SetCompleted: function(ind, bChecked, bSave)
	{
		this.arStickers[ind].obj.completed = bChecked;
		this.arStickers[ind].pCheck.checked = bChecked;

		if (this.arStickers[ind].obj.id && bSave)
			this.SaveSticker(ind);
	},

	CloseSticker: function(ind, bSave, bClose)
	{
		var oSt = this.arStickers[ind];
		if (bSave && oSt.obj.authorName && this.Params.curUserId != oSt.obj.authorId && !confirm(this.MESS.CloseConfirm.replace("#USER_NAME#", oSt.obj.authorName)))
			return;

		oSt.obj.closed = !oSt.obj.closed;

		if (ind === this.curEditorStickerInd)
			this.curEditorStickerInd = false;

		this.arStickers[ind].pWin.Close(true);
		this.arStickers[ind].pWin.onUnRegister(true);

		//Hide marker if it exist
		if (oSt.pMarkerNode)
			BX.removeClass(oSt.pMarkerNode, 'bxst-sicked');
		if (oSt.pMarker && oSt.pMarker.parentNode)
			oSt.pMarker.parentNode.removeChild(oSt.pMarker);

		this.AdjustShadow(ind);

		if (this.arStickers[ind].obj.id && bSave)
		{
			this.SaveSticker(ind);
			BX.admin.panel.Notify(this.MESS.CloseNotify.replace(/(.*?)#LINK#(.*?)#LINK#/ig, "$1<span class=\"bxst-close-notify-link\" onclick=\"window.oBXSticker.ShowList(\'current\'); return false;\">$2</span>"));
		}

		var a = document.body.getElementsByTagName('A');
		if (a && a[0])
			BX.focus(a[0]);
	},

	CollapseSticker: function(ind, bSave, bCollapse)
	{
		var oSt = this.arStickers[ind];

		if (typeof bCollapse == 'undefined')
			bCollapse = !oSt.obj.collapsed;

		if (bSave && this.curEditorStickerInd === ind)
			this.SaveAndCloseEditor(ind, true, false);

		if (bCollapse)
		{
			BX.addClass(oSt.pWin.Get(), "bxst-collapsed");
			oSt.pCollapseBut.title = this.MESS.UnCollapse;
			oSt.pWin.Get().style.height = '19px';
			oSt.pCollapsedTitle.innerHTML = this.GetCollapsedContent(oSt.obj.html_content);
		}
		else
		{
			BX.removeClass(oSt.pWin.Get(), "bxst-collapsed");
			oSt.pCollapseBut.title = this.MESS.Collapse;
			oSt.pWin.Get().style.height = parseInt(oSt.obj.height) + 'px';
		}

		this.AdjustShadow(ind);

		oSt.obj.collapsed = bCollapse;

		if (oSt.obj.id && bSave)
			this.SaveSticker(ind);
	},

	OnDragEnd: function(pWin)
	{
		setTimeout(function(){pWin.__stWasDragged = false;}, 200);
		var ind = pWin.StickerInd;

		this.arStickers[ind].obj.top = parseInt(pWin.Get().style.top);
		this.arStickers[ind].obj.left = parseInt(pWin.Get().style.left);

		this.SaveSticker(ind);
	},

	OnDragDrop: function(pWin)
	{
		this.AdjustShadow(pWin.StickerInd);
	},

	OnResizeEnd: function(pWin)
	{
		var ind = pWin.StickerInd;
		this.arStickers[ind].bResizingNow = false;
		this.arStickers[ind].obj.width = parseInt(pWin.Get().style.width);
		this.arStickers[ind].obj.height = parseInt(pWin.Get().style.height);

		if (this.arStickers[ind].obj.id)
			this.SaveSticker(ind);
	},

	OnResizeStart: function(pWin)
	{
		this.arStickers[pWin.StickerInd].bResizingNow = true;
	},

	ShowEditor: function(Params)
	{
		var
			bPreload = Params.ind === -1,
			_this = this,
			oSt = this.arStickers[Params.ind];

		// Create if it's necessary and move to the current sticker window
		// (We have one editor and simply append it to different sticker windows)
		if (!this.pEditorCont)
		{
			this.pEditorCont = (bPreload ? document.body : oSt.pBody).appendChild(BX.create("DIV", {props: {className: 'bxst-lhe-cont'}}));
		}

		this.pEditorCont.style.visibility = 'hidden';

		// Editor already loaded
		if (window.oLHESticker)
		{
			if (this.bLoadLHEEditor) // Fist init
			{
				this.PrepareEditorAfterLoading();
				this.bLoadLHEEditor = false;
			}

			if (!bPreload)
				this.DisplayEditor(oSt, Params.ind);
		}
		else if(!this.bLoadLHEEditor) // Init loading
		{
			this.Request('load_lhe', {}, function(res)
			{
				_this.pEditorCont.innerHTML = res;
				var interval = setInterval(function() // Timeout for DOM rendering
				{
					if (typeof window.LoadLHE_LHEBxStickers == 'undefined')
						return;

					clearInterval(interval);

					if (!_this.bLoadLHEEditor && !window.oLHESticker)
						LoadLHE_LHEBxStickers();

					return setTimeout(function()
					{
						_this.bLoadLHEEditor = true;
						_this.ShowEditor(Params);
					}, 50);
				}, 50);
			});
		}
		else if (_this.bLoadLHEEditor && !window.oLHESticker) // Waiting for loading complete
		{
			return setTimeout(function(){_this.ShowEditor(Params);}, 50);
		}
	},

	PrepareEditorAfterLoading: function()
	{
		if (!oLHESticker)
			return;

		oLHESticker.oSpecialParsers['st_title'] = {
			Parse: function(sName, sContent, pLEditor)
			{
				sContent = sContent.replace(/\[ST_TITLE\]((?:\s|\S)*?)\[\/ST_TITLE\]/ig, '<span id="'+ pLEditor.SetBxTag(false, {tag: "st_title"}) + '" class="bxst-title" >$1</span>');
				return sContent;
			},
			UnParse: function(bxTag, pNode, pLEditor)
			{
				var res = "[ST_TITLE]";
				for(i = 0; i < pNode.arNodes.length; i++)
					res += pLEditor._RecursiveGetHTML(pNode.arNodes[i]);
				res += "[/ST_TITLE]";
				return res;
			}
		};

		BX.addCustomEvent(oLHESticker, "OnUnParseContentAfter", function()
		{
			this.__sContent = this.__sContent.replace(/\[\/ST_TITLE\](?:\n|\r)+/ig, "[/ST_TITLE]\n");
		});
	},

	DisplayEditor: function(oSt, ind, bJustDisplay)
	{
		var _this = this;

		if (!bJustDisplay)
		{
			// Append editor
			oSt.pBody.appendChild(this.pEditorCont);
			this.AdjustToSize(ind);
			oLHESticker.SetContent(oSt.obj.content || (this.GetNewStickerContent() + "\n"));
			oLHESticker.CreateFrame(); // We need to recreate editable frame after reappending editor container
			oLHESticker.SetEditorContent(oLHESticker.content);
			window.oLHESticker.pEditorDocument.body.className = this.colorSchemes[oSt.obj.colorInd].name;

			if (this.Params.useHotkeys)
				BX.bind(window.oLHESticker.pEditorDocument, 'keyup', BX.proxy(this.OnKeyUp, this));

			setTimeout(function(){try{window.oLHESticker.pEditorDocument.execCommand("styleWithCSS", false, false);}catch(e){}}, 100);
			setTimeout(function(){try{window.oLHESticker.pEditorDocument.execCommand("styleWithCSS", false, false);}catch(e){}}, 500);
			setTimeout(function(){try{window.oLHESticker.pEditorDocument.execCommand("styleWithCSS", false, false);}catch(e){}}, 1000);

			this.curEditorStickerInd = ind;
			oSt.pBody.style.overflow = 'hidden';

			// Slow div motion for editor loading timeout
			var
				curTop = 0,
				d = 1,
				maxTop = 22;

			var movePanelInterval = setInterval(function()
			{
				if (curTop >= maxTop)
					curTop = maxTop;
				else
					curTop += d;

				oSt.pContentArea.style.top = curTop + "px";
				if (curTop == maxTop)
				{
					clearInterval(movePanelInterval);
					_this.DisplayEditor(oSt, ind, true);
				}
			}, BX.browser.IsIE() ? 5 : 10);
		}
		else
		{
			setTimeout(function()
			{
				oSt.pBody.style.overflow = 'auto';
				_this.pEditorCont.style.visibility = 'visible';
				oSt.pContentArea.style.display = 'none';
				_this.pEditorCont.style.display = 'block';

				setTimeout(function(){oLHESticker.SetFocusToEnd();}, 100);
			}, 100);
		}
	},

	AddToSticker: function(ind)
	{
		var oSt = this.arStickers[ind];
		if (this.curEditorStickerInd === ind && window.oLHESticker)
		{
			oLHESticker.SetFocusToEnd();
			oLHESticker.InsertHTML("<br />" + oLHESticker.ParseContent(this.GetNewStickerContent()) + "<br />");
			setTimeout(function(){oLHESticker.SetFocusToEnd();}, 100);
		}
		else
		{
			oSt.obj.content += "\n" + this.GetNewStickerContent();
			this.ShowEditor({ind: ind});
		}
	},

	Request : function(action, postParams, callBack, bShowWaitWin)
	{
		bShowWaitWin = bShowWaitWin === true;

		if (bShowWaitWin)
			BX.showWait();

		var actionUrl = '/bitrix/admin/fileman_stickers.php?sticker_action=' + action + "&" + this.sessid_get + '&site_id=' + this.Params.site_id;
		return BX.ajax.post(actionUrl, postParams || {},
			function(result)
			{
				if (bShowWaitWin)
					BX.closeWait();

				if(callBack)
					setTimeout(function(){callBack(result);}, 10);
			}
		);
	},

	SetUnselectable: function(arNodes)
	{
		if (typeof arNodes != 'object')
			arNodes = [arNodes];

		for (var i = 0, l = arNodes.length; i < l; i++)
		{
			BX.setUnselectable(arNodes[i]);
			arNodes[i].ondragstart = function (e){return BX.PreventDefault(e);};
		}
	},

	ShowColorOverlay: function(ind, colorInd, bFadeIn)
	{
		var
			_this = this,
			it = 0, interval,
			oSt = this.arStickers[ind];

		if (!this.pColorOverlay)
			this.pColorOverlay = document.body.appendChild(BX.create("DIV", {props: {className: 'bx-sticker-overlay'}}));

		this.pColorOverlay.style.zIndex = parseInt(oSt.pWin.Get().style.zIndex) + 10;
		this.pColorOverlay.style.top = oSt.pWin.Get().style.top;
		this.pColorOverlay.style.left = oSt.pWin.Get().style.left;
		this.pColorOverlay.style.width = oSt.pWin.Get().style.width;
		this.pColorOverlay.style.height = oSt.pWin.Get().style.height;

		interval = setInterval(function()
		{
			if (it > 2)
			{
				if (bFadeIn)
					_this.ChangeColor(ind, colorInd, true, false);
				else
					_this.pColorOverlay.className = 'bx-sticker-overlay';
				return clearInterval(interval);
			}

			if (bFadeIn)
				_this.pColorOverlay.className = 'bx-sticker-overlay bx-sticker-op-' + it;
			else
				_this.pColorOverlay.className = 'bx-sticker-overlay bx-sticker-op-' + (3 -it);

			it++;
		}, 20);
	},

	DisplayStickers: function(bVisEffects)
	{
		for (var i = 0, l = this.Stickers.length; i < l; i++)
			this.AddSticker(this.Stickers[i], bVisEffects);
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

	SaveAndCloseEditor: function(ind, bClose, bSaveSticker)
	{
		if (!window.oLHESticker || this.bLoadLHEEditor)
		{
			var _this = this;
			return setTimeout(function(){_this.SaveAndCloseEditor(ind, bClose);}, 100);
		}

		var oSt = this.arStickers[ind];
		oLHESticker.SaveContent();
		var content = oLHESticker.GetContent();
		var htmlContent = oLHESticker.ParseContent(content);

		oSt.obj.html_content = htmlContent;
		oSt.pContentArea.innerHTML = htmlContent;
		this.arStickers[ind].obj.content = content;

		if (bClose !== false)
		{
			oSt.pContentArea.style.display = 'block';
			this.pEditorCont.style.display = 'none';
			oSt.pContentArea.style.top = '0px';
			oSt.pBody.style.overflow = 'auto';
			this.curEditorStickerInd = false;
		}

		if (bSaveSticker !== false)
			this.SaveSticker(ind);
	},

	GetNewStickerContent: function()
	{
		var strDate = BX.date.format(BX.date.convertBitrixFormat(BX.message('FORMAT_DATETIME')));
		return "[ST_TITLE]" + BX.util.htmlspecialchars(this.Params.curUserName) + ' ' + strDate + "[/ST_TITLE]\n";
	},

	SaveSticker: function(ind)
	{
		if (this.access == 'R') // Readonly
			return;

		if (this.curEditorStickerInd === ind)
			this.SaveAndCloseEditor(ind, false, false);

		var oSt = this.arStickers[ind];
		var _this = this;
		var reqid = Math.round(Math.random() * 100000);
		window.__bxst_result[reqid] = false;

		if (typeof oSt.obj.content == 'undefined')
			oSt.obj.content = this.GetNewStickerContent() + "\n";

		if (oSt.obj.bNew)
		{
			if (this._arSavedStickers[ind]) // prevent double saving
				return;
			this._arSavedStickers[ind] = true;
		}

		this.Request('save_sticker',
			{
				reqid : reqid,
				id: oSt.obj.bNew ? 0 : oSt.obj.id,
				page_url: this.Params.pageUrl,
				page_title: this.Params.pageTitle,

				personal: oSt.obj.personal ? 'Y' : 'N',
				content: oSt.obj.content,

				width: oSt.obj.width,
				height: oSt.obj.height,
				top: oSt.obj.top,
				left: oSt.obj.left,
				color: oSt.obj.colorInd,

				collapsed: oSt.obj.collapsed ? 'Y' : 'N',
				completed: oSt.obj.completed ? 'Y' : 'N',
				closed: oSt.obj.closed ? 'Y' : 'N',

				marker: oSt.obj.marker
			},
			function()
			{
				if (window.__bxst_result[reqid])
				{
					var bNew = !!oSt.obj.bNew;
					_this.arStickers[ind].obj = _this.ConvertStickerObj(window.__bxst_result[reqid]);
					if (_this.arStickers[ind].pHint)
					{
						_this.arStickers[ind].pHint.HINT = _this.arStickers[ind].obj.info;
						if (_this.arStickers[ind].pHint.CONTENT_TEXT)
							_this.arStickers[ind].pHint.CONTENT_TEXT.innerHTML = _this.arStickers[ind].obj.info;
					}

					if (bNew)
					{
						_this.UpdateNewSticker(ind);

						if (!_this.arStickers[ind].obj.closed)
						{
							_this.curPageCount++;
							_this.UpdateStickersCount();
						}
					}
					else
					{
						if (_this.arStickers[ind].obj.closed)
						{
							_this.curPageCount--;
							_this.UpdateStickersCount();
						}
					}
				}
				window.__bxst_result[reqid] = null;
			}
		);
	},

	GetCollapsedContent: function(content)
	{
		var colContent = '';
		if (content.indexOf('bxst-title') != -1)
		{
			colContent = content.replace(/<span[^>]*?class="bxst-title"[^>]*?>((?:\s|\S)*?)<\/span>/ig, function(str, title)
			{
				if (title.indexOf(String.fromCharCode(160)) > 0)
					return '<span class="bxst-title">' + title.substr(0, title.indexOf(String.fromCharCode(160))) + "</span> ";
				return title;
			});

			colContent = colContent.replace(/<br( \/)?>/ig, ' ');
		}
		// else
		// {

		// }

		if (colContent != '')
			return colContent;

		return content;
	},

	ConvertStickerObj: function(Sticker)
	{
		return {
			bNew: false,
			id: parseInt(Sticker.ID),
			personal: Sticker.PERSONAL == 'Y',
			colorInd: Sticker.COLOR || 0,
			content: Sticker.CONTENT,
			html_content: Sticker.HTML_CONTENT,
			top: parseInt(Sticker.POS_TOP),
			left: parseInt(Sticker.POS_LEFT),
			width: parseInt(Sticker.WIDTH),
			height: parseInt(Sticker.HEIGHT),
			collapsed: Sticker.COLLAPSED == 'Y',
			completed: Sticker.COMPLETED == 'Y',
			closed: Sticker.CLOSED == 'Y',
			info: Sticker.INFO,
			authorName: Sticker.AUTHOR,
			authorId: Sticker.CREATED_BY,
			marker: (Sticker.MARKER_ADJUST || Sticker.MARKER_WIDTH || Sticker.MARKER_HEIGHT)  ?
				{
					top: parseInt(Sticker.MARKER_TOP),
					left: parseInt(Sticker.MARKER_LEFT),
					width: parseInt(Sticker.MARKER_WIDTH),
					height: parseInt(Sticker.MARKER_HEIGHT),
					adjust: Sticker.MARKER_ADJUST
				}
				: {}
		};
	},

	SetMarker: function(ind, mode)
	{
		var _this = this;
		var oSt = this.arStickers[ind];
		this.bHightlightElementMode = false;
		this.bSelectAreaMode = false;

		BX.removeClass(oSt.pMarkerElementBut, 'bxst-pressed');
		BX.removeClass(oSt.pMarkerAreaBut, 'bxst-pressed');

		if (!this.oMarker)
			this.oMarker = {};

		this.oMarker.StickerInd = ind;

		//Hide marker if it exist
		if (oSt.pMarkerNode)
			BX.removeClass(oSt.pMarkerNode, 'bxst-sicked');

		if (oSt.pMarker)
		{
			oSt.pMarker.style.display = "none";
			oSt.pMarker.style.top = "-1000px";
		}
		if (oSt.markerResizer && oSt.markerResizer.cont)
			oSt.markerResizer.cont.style.display = "none";

		if (oSt.obj && oSt.obj.marker)
			oSt.obj.marker = {};

		this.oMarker.node = null;

		oSt.bSetMarkerMode = true;
		if (mode == 'area')
		{
			BX.addClass(oSt.pMarkerAreaBut, 'bxst-pressed');
			setTimeout(function(){_this.bSelectAreaMode = true;}, 10);

			// Create overlay
			if (!this.oMarker.pOverlay)
				this.oMarker.pOverlay = document.body.appendChild(BX.create('DIV', {props: {className: 'bxst-marker-overlay'}}));
			// Show overlay
			this.oMarker.pOverlay.style.display = 'block';

			// Adjust overlay to size
			var ss = BX.GetWindowScrollSize(document);
			this.oMarker.pOverlay.style.width = ss.scrollWidth + "px";
			this.oMarker.pOverlay.style.height = ss.scrollHeight + "px";

			// Create hint near cursor
			if (!this.oMarker.pCursorHint)
				this.oMarker.pCursorHint = document.body.appendChild(BX.create('DIV', {props: {className: 'bxst-cursor-hint'}, text: this.MESS.CursorHint}));

			this.oMarker.pCursorHint.style.top = '';
			this.oMarker.pCursorHint.style.left = '';
			this.oMarker.pCursorHint.style.display = 'block';

			// Marker selection area object
			this.oMarker.pWnd = document.body.appendChild(BX.create('DIV'));
			this.oMarker.pWnd.className = 'bxst-cur-marker ' + this.colorSchemes[oSt.obj.colorInd].name;
		}
		else // Element
		{
			BX.addClass(oSt.pMarkerElementBut, 'bxst-pressed');
			setTimeout(function(){_this.bHightlightElementMode = true;}, 10);
		}

		// Add events
		BX.bind(document, 'mousemove', BX.proxy(this.OnMouseMove, this));
		//BX.bind(document, 'mousedown', BX.proxy(this.OnMousedown, this));
		BX.bind(document, 'mouseup', BX.proxy(this.OnMouseUp, this));
	},

	OnMousedown: function(e)
	{
		//if(!this.bHightlightElementMode && !this.bSelectAreaMode)
		//{
			if (this.curEditorStickerInd !== false && window.oLHESticker && !window.oLHESticker.bPopup)
			{
				var oSt = this.arStickers[this.curEditorStickerInd];
				if (oSt && oSt.pWin.Get())
				{
					var
						bSelMode = this.bSelectAreaMode || this.bHightlightElementMode,
						d = 3,
						top = parseInt(oSt.pWin.Get().style.top) - d,
						left = parseInt(oSt.pWin.Get().style.left) - d,
						right = left + parseInt(oSt.pWin.Get().style.width) + d * 2,
						bottom = top + parseInt(oSt.pWin.Get().style.height) + d * 2;

					e = this.MousePos(e);
					if (e.realX < left || e.realX > right || e.realY < top || e.realY > bottom)
						this.SaveAndCloseEditor(this.curEditorStickerInd, !bSelMode, !bSelMode);
				}
			}
		//}

		// Start to draw selection marker area
		if (this.bSelectAreaMode)
		{
			e = this.MousePos(e);
			this.bDrawMarkerMode = true;
			if (this.oMarker.pCursorHint)
				this.oMarker.pCursorHint.style.display = 'none';

			this.oMarker.from = {top: e.realY, left: e.realX};
		}
		else if (this.bHightlightElementMode) // Start to draw marker area
		{
			var bPrevent = false;
			if (this.pCurMarkeredNode)
			{
				bPrevent = true;
				var cn = this.pCurMarkeredNode.pNode.className;
				if (cn && (cn.indexOf('bx-sticker') != -1 || cn.indexOf('bxst') != -1) && cn.indexOf('bxst-sicked') == -1)
					bPrevent = false;
				if (bPrevent)
					bPrevent = !BX.findParent(this.pCurMarkeredNode.pNode, {className: new RegExp('bx-sticker', 'ig')});
			}

			// Prevent to go away from page
			if (bPrevent)
				return BX.PreventDefault(e);
			else
				this.MarkerHightlightNode(); // Restore onmousedown and onclick events
		}
	},

	OnMouseMove: function(e)
	{
		if(this.bHightlightElementMode)
		{
			var pEl;
			if (e.target)
				pEl = e.target;
			else if (e.srcElement)
				pEl = e.srcElement;
			if (pEl.nodeType == 3)
				pEl = pEl.parentNode;

			if (pEl && pEl.nodeName)
				this.MarkerHightlightNode(pEl);
		}

		if (this.bSelectAreaMode)
		{
			e = this.MousePos(e);

			if (this.oMarker.pCursorHint)
				this.AdjustHintToCursor(this.oMarker.pCursorHint, e);

			if (!this.bDrawMarkerMode)
				return;

			// We down mouse button and try to drop: unhightlight element and start to select area
			//this.bHightlightElementMode = false;
			//this.MarkerHightlightNode();

			this.oMarker.to = {top: e.realY, left: e.realX};
			var
				top = this.oMarker.from.top,
				left = this.oMarker.from.left,
				w = Math.abs(this.oMarker.to.left - this.oMarker.from.left),
				h = Math.abs(this.oMarker.to.top - this.oMarker.from.top);

			//00.00 - 3.00
			if (this.oMarker.to.top <= this.oMarker.from.top && this.oMarker.to.left >= this.oMarker.from.left)
			{
				top = this.oMarker.to.top;
				left = this.oMarker.from.left;
			}
			// 3.00 - 6.00
			else if (this.oMarker.to.top > this.oMarker.from.top && this.oMarker.to.left > this.oMarker.from.left)
			{
				top = this.oMarker.from.top;
				left = this.oMarker.from.left;
			}
			// 6.00 - 9.00
			else if (this.oMarker.to.top > this.oMarker.from.top && this.oMarker.to.left < this.oMarker.from.left)
			{
				top = this.oMarker.from.top;
				left = this.oMarker.to.left;
			}
			// 9.00 - 12.00
			else if (this.oMarker.to.top < this.oMarker.from.top && this.oMarker.to.left < this.oMarker.from.left)
			{
				top = this.oMarker.to.top;
				left = this.oMarker.to.left;
			}

			this.oMarker.pWnd.style.display = "block";
			this.oMarker.pWnd.style.width = w + "px";
			this.oMarker.pWnd.style.height = h + "px";
			this.oMarker.pWnd.style.top = top + "px";
			this.oMarker.pWnd.style.left = left + "px";

			this.oMarker.top = top;
			this.oMarker.left = left;
			this.oMarker.width = w;
			this.oMarker.height = h;
		}
	},

	OnMouseUp: function(e)
	{
		if (this.bHightlightElementMode && this.pCurMarkeredNode)
		{
			var bPrevent = false;
			var cn = this.pCurMarkeredNode.pNode.className;
			if (cn && (cn.indexOf('bx-sticker') != -1 || cn.indexOf('bxst') != -1) && cn.indexOf('bxst-sicked') == -1)
				bPrevent = true;
			if (!bPrevent)
				bPrevent = !!BX.findParent(this.pCurMarkeredNode.pNode, {className: new RegExp('bx-sticker', 'ig')});

			if (!bPrevent)
				this.oMarker.node = this.pCurMarkeredNode.pNode;
		}

		// Reset
		this.bDrawMarkerMode = false;
		this.bHightlightElementMode = false;
		this.bSelectAreaMode = false;

		if (this.oMarker.StickerInd >= 0 && this.arStickers[this.oMarker.StickerInd])
		{
			var oSt = this.arStickers[this.oMarker.StickerInd];
			BX.removeClass(oSt.pMarkerElementBut, 'bxst-pressed');
			BX.removeClass(oSt.pMarkerAreaBut, 'bxst-pressed');
			oSt.bSetMarkerMode = false;
		}

		// Kill events
		BX.unbind(document, 'mousemove', BX.proxy(this.OnMouseMove, this));
		//BX.unbind(document, 'mousedown', BX.proxy(this.OnMousedown, this));
		BX.unbind(document, 'mouseup', BX.proxy(this.OnMouseUp, this));

		if (this.oMarker.pOverlay)
			this.oMarker.pOverlay.style.display = 'none';
		if (this.oMarker.pCursorHint)
			this.oMarker.pCursorHint.style.display = 'none';

		// if (bPrevent)
			// this.SetMarker(this.oMarker.StickerInd);
		// else
		if (!bPrevent)
			this.CreateMarker(this.oMarker);
	},

	MarkerHightlightNode: function(node)
	{
		if (this.pCurMarkeredNode)
		{
			if (this.pCurMarkeredNode.onclick)
				this.pCurMarkeredNode.pNode.onclick = this.pCurMarkeredNode.onclick;
			if (this.pCurMarkeredNode.onmousedown)
				this.pCurMarkeredNode.pNode.onmousedown = this.pCurMarkeredNode.onmousedown;

			BX.removeClass(this.pCurMarkeredNode.pNode, 'bxst-sicked');
		}

		if (node)
		{
			this.pCurMarkeredNode = {pNode: node};

			if (node.onclick)
				this.pCurMarkeredNode.onclick = node.onclick;
			if (node.onmousedown)
				this.pCurMarkeredNode.onmousedown = node.onmousedown;

			node.onmousedown = BX.proxy(this.OnMousedown, this);
			node.onclick = function(){return BX.PreventDefault(arguments[0]);};

			BX.addClass(node, 'bxst-sicked');
		}
		else
		{
			this.pCurMarkeredNode = false;
		}
	},

	CreateMarker: function(oMarker)
	{
		if (!oMarker)
			return;

		var oSt = this.arStickers[oMarker.StickerInd];

		if (oMarker.node)
		{
			oSt.pMarkerNode = oMarker.node;
			oSt.obj.marker = {adjust: this.GetNodeAdjustInfo(oMarker.node)};

			var pos = BX.pos(oSt.pMarkerNode);
			if (pos)
			{
				oSt.obj.marker.top = pos.top - 2;
				oSt.obj.marker.left = pos.left - 2;
				oSt.obj.marker.width = pos.width - 4;
				oSt.obj.marker.height = pos.height - 4;
			}
		}
		else
		{
			oSt.obj.marker = {
				top: oMarker.top,
				left: oMarker.left,
				width: oMarker.width,
				height: oMarker.height
			};

		}

		if (oSt.obj.marker && (oSt.obj.marker.adjust || (oSt.obj.marker.width && oSt.obj.marker.height && oSt.obj.marker.top && oSt.obj.marker.left)))
		{
			this.DisplayMarker(oMarker.StickerInd, true);
			this.AdjustStickerToArea(oMarker.StickerInd);
		}

		if (this.oMarker.pWnd)
			this.oMarker.pWnd.style.display = "none";

		if (!oSt.pWin.__stWasDragged)
			this.SaveSticker(oMarker.StickerInd);
	},

	DisplayMarker: function(ind, bNew)
	{
		var oSt = this.arStickers[ind];
		if (oSt.pMarker)
			oSt.pMarker.style.display = "none";

		if (oSt.obj.marker && oSt.obj.marker.adjust)
		{
			if (!oSt.pMarkerNode)
				oSt.pMarkerNode = this.FindMarkerNode(oSt.obj.marker.adjust);

			if (oSt.pMarkerNode)
			{
				var pos = BX.pos(oSt.pMarkerNode);
				if (pos)
				{
					if (!oSt.pMarker)
						oSt.pMarker = document.body.appendChild(BX.create('DIV', {props: {className: 'bxst-sticker-marker ' + this.colorSchemes[oSt.obj.colorInd].name}}));

					if (bNew)
						BX.addClass(oSt.pMarker, "bxst-marker-over");

					oSt.pMarker.style.display = "";
					oSt.pMarker.style.width = (pos.width - 4) + "px";
					oSt.pMarker.style.height = (pos.height - 4) + "px";
					oSt.pMarker.style.top = (pos.top - 2) + "px";
					oSt.pMarker.style.left = (pos.left - 2) + "px";
				}

				//return BX.addClass(oSt.pMarkerNode, 'bxst-sicked'); // We find node and select it
				BX.removeClass(oSt.pMarkerNode, 'bxst-sicked');
				return; // We find node and select it
			}
		}

		// Select area
		if (oSt.obj.marker && oSt.obj.marker.width > 0)
		{
			if (!oSt.pMarker)
				oSt.pMarker = document.body.appendChild(BX.create('DIV', {props: {className: 'bxst-sticker-marker ' + this.colorSchemes[oSt.obj.colorInd].name}}));

			if (bNew)
				BX.addClass(oSt.pMarker, "bxst-marker-over");

			oSt.pMarker.style.display = "";
			oSt.pMarker.style.width = oSt.obj.marker.width + "px";
			oSt.pMarker.style.height = oSt.obj.marker.height + "px";
			oSt.pMarker.style.top = oSt.obj.marker.top + "px";
			oSt.pMarker.style.left = oSt.obj.marker.left + "px";
		}
	},

	GetNodeAdjustInfo: function(node)
	{
		var nodeInfo = this._GetNodeAdjustInfo(node);
		nodeInfo = this._GetNodeAdjustSiblings(node, nodeInfo);
		return nodeInfo;
	},

	_GetNodeAdjustInfo: function(node)
	{
		var nodeInfo = {
			nodeName: node.nodeName.toLowerCase(),
			attr: {},
			innerHTML: null
		};

		if (node.innerHTML && node.innerHTML.length)
		{
			nodeInfo.innerHTML = BX.util.trim(node.innerHTML.toLowerCase());

			nodeInfo.innerHTML = nodeInfo.innerHTML.replace(/class=""/ig, '');
			nodeInfo.innerHTML = nodeInfo.innerHTML.replace(/class=''/ig, '');
			nodeInfo.innerHTML = nodeInfo.innerHTML.replace(/\n+/ig, '');
			nodeInfo.innerHTML = nodeInfo.innerHTML.replace(/\r+/ig, '');
			nodeInfo.innerHTML = nodeInfo.innerHTML.replace(/\s+/ig, ' ');

			if (nodeInfo.innerHTML.length > 250)
				nodeInfo.innerHTML = nodeInfo.innerHTML.substr(0, 250);
		}

		if (node.attributes)
		{
			var i, l = node.attributes.length;
			for (i = 0; i < l; i++)
			{
				name = node.attributes[i].name;
				if (!name || typeof name != 'string')
					continue;
				name = name.toLowerCase();
				if (this.oMarkerConfig.attr[name])
				{
					val = node.attributes[i].value;
					if (name == 'class' || name == 'classname')
					{
						name = 'classname';
						val = val.replace('bxst-sicked', '');
						val = BX.util.trim(val);
					}

					if (val.length > 0)
						nodeInfo.attr[name] = val;
				}
			}
		}
		return nodeInfo;
	},

	_GetNodeAdjustSiblings: function(node, nodeInfo)
	{
		nodeInfo.withId = {};

		var pParent = BX.findParent(node, {attr : {id: new RegExp('.+', 'ig')}});
		if (pParent)
			nodeInfo.withId.parent = pParent.getAttribute('id');

		var pChildren = BX.findChild(node, {attr : {id: new RegExp('.+', 'ig')}}, true, true);
		if (pChildren)
		{
			nodeInfo.withId.children = [];
			for (var i = 0, l = pChildren.length; i < l; i++)
				nodeInfo.withId.children.push(pChildren[i].getAttribute('id'));
		}

		var pPrevSibling = BX.findPreviousSibling(node, {attr : {id: new RegExp('.+', 'ig')}});
		if (pPrevSibling)
			nodeInfo.withId.prevSibling = pPrevSibling.getAttribute('id');

		var pNextSibling = BX.findNextSibling(node, {attr : {id: new RegExp('.+', 'ig')}});
		if (pNextSibling)
			nodeInfo.withId.nextSibling = pNextSibling.getAttribute('id');

		return nodeInfo;
	},

	FindMarkerNode: function(nodeInfo)
	{
		var node = false;
		if (!nodeInfo || !nodeInfo.nodeName)
			return false;

		if (!nodeInfo.attr)
			nodeInfo.attr = {};

		// Simple and easy way
		if (nodeInfo.attr.id)
			node = BX(nodeInfo.attr.id);

		var arFindedNodes = [];
		var res;

		if (!node)
		{
			if (!nodeInfo.withId)
				nodeInfo.withId = {};

			// Find by prev sibling
			if (nodeInfo.withId.prevSibling)
			{
				var nextNode = BX(nodeInfo.withId.prevSibling);
				if (nextNode)
				{
					while(nextNode = nextNode.nextSibling)
					{
						res = this.TestNodeWithAttributes(nextNode, nodeInfo);
						if (res)
							arFindedNodes.push(res);

						if (res.coincide == 100)
							break;
					}
				}
			}

			// Find by next sibling
			if (nodeInfo.withId.nextSibling)
			{
				var prevNode = BX(nodeInfo.withId.nextSibling);
				if (prevNode)
				{
					while(prevNode = prevNode.previousSibling)
					{
						res = this.TestNodeWithAttributes(prevNode, nodeInfo);
						if (res)
							arFindedNodes.push(res);

						if (res.coincide == 100)
							break;
					}
				}
			}

			// Find by child
			if (nodeInfo.withId.children)
			{
				var i, l = nodeInfo.withId.children.length, child, parNode;
				for (i = 0; i < l; i++)
				{
					child = BX(nodeInfo.withId.children[i]);
					if (child)
					{
						parNode = child;
						while (true)
						{
							parNode = BX.findParent(parNode, {tagName: nodeInfo.nodeName});
							if (!parNode)
								break;

							res = this.TestNodeWithAttributes(prevNode, nodeInfo);
							if (res)
								arFindedNodes.push(res);

							if (res.coincide == 100)
								break;
						}
					}
				}
			}

			// Find by parent
			var parent;
			if (nodeInfo.withId.parent)
				parent = BX(nodeInfo.withId.parent);
			if (!parent)
				parent = document.body;

			var arAllNodes = parent.getElementsByTagName(nodeInfo.nodeName);
			var i, l = arAllNodes.length;
			for (i = 0; i < l; i++)
			{
				res = this.TestNodeWithAttributes(arAllNodes[i], nodeInfo);
				if (res)
					arFindedNodes.push(res);
				if (res.coincide == 100)
					break;
			}
		}
		else
		{
			arFindedNodes.push({coincide: 100, node: node, bImpAttrCoincide: true});
		}

		var i, l = arFindedNodes.length;
		var arRealNodes = [], maxCoincide = 0, mostRealNode = false;

		for (i = 0; i < l; i++)
		{
			if (arFindedNodes[i].coincide > maxCoincide)
			{
				maxCoincide = arFindedNodes[i].coincide;
				mostRealNode = arFindedNodes[i].node;
				arRealNodes = [];
			}

			if (arFindedNodes[i].coincide == maxCoincide && arFindedNodes[i].node != mostRealNode)
				arRealNodes.push(arFindedNodes[i].node);
		}

		if (arRealNodes.length == 0 && mostRealNode)
			return mostRealNode;
		else
			arRealNodes[0];

		return false;
	},

	TestNodeWithAttributes: function(pNode, nodeInfo)
	{
		if (!pNode || !pNode.nodeName)
			return false;

		var res = {coincide: 0, node: pNode};
		var info = this._GetNodeAdjustInfo(pNode);

		if (info.nodeName != nodeInfo.nodeName)
			return false;

		var delta = 0;
		var bInnerHTML = typeof nodeInfo.innerHTML == 'string';
		if (typeof info.innerHTML != 'string' && bInnerHTML)
			return false;

		var count = 0;
		for (i in nodeInfo.attr)
			if (typeof nodeInfo.attr[i] == 'string')
				count++;

		if (count > 0)
		{
			delta = 100 / (count + (bInnerHTML ? 1 : 0));
			var bImpAttrCoincide = true;

			for (i in nodeInfo.attr)
			{
				if (typeof nodeInfo.attr[i] == 'string')
				{
					// We have similar attributes
					if (nodeInfo.attr[i] == info.attr[i])
						res.coincide += delta;
					else if (this.oMarkerConfig.impAttr[i])
						bImpAttrCoincide = false;
				}
			}

			res.bImpAttrCoincide = bImpAttrCoincide;
		}

		if (bInnerHTML && info.innerHTML == nodeInfo.innerHTML)
			res.coincide += count > 0 ? delta : 95;
		res.coincide = Math.round(res.coincide);

		if (res.coincide > 0)
			return res;
		return false;
	},

	OnDivMouseOver: function(ind, bOver)
	{
		var oSt = this.arStickers[ind];
		if (oSt.bSetMarkerMode)
			return this.ShowButtonsPanel(ind, true, false);

		oSt._over = bOver;

		if (oSt._overTimeout)
			clearTimeout(oSt._overTimeout);

		var _this = this;
		oSt._overTimeout = setTimeout(function()
		{
			if (oSt._over == bOver)
			{
				_this.ShowButtonsPanel(ind, bOver);
				_this.Hightlight(ind, bOver);
			}
		}, bOver ? 100 : 500);
	},

	ShowButtonsPanel: function(ind, bShow, bEffects)
	{
		if (!this.Params.bHideBottom)
		{
			bShow = true;
			bEffects = false;
		}

		bEffects = bEffects !== false;

		var
			_this = this,
			oSt = this.arStickers[ind],
			h = 24, d = 3, i = 1,
			curHeight = oSt.obj.height - (oSt.bButPanelShowed ? 0 : h),
			resHeight = curHeight + h * (bShow ? 1 : -1),
			time = BX.browser.IsIE() ? 3 : 10;

		if (this.bSelectAreaMode || this.bHightlightElementMode // Set marker mode
		|| oSt.obj.collapsed || oSt.obj.closed || oSt.bColSelShowed || oSt.bResizingNow) // Sticker params
			return;

		if (oSt.bButPanelShowed == bShow)
		{
			oSt.pWin.Get().style.height = curHeight + 'px';
			return this.AdjustShadow(ind);
		}

		var sbpInterval = setInterval(function()
		{
			curHeight += d * i * (bShow ? 1 : -1 );
			if (bShow && curHeight >= resHeight || !bShow && curHeight <= resHeight)
				curHeight = resHeight;

			oSt.pWin.Get().style.height = curHeight + 'px';
			_this.AdjustShadow(ind);

			if (curHeight == resHeight)
			{
				clearInterval(sbpInterval);
				oSt.bButPanelShowed = bShow;
			}

			i++;
		}, time);
	},

	ShowColorSelector: function(ind)
	{
		var
			_this = this,
			oSt = this.arStickers[ind], b;

		if (!oSt)
			return;

		if (!oSt.pColSelector)
		{
			oSt.pColSelector = document.body.appendChild(BX.create("DIV", {props: {className: 'bxst-col-sel'}}));
			for (var i = 0, l = this.colorSchemes.length; i < l; i++)
			{
				b = oSt.pColSelector.appendChild(BX.create("SPAN", {props: {id: 'bxst_' + ind + '_' + i, className: 'bxst-col-pic ' + this.colorSchemes[i].name, title: this.colorSchemes[i].title}}));
				b.onclick = function(){
					_this.ChangeColor(ind, parseInt(this.id.substr(('bxst_' + ind + '_').length)), true, true);
					_this.ShowColorSelector(ind); // Hide
				};
			}
			oSt.pColSelector.style.zIndex = this.Params.zIndex + 20;
		}

		oSt.bColSelShowed = !oSt.bColSelShowed;
		if (oSt.bColSelShowed)
		{
			var pos = BX.pos(oSt.pColorBut);
			oSt.pColSelector.style.top = (parseInt(pos.top) + 16) + "px";
			oSt.pColSelector.style.left = (pos.left) + "px";
			oSt.pColSelector.style.display = "block";

			this.ShowOverlay(true, this.Params.zIndex + 15);
			this.pTransOverlay.onmousedown = function(){_this.ShowColorSelector(ind);};
			BX.bind(document, 'keydown', BX.proxy(function(e){this.OnKeyDown(e, ind);}, this));
		}
		else //hide
		{
			oSt.pColSelector.style.display = "none";
			this.ShowOverlay(false);
			BX.unbind(document, 'keydown', BX.proxy(function(e){this.OnKeyDown(e, ind);}, this));
		}
	},

	ShowOverlay: function(bShow, zIndex)
	{
		if (!this.pTransOverlay)
			this.pTransOverlay = document.body.appendChild(BX.create('DIV', {props: {className: 'bxst-trans-overlay'}}));

		if (bShow)
		{
			this.pTransOverlay.style.display = "block";
			this.pTransOverlay.style.zIndex = zIndex || 800;

			// Adjust overlay to size
			var ss = BX.GetWindowScrollSize(document);
			this.pTransOverlay.style.width = ss.scrollWidth + "px";
			this.pTransOverlay.style.height = ss.scrollHeight + "px";
		}
		else
		{
			this.pTransOverlay.style.display = "none";
			this.pTransOverlay.onmousedown = BX.False;
		}
	},

	OnKeyDown: function(e, ind)
	{
		if(!e)
			e = window.event;

		var key = e.which || e.keyCode;
		if (key == 27) // Esc
		{
			var oSt = this.arStickers[ind];
			if (oSt && oSt.bColSelShowed)
				this.ShowColorSelector(ind); // Hide
		}
	},

	Hightlight: function(ind, bOver)
	{
		var
			oSt = this.arStickers[ind];

		if (oSt.bOver === bOver)
			return;

		oSt.bOver = bOver;
		if (bOver)
		{
			if (oSt.pMarker)
				BX.addClass(oSt.pMarker, "bxst-marker-over");

			BX.addClass(oSt.pWin.Get(), "bx-sticker-over");
			BX.addClass(oSt.pHead, "bxst-header-over");

			oSt.pWin.Get().style.top = (parseInt(oSt.pWin.Get().style.top) - 1) + "px";
			oSt.pWin.Get().style.left = (parseInt(oSt.pWin.Get().style.left) - 1) + "px";
		}
		else
		{
			if (oSt.pMarker)
				BX.removeClass(oSt.pMarker, "bxst-marker-over");

			BX.removeClass(oSt.pWin.Get(), "bx-sticker-over");
			BX.removeClass(oSt.pHead, "bxst-header-over");
			oSt.pWin.Get().style.top = (parseInt(oSt.pWin.Get().style.top) + 1) + "px";
			oSt.pWin.Get().style.left = (parseInt(oSt.pWin.Get().style.left) + 1) + "px";
		}
	},

	BlinkRed: function(ind)
	{
		var
			_this = this,
			rep = 4,
			it = 0, it0 =0, interval,
			oSt = this.arStickers[ind];

		if (!this.pBlinkRed)
			this.pBlinkRed = document.body.appendChild(BX.create("DIV", {props: {className: 'bxst-blink-red'}}));

		this.pBlinkRed.style.zIndex = parseInt(oSt.pWin.Get().style.zIndex) + 10;
		this.pBlinkRed.style.top = oSt.pWin.Get().style.top;
		this.pBlinkRed.style.left = oSt.pWin.Get().style.left;
		this.pBlinkRed.style.width = oSt.pWin.Get().style.width;
		this.pBlinkRed.style.height = oSt.pWin.Get().style.height;

		bFadeIn = true;
		interval = setInterval(function()
		{
			if (it > 2)
			{
				if (bFadeIn)
				{
					_this.pBlinkRed.className = 'bxst-blink-red bx-sticker-op-3';
					it = 1;
				}
				else
				{
					_this.pBlinkRed.className = 'bxst-blink-red';
					it = 0;
				}

				it0++;
				bFadeIn = !bFadeIn;
				if (it0 >= rep)
					clearInterval(interval);

				return;
			}

			if (bFadeIn)
				_this.pBlinkRed.className = 'bxst-blink-red bx-sticker-op-' + it;
			else
				_this.pBlinkRed.className = 'bxst-blink-red bx-sticker-op-' + (3 - it);
			it++;
		}, BX.browser.IsIE() ? 30 : 60);
	},

	ShowList: function(type)
	{
		if (!this.List)
			this.List = new BXStickerList(this);

		this.List.Show(type);
	},

	OnKeyUp: function(e)
	{
		if(!e)
			e = window.event;

		var key = e.which || e.keyCode;
		if (key == 17) // Ctrl
		{
			var _this = this;
			this._bCtrlPressed = true;
			setTimeout(function(){_this._bCtrlPressed = false;}, 400);
		}
		else if (key == 16) // Shift
		{
			var _this = this;
			this._bShiftPressed = true;
			setTimeout(function(){_this._bShiftPressed = false;}, 400);
		}
		else if ((this._bShiftPressed || e.shiftKey)  && (e.ctrlKey || this._bCtrlPressed))
		{
			if (key == 83 && this.Params.access == 'W')  // CTRL + SHIFT + S
			{
				this.AddSticker();
				return BX.PreventDefault(e);
			}
			else if(key == 88) // CTRL + SHIFT + X
			{
				this.ShowAll();
				return BX.PreventDefault(e);
			}
			else if(key == 76) // CTRL + SHIFT + L
			{
				this.ShowList('current');
				return BX.PreventDefault(e);
			}
		}
	},

	UpdateStickersCount: function()
	{
		if (this.curPageCount < 0 || isNaN(parseInt(this.curPageCount)))
			this.curPageCount = 0;

		var pEl = BX.findChild(BX('bxst-show-sticker-icon'), {tagName: 'B'}, true);
		if (pEl)
			pEl.innerHTML = "(" + this.curPageCount + ")";
	}
};

function BXStickerList(BXSticker)
{
	this.BXSticker = BXSticker;
	this.access = this.BXSticker.access;
	this.MESS = this.BXSticker.MESS;
	this.arCurPageIds = {};
}

BXStickerList.prototype = {
	Show: function(type)
	{
		if (this.bShowed)
			return;

		var Config = {
			content_url: '/bitrix/admin/fileman_stickers.php?sticker_action=show_list&' + this.BXSticker.sessid_get + '&cur_page=' + encodeURIComponent(this.BXSticker.Params.pageUrl) + '&type=' + type + '&site_id=' + this.BXSticker.Params.site_id,
			title : this.MESS.StickerListTitle,
			width: this.BXSticker.Params.listWidth,
			height: this.BXSticker.Params.listHeight,
			min_width: 800,
			min_height: 400,
			resizable: true,
			resize_id: 'bx_sticker_list_resize_id'
		};

		this.type = type;
		this.bRefreshPage = false;
		this.naviSize = this.BXSticker.Params.listNaviSize;
		this.oDialog = new BX.CDialog(Config);
		this.oDialog.Show();
		this.oDialog.SetButtons([this.oDialog.btnClose]);
		this.bShowed = true;

		var _this = this;
		BX.addCustomEvent(this.oDialog, 'onWindowUnRegister', function()
		{
			_this.bShowed = false;
			if (_this.bRefreshPage)
				window.location = window.location;
		});
		// Adjust Navi size
		BX.addCustomEvent(this.oDialog, 'onWindowResizeFinished', function(){_this.AdjustNaviSize();});
		BX.addCustomEvent(this.oDialog, 'onWindowExpand', function(){_this.AdjustNaviSize();});
		BX.addCustomEvent(this.oDialog, 'onWindowNarrow', function(){_this.AdjustNaviSize();});
	},

	OnLoad: function(count)
	{
		this.pAllBut = BX('bxstl_fil_all_but');
		this.pMyBut = BX('bxstl_fil_my_but');
		this.pColorCont = BX('bxstl_col_cont');
		this.pOpenedBut = BX('bxstl_fil_opened_but');
		this.pClosedBut = BX('bxstl_fil_closed_but');
		this.pAllStickersBut = BX('bxstl_fil_all_p_but');

		this.pItemsTable = BX('bxstl_items_table');
		this.pItemsTableCnt = BX('bxstl_items_table_cnt');
		this.pNaviCont = BX('bxstl_navi_cont');

		if (this.access == 'W')
		{
			this.pActionSel = BX('bxstl_action_sel');
			this.pActionBut = BX('bxstl_action_ok');
		}

		this.pPageSelect = BX('bxstl_fil_page_sel');

		if (this.type == 'current')
		{
			this.BXSticker.Params.filterParams.status = 'all';
			this.BXSticker.Params.filterParams.page = 'current';
		}
		else if (this.type == 'all')
		{
			this.BXSticker.Params.filterParams.status = 'opened';
			this.BXSticker.Params.filterParams.page = 'all';
		}

		var _this = this;
		var _col = this.BXSticker.Params.filterParams.colors;
		if (_col && _col != 'all' && _col.length > 0)
		{
			this.checkedColors = [false, false, false, false, false, false];
			for (var i = 0, l = _col.length; i < l; i++)
				if (_col[i] != 99)
					this.checkedColors[parseInt(_col[i])] = true;
		}
		else
		{
			this.checkedColors = [true, true, true, true, true, true];
		}

		if (!this.bRefreshPage && window.__bxst_result.cur_page_ids !== false && typeof window.__bxst_result.cur_page_ids == 'object')
		{
			for (var i in window.__bxst_result.cur_page_ids)
				this.arCurPageIds[parseInt(window.__bxst_result.cur_page_ids[i])] = true;
		}

		/* Colors filter*/
		var i, l = this.BXSticker.colorSchemes.length, col, pCol, __s = (BX.browser.IsIE() && !BX.browser.IsDoctype()) ? 'style="width: 12px; height: 12px"' : '';
		for (i = 0; i < l; i++)
		{
			col = this.BXSticker.colorSchemes[i];
			pCol = this.pColorCont.appendChild(BX.create("DIV", {props: {id: 'bxstl_color_' + i, className: 'bxstl-color-pick' + (this.checkedColors[i] ? ' bxstl-color-pick-ch' : ''), title: col.title}, html: '<div class="bxstl-col-pic-l"></div><div class="bxstl-col-pic-c"><div class="' + col.name + '" ' + __s + '>&nbsp;</div></div><div class="bxstl-col-pic-r"></div>'}));
			pCol.onclick = function()
			{
				var colorInd = parseInt(this.id.substr('bxstl_color_'.length));
				_this.checkedColors[colorInd] = !_this.checkedColors[colorInd];
				if (_this.checkedColors[colorInd])
					BX.addClass(this, 'bxstl-color-pick-ch');
				else
					BX.removeClass(this, 'bxstl-color-pick-ch');

				_this.ReloadList();
			};
		}

		/* Stickers type: my | all*/
		this.SetStickerType(this.BXSticker.Params.filterParams.type, false);
		this.pAllBut.onclick = function(){_this.SetStickerType('all')};
		this.pMyBut.onclick = function(){_this.SetStickerType('my')};

		/* Stickers status: opened | closed | all*/
		this.SetStickerStatus(this.BXSticker.Params.filterParams.status, false);
		this.pOpenedBut.onclick = function(){_this.SetStickerStatus('opened');};
		this.pClosedBut.onclick = function(){_this.SetStickerStatus('closed');};
		this.pAllStickersBut.onclick = function(){_this.SetStickerStatus('all');};

		if (this.access == 'W')
			this.pActionBut.onclick = function(){_this.Action();};
		this.pPageSelect.onchange = function(){_this.SetPage(this.value);};

		this.SetPage(this.BXSticker.Params.filterParams.page == 'current' ? this.BXSticker.Params.pageUrl : this.BXSticker.Params.filterParams.page, false);

		count = parseInt(count);
		this.oDialog.SetTitle(this.MESS.StickerListTitle + " (" + count + ")");

		this.EnableActionBut(false);
		//this.AdjustToSize();
	},

	SetStickerStatus: function(status, bReload)
	{
		if (status == 'opened')
		{
			BX.addClass(this.pOpenedBut, 'bxstl-but-checked');
			BX.removeClass(this.pClosedBut, 'bxstl-but-checked');
			BX.removeClass(this.pAllStickersBut, 'bxstl-but-checked');
		}
		else if (status == 'closed')
		{
			BX.removeClass(this.pOpenedBut, 'bxstl-but-checked');
			BX.addClass(this.pClosedBut, 'bxstl-but-checked');
			BX.removeClass(this.pAllStickersBut, 'bxstl-but-checked');
		}
		else
		{
			BX.removeClass(this.pOpenedBut, 'bxstl-but-checked');
			BX.removeClass(this.pClosedBut, 'bxstl-but-checked');
			BX.addClass(this.pAllStickersBut, 'bxstl-but-checked');
		}

		this.StickersStatus = status;
		if (bReload !== false)
			this.ReloadList();
	},

	SetStickerType: function(type, bReload)
	{
		if (type == 'all')
		{
			BX.addClass(this.pAllBut, 'bxstl-but-checked');
			BX.removeClass(this.pMyBut, 'bxstl-but-checked');
		}
		else
		{
			BX.addClass(this.pMyBut, 'bxstl-but-checked');
			BX.removeClass(this.pAllBut, 'bxstl-but-checked');
		}

		this.StickersType = type;
		if (bReload !== false)
			this.ReloadList();
	},

	SetPage: function(value, bReload)
	{
		this.pPageSelect.value = value;
		this.StickersPage = value;

		if (bReload !== false)
			this.ReloadList();
	},

	NaviGet: function(page, navNum)
	{
		var params = {};
		params['PAGEN_' + navNum] = page;
		this.ReloadList(params)
	},

	ReloadList: function(params)
	{
		var _this = this;
		if (!params)
			params = {};

		params.sticker_just_res = 'Y';
		params.colors = [99];
		params.sticker_type = this.StickersType;
		params.sticker_status = this.StickersStatus;
		params.sticker_page = this.StickersPage;
		params.navi_size = this.naviSize;
		params.cur_page = this.BXSticker.Params.pageUrl;
		params.type = this.type;

		// Fetch filter color params
		var i, l = this.checkedColors.length;
		for (i = 0; i < l; i++)
			if (this.checkedColors[i] === true)
				params.colors.push(i);

		window.__bxst_result.list_rows_count = false;
		window.__bxst_result.cur_page_ids = false;
		this.BXSticker.Request('show_list', params,
			function(result)
			{
				var arRes = result.split('#BX_STICKER_SPLITER#');
				if (arRes.length == 2)
				{
					_this.pItemsTableCnt.innerHTML = arRes[0];
					_this.pNaviCont.innerHTML = arRes[1];
				}

				// Display count of selected rows in title
				if (window.__bxst_result.list_rows_count !== false)
					_this.oDialog.SetTitle(_this.MESS.StickerListTitle + " (" + parseInt(window.__bxst_result.list_rows_count) + ")");

				if (!_this.bRefreshPage && window.__bxst_result.cur_page_ids !== false && typeof window.__bxst_result.cur_page_ids == 'object')
				{
					for (var i in window.__bxst_result.cur_page_ids)
						_this.arCurPageIds[parseInt(window.__bxst_result.cur_page_ids[i])] = true;
				}

				_this.pItemsTable = BX('bxstl_items_table');
				_this.EnableActionBut(false);
			}, true
		);
	},

	AdjustToSize: function(w, h)
	{
		return;
	},

	AdjustNaviSize: function()
	{
		var
			newNaviSize,
			h = parseInt(this.oDialog.GetContent().style.height),
			rowHeight = 40,
			maxHeight = (h - 100 /* header */ - 80 /* footer */);

		if (maxHeight != (rowHeight * this.naviSize))
			newNaviSize = Math.floor(maxHeight / rowHeight);

		if (newNaviSize < 5)
			newNaviSize = 5;
		if (newNaviSize > 30)
			newNaviSize = 30;

		if (this.naviSize != newNaviSize)
		{
			this.naviSize = newNaviSize;
			this.ReloadList();
		}
	},

	CheckAll: function(checked)
	{
		var i, l = this.pItemsTable.rows.length, bFind = false;
		for (i = 1; i < l; i++)
		{
			if (this.pItemsTable.rows[i].cells.length == 7)
			{
				this.pItemsTable.rows[i].cells[6].firstChild.checked = !!checked;
				bFind = true;
			}
		}

		if (bFind)
			this.EnableActionBut(checked);
	},

	Action: function()
	{
		if (this.access != 'W')
			return;

		var action = this.pActionSel.value;
		if (action == '' || (action == 'del' && !confirm(this.MESS.DelConfirm)))
			return;

		var i, l = this.pItemsTable.rows.length, arIds = [];
		for (i = 1; i < l; i++)
		{
			if (this.pItemsTable.rows[i].cells.length < 7)
				continue;
			ch = this.pItemsTable.rows[i].cells[6].firstChild;
			if (ch.checked)
			{
				arIds.push(ch.value);
				if (!this.bRefreshPage && this.arCurPageIds[parseInt(ch.value)])
					this.bRefreshPage = true;
			}
		}
		this.ReloadList({list_action: action, list_ids: arIds});
	},

	EnableActionBut: function(bEnable)
	{
		if (this.access != 'W')
			return;

		if (bEnable == 'check')
		{
			var i, l = this.pItemsTable.rows.length, bEnable = false;
			for (i = 1; i < l; i++)
			{
				if (this.pItemsTable.rows[i].cells.length < 7)
					continue;
				if (this.pItemsTable.rows[i].cells[6].firstChild.checked)
				{
					bEnable = true;
					break;
				}
			}
		}
		this.pActionBut.disabled = !bEnable;
		this.pActionSel.disabled = !bEnable;
	}
};
