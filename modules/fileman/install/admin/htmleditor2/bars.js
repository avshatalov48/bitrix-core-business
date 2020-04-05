function BXTaskbarSet(pColumn, pMainObj, iNum)
{
	if (typeof SETTINGS[pMainObj.name].arTBSetsSettings != 'object' )
		SETTINGS[pMainObj.name].arTBSetsSettings = arTBSetsSettings_default;

	var
		arTBSetsSet = SETTINGS[pMainObj.name].arTBSetsSettings,
		_this = this,
		bVertical = (iNum == 1 || iNum==2);

	ar_BXTaskbarSetS.push(this);

	this.pParentWnd = pColumn;
	this.pParentWnd.unselectable = "on";
	this.pMainObj = pMainObj;
	this.bVertical = bVertical;
	this.iNum = iNum;
	this.bShowing = !!arTBSetsSet[iNum].show;
	this.bFirstDisplay = true;

	this.pWnd = BX.findChild(this.pParentWnd, {tagName: "TABLE"});
	this.sActiveTaskbar = '';

	this.pMoveColumn = this.pWnd.rows[0].cells[0];
	if (this.iNum == 2) //right
	{
		this.pTitleColumn = this.pWnd.rows[0].cells[1];
		this.pMainCell = this.pWnd.rows[1].cells[0];
		this.pBottomColumn = this.pWnd.rows[2].cells[0];
	}
	else //this.iNum == 3  - bottom
	{
		this.pTitleColumn = this.pWnd.rows[1].cells[0];
		this.pMainCell = this.pWnd.rows[2].cells[0];
		this.pBottomColumn = this.pWnd.rows[3].cells[0];
	}

	this.pDataColumn = this.pMainCell.appendChild(BX.create("DIV", {props: {className: "bxedtaskbar-scroll"}}));
	this.arTaskbars = [];

	this.pMoveColumn.style.display = "none";
	this.pMoveColumn.unselectable = "on";
	this.pMoveColumn.ondragstart = function (e){return BX.PreventDefault(e);};
	this.pMoveColumn.onmousedown = function(e){_this.MouseDown(e); return false;};

	if (!CACHE_DISPATCHER['TranspToggle'])
		CACHE_DISPATCHER['TranspToggle'] = document.body.appendChild(BX.create('IMG', {props: {src: one_gif_src}, styles: {display: 'none'}}));
}

