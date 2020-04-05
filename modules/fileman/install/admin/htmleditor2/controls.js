//Colors of borders and backgrounds for diferent button states
var borderColorNormal = "#e4e2dc";
var borderColorOver = "#4B4B6F";
var borderColorSet = "#4B4B6F";
var borderColorSetOver = "#4B4B6F";

var bgroundColorOver = "#FFC678";
var bgroundColorSet = "#FFC678";
var bgroundColorSetOver = "#FFA658";

// BXButton - class
function BXButton()
{
	this._prevDisabledState = false;
}

BXButton.prototype = {
_Create: function ()
{
	if(this.OnCreate && this.OnCreate()==false)
		return false;

	var obj = this;

	if (this.id && this.iconkit)
	{
		this.pWnd = this.CreateElement("IMG", {src: one_gif_src, alt: (this.title ? this.title : this.name), title: (this.title?this.title:this.name), width: '20', height: '20', id: "bx_btn_"+obj.id});
		this.pWnd.className = 'bxedtbutton';
		this.pWnd.style.backgroundImage = "url(" + image_path + "/" + this.iconkit + ")";
	}
	else
	{
		this.pWnd = this.CreateElement("IMG", {'src' : this.src, 'alt' : (this.title ? this.title : this.name), 'title': (this.title ? this.title : this.name), 'width' : '20', 'height' : '20'});
		this.pWnd.className = 'bxedtbutton';
	}

	if (this.show_name)
	{
		var _icon = this.pWnd;
		this.pWnd = BX.create("TABLE", {props: {className: "bxedtbuttonex", title: this.title ? this.title: this.name, id: "bx_btnex_" + obj.id}});

		this.pWnd.checked = false;
		this.pWnd.disabled = false;

		var r = this.pWnd.insertRow(-1);
		r.insertCell(-1).appendChild(_icon);
		BX.adjust(r.insertCell(-1), {props: {className: 'tdbutex_txt'}, html: "<div>" + this.name + "</div>"});
	}
	else
	{
		this.pWnd.style.borderColor  = borderColorNormal;
		this.pWnd.style.borderWidth = "1px";
		this.pWnd.style.borderStyle = "solid";
	}

	if(!this.no_actions || this.no_actions != true) // for context menu
	{
		this.pWnd.onmouseover = function(e)
		{
			if(!this.disabled)
			{
				if (this.nodeName.toLowerCase() == 'table')
				{
					BX.addClass(this, 'bxedtbuttonex-over');
					if (BX.browser.IsOpera())
						this.border = "1px solid #4B4B6F"; // Special for Opera
				}
				else
				{
					this.style.borderColor = borderColorOver;
					this.style.border = "#4B4B6F 1px solid";
					this.style.backgroundColor = this.checked ? bgroundColorSetOver : bgroundColorOver;
				}
			}
		};

		this.pWnd.onmouseout = function(e)
		{
			if(!this.disabled)
			{
				if (this.nodeName.toLowerCase() == 'table')
				{
					BX.removeClass(this, 'bxedtbuttonex-over');
					if (BX.browser.IsOpera())
						this.border = "1px solid #E4E2DC"; // Special for Opera
				}
				else
				{
					this.style.borderColor = this.checked ? borderColorSet : borderColorNormal;
					this.style.backgroundColor = this.checked ? bgroundColorSet : 'transparent';
				}
			}
		};
		if (this.defaultState)
			this.Check(true);

		addCustomElementEvent(this.pWnd, 'click', this.OnClick, this);
		this.pMainObj.AddEventHandler("OnSelectionChange", this._OnSelectionChange, this);
		this.pMainObj.AddEventHandler("OnChangeView", this.OnChangeView, this);
	}
},

_OnChangeView: function (mode, split_mode)
{
	mode = (mode == 'split' ? split_mode : mode);
	if(mode == 'code' && !this.codeEditorMode || (mode=='html' && this.hideInHtmlEditorMode))
	{
		this._prevDisabledState = this.pWnd.disabled;
		this.Disable(true);
	}
	else if(mode == 'code' && this.codeEditorMode || (this.hideInHtmlEditorMode && mode != 'html'))
		this.Disable(false);
	else if(!this.codeEditorMode)
		this.Disable(this._prevDisabledState);
},

OnChangeView: function (mode, split_mode)
{
	this._OnChangeView(mode, split_mode);
},

Disable: function (bFlag)
{
	if(bFlag == this.pWnd.disabled)
		return false;
	this.pWnd.disabled = bFlag;
	if(bFlag)
	{
		BX.addClass(this.pWnd, 'bxedtbutton-disabled');
		if (this.id && this.iconkit)
		{
			//this.pWnd.className = 'bxedtbuttondisabled';
			//this.pWnd.style.backgroundImage = "url(" + image_path + "/" + this.iconkit + ")";
		}
		else
		{
			//this.pWnd.className = 'bxedtbuttondisabled';
		}
		//this.pWnd.style.filter = 'gray() alpha(opacity=30)';
	}
	else
	{
		BX.removeClass(this.pWnd, 'bxedtbutton-disabled');
		//this.pWnd.style.filter = '';
		//this.pWnd.className = 'bxedtbutton';
		if(this.pWnd.checked)
		{
			this.pWnd.style.borderColor = borderColorSet;
			this.pWnd.style.backgroundColor = bgroundColorSet;
		}
		else
		{
			this.pWnd.style.backgroundColor ="";
			this.pWnd.style.borderColor = borderColorNormal;
		}
	}
},

Check: function (bFlag)
{
	if(bFlag == this.pWnd.checked)
		return false;
	this.pWnd.checked = bFlag;
	if(!this.pWnd.disabled)
	{
		if(this.pWnd.checked)
		{
			this.pWnd.style.borderColor = borderColorSet;
			this.pWnd.style.backgroundColor = bgroundColorSet;
		}
		else
		{
			this.pWnd.style.backgroundColor ="";
			this.pWnd.style.borderColor = borderColorNormal;
		}
	}
},

OnMouseOver: function (e)
{
	if(!this.disabled)
	{
		this.style.borderColor = borderColorOver;
		this.style.border = "#4B4B6F 1px solid";
		if(this.checked)
			this.style.backgroundColor = bgroundColorSetOver;
		else
			this.style.backgroundColor = bgroundColorOver;
	}
},

OnMouseOut: function (e)
{
	if(!this.disabled)
	{
		if(this.checked)
		{
			this.style.borderColor = borderColorSet;
			this.style.backgroundColor = bgroundColorSet;
		}
		else
		{
			this.style.backgroundColor ="";
			this.style.borderColor = borderColorNormal;
		}
	}
},

OnClick: function (e)
{
	if(this.pWnd.disabled) return false;
	this.pMainObj.SetFocus();
	var res = false;
	if(this.handler)
		if(this.handler(this.pMainObj) !== false)
			res = true;

	if(!res)
		res = this.pMainObj.executeCommand(this.cmd);

	if(!this.bNotFocus)
		this.pMainObj.SetFocus();

	return res;
},

_OnSelectionChange: function()
{
	if(this.OnSelectionChange)
		this.OnSelectionChange();
	else if(this.cmd)
	{
		var res;

		if(this.cmd=='Unlink' && !BXFindParentByTagName(this.pMainObj.GetSelectionObject(), 'A'))
			res = 'DISABLED';
		else
			res = this.pMainObj.queryCommandState(this.cmd);

		if(res == 'DISABLED')
			this.Disable(true);
		else if(res == 'CHECKED')
		{
			this.Disable(false);
			this.Check(true);
		}
		else
		{
			this.Disable(false);
			this.Check(false);
		}
	}
}
};