BXTaskbarSet.prototype =
{
	// Start move toogle
	MouseDown: function (e)
	{
		var val, maxVal, minVal, bVertical, w, h;

		if (this.iNum == 2)  // Right
		{
			val = parseInt(this.pWnd.offsetWidth);
			maxVal = val + parseInt(this.pMainObj.cEditor.offsetWidth) - 155;
			minVal = 210;
			bVertical = false;
			w = false;
			h = this.pMoveColumn.offsetHeight;
		}
		else // Bottom
		{
			val = parseInt(this.pWnd.offsetHeight);
			maxVal = val + parseInt(this.pMainObj.cEditor.offsetHeight) - (BX.browser.IsIE() ? 50 : 155);
			minVal = 120;
			bVertical = true;
			w = this.pMoveColumn.offsetWidth;
			h = false;
		}

		this.pMainObj.ClearPosCache();

		showTranspToggle({
			e: e,
			bVertical: bVertical,
			pMainObj: this.pMainObj,
			pos: BX.pos(this.pMoveColumn),
			width: w,
			height: h,
			callbackFunc: this.Resize,
			callbackObj: this,
			value: val,
			maxValue: maxVal,
			minValue: minVal
		});
	},

	Resize: function (value, bSave, bSetTmpClass)
	{
		// Get value from saved settings resize
		if (value === false || typeof value == 'undefined')
		{
			value = SETTINGS[this.pMainObj.name].arTBSetsSettings[this.iNum].size;
			bSave = false;
		}

		if (!this.bShowing)
		{
			value = 0;
			bSave = false;
		}

		var maxVal, minVal;
		if (this.iNum == 2)  // Right
		{
			maxVal = parseInt(this.pMainObj.pWnd.offsetWidth) - 200;
			minVal = 210;
		}
		else // Bottom
		{
			maxVal = parseInt(this.pMainObj.pWnd.offsetHeight) - 250;
			minVal = 120;
		}

		if (value > maxVal && maxVal > minVal)
			value = maxVal;

		if (value < minVal && value !== 0 && minVal < maxVal)
			value = minVal;

		value = parseInt(value);
		var
			_this = this,
			rightTaskbar = this.pMainObj.arTaskbarSet[2],
			bottomTaskbar = this.pMainObj.arTaskbarSet[3],
			h = parseInt(this.pMainObj.pWnd.offsetHeight), // sceleton height
			h1 = parseInt(this.pMainObj.pTopToolbarset.offsetHeight), // top toolbarset
			h2 = 29; //taskbars tabs

		// If editor is invisible - try to resize it every 0.5 sec
		if (!h || !h1)
			return setTimeout(function(){_this.Resize(value, bSave, bSetTmpClass);}, 500);

		if (this.iNum == 2)  // Resize vertical taskbar set
		{
			var h4 = bottomTaskbar.pWnd.offsetHeight;
			this.pParentWnd.style.height = (h - h1 - h2 - h4) + "px";
			if (this.bShowing)
			{
				this.pWnd.style.width = value + 'px';
				this.pDataColumn.style.width = (value - 8) + 'px';
				this.pParentWnd.style.width = value + 'px';
			}

			var w = parseInt(this.pMainObj.pWnd.offsetWidth); // sceleton width
			this.pMainObj.cEditor.style.width = (w - value - 2) + 'px';
		}
		else // Resize horizontal taskbar set
		{
			this.pMainObj.cEditor.style.height = (h - h1 - h2 - value) + "px";
			rightTaskbar.pParentWnd.style.height = (h - h1 - h2 - value) + "px";

			if (this.bShowing)
			{
				this.pWnd.style.height = value + 'px';
				this.pDataColumn.style.height = (value - 34) + 'px';
			}
		}

		if (this.adjustTimeout)
			clearTimeout(this.adjustTimeout);

		this.adjustTimeout = setTimeout(function()
		{
			var
				h3 = rightTaskbar.pDataColumn.parentNode.offsetHeight,
				w3 = rightTaskbar.pDataColumn.parentNode.offsetWidth;

			h3 = BX.findParent(rightTaskbar.pDataColumn, {tagName: "TABLE"}).offsetHeight - 26 /*top title*/;
			if (rightTaskbar.arTaskbars.length > 1)
				h3 -= 26;


			if (h3 > 0)
				rightTaskbar.pDataColumn.style.height = h3 + "px";

			if (w3 > 0)
				rightTaskbar.pDataColumn.style.width = w3 + "px";

			if (BX.browser.IsIE())
				rightTaskbar.pWnd.parentNode.appendChild(rightTaskbar.pWnd); // IE needs to refresh DOM tree

			if (bSetTmpClass !== false)
				_this._SetTmpClass(false);
		}, 100);

		if (bSave !== false)
		{
			SETTINGS[this.pMainObj.name].arTBSetsSettings[this.iNum].size = value;
			this.SaveConfig();
		}
	},

	_SetTmpClass: function(bSet)
	{
		var
			d2 = this.pMainObj.arTaskbarSet[2].pDataColumn,
			d3 = this.pMainObj.arTaskbarSet[3].pDataColumn,
			c = "bxedtaskbar-scroll-tmp";

		if (bSet)
		{
			BX.addClass(d2, c);
			BX.addClass(d3, c);
		}
		else
		{
			BX.removeClass(d2, c);
			BX.removeClass(d3, c);
		}
	},

	SaveConfig: function ()
	{
		this.pMainObj.SaveConfig("taskbars", {
			tskbrsetset: SETTINGS[this.pMainObj.name].arTBSetsSettings,
			tskbrset: SETTINGS[this.pMainObj.name].arTaskbarSettings
		});
	},

	Show: function ()
	{
		this.bShowing = true;
		SETTINGS[this.pMainObj.name].arTBSetsSettings[this.iNum].show = true;

		var _this = this;
		var btt = this.pMainObj.oBXTaskTabs;

		if (this.pMainObj.visualEffects && btt)
			this.pMainObj.oBXVM.Show({
				sPos: btt.GetVPos(),
				ePos: this.GetVPos(),
				callback: function(){_this.Display(true);}
			});
		else
			this.Display(true);
	},

	Hide: function ()
	{
		this.bShowing = false;
		SETTINGS[this.pMainObj.name].arTBSetsSettings[this.iNum].show = false;

		this.Display(false);

		if (this.pMainObj.oBXTaskTabs)
		{
			if (this.pMainObj.visualEffects)
				this.pMainObj.oBXVM.Show({sPos: this.GetVPos(), ePos: this.pMainObj.oBXTaskTabs.GetVPos()});

			this.pMainObj.oBXTaskTabs.Refresh();
		}

		this.SaveConfig();
	},

	Display: function(bDisplay)
	{
		// It's first taskbarset opening - lets draw tabs for taskbars
		if (this.bFirstDisplay)
		{
			this.DrawTabs();
			this.bFirstDisplay = false;
		}

		this.bShowing = !!bDisplay && this.arTaskbars.length > 0;

		var dispStr = bDisplay ? '' : 'none';

		if (bDisplay)
			this._SetTmpClass(true);

		var _this = this;
		setTimeout(function()
		{
			_this.Resize();
			_this.pWnd.style.display = _this.pWnd.parentNode.style.display = dispStr;
			if (!_this.bVertical)
				_this.pWnd.parentNode.parentNode.style.display = dispStr;

		}, 10);
	},

	ShowToggle: function(e)
	{
		if(this.bShowing)
			this.Hide();
		else
			this.Show();

		SETTINGS[this.pMainObj.name].arTBSetsSettings[this.iNum].show = this.bShowing;
		this.SaveConfig();
		BX.PreventDefault(e);
	},

	AddTaskbar: function (pTaskbar, bDontRefresh)
	{
		var arTBSetsSet = SETTINGS[this.pMainObj.name].arTBSetsSettings;

		pTaskbar.pWnd.style.display = "";
		pTaskbar.pWnd.style.width = "100%";
		pTaskbar.pWnd.style.height = "100%";

		pTaskbar.pTaskbarSet = this;
		pTaskbar.parentCell = this.pWnd;
		//this.pWnd.style.height = '100%'; // ??????????????/

		this.arTaskbars.push(pTaskbar);
		this.pMoveColumn.style.display = "";

		if (this.bVertical)
		{
			this.pWnd.style.width = arTBSetsSet[this.iNum].size + "px";
			this.pWnd.style.height = "100%";
			this.pWnd.parentNode.style.height = "100%";
		}
		else
		{
			this.pWnd.style.width = "100%";
			this.pWnd.style.height = arTBSetsSet[this.iNum].size + "px";
		}

		if (this.arTaskbars.length > 0)
		{
			this.DrawTabs();

			// We add tab to taskbar set - need for resize right taskbar
			this._SetTmpClass(true);
			this.Resize();
		}

		pTaskbar.OnCreate();
	},

	GetVPos: function (pTaskbar, bDontRefresh)
	{
		var arVPos = [];
		var iNum = this.iNum;
		//var edPos = GetRealPos(this.pMainObj.pWnd);
		var edPos = BX.pos(this.pMainObj.pWnd);
		if (this.bVertical)
		{
			arVPos[iNum] = {
				t: parseInt(edPos.top) + 60,
				l: parseInt(edPos.right) - 200,
				w: 200,
				h: parseInt(this.pMainObj.pWnd.offsetHeight) - 150
			};
		}
		else
		{
			arVPos[iNum] = {
				t: parseInt(edPos.bottom) - 200,
				l: parseInt(edPos.left),
				w: parseInt(this.pMainObj.pWnd.offsetWidth),
				h: 200
			};
		}
		return arVPos[iNum];
	},

	DelTaskbar: function (pTaskbar, bRedrawTabs)
	{
		if (pTaskbar.pWnd && pTaskbar.pWnd.parentNode)
			pTaskbar.pWnd.parentNode.removeChild(pTaskbar.pWnd);

		//ar_BXTaskbarS[pTaskbar.name + "_" + this.pMainObj.name] = null;
		for(var i = 0; i < this.arTaskbars.length; i++)
		{
			if(pTaskbar.id == this.arTaskbars[i].id)
			{
				this.arTaskbars = BX.util.deleteFromArray(this.arTaskbars, i);
				this.DrawTabs();
				if(this.arTaskbars.length > 0)
					this.ActivateTaskbar(this.arTaskbars[0].id, false);
			}
		}

		if (bRedrawTabs !== false)
		{
			this.pMainObj.oBXTaskTabs.Draw();
			this.pMainObj.oBXTaskTabs.Refresh();
		}

		if(this.arTaskbars.length == 0)
			this.Display(false);
	},

	DrawTabs: function ()
	{
		this.pMoveColumn.style.display = this.arTaskbars.length == 0 ? "none" : "";
		if(this.arTaskbars.length <= 1)
		{
			//this.pBottomColumn.parentNode.style.display = this.pBottomColumn.style.display = 'none';
			this.pBottomColumn.style.display = 'none';
			return;
		}

		//If more than one taskbars for one taskbarsets
		//this.pBottomColumn.parentNode.style.display = this.pBottomColumn.style.display = "";
		this.pBottomColumn.style.display = "";
		BX.cleanNode(this.pBottomColumn);

		var
			_this = this,
			pIconTable = this.pBottomColumn.appendChild(BX.create("TABLE", {props: {className: "bx-taskbar-tabs", unselectable: "on"}})),
			r = pIconTable.insertRow(0), c,
			tabIsAct, cn, k, l = this.arTaskbars.length;

		BX.adjust(r.insertCell(-1), {style: {width: "9px"}}).appendChild(BX.create("DIV", {props: {className: 'tabs_common bx_btn_tabs_0a'}}));

		this.sActiveTaskbar = this.arTaskbars[0].id;

		for(k = 0; k < l; k++)
		{
			tabIsAct = true;
			if (k != 0)
			{
				BX.adjust(r.insertCell(-1), {style: {width: "9px"}}).appendChild(BX.create("DIV", {props: {className: 'tabs_common ' + (k==1 ? 'bx_btn_tabs_ad' : 'bx_btn_tabs_dd')}}));
				tabIsAct = false;
			}

			c = BX.adjust(r.insertCell(-1), {props: {className: tabIsAct ? 'bx-tsb-tab-act' : 'bx-tsb-tab'}, style:{width: "0%"}, html: '<span unselectable="on" class="bx-tsb-title">' + this.arTaskbars[k].title + '</span>'});
			c.tid = this.arTaskbars[k].id;
			c.onclick = function (e){_this.ActivateTaskbar(this.tid);};
		}

		BX.adjust(r.insertCell(-1), {style: {width: "9px"}}).appendChild(BX.create("DIV", {props: {className: 'tabs_common bx_btn_tabs_d0'}}));
		BX.adjust(r.insertCell(-1), {props: {className: "bxedtaskbaricontable", unselectable: "on"}});
	},

	ActivateTaskbar: function(id, bSave)
	{
		if (this.bShowing && this.bFirstDisplay)
			this.Show();

		BX.cleanNode(this.pDataColumn);
		BX.cleanNode(this.pTitleColumn);
		var j, i, l = this.arTaskbars.length, oActiveTaskbar;

		for(j = 0; j < l; j++)
		{
			this.arTaskbars[j].bActivated = false;
			if(this.arTaskbars[j].id == id)
			{
				this.pDataColumn.appendChild(this.arTaskbars[j].pWnd);
				this.pTitleColumn.appendChild(this.arTaskbars[j].pHeaderTable);

				this.arTaskbars[j].pWnd.style.display = "";
				this.sActiveTaskbar = id;

				oActiveTaskbar = this.arTaskbars[j];
				this.arTaskbars[j].bActivated = true;
				//this.Resize(); // ???
			}
			SETTINGS[this.pMainObj.name].arTaskbarSettings[this.arTaskbars[j].name].active = this.arTaskbars[j].bActivated;
		}

		if(this.pBottomColumn.childNodes[0])
		{
			var tsb_cells = this.pBottomColumn.childNodes[0].rows[0].cells;
			for(i = 0; i < tsb_cells.length - 1; i++)
			{
				if (i == 0)
				{
					if (tsb_cells[1].tid == id)
						tsb_cells[i].firstChild.className = 'tabs_common bx_btn_tabs_0a';
					else
						tsb_cells[i].firstChild.className = 'tabs_common bx_btn_tabs_0d';
					continue;
				}
				else if (i == tsb_cells.length - 2)
				{
					if (tsb_cells[tsb_cells.length-3].tid==id)
						tsb_cells[i].firstChild.className = 'tabs_common bx_btn_tabs_a0';
					else
						tsb_cells[i].firstChild.className = 'tabs_common bx_btn_tabs_d0';
				}
				else if((i+1)%2==0)
				{
					//TaskbarTasb cells
					if (tsb_cells[i].tid==id)
					{
						tsb_cells[i].className = 'bxedtaskbaricontableact';
						tsb_cells[i].style.backgroundImage = 'url(' + image_path + '/taskbar_tabs/a-bg.gif)';
					}
					else
					{
						tsb_cells[i].className = 'bxedtaskbaricontable';
						tsb_cells[i].style.backgroundImage = 'url(' + image_path + '/taskbar_tabs/d-bg.gif)';
					}
				}
				else
				{
					//switching between tabs
					if (tsb_cells[i-1].tid==id)
						tsb_cells[i].firstChild.className = 'tabs_common bx_btn_tabs_ad';
					else if (tsb_cells[i+1].tid==id)
						tsb_cells[i].firstChild.className = 'tabs_common bx_btn_tabs_da';
					else
						tsb_cells[i].firstChild.className = 'tabs_common bx_btn_tabs_dd';
				}
			}
			tsb_cells = null;
		}

		if (this.pMainObj.oBXTaskTabs)
			this.pMainObj.oBXTaskTabs.Refresh();

		if (bSave !== false)
			this.SaveConfig();
	}
}


function BXTaskbar()
{
}

BXTaskbar.prototype = {
Create: function(name, pMainObj, title, dx, dy)
{
	this.name = name;
	ar_BXTaskbarS[this.name + "_" + pMainObj.name] = this;
	this.pMainObj = pMainObj;
	this.pref = this.pMainObj.name.toUpperCase()+'_BXTaskBar_';
	this.id = "tb_" + Math.round(Math.random() * 100000);
	this.bVertical = false;
	this.title = title;
	this.bDeleted = false;
	this.thirdlevel = false;
	var _this = this;
	this.fullyLoaded = true;
	this.bActivated = false;

	if (!SETTINGS[this.pMainObj.name].arTaskbarSettings[this.name])
		SETTINGS[this.pMainObj.name].arTaskbarSettings[this.name] = arTaskbarSettings_default[this.name];

	this.bActive = SETTINGS[this.pMainObj.name].arTaskbarSettings[this.name].active;

	this.pWnd = BX.create("DIV", {props: {className: "bxedtaskbar", unselectable: "on"}});
	this.rootElementsCont = BX.create("DIV", {props: {className: "bxedtaskbar-root"}});
},

OnCreate: function()
{
	// Create taskbar title
	var _this = this;
	var pHeaderTable = this.pTaskbarSet.pTitleColumn.appendChild(BX.create("TABLE", {props: {className: "bxedtaskbartitletext"}}));

	pHeaderTable.setAttribute("__bxtagname", "_taskbar_default");
	this.pHeaderTable = pHeaderTable;

	var r = pHeaderTable.insertRow(-1);

	this.iconDiv = BX.adjust(r.insertCell(-1), {props: {className: 'def'}, style: {width: "1%", paddingLeft: "2px"}}).appendChild(BX.create("DIV"));
	BX.adjust(r.insertCell(-1), {props: {className: "head_text", noWrap: true, unselectable: "on"}, text: this.title});
	var cmBut = BX.adjust(r.insertCell(-1), {props:{className: "head-button-menu", title: BX_MESS.Actions}}).appendChild(BX.create("DIV"));

	cmBut.onmouseover = function(e)
	{
		this.style.margin =  "0px";
		this.style.border =  "#4B4B6F 1px solid";
		this.style.backgroundColor = "#FFC678";
	};
	cmBut.onmouseout = function(e)
	{
		this.style.margin =  "1px";
		this.style.borderStyle = "none";;
		this.style.backgroundColor = "transparent";
	};
	cmBut.onclick = function(e)
	{
		var _bxtgn = pHeaderTable.getAttribute("__bxtagname");
		if (!_bxtgn)
			return;

		var pos = BX.pos(this);
		pos.left += 22;
		pos.top += 20;
		oBXContextMenu.Show(2500, 0, pos, false, {pTaskbar: _this, bxtagname: _bxtgn}, _this.pMainObj, true);
	};

	var hideBut = BX.adjust(r.insertCell(-1), {props:{className: "head-button-hide", title: BX_MESS.Hide}, style: {width: "20px"}}).appendChild(BX.create("DIV"));
	hideBut.onclick = function(e)
	{
		_this.pTaskbarSet.Hide();
		SETTINGS[_this.pMainObj.name].arTBSetsSettings[_this.pTaskbarSet.iNum].show = false;
		_this.pTaskbarSet.SaveConfig();
	};

	if(this.OnTaskbarCreate)
		this.OnTaskbarCreate();
},

SetActive: function ()
{
	if(this.pTaskbarSet)
		this.pTaskbarSet.ActivateTaskbar(this.id);
},

Close: function(bRedrawTabs, bSaveConfig)
{
	SETTINGS[this.pMainObj.name].arTaskbarSettings[this.name].show = false;
	SETTINGS[this.pMainObj.name].arTaskbarSettings[this.name].active = false;
	ar_BXTaskbarS[this.name + "_" + this.pMainObj.name].bDeleted = true;

	if(this.pTaskbarSet)
	{
		if (bSaveConfig !== false)
			this.pTaskbarSet.SaveConfig();
		this.pTaskbarSet.DelTaskbar(this, bRedrawTabs);
	}
},

SetContent: function (sContent)
{
	this.pWnd.innerHTML = sContent;
},

CreateScrollableArea: function (pParent)
{
	return pParent;
	return pParent.appendChild(BX.create("DIV", {props: {className: "bx-taskbar-scroll"}}));

	var res = this.pMainObj.pDocument.createElement("DIV");
	res.style.position = "relative";
	res.style.left = "0px";
	res.style.right = "0px";
	res.style.width = "100%";
	res.style.height = "100%";
	pParent = pParent.appendChild(res);
	res = null;

	res = this.pMainObj.pDocument.createElement("DIV");
	res.style.position = "absolute";
	res.style.left = "0px";
	res.style.right = "0px";
	res.style.width = "100%";
	res.style.height = "100%";

	if(!BX.browser.IsIE())
		res.style.overflow = "-moz-scrollbars-vertical";

	//res.style.overflowY = "scroll";
	res.style.overflowY = "auto";
	res.style.overflowX = "hidden";

	res.style.scrollbar3dLightColor = "#C0C0C0";
	res.style.scrollbarArrowColor = "#252525";
	res.style.scrollbarBaseColor = "#C0C0C0";
	res.style.scrollbarDarkShadowColor = "#252525";
	res.style.scrollbarFaceColor = "#D4D4D4";
	res.style.scrollbarHighlightColor = "#EFEFEF";
	res.style.scrollbarShadowColor = "#EFEFEF";
	res.style.scrollbarTrackColor = "#DFDFDF";


	pParent = pParent.appendChild(res);
	res = null;

	return pParent;
},

DisplayElementList: function (arElements, oCont)
{
	BX.cleanNode(oCont);
	var hi, hlen = arElements.length;
	for (hi = 0; hi < hlen; hi++)
		this.DisplayElement(arElements[hi], oCont);
},

DisplayElement: function (arElement, oCont, orderInd, sPath)
{
	if (orderInd == undefined)
		orderInd = -1;

	if (arElement['isGroup'])
		this.DisplayGroupElement(arElement, oCont, orderInd, sPath);
	else
	{
		if (arElement['thirdlevel'])
		{
			if(this.thirdlevel.name && this.thirdlevel.name != arElement['thirdlevel'])
				this.Display3rdLevelSep(oCont,this.thirdlevel.sPath);
			this.DisplaySingleElement(arElement,oCont,orderInd,sPath);
			this.thirdlevel = {
					name  : arElement['thirdlevel'],
					sPath : sPath
				};
		}
		else
		{
			if(this.thirdlevel.name)
			{
				this.Display3rdLevelSep(oCont,this.thirdlevel.sPath);
				this.thirdlevel = [];
			}
			this.DisplaySingleElement(arElement,oCont,orderInd,sPath);
		}
	}

	if (this.rootElementsCont.parentNode)
		this.rootElementsCont.parentNode.removeChild(this.rootElementsCont);
	oCont.appendChild(this.rootElementsCont);
},

DisplaySingleElement: function (oElement, oCont, orderInd, sPath)
{
	if (sPath==undefined)
		sPath='';

	var _oTable = BX.create('TABLE', {props: {className: 'bxgroupblock1', title: BX_MESS.InsertTitle}});
	_oTable.setAttribute('__bxgroup1', '__' + oElement.name);
	var rowTitle = _oTable.insertRow(-1); //Group header
	//Left cell - icon
	var c = BX.adjust(rowTitle.insertCell(-1), {props: {className: 'iconcell1', unselectable: "on"}});

	var _this = this;
	//*** ICON ***
	ic = this.pMainObj.CreateElement('IMG', {src: oElement.icon || (image_path + '/component.gif')});
	ic.onerror = function(){this.src = image_path + '/component.gif';};
	ic.ondragstart = function(){if(window.event) window.event.cancelBubble = true;};
	this.pMainObj.SetBxTag(ic, {tag: oElement.tagname, params: oElement.params});

	if (_this.OnElementClick)
		ic.onclick = function(e){_this.OnElementClick(this, oElement);};

	_oTable.ondblclick = function()
	{
		var ic = BX.findChild(this, {tagName: 'IMG'}, true);
		if (!ic || !ic.id)
			return;

		var draggedElId = ic.id;
		_this.pMainObj.insertHTML('<img src="' + ic.src + '" id="' + ic.id + '">');
		setTimeout(function()
		{
			_this.OnElementDragEnd(_this.pMainObj.pEditorDocument.getElementById(draggedElId));
		}, 20);
	};

	if(BX.browser.IsIE())
	{
		ic.onmousedown = function (e)
		{
			_this.pMainObj.nLastDragNDropElement = this.id;
		};

		ic.ondragend = function (e)
		{
			_this.pMainObj.nLastDragNDropElementFire = false;
			if (_this.OnElementDragEnd != undefined)
				_this.pMainObj.nLastDragNDropElementFire = _this.OnElementDragEnd;
			_this.pMainObj.OnDragDrop();
		};
	}
	else
	{
		ic.onmousedown = function (e)
		{
			_this.pMainObj.SetFocus();
			_this.pMainObj.nLastDragNDropElement = this.id;
			_this.pMainObj.nLastDragNDropElementFire = false;
			if (_this.OnElementDragEnd != undefined)
				_this.pMainObj.nLastDragNDropElementFire = _this.OnElementDragEnd;
		};

		ic.ondragend = function (e) // For Firefox 3.5 and later
		{
			_this.pMainObj.nLastDragNDropElementFire = false;
			if (_this.OnElementDragEnd != undefined)
				_this.pMainObj.nLastDragNDropElementFire = _this.OnElementDragEnd;
			_this.pMainObj.OnDragDrop();
		};
	}
	c.appendChild(ic);
	c.id = 'element_' + oElement.name;
	ic = null;

	//*** TITLE ***
	c = rowTitle.insertCell(-1);
	c.style.paddingLeft = '5px';
	c.className = 'titlecell1';
	c.appendChild(document.createTextNode(oElement.title || oElement.name));

	if (sPath == '')
	{
		this.rootElementsCont.appendChild(_oTable);
	}
	else
	{
		var oGroup = this.GetGroup(oCont, sPath);
		if (oGroup)
			oGroup.rows[1].cells[0].appendChild(_oTable);
	}
},

Display3rdLevelSep: function (oCont,sPath)
{
	var _oSeparator = document.createElement('TABLE');
	_oSeparator.style.width = BX.browser.IsIE() ? '80%' : '100%';
	_oSeparator.style.height = "1px";
	var _oSepTR = _oSeparator.insertRow(-1);
	var _oSepTD = _oSepTR.insertCell(-1);
	_oSepTD.style.backgroundImage = 'url(' + image_path + '/new_taskbars/point.gif)';

	if (sPath=='')
		oCont.appendChild(_oSeparator);
	else
	{
		var oGroup = this.GetGroup(oCont,sPath);
		var childCell = oGroup.rows[1].cells[0];

		childCell.appendChild(_oSeparator);
	}
	_oSepTD = null;
	_oSepTR = null;
	_oSeparator = null;
},

DisplayGroupElement: function (arElement, oCont, orderInd, sPath)
{
	// create group
	var _this = this;
	if (sPath == undefined)
		sPath = '';

	if (sPath=='')
	{
		//Hight level group
		var _oTable = document.createElement('TABLE');
		oCont.appendChild(_oTable);
		_oTable.cellPadding = 0;
		_oTable.cellSpacing = 0;
		_oTable.width = '100%';
		_oTable.className = 'bxgroupblock0';
		_oTable.setAttribute('__bxgroup0', '__'+arElement.name);

		var rowTitle = _oTable.insertRow(-1); //Group header
		c = rowTitle.insertCell(-1); // Plus/Minus cell
		c.className = 'pluscell0';
		c.unselectable = "on";
		c.style.width = '20px';
		c.style.backgroundImage = 'url(' + image_path + '/new_taskbars/part_l.gif)';
		c.appendChild(this.pMainObj.CreateElement("DIV", {className: 'tskbr_common bx_btn_tabs_plus_small', id: this.pref + 'Group_plus_'+arElement.name}));

		c = rowTitle.insertCell(-1); //Central cell - title
		c.className = 'titlecell0';
		c.style.width = '900px';
		c.unselectable = "on";
		c.innerHTML = BXReplaceSpaceByNbsp((arElement.title) ? arElement.title : arElement.name);
		c.style.backgroundImage = 'url(' + image_path + '/new_taskbars/part_l.gif)';

		var rowData = _oTable.insertRow(-1); // Cell with child elements
		rowData.id = this.pref + 'Group_' + arElement.name;
		rowData.style.display = GetDisplStr(0);
		c = rowData.insertCell(-1);
		c.className = 'datacell0';
		c.colSpan = "2";

		arElement.hidden = true;
		rowTitle._el = arElement;
		rowTitle.onclick = function()
		{
			if (_this.PreBuildList && !_this.fullyLoaded)
			{
				var __this = this;
				BX.showWait();
				setTimeout(function()
				{
					_this.PreBuildList();
					_this.HideGroup(__this._el, !__this._el.hidden, 0);
					BX.closeWait();
				}, 1);
			}
			else
				_this.HideGroup(this._el, !this._el.hidden, 0);
		};

		var len = arElement.childElements.length;
		if (len<=0)
			return;

		for (var i=0; i<len; i++)
			this.DisplayElement(arElement.childElements[i],oCont,-1,arElement.name);
	}
	else
	{
		//1st level subgroup
		if (sPath.indexOf(',')!=-1)
			return;

		try
		{
			var oGroup = this.GetGroup(oCont,sPath);
			var childCell = oGroup.rows[1].cells[0];

			var _oTable = document.createElement('TABLE');
			_oTable.cellPadding = 0;
			_oTable.cellSpacing = 0;
			_oTable.width = '100%';
			_oTable.className = 'bxgroupblock1';

			_oTable.setAttribute('__bxgroup1','__'+arElement.name);

			var rowTitle = _oTable.insertRow(-1); //group title
			var c = rowTitle.insertCell(-1); //plus
			c.unselectable = "on";
			c.style.width = '0%';
			c.className = 'pluscell1';
			c.appendChild(this.pMainObj.CreateElement("IMG", {src: one_gif_src, className: 'tskbr_common bx_btn_tabs_plus_big', id: this.pref + 'Plus_1_icon_'+arElement.name}));

			var c = rowTitle.insertCell(-1); //icon
			c.unselectable = "on";
			c.style.width = '0%';
			c.className = 'iconfoldercell1';

			c.appendChild(this.pMainObj.CreateElement("DIV", {className: 'tskbr_common bx_btn_tabs_folder_c', id: this.pref + 'Folder_1_icon_'+arElement.name}));

			c = rowTitle.insertCell(-1); // title
			c.unselectable = "on";
			c.className = 'titlecell1';
			c.innerHTML = (arElement.title) ? arElement.title : arElement.name;

			var rowData = _oTable.insertRow(-1); //Cell with child elements
			rowData.style.display = GetDisplStr(0);
			rowData.id = this.pref + 'Group_1_'+arElement.name;
			c = rowData.insertCell(-1);
			c.className = 'datacell1';
			c.colSpan = "3";

			rowTitle._el = arElement;
			rowTitle.onclick = function(){_this.HideGroup(this._el,!this._el.hidden,1)};

			childCell.appendChild(_oTable);

			arElement.hidden = true;

			var len = arElement.childElements.length;
			if (len<=0)
				return;
			for (var i=0;i<len;i++)
				this.DisplayElement(arElement.childElements[i],oCont,-1,arElement.name);
		}
		catch(e)
		{
			return false;
		}
	}
	rowTitle = null;
	rowData = null;
	rowBottom = null;
	c = null;
	r = null;
},

//sPath - path  in tree
AddElement: function(oElement, oCont, sPath, orderInd)
{
	if (orderInd==undefined)
		orderInd = -1;
	this.DisplayElement(oElement, oCont, orderInd, sPath || "");
},

RemoveElement: function(elName, oCont, sPath)
{
	if (sPath == "")
	{
		var child, __bxgroup;
		for (var i = 0; i < oCont.childNodes.length; i++)
		{
			child = oCont.childNodes[i];
			__bxgroup = child.getAttribute('__bxgroup');
			if (__bxgroup == '__' + elName)
				oCont.removeChild(child);
		}
	}
	else
	{
		var arPath = sPath.split(',');
		var _len = arPath.length;

		if (_len == 0 || _len > 1)
			return false;

		for (var iCh = 0;iCh<oCont.childNodes.length;iCh++)
		{
			try
			{
				var grName = oCont.childNodes[iCh].getAttribute('__bxgroup'), row;
				if(grName == '__'+arPath[0])
				{
					_oCont = BX(this.pref + 'Group_'+arPath[0]);
					for (var j=0;j<_oCont.rows.length;j++)
					{
						row = _oCont.rows[j];
						if (row.cells[0].id=='element_'+elName)
							row.parentNode.removeChild(row);
					}
					break;
				}
			}
			catch(e)
			{
				continue;
			}
		}
	}
	return true;
},

HideGroup: function (arElement, bHide, ilevel)
{
	if (ilevel==undefined)
		ilevel = 0;

	if (ilevel==0)
	{
		var im_plus = BX(this.pref + 'Group_plus_'+arElement.name);
		var elementsGroup = BX(this.pref + 'Group_'+arElement.name);
		if(!bHide)
		{
			arElement.hidden = false;
			elementsGroup.style.display = GetDisplStr(1);
			im_plus.className = 'tskbr_common bx_btn_tabs_minus_small';
		}
		else
		{
			arElement.hidden = true;
			elementsGroup.style.display = GetDisplStr(0);
			im_plus.className = 'tskbr_common bx_btn_tabs_plus_small';
		}
	}
	else if(ilevel==1)
	{
		var plusIcon = BX(this.pref + 'Plus_1_icon_'+arElement.name);
		var groupIcon = BX(this.pref + 'Folder_1_icon_'+arElement.name);
		var elementsGroup1 = BX(this.pref + 'Group_1_'+arElement.name);
		if(!bHide)
		{
			arElement.hidden = false;
			elementsGroup1.style.display = GetDisplStr(1);
			plusIcon.className = 'tskbr_common bx_btn_tabs_minus_big';
			groupIcon.className = "tskbr_common bx_btn_tabs_folder_o";
		}
		else
		{
			arElement.hidden = true;
			elementsGroup1.style.display = GetDisplStr(0);
			plusIcon.className = 'tskbr_common bx_btn_tabs_plus_big';
			groupIcon.className = "tskbr_common bx_btn_tabs_folder_c";
		}
	}
},

GetGroup: function(oCont, sPath)
{
	var arPath = sPath.split(',');
	var len = arPath.length, grName, grName2, newCont;
	if (len<=2)
	{
		for (var iCh = 0; iCh < oCont.childNodes.length; iCh++)
		{
			try
			{
				grName = oCont.childNodes[iCh].getAttribute('__bxgroup0');
				if(grName == '__'+arPath[0])
				{
					if (len==1)
						return oCont.childNodes[iCh];
					else
					{
						newCont = oCont.childNodes[iCh].rows[1].cells[0];
						for (var iCh2 = 0; iCh2<newCont.childNodes.length; iCh2++)
						{
							grName2 = newCont.childNodes[iCh2].getAttribute('__bxgroup1');
							if(grName2 == '__'+arPath[1])
								return newCont.childNodes[iCh2];
						}
					}

				}
			}
			catch(e)
			{
				continue;
			}
		}

	}
	return false;
},

insertHTML: function(_html){this.pMainObj.insertHTML(_html);}
}