function BXButtonSeparator(){}
BXButtonSeparator.prototype._Create = function ()
{
	this.pWnd = this.CreateElement("DIV", {className: 'bxseparator'});
	this.OnToolbarChangeDirection = function(bVertical)
	{
		if(bVertical)
			BX.addClass(this.pWnd, 'bxseparator-ver');
		else
			BX.removeClass(this.pWnd, 'bxseparator-ver');
	};
}

// BXEdList - class
function BXEdList()
{
	this.iSelectedIndex = -1;
	this.disabled = false;
	this.bCreated = false;
	this.bOpened = false;
	this.zIndex = 2090;

	this.CSS = "div.bx-list-cont {background-color: #fff; display: none; overflow: auto; overflow-x: hidden; overflow-y: auto; text-overflow: ellipsis;}" +
"div.bx-list-cont-vis-ef{overflow: hidden!important;}" +
"div.bx-list-cont table.bx-list-popup-tbl{width: 100%!important; border-collapse: collapse !important;}" +
"div.bx-list-cont table.bx-list-popup-tbl td{padding: 0!important;}" +
"div.bx-list-cont .bx-list-item{background: #fff; padding: 0px !important; border: 1px solid #fff; padding: 3px 4px !important; margin: 1px 0!important; cursor: default!important; font-family: Verdana,Tahoma,Courier New !important;}" +
"div.bx-list-cont .bx-list-item-over{border: 1px solid #4B4B6F; background-color: #FFC678 !important;}" +
"div.bx-list-cont .bx-list-item *{padding: 0!important; margin: 0!important; font-family: Verdana,Tahoma,Courier New !important;}" +
"div.bx-list-cont table.bx-list-item{border-collapse: collapse!important; width:100%!important; padding: 0!important;}" +
"div.bx-list-cont table.bx-list-item td{padding: 3px 4px !important;}" +
"div.bx-list-cont a.bx-list-conf-link{display: block!important; font-size: 11px!important; margin: 5px!important; color: #000!important; cursor: pointer!important;}" +
"div.bx-list-cont  td.bx-list-conf-cell{background: #FFF!important; border-top: 2px solid #808080!important;}" +
"div.bx-list-cont  td.bx-list-conf-cell a{color: #000!important; font-weight: normal!important; text-decoration: underline!important; font-size: 14px!important; cursor: pointer!important; display: block!important; margin: 5px 10px;}";
}