//BXPropertiesTaskbar
function BXPropertiesTaskbar()
{
	ar_BXPropertiesTaskbarS.push(this);
	var obj = this;
	obj.bDefault = false;
	obj.emptyInnerHTML = "<br /><span style='padding-left: 15px;'>" + BX_MESS.SelectAnyElement + "</span>";

	BXPropertiesTaskbar.prototype.OnTaskbarCreate = function ()
	{
		this.pHeaderTable.setAttribute("__bxtagname", "_taskbar_properties");
		BX.addClass(obj.pWnd, "bx-props-taskbar")
		this.pMainObj.oPropertiesTaskbar = this;
		this.icon = 'properties';
		this.iconDiv.className = 'tb_icon bxed-taskbar-icon-' + this.icon;
		var table = this.pMainObj.pDocument.createElement("TABLE");
		table.style.width = "100%";
		this.pCellPath = table.insertRow(-1).insertCell(-1);
		this.pCellPath.className = "bxproptagspath";
		this.pCellProps = table.insertRow(-1).insertCell(-1);
		this.pCellProps.style.height = "100%";
		this.pCellProps.vAlign = "top";

		this.pWnd.appendChild(table);

		this.pCellPath.style.height = "0%";
		this.pCellProps.style.height = "100%";

		this.pCellProps = this.CreateScrollableArea(this.pCellProps);
		this.pCellProps.className = "bxtaskbarprops";
		this.pCellProps.innerHTML = obj.emptyInnerHTML;

		this.pMainObj.AddEventHandler("OnSelectionChange", obj.OnSelectionChange);

		table = null;
	}

	BXPropertiesTaskbar.prototype.OnSelectionChange = function (sReloadControl, pElement)
	{
		try{ // In split mode in IE fast view mode changing occurs Permission denied ERROR
		if (!obj.bActivated || !obj.pTaskbarSet.bShowing)
			return;

		var oSelected, pElementTemp, strPath = '';
		if (pElement)
			oSelected = pElement;
		else
			pElement = oSelected = obj.pMainObj.GetSelectionObject();

		if (pElement && pElement.ownerDocument != obj.pMainObj.pEditorDocument)
		{
			try{
				var pBody = obj.pMainObj.pEditorDocument.body;
				pElement = pBody.lastChild || pBody.appendChild(obj.pMainObj.pEditorDocument.createElement('BR'));
				obj.pMainObj.SelectElement(pElement);
			}catch(e){}
		}

		if(sReloadControl == "always" || !obj.oOldSelected || !BXElementEqual(oSelected, obj.oOldSelected))
		{
			obj.oOldSelected = oSelected;
			BX.cleanNode(obj.pCellPath);

			var tPath = BX.create("TABLE");
			tPath.className = "bxproptagspathinl";
			tPath.cellSpacing = 0;
			tPath.cellPadding = 1;
			var
				bxTag,
				rPath = tPath.insertRow(-1),
				cPath, pBut, oRange,
				cActiveTag = null,
				fPropertyPanel = null,
				fPropertyPanelElement = null;

			if(obj.pMainObj.pEditorDocument.body.createTextRange)
				oRange = obj.pMainObj.pEditorDocument.body.createTextRange();

			while(pElement && (pElementTemp = pElement.parentNode) != null)
			{
				if(pElementTemp.nodeType !=1 || !pElement.tagName)
				{
					pElement = pElementTemp;
					continue;
				}

				strPath = pElement.tagName.toLowerCase();

				bxTag = obj.pMainObj.GetBxTag(pElement);

				if (bxTag.tag)
				{
					strPath = bxTag.tag;
					fPropertyPanel = false;
					tPath.deleteRow(rPath);
					rPath = tPath.insertRow(-1);
				}

				if(strPath == 'tbody')
				{
					pElement = pElementTemp;
					continue;
				}

				cPath = rPath.insertCell(0);
				if(!fPropertyPanel && pPropertybarHandlers[strPath])
				{
					fPropertyPanel = pPropertybarHandlers[strPath];
					fPropertyPanelElement = pElement;
					cActiveTag = cPath;
				}

				cPath.innerHTML = '&lt;' + strPath + '&gt;';
				cPath.pElement = pElement;
				cPath.oRange = oRange;
				cPath.pMainObj = obj.pMainObj;
				cPath.onclick = function ()
				{
					if(this.oRange && this.oRange.moveToElementText)
					{
						this.oRange.moveToElementText(this.pElement);
						this.oRange.select();
					}
					else
					{
						this.pMainObj.pEditorWindow.getSelection().selectAllChildren(this.pElement);
					}
					this.pMainObj.OnEvent("OnSelectionChange");
				};

				pElement = pElementTemp;
			}

			// temp hack...
			var cPathLast = rPath.insertCell(-1);
			cPathLast.style.width = '100%';
			cPathLast.innerHTML = "&nbsp;";

			var bDefault = false;
			obj.pCellPath.appendChild(tPath);
			if(!fPropertyPanel)
			{
				fPropertyPanel = pPropertybarHandlers['default'];
				fPropertyPanelElement = oSelected;
				bDefault = true;
			}

			if(cActiveTag)
				cActiveTag.className = 'bxactive-tag';

			if(fPropertyPanelElement && fPropertyPanelElement.tagName && (!(obj.oOldPropertyPanelElement && BXElementEqual(fPropertyPanelElement, obj.oOldPropertyPanelElement)) || sReloadControl == "always"))
			{
				var sRealTag = fPropertyPanelElement.tagName.toLowerCase();
				bxTag = obj.pMainObj.GetBxTag(fPropertyPanelElement);

				if (bxTag.tag)
					sRealTag = bxTag.tag;
				obj.oOldPropertyPanelElement = fPropertyPanelElement;

				var bNew = false;
				if((sReloadControl == "always") || (bDefault && obj.bDefault != bDefault) || (!bDefault && (!obj.sOldTag || obj.sOldTag != sRealTag)))
				{
					obj.pMainObj.OnChange("OnPropertyChange", "");
					bNew = true;
					BX.cleanNode(obj.pCellProps);
				}

				obj.sOldTag = sRealTag;

				if(fPropertyPanel)
					fPropertyPanel(bNew, obj, fPropertyPanelElement);

				var w = (parseInt(obj.pTaskbarSet.pDataColumn.parentNode.offsetWidth) - 2);
				obj.pTaskbarSet.pDataColumn.style.width = (w > 0 ? w : 0) + 'px';
				obj.pMainObj.OnEvent("OnPropertybarChanged");
				ar_PROP_ELEMENTS.push(obj);
				obj.bDefault = bDefault;
			}
			tPath = rPath = cPath = pBut = null;
		}
		pElement = null;
		oSelected = null;

		}catch(e){}
		return true;
	}
}
oBXEditorUtils.addTaskBar('BXPropertiesTaskbar', 3 , BX_MESS.CompTBProp, [], 5);

function BXCreateTaskbars(pMainObj)
{
	var _sort = function(arr)
	{
		var l = arr.length, tmp, flag = false, i = 0;
		while (i < l - 1)
		{
			if (arr[i].sort - arr[i + 1].sort > 0)
			{
				tmp = arr[i + 1];
				arr[i + 1] = arr[i];
				arr[i] = tmp;
				i--;
			}
			i++;
		}
	}
	_sort(arBXTaskbars);

	if (!SETTINGS[pMainObj.name].arTaskbarSettings)
		SETTINGS[pMainObj.name].arTaskbarSettings = arTaskbarSettings_default;

	var _old_visualEffects = pMainObj.visualEffects;
	pMainObj.visualEffects = false;
	var i, aroTBSet, l = arBXTaskbars.length, oTB, tbkey, pTaskbar;

	for(i = 0; i < l; i++)
	{
		oTB = arBXTaskbars[i];
		tbkey = oTB.name + "_" + pMainObj.name;

		if (ar_BXTaskbarS[tbkey] && (ar_BXTaskbarS[tbkey].pMainObj.name == pMainObj.name) && !ar_BXTaskbarS[tbkey].bDeleted)
			continue;

		aroTBSet = SETTINGS[pMainObj.name].arTaskbarSettings[oTB.name];
		if (!aroTBSet || !pMainObj.allowedTaskbars[oTB.name])
			continue;

		if (!aroTBSet.show && oTB.name != "BXPropertiesTaskbar")
			continue;

		if (oTB.pos !== 2) // Right or bottom
			oTB.pos = 3;

		if ((oTB.arParams['bWithoutPHP'] === false && !pMainObj.arConfig["bWithoutPHP"]) ||
		oTB.arParams['bWithoutPHP'] !== false)
		{
			BX.extend(window[oTB.name], window.BXTaskbar);
			pTaskbar = new window[oTB.name]();

			pTaskbar.Create(oTB.name, pMainObj, oTB.title);
			pMainObj.arTaskbarSet[oTB.pos].AddTaskbar(pTaskbar, true);
		}
	}

	var tbs, l, i, j, bActivate;
	for(i in pMainObj.arTaskbarSet)
	{
		tbs = pMainObj.arTaskbarSet[i];
		if (!tbs || typeof tbs !== 'object' || !tbs.arTaskbars || !tbs.arTaskbars[0])
			continue;

		l = tbs.arTaskbars.length;

		if (l == 1 && !tbs.bShowing)
		{
			tbs.sActiveTaskbar = tbs.arTaskbars[0].id;
		}
		else
		{
			bActivate = false;
			for (j = 0; j < l; j++)
			{
				if (SETTINGS[pMainObj.name].arTaskbarSettings[tbs.arTaskbars[j].name].active)
				{
					tbs.ActivateTaskbar(tbs.arTaskbars[j].id, false);
					bActivate = true;
					break; // Activate and exit if we find active taskbar
				}
			}

			// If no active taskbars find - activate first
			if (!bActivate)
				tbs.ActivateTaskbar(tbs.arTaskbars[0].id, false);
		}
	}

	if (!pMainObj.oBXTaskTabs)
		pMainObj.oBXTaskTabs = new BXTaskTabs(pMainObj);

	// Refresh Taskbars
	var arTskbrSet = SETTINGS[pMainObj.name].arTaskbarSettings;
	var tId, tTitle, BXTaskbar;
	for (var k in ar_BXTaskbarS)
	{
		BXTaskbar = ar_BXTaskbarS[k];
		if(!pMainObj.CheckTaskbar(BXTaskbar))
			continue;

		tId = BXTaskbar.name;
		if (BXTaskbar.pMainObj.name != pMainObj.name || !arTskbrSet || !arTskbrSet[tId])
			continue;

		if (arTskbrSet[tId].show && !BXTaskbar.pTaskbarSet)
			pMainObj.arTaskbarSet[arTskbrSet[tId].position[0]].AddTaskbar(BXTaskbar, true);
		else if (!arTskbrSet[tId].show && BXTaskbar.pTaskbarSet)
			BXTaskbar.Close(false);
	}

	pMainObj.oBXTaskTabs.Draw();
	pMainObj.oBXTaskTabs.Refresh();

	setTimeout(function(){pMainObj.visualEffects = _old_visualEffects; pMainObj.oBXTaskTabs.Refresh();}, 500);
}