BXEdList.prototype = {
_Create: function ()
{
	if(this.OnCreate && this.OnCreate()==false)
		return false;

	if (this.maxHeight)
		this.maxHeight = parseInt(this.maxHeight);

	this.width = parseInt(this.width) || 160;
	this.height = parseInt(this.height) || 250;
	this.field_size = parseInt(this.field_size) || 75;

	if(this.OnSelectionChange)
		this.pMainObj.AddEventHandler("OnSelectionChange", this.OnSelectionChange, this);

	if(this.disableOnCodeView)
		this.pMainObj.AddEventHandler("OnChangeView", this.OnChangeView, this);

	this.pWnd = BX.create("DIV", {props: {className: 'bx-list'}});

	if (BX.browser.IsIE() && !BX.browser.IsDoctype())
		this.pWnd.style.height = "20px";

	this.pWnd.appendChild(BX.create("IMG", {props: {src: one_gif_src, className: 'bx-list-over'}}));

	var
		pTable = this.pWnd.appendChild(BX.create("TABLE")),
		r = pTable.insertRow(-1);

	if (this.field_size)
		this.pWnd.style.width = pTable.style.width = this.field_size + "px";

	this.pTitleCell = r.insertCell(-1);
	this.pTitle = this.pTitleCell.appendChild(BX.create("DIV", {props: {className: "bx-listtitle", unselectable: "on"}, text: this.title || "", style: {width: (this.field_size - 24) + "px"}}));

	BX.adjust(r.insertCell(-1), {props: {className: 'bx-listbutton', unselectable: "on"}, html: '&nbsp;'});
	this.pWnd.onmouseover = BX.proxy(this.OnMouseOver, this);
	this.pWnd.onmouseout = BX.proxy(this.OnMouseOut, this);
	this.pWnd.onclick = BX.proxy(this.OnClick, this);

	this.Create();

	if (this.values)
		this.SetValues(this.values);

	if(this._OnInit && typeof this._OnInit == 'function')
		this._OnInit();

	if(this.OnInit && this.OnInit() == false)
		return false;

	return true;
},

Create: function ()
{
	if (!BXPopupWindow.bCreated)
		BXPopupWindow.Create();

	this.pPopupNode = BXPopupWindow.pDocument.body.appendChild(BX.create("DIV", {props: {className: "bx-list-cont"}, style: {zIndex: this.zIndex}}, BXPopupWindow.pDocument));

	this.bCreated = true;
	this.pPopupNode.style.width = this.width + "px";
	this.pPopupNode.style.height = this.height + "px";

	this.pDropDownList = this.pPopupNode.appendChild(BX.create("TABLE", {props: {className: "bx-list-popup-tbl", unselectable: 'on'}}, BXPopupWindow.pDocument));
},

OnClick: function (e)
{
	if(this.disabled)
		return false;

	if (this.bOpened)
		return this.Close();

	this.Open();
	this.ShowPopup(true);
},

ShowPopup: function(bOpen)
{
	var pFrame = BXPopupWindow.pFrame;
	if (bOpen)
	{
		pFrame.height = "1px";
		pFrame.width = this.field_size + "px";
	}

	var
		_this = this,
		curHeight = bOpen ? 1 : parseInt(pFrame.height),
		curWidth = bOpen ? this.field_size : parseInt(pFrame.width),
		count = 0,
		timeInt = BX.browser.IsIE() ? 1 : 8,
		maxHeight = 0,
		maxWidth = this.width,
		dx = 20,
		dy = BX.browser.IsIE() ? 20 : 10;

	if (this.Interval)
		clearInterval(this.Interval);

	BX.addClass(_this.pPopupNode, "bx-list-cont-vis-ef");

	this.Interval = setInterval(function()
		{
			if (bOpen)
			{
				if (maxHeight == 0)
				{
					maxHeight = parseInt(_this.pDropDownList.offsetHeight);
					if (_this.maxHeight && maxHeight >= _this.maxHeight)
						maxHeight = _this.maxHeight;
				}

				curHeight += Math.round(dy * count);
				curWidth += Math.round(dx * count);

				if (curWidth > maxWidth)
					curWidth = maxWidth;

				if (curHeight > maxHeight)
				{
					BX.removeClass(_this.pPopupNode, "bx-list-cont-vis-ef");
					clearInterval(_this.Interval);

					if (BX.browser.IsIE())
						_this.pDropDownList.style.width = (parseInt(_this.pDropDownList.offsetWidth) - 2) + "px";

					curHeight = parseInt(_this.pDropDownList.offsetHeight);
					if (_this.maxHeight && curHeight >= _this.maxHeight)
						curHeight = _this.maxHeight;
				}
			}
			else
			{
				curHeight -= Math.round(dy * count);
				curWidth -= Math.round(dx * count);
				if (curWidth < _this.field_size)
					curWidth = _this.field_size;

				if (curHeight < 0)
				{
					BX.removeClass(_this.pPopupNode, "bx-list-cont-vis-ef");
					_this._Close();
					curHeight = 0;
					clearInterval(_this.Interval);
				}
			}

			pFrame.width = _this.pPopupNode.style.width = curWidth + 'px';
			pFrame.height = _this.pPopupNode.style.height = curHeight + 'px';
			count++;
		},
		timeInt
	);
},

Open: function ()
{
	var
		pOverlay = this.pMainObj.oTransOverlay.Show(),
		pos = BX.pos(this.pWnd),
		_this = this;

	BX.bind(document, "keyup", BX.proxy(this.OnKey, this));
	BX.bind(this.pMainObj.pEditorDocument, "keyup", BX.proxy(this.OnKey, this));

	oPrevRange = BXGetSelectionRange(this.pMainObj.pEditorDocument, this.pMainObj.pEditorWindow);
	pOverlay.onclick = function(){_this.Close()};

	if(this.bSetGlobalStyles)
		BXPopupWindow.SetStyles(this.CSS);
	else
		BXPopupWindow.SetStyles(this.CSS + "\n\n" + this.pMainObj.oStyles.sStyles, false);

	BXPopupWindow.Show({
		top: pos.top + (BX.browser.IsIE() && !BX.browser.IsDoctype() ? 17 : 19),
		left: pos.left + (BX.browser.IsIE() && !BX.browser.IsDoctype() ? -2 : 0),
		node: this.pPopupNode,
		width: this.width,
		height: this.height
	});
	this.bOpened = true;
},

Close: function ()
{
	this.ShowPopup(false);
},

_Close: function ()
{
	BXPopupWindow.Hide();
	this.pPopupNode.style.display = 'none';
	this.pMainObj.oTransOverlay.Hide();

	BX.unbind(document, "keyup", BX.proxy(this.OnKey, this));
	BX.unbind(this.pMainObj.pEditorDocument, "keyup", BX.proxy(this.OnKey, this));
	this.bOpened = false;
},

OnKey: function (e)
{
	if(!e)
		e = window.event;
	if(e.keyCode == 27 && this.bOpened)
		this.Close();
},

SetValues: function (values)
{
	if (typeof values == 'object')
		this.values = values;

	BX.cleanNode(this.pDropDownList);

	var c, item, _this = this, i, l = this.values.length;
	for(i = 0; i < l; i++)
	{
		this.values[i].index = i;
		c = this.pDropDownList.insertRow(-1).insertCell(-1);
		item = c.appendChild(BX.create("DIV", {props: {className: "bx-list-item", title: this.values[i].name}}, BXPopupWindow.pDocument));
		item.innerHTML = this.OnDrawItem ? this.OnDrawItem(this.values[i]) : this.values[i].name;
		item.value = this.values[i];
		if(this.bSetFontSize)
			item.style.fontSize = "12px";

		item.onmouseover = function (e){BX.addClass(this, "bx-list-item-over");};
		item.onmouseout = function (e){BX.removeClass(this, "bx-list-item-over");};
		item.onclick = function ()
		{
			if (oPrevRange)
				BXSelectRange(oPrevRange, _this.pMainObj.pEditorDocument, _this.pMainObj.pEditorWindow);
			_this.Close();
			_this._OnChange(this.value);
			_this.FireChangeEvent();
		};
	}

	if (this.bAdminConfigure && false)
	{
		c = this.pDropDownList.insertRow(-1).insertCell(-1);
		c.className = "bx-list-conf-cell";
		var pConf = c.appendChild(BX.create("A", {props: {className: "bx-list-conf-link", title: BX_MESS.ListConfigTitle, href: "javascript:void(0);"}, text: BX_MESS.ListConfig}, BXPopupWindow.pDocument));
		pConf.onclick = function()
		{
			_this.Close();
		}
	}
},

_OnChangeView: function (mode, split_mode)
{
	mode = (mode=='split'?split_mode:mode);
	this.Disable(mode=='code');
},

OnChangeView: function (mode, split_mode)
{
	this._OnChangeView(mode, split_mode);
},

Disable: function(flag)
{
	if(this.disabled == flag)
		return false;
	this.disabled = flag;

	if(flag)
		BX.addClass(this.pWnd, "bx-list-disabled");
	else
		BX.removeClass(this.pWnd, "bx-list-disabled");
},

FireChangeEvent: function()
{
	if(this.OnChange)
		this.OnChange(this.arSelected);
},

_OnChange: function (selected)
{
	this.Select(selected["index"]);
},

SetValue: function(val)
{
	if(!this.pTitle)
		return;

	this.pTitle.innerHTML = val || this.title || '';
},

OnMouseOver: function(e)
{
	if(!this.disabled)
		BX.addClass(this.pWnd, "bx-list-over");
},

OnMouseOut: function(e)
{
	if(!this.disabled)
		BX.removeClass(this.pWnd, "bx-list-over");
},

Select: function(v)
{
	if(this.iSelectedIndex == v || v >= this.values.length)
		return;

	var sel = this.values[v];
	this.iSelectedIndex = v;
	this.arSelected = sel;
	this.SetValue(sel["name"]);
},

SelectByVal: function(val, bAddIfNotList)
{
	if(val)
	{
		var i, l = this.values.length;
		for(i = 0; i < l; i++)
		{
			if(this.values[i].value == val)
				return this.Select(i);
		}

		if (bAddIfNotList)
		{
			var ind = this.values.length;
			this.values.push({name: val, value: val});
			this.SetValues(this.values);
			if (this.CreateListRow)
				this.additionalClass = val;
			return this.Select(ind);
		}
	}
	else
	{
		this.SetValue(this.title || '');
		this.iSelectedIndex = -1;
	}
},

OnToolbarChangeDirection: function (bVertical)
{
	if(bVertical)
	{
		this.pWnd.style.width = "18px";
		this.pTitleCell.style.visibility = "hidden";
	}
	else
	{
		this.pWnd.style.width = this.field_size;
		this.pTitleCell.style.visibility = "inherit";
	}
	//this.pWnd.className = 'bx-list';
}
};