function BXTaskTabs(pMainObj)
{
	this.pMainObj = pMainObj;
	this.bRendered = false;
};

BXTaskTabs.prototype = {
	Draw: function()
	{
		var tbs, i, j, l, k, tb, pTab, _this = this, id;
		this.arTabs = [];
		this.arTabIndex = {};
		BX.cleanNode(this.pTabsCont);

		for(i in this.pMainObj.arTaskbarSet)
		{
			tbs = this.pMainObj.arTaskbarSet[i];
			if (!tbs || typeof tbs !== 'object' || !tbs.arTaskbars || !tbs.arTaskbars.length)
				continue;

			for(j = 0, k = tbs.arTaskbars.length; j < k; j++)
			{
				if (!this.bRendered)
				{
					this.pTabsCont = this.pMainObj.pTaskTabs.appendChild(BX.create("DIV", {props: {className: 'bxed-tasktab-cnt', unselectable : "on"}}));
					this.bRendered = true;
				}

				tb = tbs.arTaskbars[j];
				id = "tab_" + tb.id;

				pTab = BX.create("SPAN", {
					props: {
						id: id,
						className: "bxed-tasktab",
						title: tb.title,
						unselectable: "on"
					},
					events: {
						click: function(){_this.OnClick(this)},
						mouseover:  function(){_this.OnMouseOver(this)},
						mouseout:  function(){_this.OnMouseOut(this)}
					},
					html: '<i class="tasktab-left"></i><span class="tasktab-center"><span class="tasktab-icon bxed-taskbar-icon-' + tb.icon + '" unselectable="on"></span><span class="tasktab-text" unselectable="on">' + tb.title + '</span></span><i class="tasktab-right"></i>'
				});

				this.arTabs.push({
					id: id,
					cont: pTab,
					tb: tb,
					tbs: tbs,
					bPushed: tbs.sActiveTaskbar == tb.id
				});
				this.arTabIndex[id] = this.arTabs.length - 1;

				if (tbs.sActiveTaskbar == tb.id && tbs.bShowing)
					BX.addClass(pTab, "bxed-tasktab-pushed");

				this.pTabsCont.appendChild(pTab);
			}
		}
	},

	Refresh: function()
	{
		var i, l = this.arTabs.length, tab, bAct;
		for(i = 0; i < l; i++)
		{
			tab = this.arTabs[i];
			bAct = (tab.tbs.bShowing && tab.tb.id == tab.tbs.sActiveTaskbar);

			if (tab.bPushed == bAct)
				continue;

			tab.bPushed = bAct;
			if (bAct)
				BX.addClass(tab.cont, "bxed-tasktab-pushed");
			else
				BX.removeClass(tab.cont, "bxed-tasktab-pushed");
		}
	},

	OnClick: function(pObj)
	{
		var oTab = this.arTabs[this.arTabIndex[pObj.id]];
		if (oTab.bPushed)
		{
			oTab.bPushed = false;
			BX.removeClass(pObj, "bxed-tasktab-pushed");
			oTab.tbs.Hide();
		}
		else
		{
			oTab.bPushed = true;
			BX.addClass(pObj, "bxed-tasktab-pushed");
			if (!oTab.tbs.bShowing)
				oTab.tbs.Show();
			oTab.tbs.ActivateTaskbar(oTab.tb.id);
		}

		SETTINGS[oTab.tbs.pMainObj.name].arTBSetsSettings[oTab.tbs.iNum].show = oTab.bPushed;
	},

	OnMouseOver: function(pObj)
	{
		BX.addClass(pObj, "bxed-tasktab-over");
	},

	OnMouseOut: function(pObj)
	{
		BX.removeClass(pObj, "bxed-tasktab-over");
	},

	GetVPos: function()
	{
		var edPos = BX.pos(this.pMainObj.pWnd);
		return {
			t: parseInt(edPos.bottom) - 25,
			l: parseInt(edPos.left) + 50,
			w: 300,
			h: 25
		};
	}
}

function BXVisualMinimize(par)
{
	this.oDiv = document.body.appendChild(BX.create('DIV', {props: {className: 'visual_minimize'}, style: {display: 'none'}}));
}

BXVisualMinimize.prototype.Show = function(par)
{
	par.num = BX.browser.IsIE() ? 4 : 8;
	par.time = BX.browser.IsIE() ? 1 : 5;
	this.oDiv.style.display = 'block';

	var
		_this = this,
		i = 0,
		dt = Math.round((par.ePos.t - par.sPos.t) / par.num),
		dl = Math.round((par.ePos.l - par.sPos.l) / par.num),
		dw = Math.round((par.ePos.w - par.sPos.w) / par.num),
		dh = Math.round((par.ePos.h - par.sPos.h) / par.num),
		show = function()
		{
			i++;
			if (i > par.num - 1)
			{
				clearInterval(intId);
				_this.oDiv.style.display = 'none';
				if (par.callback)
					par.callback();
				return;
			}

			_this.oDiv.style.top = (par.sPos.t + dt * i) + 'px';
			_this.oDiv.style.left = (par.sPos.l + dl * i) + 'px';
			_this.oDiv.style.width = Math.abs(par.sPos.w + dw * i) + 'px';
			_this.oDiv.style.height = Math.abs(par.sPos.h + dh * i) + 'px';
		},
		intId = setInterval(show, par.time);
};