// BXStyleList - class
function BXStyleList(){}
BXStyleList.prototype = new BXEdList;

BXStyleList.prototype._OnInit = function()
{
	this.pMainObj.AddEventHandler("OnTemplateChanged", this.FillList, this);
	this.FillList();
}

BXStyleList.prototype.FillList = function()
{
	var i, j, arStyles, l;

	BX.cleanNode(this.pDropDownList);

	if(!this.filter)
		this._SetFilter();

	this.values = [];
	if(!this.tag_name)
		this.tag_name = '';

	//"clear style" item
	this.CreateListRow('', BX_MESS.DeleteStyleOpt, {value: '', name: BX_MESS.DeleteStyleOptTitle});

	var
		style_title, counter = 0,
		arStyleTitle = this.pMainObj.arTemplateParams["STYLES_TITLE"];

	// other styles
	for(i = 0, l = this.filter.length; i < l;  i++)
	{
		arStyles = this.pMainObj.oStyles.GetStyles(this.filter[i]);
		for(j = 0; j < arStyles.length; j++)
		{
			if(arStyles[j].className == '')
				continue;

			if(this.pMainObj.arTemplateParams && arStyleTitle && arStyleTitle[arStyles[j].className])
				style_title = arStyleTitle[arStyles[j].className] ;
			else if(!this.pMainObj.arConfig["bUseOnlyDefinedStyles"])
			 	style_title = arStyles[j].className;
			else
			 	continue;

			this.CreateListRow(arStyles[j].className, style_title, {value: arStyles[j].className, name: style_title});
			counter++;
		}
	}

	if (this.additionalClass)
		this.CreateListRow(this.additionalClass, this.additionalClass, {value: this.additionalClass, name: this.additionalClass});

	if (this.deleteIfNoItems)
		this.pWnd.style.display = (counter == 0) ? "none" : "block";
};

BXStyleList.prototype.CreateListRow = function(className, name, value)
{
	value.index = this.values.length;

	var
		_this = this,
		c = this.pDropDownList.insertRow(-1).insertCell(-1),
		itemTable = c.appendChild(BX.create("TABLE", {props: {className: "bx-list-item", title: name, unselectable: "on"}}, BXPopupWindow.pDocument)),
		itemRow = itemTable.insertRow(-1),
		itemCell = itemRow.insertCell(-1);

	itemCell.innerHTML = name;
	if(this.bSetFontSize)
		itemCell.style.fontSize = "12px";

	if (this.pMainObj.bRenderStyleList)
	{
		switch(this.tag_name.toUpperCase())
		{
			case "TD":
				itemCell.className = className;
				break;
			case "TABLE":
				itemTable.className = className;
				break;
			case "TR":
				itemRow.className = className;
				break;
			default:
				itemCell.innerHTML = '<span class="' + className + '">'+name+'</span>';
		}
	}

	itemTable.value = value;
	itemTable.onmouseover = function (e){BX.addClass(this, "bx-list-item-over");};
	itemTable.onmouseout = function (e){BX.removeClass(this, "bx-list-item-over");};
	itemTable.onclick = function (e)
	{
		_this.Close();
		_this._OnChange(this.value);
		_this.FireChangeEvent();

		if(this.value.value=='')
			_this.SelectByVal();
	};

	this.values.push(value);
};

BXStyleList.prototype.OnChange = function(arSelected)
{
	this.pMainObj.WrapSelectionWith("SPAN", {props: {className: arSelected["value"]}});
};

BXStyleList.prototype._SetFilter = function()
{
	this.filter = ["DEFAULT"];
};

BXStyleList.prototype.OptimizeSelection = function(params)
{
	var
		arNodes = params.nodes,
		node, i, l = arNodes.length;


	for(i = 0; i < l; i++)
	{
		node = arNodes[i];

		// Check parrent nodes
		if (node.parentNode)
		{
		}

		// Check child nodes
		this.CleanChildsClass(node);
	}
},

BXStyleList.prototype.RemoveClass = function(pElement, params)
{
	if(!pElement)
		return;

	var bFind = false, tag;

	while(!bFind)
	{
		if (!pElement)
			break;

		if (pElement.nodeType == 1)
		{
			tag = pElement.nodeName.toLowerCase();
			if (tag == 'span' || tag == 'font' && pElement.className)
			{
				bFind = true;
				break;
			}
		}
		pElement = pElement.parentNode;
	}

	if (bFind)
	{
		pElement.className = '';
		pElement.removeAttribute('class');
		if (this.CheckNodeAttributes(pElement))
			BXCutNode(pElement);

		// Clean childs
		this.CleanChildsClass(pElement);
	}
},