function showTranspToggle(arParams)
{
	if (!arParams) return;
	var pMainObj = arParams.pMainObj;
	var e = arParams.e;

	// For main document
	var TranspToggleMove = function(e)
	{
		e = getRealMousePos(e, pMainObj);
		if (arParams.bVertical)
			TranspToggle.style.top = adjustValue(arParams.pos.top, dY + e.realY);
		else
			TranspToggle.style.left = adjustValue(arParams.pos.left, dX + e.realX);
	};

	// For editor document
	var TranspToggleMoveF = function(e)
	{
		e = getRealMousePos(e, pMainObj, true);
		if (arParams.bVertical)
			TranspToggle.style.top = adjustValue(arParams.pos.top, dY + e.realY);
		else
			TranspToggle.style.left = adjustValue(arParams.pos.left, dX + e.realX);
	};

	var adjustValue = function(value, new_value)
	{
		var _cursor = cursor;

		if ((new_value < value) && (value - new_value > maxDiff))
		{
			new_value = value - maxDiff;
			_cursor = "not-allowed";
		}
		else if((new_value > value) && (new_value - value > minDiff))
		{
			new_value = value + minDiff;
			_cursor = "not-allowed";
		}

		if (curCursor != _cursor)
		{
			curCursor = _cursor;
			pBXEventDispatcher.SetCursor(curCursor);
		}

		return new_value + 'px';
	};

	// MouseUp handler
	var TranspToggleMouseUp = function()
	{
		pMainObj.arTaskbarSet[2]._SetTmpClass(true);

		// Clean event handlers
		removeAdvEvent(document, "mousemove", TranspToggleMove, true);
		removeAdvEvent(document, "mouseup", TranspToggleMouseUp, true);
		removeAdvEvent(pMainObj.pEditorDocument, "mousemove", TranspToggleMoveF, true);
		removeAdvEvent(pMainObj.pEditorDocument, "mouseup", TranspToggleMouseUp, true);

		if (BX.browser.IsIE())
		{
			removeAdvEvent(pMainObj.pEditorDocument, "selectstart", preventselect, true);
			removeAdvEvent(document, "selectstart", preventselect, true);
		}

		// Remove toggle
		TranspToggle.style.display = 'none';
		pBXEventDispatcher.SetCursor("default");

		var value = arParams.value - (arParams.bVertical ? (parseInt(TranspToggle.style.top) - arParams.pos.top) : (parseInt(TranspToggle.style.left) - arParams.pos.left));

		if (arParams.callbackObj)
			arParams.callbackFunc.apply(arParams.callbackObj, [value]);
		else
			arParams.callbackFunc(value);
	};

	var
		w, h, dY, dX, cursor, className, top, left,
		maxDiff = arParams.maxValue - arParams.value,
		minDiff = arParams.value - arParams.minValue;
	e = getRealMousePos(e, pMainObj);

	if (arParams.bVertical)
	{
		w = parseInt(arParams.width) + "px",
		h = parseInt(arParams.height ? arParams.height : 6)  + "px";
		dY = e.realY - parseInt(arParams.pos.top) - 6;
		top = (arParams.pos.top + 10) + "px";
		left = arParams.pos.left + "px";
		cursor = "row-resize";
		className = "transp_tog_h";
	}
	else
	{
		h = parseInt(arParams.height) + "px";
		w = parseInt((arParams.width) ? arParams.width : 6) + "px";
		dX = e.realX - parseInt(arParams.pos.left) - 6;
		top = arParams.pos.top + "px";
		left = (arParams.pos.left  + 10) + "px";
		cursor = "col-resize";
		className = "transp_tog_v";
	}
	var curCursor = cursor;
	pBXEventDispatcher.SetCursor(cursor);

	// Create toggle
	var TranspToggle = CACHE_DISPATCHER['TranspToggle'];
	BX.adjust(TranspToggle, {props: {className: className}, style: {display: 'block', width: w, height: h, top: top, left: left}});

	addAdvEvent(document, "mousemove", TranspToggleMove, true);
	addAdvEvent(document, "mouseup", TranspToggleMouseUp, true);
	addAdvEvent(pMainObj.pEditorDocument, "mousemove", TranspToggleMoveF, true);
	addAdvEvent(pMainObj.pEditorDocument, "mouseup", TranspToggleMouseUp, true);

	if (BX.browser.IsIE())
	{
		addAdvEvent(pMainObj.pEditorDocument, "selectstart", preventselect, true);
		addAdvEvent(document, "selectstart", preventselect, true);
	}

	return BX.PreventDefault(e);
}

// # # # # # # # # # # # # # # # #    ONE BIG TOOLBAR # # # # # # # # # # # # # # # #
// For lightMode == true
function BXGlobalToolbar(pMainObj)
{
	this.pMainObj = pMainObj;
	this.oCont = this.pMainObj.pTopToolbarset;
	this.oCont.style.display = this.oCont.parentNode.style.display = "";
	this.oCont.className = "bxedtoolbarset";
	this.oCont.unselectable = "on";
	this.oCont.appendChild(BX.create("DIV", {style: {width: '100%'}}));
}

BXGlobalToolbar.prototype = {
	AddButton: function(pButton)
	{
		this.oCont.firstChild.appendChild(BX.create("DIV", {props: {className: "bx-g-tlbr-but"}})).appendChild(pButton.pWnd);
	},

	LineBegin: function(bFirst)
	{
		// Hack for IE 7
		if (!bFirst && BX.browser.IsIE())
			this.oCont.firstChild.appendChild(BX.create("IMG", {props: {src: one_gif_src, className: "bx-g-tlbr-line-ie"}}));
		this.oCont.firstChild.appendChild(BX.create("DIV", {props: {className: "bx-g-tlbr-line-begin"}}));
	},

	LineEnd: function()
	{
		this.oCont.firstChild.appendChild(BX.create("DIV", {props: {className: "bx-g-tlbr-line-end"}}));
	}
}