BXStyleList.prototype.CleanChildsClass = function(node)
{
	CheckChilds(node, {func: function(node)
	{
		if (node.nodeType != 1)
			return;
		var tag = node.nodeName.toLowerCase();
		if (tag == 'span' || tag == 'font' && node.className != "")
		{
			node.className = '';
			node.removeAttribute('class');
			if (this.CheckNodeAttributes(node))
				BXCutNode(node);
		}
	}
	, obj: this});
},

BXStyleList.prototype.CheckNodeAttributes = function(node)
{
	var bClean = node.attributes.length <= 0;
	if (!bClean)
	{
		var
			bAtrExists = false, val, name, j,
			n = node.attributes.length,
			checkableAttributes = {
				title: true,
				id: true,
				name: true,
				style: true
			};

		for (j = 0; j < n; j++)
		{
			val = BX.util.trim(node.attributes[j].value);
			name = node.attributes[j].name.toString().toLowerCase();
			if (checkableAttributes[name] && val != "" && val != "null")
			{
				bAtrExists = true;
				break;
			}
		}

		if (!bAtrExists)
			bClean = true;
	}
	return bClean;
}


function BXTransOverlay(arParams)
{
	this.id = 'lhe_trans_overlay';
	this.zIndex = arParams.zIndex || 100;
}

BXTransOverlay.prototype = {
Create: function ()
{
	this.bCreated = true;
	this.bShowed = false;
	var windowSize = BX.GetWindowScrollSize();
	this.pWnd = document.body.appendChild(BX.create("DIV", {props: {id: this.id, className: "bxed-trans-overlay"}, style: {zIndex: this.zIndex, width: windowSize.scrollWidth + "px", height: windowSize.scrollHeight + "px"}}));

	this.pWnd.ondrag = BX.False;
	this.pWnd.onselectstart = BX.False;
},

Show: function(arParams)
{
	if (!this.bCreated)
		this.Create();
	this.bShowed = true;

	var windowSize = BX.GetWindowScrollSize();

	this.pWnd.style.display = 'block';
	this.pWnd.style.width = windowSize.scrollWidth + "px";
	this.pWnd.style.height = windowSize.scrollHeight + "px";

	if (!arParams)
		arParams = {};

	if (arParams.zIndex)
		this.pWnd.style.zIndex = arParams.zIndex;

	BX.bind(window, "resize", BX.proxy(this.Resize, this));
	return this.pWnd;
},

Hide: function ()
{
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
};

function BXEdColorPicker()
{
	this.disabled = false;
	this.bCreated = false;
	this.bOpened = false;
	this.zIndex = 2090;

	this.arColors = [
		'#FF0000', '#FFFF00', '#00FF00', '#00FFFF', '#0000FF', '#FF00FF', '#FFFFFF', '#EBEBEB', '#E1E1E1', '#D7D7D7', '#CCCCCC', '#C2C2C2', '#B7B7B7', '#ACACAC', '#A0A0A0', '#959595',
		'#EE1D24', '#FFF100', '#00A650', '#00AEEF', '#2F3192', '#ED008C', '#898989', '#7D7D7D', '#707070', '#626262', '#555', '#464646', '#363636', '#262626', '#111', '#000000',
		'#F7977A', '#FBAD82', '#FDC68C', '#FFF799', '#C6DF9C', '#A4D49D', '#81CA9D', '#7BCDC9', '#6CCFF7', '#7CA6D8', '#8293CA', '#8881BE', '#A286BD', '#BC8CBF', '#F49BC1', '#F5999D',
		'#F16C4D', '#F68E54', '#FBAF5A', '#FFF467', '#ACD372', '#7DC473', '#39B778', '#16BCB4', '#00BFF3', '#438CCB', '#5573B7', '#5E5CA7', '#855FA8', '#A763A9', '#EF6EA8', '#F16D7E',
		'#EE1D24', '#F16522', '#F7941D', '#FFF100', '#8FC63D', '#37B44A', '#00A650', '#00A99E', '#00AEEF', '#0072BC', '#0054A5', '#2F3192', '#652C91', '#91278F', '#ED008C', '#EE105A',
		'#9D0A0F', '#A1410D', '#A36209', '#ABA000', '#588528', '#197B30', '#007236', '#00736A', '#0076A4', '#004A80', '#003370', '#1D1363', '#450E61', '#62055F', '#9E005C', '#9D0039',
		'#790000', '#7B3000', '#7C4900', '#827A00', '#3E6617', '#045F20', '#005824', '#005951', '#005B7E', '#003562', '#002056', '#0C004B', '#30004A', '#4B0048', '#7A0045', '#7A0026'
];
}

BXEdColorPicker.prototype = {
	_Create: function ()
	{
		this.pWnd = BX.create("DIV", {props: {className: 'bx-ed-colorpicker'}});
		var _this = this;

		if(this.OnSelectionChange)
			this.pMainObj.AddEventHandler("OnSelectionChange", this.OnSelectionChange, this);

		if(this.disableOnCodeView)
			this.pMainObj.AddEventHandler("OnChangeView", this.OnChangeView, this);

		if(this.with_input)
		{
			this.pInput = this.pWnd.appendChild(BX.create("INPUT", {props: {size: 7}}));
			if (_this.OnChange)
				this.pInput.onchange = function(){_this.OnChange(this.value);};
		}

		if (!this.id)
			this.id = 'BackColor';

		this.pIcon = this.pWnd.appendChild(BX.create("IMG", {props: {id: 'bx_btn_' + this.id, title: this.title, src: one_gif_src, className: "bxedtbutton"}, style:  {border: '1px solid '+borderColorNormal, backgroundImage: "url(" + image_path + "/_global_iconkit.gif)"}}));

		this.pIcon.onclick = function(e){_this.OnClick(e, this)};
		this.pIcon.onmouseover = function (e){if(!_this.disabled){BX.addClass(this, "bxedtbuttonover");}};
		this.pIcon.onmouseout = function (e){if(!_this.disabled){BX.removeClass(this, "bxedtbuttonover");}};
	},

	Create: function ()
	{
		var _this = this;
		this.pColCont = document.body.appendChild(BX.create("DIV", {props: {className: "bx-colpick-cont"}, style: {zIndex: this.zIndex}}));

		var
			row, cell, colorCell,
			tbl = BX.create("TABLE", {props: {className: 'bx-colpic-tbl'}}),
			i, l = this.arColors.length;

		row = tbl.insertRow(-1);
		cell = row.insertCell(-1);
		cell.colSpan = 8;

		var defBut = cell.appendChild(BX.create("SPAN", {props: {className: 'bx-colpic-def-but'}, text: BX_MESS.CPickDef}));
		colorCell = BX.adjust(row.insertCell(-1), {props: {colSpan: 8, className: 'bx-color-inp-cell'}, style: {backgroundColor: this.arColors[38]}});

		defBut.onmouseover = function()
		{
			this.className = 'bx-colpic-def-but bx-colpic-def-but-over';
			colorCell.style.backgroundColor = 'transparent';
		};
		defBut.onmouseout = function(){this.className = 'bx-colpic-def-but';};
		defBut.onclick = function(e){_this.Select(false);}

		for(i = 0; i < l; i++)
		{
			if (Math.round(i / 16) == i / 16) // new row
				row = tbl.insertRow(-1);

			cell = BX.adjust(row.insertCell(-1), {props: {className: 'bx-col-cell', id: 'bx_color_' + i}, html: '<img src="' + one_gif_src + '" />', style: {backgroundColor: this.arColors[i]}});

			cell.onmouseover = function (e)
			{
				this.className = 'bx-col-cell bx-col-cell-over';
				colorCell.style.backgroundColor = _this.arColors[this.id.substring('bx_color_'.length)];
			};
			cell.onmouseout = function (e){this.className = 'bx-col-cell';};
			cell.onclick = function(e){_this.Select(_this.arColors[this.id.substring('bx_color_'.length)]);};
		}

		this.pColCont.appendChild(tbl);
		this.bCreated = true;
	},

	OnClick: function (e, pEl)
	{
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
			pOverlay = this.pMainObj.oTransOverlay.Show(),
			pos = BX.align(BX.pos(this.pIcon), 325, 155),
			_this = this;

		BX.bind(document, "keyup", BX.proxy(this.OnKey, this));
		BX.bind(this.pMainObj.pEditorDocument, "keyup", BX.proxy(this.OnKey, this));

		oPrevRange = BXGetSelectionRange(this.pMainObj.pEditorDocument, this.pMainObj.pEditorWindow);
		pOverlay.onclick = function(){_this.Close()};

		this.pColCont.style.display = 'block';
		this.pColCont.style.top = pos.top + 'px';
		this.pColCont.style.left = pos.left + 'px';
		this.bOpened = true;
	},

	Close: function ()
	{
		this.pColCont.style.display = 'none';
		this.pMainObj.oTransOverlay.Hide();

		BX.unbind(document, "keyup", BX.proxy(this.OnKey, this));
		BX.unbind(this.pMainObj.pEditorDocument, "keyup", BX.proxy(this.OnKey, this));

		this.bOpened = false;
	},

	OnMouseOver: function (e)
	{
		if(!this.disabled)
		{
			this.pIcon.style.borderColor = borderColorOver;
			this.pIcon.style.border = "#4B4B6F 1px solid";
			this.pIcon.style.backgroundColor = bgroundColorOver;
		}
	},

	OnMouseOut: function (e)
	{
		if(!this.disabled)
		{
			this.pIcon.style.backgroundColor = "";
			this.pIcon.style.borderColor = borderColorNormal;
		}
	},

	OnKey: function(e)
	{
		if(!e)
			e = window.event
		if(e.keyCode == 27)
			this.Close();
	},

	Select: function (color)
	{
		if (!color)
			color = '';

		if(this.pInput)
			this.pInput.value = color;

		BXSelectRange(oPrevRange, this.pMainObj.pEditorDocument, this.pMainObj.pEditorWindow);
		if(this.OnChange)
			this.OnChange(color);

		this.Close();
	},

	OnChangeView: function (mode, split_mode)
	{
		mode = (mode == 'split' ? split_mode : mode);
		this.Disable(mode == 'code');
	},

	Disable: function(bFlag)
	{
		if(bFlag == this.disabled)
			return false;

		this.disabled = this.pIcon.disabled = bFlag;
		if(bFlag)
			BX.addClass(this.pIcon, 'bxedtbutton-disabled');
		else
			BX.removeClass(this.pIcon, 'bxedtbutton-disabled');
	},

	SetValue: function(val)
	{
		if(this.pInput)
			this.pInput.value = val;
	}
};

// BXTAlignPicker - class
function BXTAlignPicker()
{
	this.disabled = false;
	this.bCreated = false;
	this.bOpened = false;
	this.zIndex = 2090;

	this.arIcon = ["tl", "tc", "tr", "cl", "cc", "cr", "bl", "bc", "br"];
	this.arIconH = ["left", "center", "right"];
	this.arIconV = ["top", "middle", "bottom"];
	this.arIconName = [
		BX_MESS.TAlign1, BX_MESS.TAlign2, BX_MESS.TAlign3,
		BX_MESS.TAlign4, BX_MESS.TAlign5, BX_MESS.TAlign6,
		BX_MESS.TAlign7, BX_MESS.TAlign8, BX_MESS.TAlign9];
}

BXTAlignPicker.prototype = {
_Create: function ()
{
	this.pWnd = BX.create("TABLE", {props: {className: 'bx-ed-alignpicker'}});
	var
		_this = this,
		row = this.pWnd.insertRow(-1),
		cell = row.insertCell(-1);

	this.pIcon = cell.appendChild(BX.create("IMG", {props: {id: 'bx_btn_align_tl', src: one_gif_src, className: "bxedtbutton"}, style:  {border: '1px solid '+borderColorNormal, backgroundImage: "url(" + image_path + "/_global_iconkit.gif)"}}));

	if (this.title)
		this.pIcon.title = this.title;

	this.pIcon.onclick = function(e){_this.OnClick(e, this)};
	this.pIcon.onmouseover = function (e){BX.addClass(this, "bxedtbuttonover");};
	this.pIcon.onmouseout = function (e){BX.removeClass(this, "bxedtbuttonover");};
},

Create: function ()
{
	this.pPopupNode = document.body.appendChild(BX.create("DIV", {props: {className: "bx-alpick-cont"}, style: {zIndex: this.zIndex}}));

	var
		_this = this,
		row, cell, j, but,
		tbl = this.pPopupNode.appendChild(BX.create("TABLE", {props: {className: 'bx-alpic-tbl'}})),
		i;

	row = tbl.insertRow(-1);
	cell = BX.adjust(row.insertCell(-1), {props: {className: 'bx-alpic-default', colSpan: 3}, html: '<nobr>' + BX_MESS.TAlignDef + '</nobr>'});

	cell.onmouseover = function (e) {BX.addClass(this, "bxedtbuttonover");};
	cell.onmouseout = function (e){BX.removeClass(this, "bxedtbuttonover");};
	cell.onclick = function (e){_this._OnChange('', ''); _this.Close();};

	for(i = 0; i < 3; i++)
	{
		row = tbl.insertRow(-1);

		for(j = 0; j < 3; j++)
		{
			cell = row.insertCell(-1);
			cell.className = 'bx-alpic-but';

			if(this.type != 'image' || i == 1 || j == 1)
			{
				but = cell.appendChild(BXPopupWindow.CreateElement("DIV", {id: 'bx_btn_align_'+this.arIcon[i * 3 + j], className: 'bxedtbutton', title: this.arIconName[i * 3 + j]}, {border: '1px solid '+borderColorNormal, backgroundImage: "url(" + global_iconkit_path + ")"}));

				if(this.type == 'image')
				{
					but.val = j==1 ? this.arIconV[i] : this.arIconH[j];
					but.onclick = function (e){_this._OnChangeI(this.val); _this.Close();};
				}
				else
				{
					but.valH = this.arIconH[j];
					but.valV = this.arIconV[i];
					but.onclick = function (e){_this._OnChange(this.valH, this.valV); _this.Close();};
				}

				but.onmouseover = function (e){BX.addClass(this, "bxedtbuttonover");};
				but.onmouseout = function (e){BX.removeClass(this, "bxedtbuttonover");};
			}
		}
	}

	this.bCreated = true;
},

Open: function ()
{
	var
		pOverlay = this.pMainObj.oTransOverlay.Show(),
		pos = BX.align(BX.pos(this.pIcon), 91, 102),
		_this = this;

	BX.bind(document, "keyup", BX.proxy(this.OnKey, this));
	BX.bind(this.pMainObj.pEditorDocument, "keyup", BX.proxy(this.OnKey, this));
	oPrevRange = BXGetSelectionRange(this.pMainObj.pEditorDocument, this.pMainObj.pEditorWindow);
	pOverlay.onclick = function(){_this.Close()};

	this.pPopupNode.style.display = 'block';
	this.pPopupNode.style.top = pos.top + 'px';
	this.pPopupNode.style.left = pos.left + 'px';
	this.bOpened = true;
},

Close: function ()
{
	this.pPopupNode.style.display = 'none';
	this.pMainObj.oTransOverlay.Hide();
	BX.unbind(document, "keyup", BX.proxy(this.OnKey, this));
	BX.unbind(this.pMainObj.pEditorDocument, "keyup", BX.proxy(this.OnKey, this));
	this.bOpened = false;
},

_OnChange: function (valH, valV)
{
	if(this.OnChange)
		this.OnChange(valH, valV);

	this.SetValue(valH, valV);
},

_OnChangeI: function (val)
{
	if(this.OnChange)
		this.OnChange(val);

	this.SetValueI(val);
},

SetValue: function(valH, valV)
{
	if(this.type == 'image')
		return this.SetValueI(valH);

	for(var j = 0; j < 3; j++)
		if(this.arIconH[j] == valH)
			break;

	for(var i = 0; i < 3; i++)
		if(this.arIconV[i] == valV)
			break;

	if(i > 2)
		i = 1;
	if(j > 2)
		j=0;

	this.pIcon.id = "bx_btn_align_"+this.arIcon[i * 3 + j];
	this.pIcon.title = this.arIconName[i * 3 + j];
	return i * 3 + j;
},

SetValueI: function(val)
{
	var i, j = 0;
	for(i = 0; i < 3; i++)
		if(this.arIconV[i] == val)
		{
			j = 1;
			break;
		}
	if(j != 1)
		for(j = 0; j < 3; j++)
			if(this.arIconH[j] == val)
			{
				i = 1;
				break;
			}

	if(i > 2)
		i=1;
	if(j > 2)
		j=0;

	this.pIcon.id = "bx_btn_align_"+this.arIcon[i * 3 + j];
	this.pIcon.title = this.arIconName[i * 3 + j];
	return i * 3 + j;
},

OnClick: function (e)
{
	if(this.disabled)
		return false;

	if (!this.bCreated)
		this.Create();

	if (this.bOpened)
		return this.Close();

	this.Open();
},

OnKey: function(e)
{
	if (this.bOpened)
	{
		if(!e) e = window.event
		if(e.keyCode == 27)
			this.Close();
	}
}
};

// function BXGroupedButton()
// {
	// this.disabled = false;
	// this.bCreated = false;
	// this.bOpened = false;
	// this.zIndex = 2090;
// }

// BXGroupedButton.prototype = {
	// _Create: function()
	// {
		// var _this = this;

		// this.pWnd = BX.create("IMG", {props: {className: 'bxedtbutton', src: one_gif_src, id: "bx_btn_" + this.id}, style: {backgroundImage: "url(" + global_iconkit_path + ")"}});

		// this.pWnd.onmouseover = function(e)
		// {
			// if(!this.disabled)
				// BX.addClass(this, 'bxedtbutton-over');
		// };

		// this.pWnd.onmouseout = function(e)
		// {
			// if(!this.disabled)
				// BX.removeClass(this, 'bxedtbutton-over');
		// };

		// this.pWnd.onclick = BX.proxy(this.OnClick, this);

		// this.pPopupNode = document.body.appendChild(BX.create("DIV", {props: {className: "bx-but-group"}, style: {zIndex: this.zIndex}}));
		// this.bCreated = true;

		// this.pPopupNode.style.height = ((this.buttons.length - 1) * 20) + "px";

		// var i, l = this.buttons.length, pBut;
		// for (i = 0; i < l; i++)
		// {
			// //pBut = this.pMainObj.CreateCustomElement("BXButton", arButton[1]);
			// //this.pPopupNode.appendChild(BX.create("DIV", {props: {className: "bx-g-tlbr-but"}})).appendChild(pBut.pWnd);
		// }

		// // if(!this.no_actions || this.no_actions != true) // for context menu
		// // {
			// //if (this.defaultState)
			// //	this.Check(true);
			// // addCustomElementEvent(this.pWnd, 'click', this.OnClick, this);
			// // this.pMainObj.AddEventHandler("OnSelectionChange", this._OnSelectionChange, this);
			// // this.pMainObj.AddEventHandler("OnChangeView", this.OnChangeView, this);
		// //}
	// },

	// // _OnChangeView: function (mode, split_mode)
	// // {
		// // mode = (mode == 'split' ? split_mode : mode);
		// // if(mode == 'code' && !this.codeEditorMode || (mode=='html' && this.hideInHtmlEditorMode))
		// // {
			// // this._prevDisabledState = this.pWnd.disabled;
			// // this.Disable(true);
		// // }
		// // else if(mode == 'code' && this.codeEditorMode || (this.hideInHtmlEditorMode && mode != 'html'))
			// // this.Disable(false);
		// // else if(!this.codeEditorMode)
			// // this.Disable(this._prevDisabledState);
	// // },

	// OnChangeView: function (mode, split_mode)
	// {
		// //this._OnChangeView(mode, split_mode);
	// },

	// OnClick: function (e)
	// {
		// if(this.disabled)
			// return false;

		// if (this.bOpened)
			// return this.Close();

		// this.Open();
	// },

	// Open: function ()
	// {
		// var
			// pOverlay = this.pMainObj.oTransOverlay.Show(),
			// pos = BX.pos(this.pWnd),
			// _this = this;

		// //BX.bind(document, "keyup", BX.proxy(this.OnKey, this));
		// //BX.bind(this.pMainObj.pEditorDocument, "keyup", BX.proxy(this.OnKey, this));

		// oPrevRange = BXGetSelectionRange(this.pMainObj.pEditorDocument, this.pMainObj.pEditorWindow);
		// pOverlay.onclick = function(){_this.Close()};

		// this.pPopupNode.style.top = pos.top + (BX.browser.IsIE() && !BX.browser.IsDoctype() ? 17 : 19);
		// this.pPopupNode.style.left = pos.left + (BX.browser.IsIE() && !BX.browser.IsDoctype() ? -2 : 0);
		// this.pPopupNode.style.display = 'block';

		// // BXPopupWindow.Show({
			// // top: pos.top + (BX.browser.IsIE() && !BX.browser.IsDoctype() ? 17 : 19),
			// // left: pos.left + (BX.browser.IsIE() && !BX.browser.IsDoctype() ? -2 : 0),
			// // node: this.pPopupNode,
			// // width: this.width,
			// // height: this.height
		// // });
		// this.bOpened = true;
	// },

	// Close: function ()
	// {
		// this._Close();
	// },

	// _Close: function ()
	// {
		// this.pPopupNode.style.display = 'none';
		// this.pMainObj.oTransOverlay.Hide();

		// //BX.unbind(document, "keyup", BX.proxy(this.OnKey, this));
		// //BX.unbind(this.pMainObj.pEditorDocument, "keyup", BX.proxy(this.OnKey, this));
		// this.bOpened = false;
	// },

	// OnKey: function (e)
	// {
		// if(!e)
			// e = window.event
		// if(e.keyCode == 27 && this.bOpened)
			// this.Close();
	// }
// }

function BXDialog() {}
BXDialog.prototype = {
	_Create: function()
	{
		var _this = this;
		this.pMainObj._DisplaySourceFrame(true);

		if(!this.params || typeof(this.params) != "object")
			this.params = {};

		this.params.pMainObj = this.pMainObj;
		pObj = window.pObj = this;

		oPrevRange = BXGetSelectionRange(this.pMainObj.pEditorDocument, this.pMainObj.pEditorWindow);
		var ShowResult = function(result, bFastMode)
		{
			BX.closeWait();

			if (window.oBXEditorDialog && window.oBXEditorDialog.isOpen)
				return false;

			var arDConfig = {
				title : _this.name,
				width: _this.width,
				height: 300,
				resizable: false
			};

			if (bFastMode)
			{
				if (result.title)
					arDConfig.title = result.title;

				if (result.width)
					arDConfig.width = result.width;
				if (result.height)
					arDConfig.height = result.height;

				if (result.resizable)
				{
					arDConfig.resizable = true;
					arDConfig.min_width = result.min_width;
					arDConfig.min_height = result.min_height;
					arDConfig.resize_id = result.resize_id;
				}
			}

			window.oBXEditorDialog = new BX.CEditorDialog(arDConfig);
			window.oBXEditorDialog.editorParams = _this.params;

			BX.addCustomEvent(window.oBXEditorDialog, 'onWindowUnRegister', function()
			{
				if (window.oBXEditorDialog && window.oBXEditorDialog.DIV && window.oBXEditorDialog.DIV.parentNode)
					window.oBXEditorDialog.DIV.parentNode.removeChild(window.oBXEditorDialog.DIV);
			});

			if (bFastMode)
			{
				window.oBXEditorDialog.Show();
				window.oBXEditorDialog.SetContent(result.innerHTML);

				if (result.OnLoad && typeof result.OnLoad == 'function')
					result.OnLoad();
			}
		}
		BX.showWait();

		var potRes = this.GetFastDialog();
		if (potRes !== false)
			return ShowResult(potRes, true);

		var
			addUrl = (this.params.PHPGetParams ? this.params.PHPGetParams : '') + '&mode=public' + '&sessid=' + BX.bitrix_sessid() + (this.not_use_default ? '&not_use_default=Y' : ''),
			handler = this.handler ? '/bitrix/admin/' + this.handler : editor_dialog_path,
			url = handler + '?lang=' + BXLang + '&bxpublic=Y&site=' + BXSite + '&name=' + this.name + addUrl;

		if (_this.params.bUseTabControl)
		{
			BX.closeWait();
			window.oBXEditorDialog = new BX.CAdminDialog({
				title : _this.name,
				content_url: url,
				width: _this.width,
				resizable: false
			});
			window.oBXEditorDialog.bUseTabControl = true;
			window.oBXEditorDialog.Show();
		}
		else
		{
			// hack to loading auth
			url += '&bxsender=core_window_cadmindialog';

			BX.ajax.post(url, {}, ShowResult);
		}
	},

	Close: function(){},

	GetFastDialog: function()
	{
		return window.arEditorFastDialogs[this.name] ? window.arEditorFastDialogs[this.name](this) : false;
	}
}

BXHTMLEditor.prototype.OpenEditorDialog = function(dialogName, obj, width, arParams, notUseDefaultButtons)
{
	this.CreateCustomElement("BXDialog", {width: parseInt(width) || 500, name: dialogName, params: arParams || {}, not_use_default: notUseDefaultButtons});
}