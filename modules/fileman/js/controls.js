var BXConst =
{
	'arColor': ["000000", "993300", "333300", "003300", "003366", "000080", "333399", "333333",
		"800000", "FF6600", "808000", "008000", "008080", "0000FF", "666699", "808080",
		"FF0000", "FF9900", "99CC00", "339966", "33CCCC", "3366FF", "800080", "999999",
		"FF00FF", "FFCC00", "FFFF00", "00FF00", "00FFFF", "00CCFF", "993366", "C0C0C0",
		"FF99CC", "FFCC99", "FFFF99", "CCFFCC", "CCFFFF", "99CCFF", "CC99FF", "FFFFFF"],

	'arColorName': [BX_MESS.CPickClr1, BX_MESS.CPickClr2, BX_MESS.CPickClr3, BX_MESS.CPickClr4, BX_MESS.CPickClr5, BX_MESS.CPickClr6, BX_MESS.CPickClr7, BX_MESS.CPickClr8,
		BX_MESS.CPickClr9, BX_MESS.CPickClr10, BX_MESS.CPickClr11, BX_MESS.CPickClr12, BX_MESS.CPickClr13, BX_MESS.CPickClr14, BX_MESS.CPickClr15, BX_MESS.CPickClr16,
		BX_MESS.CPickClr17, BX_MESS.CPickClr18, BX_MESS.CPickClr19, BX_MESS.CPickClr20, BX_MESS.CPickClr21, BX_MESS.CPickClr22, BX_MESS.CPickClr23, BX_MESS.CPickClr24,
		BX_MESS.CPickClr25, BX_MESS.CPickClr26, BX_MESS.CPickClr27, BX_MESS.CPickClr28, BX_MESS.CPickClr29, BX_MESS.CPickClr30, BX_MESS.CPickClr31, BX_MESS.CPickClr32,
		BX_MESS.CPickClr33, BX_MESS.CPickClr34, BX_MESS.CPickClr35, BX_MESS.CPickClr36, BX_MESS.CPickClr37, BX_MESS.CPickClr38, BX_MESS.CPickClr39, BX_MESS.CPickClr1]
};


/**************************************************************************************
BXButton - class
**************************************************************************************/
function BXButton()
{
	BXButton.prototype._Create = function ()
	{
		var pElement, i, j, obj = this;
		this.className = 'BXButton';

		this.pWnd = this.CreateElement("IMG", {'src': this.src, 'alt': (this.title?this.title:this.name), 'title': (this.title?this.title:this.name), 'width': '20', 'height': '20'});
		this.pWnd.className = 'bxedtbutton';
		if(!this.no_actions || this.no_actions != true) // for context menu
		{
			this.pWnd.onmouseover = this.onMouseOver;
			this.pWnd.onmouseout = this.onMouseOut;
			//addCustomElementEvent(this.pWnd, 'mouseover', this.onMouseOver, this);
			//addCustomElementEvent(this.pWnd, 'mouseout', this.onMouseOut, this);
			addCustomElementEvent(this.pWnd, 'click', this.onClick, this);
			this.pMainObj.AddEventHandler("OnSelectionChange", this._OnSelectionChange, this);
			this.pMainObj.AddEventHandler("OnChangeView", this.OnChangeView, this);
		}
	}

	BXButton.prototype._OnChangeView = function (mode, split_mode)
	{
		mode = (mode=='split'?split_mode:mode);
		if(mode=='code' && !this.codeEditorMode)
		{
			this._prevDisabledState = this.pWnd.disabled;
			this.Disable(true);
		}
		else if(mode=='code' && this.codeEditorMode)
			this.Disable(false);
		else if(!this.codeEditorMode)
			this.Disable(this._prevDisabledState);
	}

	BXButton.prototype.OnChangeView = function (mode, split_mode)
	{
		this._OnChangeView(mode, split_mode);
	}

	BXButton.prototype.Disable = function (bFlag)
	{
		if(bFlag == this.pWnd.disabled)
			return false;
		this.pWnd.disabled = bFlag;
		if(bFlag)
			this.pWnd.className = 'bxedtbuttondisabled';
		else
		{
			if(this.pWnd.checked)
				this.pWnd.className = 'bxedtbuttonset';
			else
				this.pWnd.className = 'bxedtbutton';
		}
	}

	BXButton.prototype.Check = function (bFlag)
	{
		if(bFlag == this.pWnd.checked)
			return false;
		this.pWnd.checked = bFlag;
		if(!this.pWnd.disabled)
		{
			if(this.pWnd.checked)
				this.pWnd.className = 'bxedtbuttonset';
			else
				this.pWnd.className = 'bxedtbutton';
		}
	}

	BXButton.prototype.onMouseOver = function (e)
	{
		if(!this.disabled)
		{
			if(this.checked)
				this.className = 'bxedtbuttonsetover';
			else
				this.className = 'bxedtbuttonover';
		}
	}

	BXButton.prototype.onMouseOut = function (e)
	{
		if(!this.disabled)
		{
			if(this.checked)
				this.className = 'bxedtbuttonset';
			else
				this.className = 'bxedtbutton';
		}
	}

	BXButton.prototype.onClick = function (e)
	{
		if(this.pWnd.disabled) return false;
		this.pMainObj.SetFocus();
		var res = false;
		if(this.handler)
			if(this.handler()!==false)
				res = true;

		if(!res)
			res = this.pMainObj.executeCommand(this.cmd);

		if(!this.bNotFocus)
			this.pMainObj.SetFocus();
		return res;
	}

	BXButton.prototype._OnSelectionChange = function()
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
}

function BXButtonSeparator()
{
	BXButtonSeparator.prototype._Create = function ()
	{
		var pElement, i, j;
		this.className = 'BXButtonSeparator';
		this.pWnd = this.CreateElement("IMG", {'src': '/bitrix/images/fileman/htmledit2/separator.gif', 'width': '2', 'height': '20'});
		this.pWnd.className = 'bxseparator';
	}

	BXButtonSeparator.prototype.onToolbarChangeDirection = function (bVertical)
	{
		if(bVertical)
		{
			this.pWnd.src='/bitrix/images/fileman/htmledit2/separator-v.gif';
			this.pWnd.style.width = "20px";
			this.pWnd.style.height = "2px";
		}
		else
		{
			this.pWnd.src='/bitrix/images/fileman/htmledit2/separator.gif';
			this.pWnd.style.width = "2px";
			this.pWnd.style.height = "24px";
		}
	}
}

/**************************************************************************************
BXList - class
**************************************************************************************/
function BXList()
{
	this.className = 'BXList';
	this.iSelectedIndex = -1;
}

BXList.prototype._Create = function ()
{
	if(this.onCreate && this.onCreate()==false)
		return false;

	if(this.OnSelectionChange)
	{
		this.pMainObj.AddEventHandler("OnSelectionChange", this.OnSelectionChange, this);
	}

	if(this.disableOnCodeView)
		this.pMainObj.AddEventHandler("OnChangeView", this.OnChangeView, this);

	this._PreCreate();
	this.SetValues(this.values);

	if(this.onInit && this.onInit()==false)
		return false;

	//alert(this.pDropDownList.innerHTML);
	return true;
}

BXList.prototype._OnChangeView = function (mode, split_mode)
{
	mode = (mode=='split'?split_mode:mode);
	this.Disable(mode=='code');
}

BXList.prototype.OnChangeView = function (mode, split_mode)
{
	this._OnChangeView(mode, split_mode);
}

BXList.prototype.Disable = function(flag)
{
	if(this.disabled==flag) return false;
	this.disabled=flag;
	if(flag)
	{
		this.pWnd.className = 'bxlistdisabled';
	}
	else
	{
		this.pWnd.className = 'bxlist';
	}
}

BXList.prototype.SetValues = function (values)
{
	this.values = values;
	while(this.pDropDownList.childNodes.length>0)
		this.pDropDownList.removeChild(this.pDropDownList.childNodes[0]);

	for(var i=0; i<this.values.length; i++)
	{
		var r = this.pDropDownList.insertRow(-1);
		var c = r.insertCell(-1);

		var t1 = BXPopupWindow.CreateElement("TABLE", {'border': '0', 'cellSpacing': '0', 'width': '100%', 'cellPadding': '1', 'className': 'bxedlistitem'});
		var r1 = t1.insertRow(-1);
		var c1 = r1.insertCell(-1);
		c1.style.height = "16px";
		c1.style.cursor = "default";
		c1.noWrap = true;
		this.values[i].index = i;
		c1.title = this.values[i].name;
		c1.value = this.values[i];

		c1.style.border = '1px solid #FFFFFF';
		c1.onmouseover = function (e){this.style.border = '1px solid #4B4B6F';};
		c1.onmouseout = function (e){this.style.border = '1px solid #FFFFFF';};
		c1.obj = this;
		c1.onclick = function ()
			{
				BXPopupWindow.Hide();
				this.obj._onChange(this.value);
				this.obj.FireChangeEvent();
				//this.style.border = '1px solid #CCCCCC';
			};

		if(this.onDrawItem)
			c1.innerHTML = this.onDrawItem(this.values[i]);
		else
			c1.innerHTML = this.values[i].name;

		t1.unselectable = "on";
		c.appendChild(t1);
	}
}

BXList.prototype.FireChangeEvent = function()
{
	//alert(this.onChange);
	if(this.onChange)
		this.onChange(this.arSelected);
}

BXList.prototype._onChange = function (selected)
{
	this.Select(selected["index"]);
}


BXList.prototype.SetValue = function(val)
{
	if(this.pTitle)
	{
		if(val.length<=0)
			this.pTitle.innerHTML = (this.title?this.title:'');
		else
			this.pTitle.innerHTML = val;
	}
}

BXList.prototype.onMouseOver = function (e)
{
	if(this.disabled) return false;
	this.pWnd.className = 'bxlist bxlistover';
}

BXList.prototype.onMouseOut = function (e)
{
	if(this.disabled) return false;
	this.pWnd.className = 'bxlist';
}

BXList.prototype._PreCreate = function ()
{
	this.pWnd = this.pMainObj.CreateElement("DIV", {'className': 'bxlist', 'border': '0'});
	this.pWnd.style.width = this.field_size;
	this.pTable = this.pWnd.appendChild(this.pMainObj.CreateElement("TABLE", {'cellPadding': 0, 'cellSpacing': 0, 'border': 0, 'width':'100%'}));
	this.pTable.style.tableLayout = "fixed";
	//this.pTable.onmouseover = function (e){this.className = 'bxlistover'; return false;};
	//this.pTable.onmouseout = function (e){this.className = 'bxlist';return false;};
	var row = this.pTable.insertRow(-1), cell = row.insertCell(-1);

	this.pTitle = this.pMainObj.CreateElement("DIV", {'className': 'bxlisttitle', 'border': '0'});
	this.pTitle.innerHTML = (this.title?this.title:'');
	this.pTitle.unselectable = "on";
	cell.appendChild(this.pTitle);
	this.pTitleCell = cell;

	cell = row.insertCell(-1);
	cell.className = 'bxlistbutton';
	cell.innerHTML = '&nbsp;';
	cell.unselectable = "on";

	addCustomElementEvent(this.pWnd, 'mouseover', this.onMouseOver, this);
	addCustomElementEvent(this.pWnd, 'mouseout', this.onMouseOut, this);
	addCustomElementEvent(this.pWnd, 'click', this.onClick, this);

	BXPopupWindow.Create();

	this.pPopupNode = BXPopupWindow.CreateElement("DIV", {'border': "0"});
	this.pPopupNode.style.border = "1px solid #A0A0A0";
	this.pPopupNode.style.overflow = "auto";
	this.pPopupNode.style.width = (this.width?this.width:"150px");
	this.pPopupNode.style.overflowX = "hidden";
	this.pPopupNode.style.height = (this.height?this.height:"200px");
	this.pPopupNode.style.overflowY = "auto";
	this.pPopupNode.style.textOverflow = "ellipsis";
	//this.onclick = function (e){BXPopupWindow.Hide();};

	this.pDropDownList = BXPopupWindow.CreateElement("TABLE", {'border': '0', 'width': '100%', 'cellSpacing': '0', 'cellPadding': '0', 'unselectable': 'on'});
	//this.pDropDownList.onclick = function (e){BXPopupWindow.Hide();};

	/*
	var r = this.pDropDownList.insertRow(-1);
	var c = r.insertCell(-1);
	c.innerHTML = this.title;
	c.val = "";
	c.style.cssText = "border: 1px solid #CCCCCC; height: 17px; font-size: 11px; font-family: Tahoma, Courier New; cursor: default; white-space: nowrap;";

	c.onmouseover = function (e){this.style.border = '1px solid #000000';};
	c.onmouseout = function (e){this.style.border = '1px solid #C0C0C0';};
	addCustomElementEvent(c, 'click', function (e) {this._onChange(''); BXPopupWindow.Hide();}, this);
	*/

	this.pPopupNode.appendChild(this.pDropDownList);
}


BXList.prototype.onClick = function (e)
{
	if(this.disabled) return false;
	//this.pWnd.className = 'bxedtbutton';
	var arPos = GetRealPos(this.pWnd);
	if(this.bSetGlobalStyles)
		BXPopupWindow.SetCurStyles();
	else
		this.pMainObj.oStyles.SetToDocument(BXPopupWindow.GetDocument());
	BXPopupWindow.Show([arPos["left"], arPos["right"]], [arPos["top"], arPos["bottom"]], this.pPopupNode);
}

BXList.prototype.Select = function(v)
{
	if(this.iSelectedIndex==v)
		return;
	var sel = this.values[v];
	this.iSelectedIndex = v;
	this.arSelected = sel;
	/*
	if(this.onChange)this.onChange(this.arSelected);
	*/
	this.SetValue(sel["name"]);
}



BXList.prototype.SelectByVal = function(val)
{
	//alert(val);
	if(val)
	{
		for(var i=0; i<this.values.length; i++)
		{
			if(this.values[i].value == val)
			{
				this.Select(i);
				return;
			}
		}
	}

	if(this.title)
		this.SetValue(this.title);
	else
		this.SetValue('');

	this.iSelectedIndex = -1;
}

BXList.prototype.onToolbarChangeDirection = function (bVertical)
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

	this.pWnd.className = 'bxlist';
}

/**************************************************************************************
BXTemplateList - class
**************************************************************************************/
function BXTemplateList()
{
}

BXTemplateList.prototype = new BXList;

BXTemplateList.prototype._Create = function ()
{
	var pElement, i, j, obj = this;
	this.className = 'BXTemplateList';
	this._PreCreate();

	var arStyles;
	var r1, c1, t1, r, c;

	/*
	this.values = [];
	for(i=0; i<this.filter.length; i++)
	{
		arStyles = this.pMainObj.oStyles.GetStyles(this.filter[i]);
		for(j=0; j<arStyles.length; j++)
		{
			if(arStyles[j].className.length<=0)
				continue;

			r = this.pDropDownList.insertRow(-1);
			c = r.insertCell(-1);

			t1 = BXPopupWindow.CreateElement("TABLE", {'border': '0', 'cellSpacing': '0', 'width': '100%', 'cellPadding': '1'});
			r1 = t1.insertRow(-1);
			c1 = r1.insertCell(-1);
			c1.style.height = "16px";
			c1.style.cursor = "default";
			c1.innerHTML = arStyles[j].className;
			if(!this.tag_name)
				this.tag_name = '';

			switch(this.tag_name.toUpperCase())
			{
				case "TD":
					c1.className = arStyles[j].className;
					break;
				case "TABLE":
					t1.className = arStyles[j].className;
					break;
				case "TR":
					r1.className = arStyles[j].className;
					break;
				default:
					c1.innerHTML = '<span class="'+arStyles[j].className+'">'+arStyles[j].className+'</span>';
			}
			c1.style.border = '1px solid #CCCCCC';
			c1.val = arStyles[j].className;
			c1.onmouseover = function (e){this.style.border = '1px solid #000000';};
			c1.onmouseout = function (e){this.style.border = '1px solid #CCCCCC';};
			c1.onclick = function (e){obj._onChange(this.value); BXPopupWindow.Hide(); this.style.border = '1px solid #CCCCCC'; return false;};
			c1.title = arStyles[j].className;
			t1.unselectable = "on";
			c.appendChild(t1);

			var value = {'index': this.values.length, 'value': arStyles[j].className, 'name': arStyles[j].className};
			c1.value = value;
			c1.obj = this;
			this.values.push(value);
		}
	}
	*/
}

BXTemplateList.prototype.onClick = function (e)
{
	var arPos = GetRealPos(this.pWnd);
	this.pMainObj.oStyles.SetToDocument(BXPopupWindow.GetDocument());
	BXPopupWindow.Show([arPos["left"], arPos["right"]], [arPos["top"], arPos["bottom"]], this.pPopupNode);
}


/**************************************************************************************
BXStyleList - class
**************************************************************************************/
function BXStyleList()
{
}

BXStyleList.prototype = new BXList;

BXStyleList.prototype._Create = function ()
{
	this.className = 'BXStyleList';
	this._PreCreate();

	if(this.OnSelectionChange)
		this.pMainObj.AddEventHandler("OnSelectionChange", this.OnSelectionChange, this);

	this.pMainObj.AddEventHandler("OnTemplateChanged", this.FillList, this);

	if(this.disableOnCodeView)
		this.pMainObj.AddEventHandler("OnChangeView", this.OnChangeView, this);

	this.FillList();
}

BXStyleList.prototype.FillList = function()
{
	var i, j, arStyles;

	if(!this.filter)
		this._SetFilter();

	while(this.pDropDownList.rows.length>0)
		this.pDropDownList.deleteRow(0);

	this.values = [];
	if(!this.tag_name)
		this.tag_name = '';

	//"clear style" item
	this.__CreateRow('', BX_MESS.DeleteStyleOpt, {'index': this.values.length, 'value': '', 'name': BX_MESS.DeleteStyleOptTitle});

	var style_title;
	// other styles
	for(i=0; i<this.filter.length; i++)
	{
		arStyles = this.pMainObj.oStyles.GetStyles(this.filter[i]);
		for(j=0; j<arStyles.length; j++)
		{
			if(arStyles[j].className.length<=0)
				continue;

			style_title = null;
			if(this.pMainObj.arTemplateParams && this.pMainObj.arTemplateParams["STYLES_TITLE"] && this.pMainObj.arTemplateParams["STYLES_TITLE"][arStyles[j].className])
				style_title = this.pMainObj.arTemplateParams["STYLES_TITLE"][arStyles[j].className];
			if(!style_title)
			{
				if(this.pMainObj.arConfig["bUseOnlyDefinedStyles"]==true)
			 		continue;
			 	style_title = arStyles[j].className;
			}

			this.__CreateRow(arStyles[j].className, style_title, {'index': this.values.length, 'value': arStyles[j].className, 'name': style_title});
		}
	}
}

BXStyleList.prototype.__CreateRow = function(className, Name, value)
{
	var r1, c1, t1, r, c;

	r = this.pDropDownList.insertRow(-1);
	c = r.insertCell(-1);

	t1 = BXPopupWindow.CreateElement("TABLE", {'border': '0', 'cellSpacing': '0', 'width': '100%', 'cellPadding': '1'});
	r1 = t1.insertRow(-1);
	c1 = r1.insertCell(-1);
	c1.style.height = "16px";
	c1.style.cursor = "default";
	c1.innerHTML = Name;

	switch(this.tag_name.toUpperCase())
	{
		case "TD":
			c1.className = className;
			break;
		case "TABLE":
			t1.className = className;
			break;
		case "TR":
			r1.className = className;
			break;
		default:
			c1.innerHTML = '<span class="'+className+'">'+Name+'</span>';
	}
	c1.style.border = '1px solid #CCCCCC';
	c1.val = className;
	c1.onmouseover = function (e){this.style.border = '1px solid #000000';};
	c1.onmouseout = function (e){this.style.border = '1px solid #CCCCCC';};
	c1.onclick = function (e){this.obj._onChange(this.value); this.obj.FireChangeEvent(); BXPopupWindow.Hide(); this.style.border = '1px solid #CCCCCC'; if(this.value.value=='')this.obj.SelectByVal(); /*return false;*/};
	c1.title = Name;
	t1.unselectable = "on";
	c.appendChild(t1);
	c1.value = value;
	c1.obj = this;
	this.values.push(value);
}

BXStyleList.prototype.onChange = function(arSelected)
{
	//
	this.pMainObj.WrapSelectionWith("span", {"class":arSelected["value"]});
	//this.pMainObj.pEditorDocument.execCommand("fontsize", false, "0");
	//alert(this.pMainObj.pEditorDocument.body.innerHTML);
}

BXStyleList.prototype._SetFilter = function()
{
	this.filter = ["DEFAULT"];
}

BXStyleList.prototype.onClick = function (e)
{
	//this.pWnd.className = 'bxedtbutton';
	var arPos = GetRealPos(this.pWnd);
	this.pMainObj.oStyles.SetToDocument(BXPopupWindow.GetDocument());
	BXPopupWindow.Show([arPos["left"], arPos["right"]], [arPos["top"], arPos["bottom"]], this.pPopupNode);
}


/**************************************************************************************
BXColorPicker - class
**************************************************************************************/
function _BXColorPicker()
{
	_BXColorPicker.prototype._Create = function ()
	{
		var pElement, i, j, obj = this;
		this.className = 'BXColorPicker';

		this.pWnd = this.pMainObj.CreateElement("TABLE", {'cellPadding': 0, 'cellSpacing': 0, 'border': 0});
		var row = this.pWnd.insertRow(-1), cell = row.insertCell(-1);

		if(this.OnSelectionChange)
			this.pMainObj.AddEventHandler("OnSelectionChange", this.OnSelectionChange, this);

		if(this.with_input)
		{
			this.pInput = this.pMainObj.CreateElement("INPUT", {'type': 'text', 'size': 7});
			cell.appendChild(this.pInput);
			cell = row.insertCell(-1);
			this.pInput.onchange = function (){obj._onChange(this.value);};
		}

		this.pIcon = this.pMainObj.CreateElement("IMG", {'src': (this.icon?this.icon:'/bitrix/images/fileman/htmledit2/bgcolor.gif'), 'width': '20', 'height': '20', 'alt': this.title});
		this.pIcon.className = 'bxedtbutton';
		cell.appendChild(this.pIcon);
		addCustomElementEvent(this.pIcon, 'mouseover', this.onMouseOver, this);
		addCustomElementEvent(this.pIcon, 'mouseout', this.onMouseOut, this);
		addCustomElementEvent(this.pIcon, 'click', this.onClick, this);


		BXPopupWindow.Create();

		this.pPopupNode = BXPopupWindow.CreateElement("DIV", {'border': "0"});
		this.pPopupNode.style.border = "1px solid #A0A0A0";
		this.onclick = function (e){BXPopupWindow.Hide();};
		var t = BXPopupWindow.CreateElement("TABLE", {'border': '0', 'width': '160', 'cellSpacing': '1', 'cellPadding': '2'});
		t.onclick = function (e){BXPopupWindow.Hide();};
		var r = t.insertRow(-1);
		var c = r.insertCell(-1);
		t.className = 'bxedcolorpicker';
		c.style.height = "0%";
		c.innerHTML = BX_MESS.CPickDef;
		c.style.border = '1px solid #C0C0C0';
		c.onmouseover = function (e){this.style.border = '1px solid #000000';};
		c.onmouseout = function (e){this.style.border = '1px solid #C0C0C0';};
		c.onclick = function (e){obj._onChange(''); BXPopupWindow.Hide();};

		r = t.insertRow(-1);
		c = r.insertCell(-1);
		c.style.height = "100%";

		var iColumnCount = 8;

		var r1, c1, t1 = BXPopupWindow.CreateElement("TABLE", {'border': '0', 'cellSpacing': '3', 'cellPadding': '0'});

		for(i=0; i<BXConst.arColor.length/iColumnCount; i++)
		{
			r1 = t1.insertRow(-1);
			for(j=0; j<iColumnCount; j++)
			{
				c1 = r1.insertCell(-1);
				c1.style.height = "16px";
				c1.style.width = "16px";
				c1.style.backgroundColor = "#" + BXConst.arColor[i*iColumnCount + j];
				c1.style.border = '1px solid #C0C0C0';
				c1.val = "#" + BXConst.arColor[i*iColumnCount + j];
				c1.onmouseover = function (e){this.style.border = '1px solid #000000';};
				c1.onmouseout = function (e){this.style.border = '1px solid #C0C0C0';};
				c1.onclick = function (e){BXPopupWindow.Hide(); obj._onChange(this.val); this.style.border = '1px solid #C0C0C0'; /*return false;*/};
				c1.innerHTML = '<img src="/bitrix/images/1.gif" width="1" height="1"/>';
				c1.title = BXConst.arColorName[i*iColumnCount + j];
			}
		}

		c.appendChild(t1);
		this.pPopupNode.appendChild(t);
	}

	_BXColorPicker.prototype._onChange = function (color)
	{
		if(this.with_input)
			this.pInput.value = color;

		if(this.onChange)
			this.onChange(color);
	}

	_BXColorPicker.prototype.SetValue = function(val)
	{
		if(this.pInput)
			this.pInput.value = val;
	}

	_BXColorPicker.prototype.onMouseOver = function (e)
	{
		this.pIcon.className = 'bxedtbuttonover';
	}

	_BXColorPicker.prototype.onMouseOut = function (e)
	{
		this.pIcon.className = 'bxedtbutton';
	}

	_BXColorPicker.prototype.onClick = function (e)
	{
		//this.pWnd.className = 'bxedtbutton';
		var arPos = GetRealPos(this.pIcon);
		BXPopupWindow.SetCurStyles();
		BXPopupWindow.Show([arPos["left"], arPos["right"]], [arPos["top"], arPos["bottom"]], this.pPopupNode);
	}
}

var pBXColorPicker = new _BXColorPicker();
function BXColorPicker()
{
	return new _BXColorPicker();
	//return pBXColorPicker;
}

/**************************************************************************************
BXTAlignPicker - class
**************************************************************************************/
function _BXTAlignPicker()
{
	this.arIcon = ["tl", "tc", "tr", "cl", "cc", "cr", "bl", "bc", "br"];
	this.arIconH = ["left", "center", "right"];
	this.arIconV = ["top", "middle", "bottom"];
	this.arIconName = [
		BX_MESS.TAlign1, BX_MESS.TAlign2, BX_MESS.TAlign3,
		BX_MESS.TAlign4, BX_MESS.TAlign5, BX_MESS.TAlign6,
		BX_MESS.TAlign7, BX_MESS.TAlign8, BX_MESS.TAlign9];

	_BXTAlignPicker.prototype._Create = function ()
	{
		var pElement, i, j, obj = this;
		this.className = 'BXTAlignPicker';

		this.pWnd = this.pMainObj.CreateElement("TABLE", {'cellPadding': 0, 'cellSpacing': 0, 'border': 0});
		var row = this.pWnd.insertRow(-1), cell = row.insertCell(-1);

		this.pIcon = this.pMainObj.CreateElement("IMG", {'src': '/bitrix/images/fileman/htmledit2/talign-tl.gif', 'width': '20', 'height': '20', 'alt': this.title});
		this.pIcon.className = 'bxedtbutton';
		cell.appendChild(this.pIcon);
		addCustomElementEvent(this.pIcon, 'mouseover', this.onMouseOver, this);
		addCustomElementEvent(this.pIcon, 'mouseout', this.onMouseOut, this);
		addCustomElementEvent(this.pIcon, 'click', this.onClick, this);

		BXPopupWindow.Create();

		this.pPopupNode = BXPopupWindow.CreateElement("DIV", {'border': "0"});
		this.pPopupNode.style.border = "1px solid #A0A0A0";
		this.onclick = function (e){BXPopupWindow.Hide();};
		var t = BXPopupWindow.CreateElement("TABLE", {'border': '0', 'width': '80', 'cellSpacing': '1', 'cellPadding': '2'});
		t.onclick = function (e){BXPopupWindow.Hide();};
		var r = t.insertRow(-1);
		var c = r.insertCell(-1);
		t.className = 'bxedtalignpicker';
		//c.style.height = "0%";
		c.innerHTML = '<nobr>'+BX_MESS.TAlignDef+'</nobr>';
		c.className = 'bxedtbutton';
		c.noWrap = true;
		c.onmouseover = function (e){this.className = 'bxedtbuttonover';};
		c.onmouseout = function (e){this.className = 'bxedtbutton';};
		c.onclick = function (e){obj._onChange('', ''); BXPopupWindow.Hide();};

		r = t.insertRow(-1);
		c = r.insertCell(-1);
		c.style.height = "100%";
		if(!this.type)
			this.type = "default";

		var r1, c1, t1 = BXPopupWindow.CreateElement("TABLE", {'border': '0', 'cellSpacing': '3', 'cellPadding': '0'});
		for(i=0; i<3; i++)
		{
			r1 = t1.insertRow(-1);
			if(this.type == 'table')
				i = 1;
			for(j=0; j<3; j++)
			{
				c1 = r1.insertCell(-1);
				if(this.type == 'image' && i!=1 && j!=1)
				{
					c1 = c1.appendChild(BXPopupWindow.CreateElement("IMG", {"src": "/bitrix/images/fileman/htmledit2/1.gif", "border": 0, "alt": "", "title": ""}));
					c1.className = 'bxedtbutton';
				}
				else
				{
					c1 = c1.appendChild(BXPopupWindow.CreateElement("IMG", {"src": "/bitrix/images/fileman/htmledit2/talign-"+this.arIcon[i*3+j]+".gif", "border": 0, "alt": this.arIconName[i*3 + j], "title": this.arIconName[i*3 + j]}));
					c1.className = 'bxedtbutton';
					if(this.type == 'image')
					{
						if(j==1)
							c1.val = this.arIconV[i];
						else
							c1.val = this.arIconH[j];
						c1.onclick = function (e){obj._onChangeI(this.val); BXPopupWindow.Hide(); this.className = 'bxedtbutton'; /*return false;*/};
					}
					else
					{
						c1.valH = this.arIconH[j];
						c1.valV = this.arIconV[i];
						c1.onclick = function (e){obj._onChange(this.valH, this.valV); BXPopupWindow.Hide(); this.className = 'bxedtbutton'; /*return false;*/};
					}
					c1.onmouseover = function (e){this.className = 'bxedtbuttonover';};
					c1.onmouseout = function (e){this.className = 'bxedtbutton';};
				}
			}
			if(this.type == 'table')
				break;
		}

		c.appendChild(t1);
		this.pPopupNode.appendChild(t);
	}

	_BXTAlignPicker.prototype._onChange = function (valH, valV)
	{
			if(this.onChange)
				this.onChange(valH, valV);

			this.SetValue(valH, valV);
	}

	_BXTAlignPicker.prototype._onChangeI = function (val)
	{
			if(this.onChange)
				this.onChange(val);

			this.SetValueI(val);
	}

	_BXTAlignPicker.prototype.SetValue = function(valH, valV)
	{
		if(this.type=='image')
			return this.SetValueI(valH);

		for(var j=0; j<3; j++)
			if(this.arIconH[j] == valH)
				break;

		for(var i=0; i<3; i++)
			if(this.arIconV[i] == valV)
				break;

		if(i>2)i=1;
		if(j>2)j=0;

		this.pIcon.src = "/bitrix/images/fileman/htmledit2/talign-"+this.arIcon[i*3+j]+".gif";
		this.pIcon.alt = this.arIconName[i*3 + j];
		this.pIcon.title = this.pIcon.alt;
		return i*3 + j;
	}

	_BXTAlignPicker.prototype.SetValueI = function(val)
	{
		var i, j = 0;
		for(i=0; i<3; i++)
			if(this.arIconV[i] == val)
			{
				j = 1;
				break;
			}
		if(j!=1)
			for(j=0; j<3; j++)
				if(this.arIconH[j] == val)
				{
					i = 1;
					break;
				}

		if(i>2)i=1;
		if(j>2)j=0;

		this.pIcon.src = "/bitrix/images/fileman/htmledit2/talign-"+this.arIcon[i*3+j]+".gif";
		this.pIcon.alt = this.arIconName[i*3 + j];
		this.pIcon.title = this.pIcon.alt;
		return i*3 + j;
	}

	_BXTAlignPicker.prototype.onMouseOver = function (e)
	{
		this.pIcon.className = 'bxedtbuttonover';
	}

	_BXTAlignPicker.prototype.onMouseOut = function (e)
	{
		this.pIcon.className = 'bxedtbutton';
	}

	_BXTAlignPicker.prototype.onClick = function (e)
	{
		//this.pWnd.className = 'bxedtbutton';
		var arPos = GetRealPos(this.pIcon);
		BXPopupWindow.SetCurStyles();
		BXPopupWindow.Show([arPos["left"], arPos["right"]], [arPos["top"], arPos["bottom"]], this.pPopupNode);
	}
}

var pBXTAlignPicker = new _BXTAlignPicker();
function BXTAlignPicker()
{
	return new _BXTAlignPicker();
	//return pBXColorPicker;
}




/*************************************************************************************
**************************************************************************************
**************************************************************************************
**************************************************************************************
**************************************************************************************
*************************************************************************************/
function BXCombo(pMainObj, id, pHandler)
{
	this.className = "BXCombo";
	this.id = id;
	this.items = Array();
	this.pHandler = pHandler;
	this.pMainObj = pMainObj;

	BXCombo.prototype.AddRow = function(id, value, title, handler)
	{
		this.items[id] = new Object;
		this.items[id].id = id;
		this.items[id].value = value;
		if(handler)
			this.items[id].handler = handler;
		if(title)
			this.items[id].title = title;
		else
			this.items[id].title = value;
	}

	BXCombo.prototype.Show = function()
	{
		var obj = this;
		this.dx = this.params[1];
		this.dy = this.params[2];
		this.title_size = this.params[3];
		this.title_name = this.params[4];

		this.pWnd = this.pMainObj.pDocument.createElement("TABLE");
		this.pWnd.cellPadding = 0;
		this.pWnd.cellSpacing = 0;
		this.pWnd.className = 'bxcombo';
		this.pWnd.unselectable = "on";
		var obwnd = this.pWnd;
		this.pWnd.onmouseover = function (e){obj.pWnd.className = 'bxcomboover'; return false;};
		this.pWnd.onmouseout = function (e){obj.pWnd.className = 'bxcombo';return false;};
		var r  = this.pWnd.insertRow(0);
		var c = r.insertCell(0);
		this.title = c.appendChild(this.pMainObj.pDocument.createElement("DIV"));
		this.title.className = 'bxcombotitle';
		this.title.style.width = this.title_size;
		this.title.innerHTML = this.title_name;
		this.title.unselectable = "on";

		var c2 = r.insertCell(1);
		c2.appendChild(this.pMainObj.CreateElement('IMG', {'src':'/bitrix/images/fileman/htmledit2/combo_arr.gif', 'border':'0', 'width':'11', 'height':'17'}));

		this.pWnd.onclick = function e(){obj.Drop(true)};
		this.pDropDown = new BXPopup(this.pMainObj, this.pWnd, this.pMainObj.pDocument);

		var itemstable = this.pDropDown.pDocument.createElement("TABLE");
		itemstable.style.width = "100%";
		for(var it_id in this.items)
		{
			var item = itemstable.insertRow(-1).insertCell(0);
			item.unselectable = "on";
			item.style.border = "1px #CCCCCC solid";
			item.style.cursor = "default";
			item.onmousemove = function (e)
			{
				this.style.border = "1px #0000FF solid";
			}
			item.onmouseout = function (e)
			{
				this.style.border = "1px #CCCCCC solid";
			}
			item.onclick = function (e)
			{
				this.style.border = "1px #CCCCCC solid";
				obj.title.innerText = this.title;
				obj.Drop(false);
				var id = it_id;
				obj.selectedItem = this.id;
				if(item.handler)
					item.handler();
				else
					obj.OnSelect();
			}
			item.innerHTML = this.items[it_id].value;
			item.title = this.items[it_id].title;
			item.id = this.items[it_id].id;
			item.handler = this.items[it_id].handler;
		}

		this.pDropDown.AddContent(itemstable);
	}

	BXCombo.prototype.Drop = function(bDrop)
	{
		if(bDrop)
		{
			var pos = GetRealPos(this.pWnd);
			this.pDropDown.Show(pos["left"], pos["bottom"], this.dx, this.dy)
		}
		else
			this.pDropDown.Hide();
		this.pMainObj.SetFocus();
	}

	BXCombo.prototype.onToolbarChangeDirection = function (bVertical)
	{
		if(bVertical)
			this.title.style.width = "0px";
		else
			this.title.style.width = this.title_size;
		this.pWnd.className = 'bxcombo';
	}
}

function BXTBSelect()
{

}

//BXCombo.prototype = new BXToolbarItem;
BXTBSelect.prototype = new BXCombo;
//BXTBSelect.prototype = new BXCombo;

function FontStyleListSelect()
{
	var obj = this;
	obj.className = 'FontStyleListSelect';
	obj.items = Array();
	obj.__Show = obj.Show;
	obj.Show = function ()
	{
		//this.arStyles
		//
		//for(var ix=0; ix<obj.params[5].length; ix++)
		//	obj.AddRow(ix, '<span style="font-name:' + obj.params[5][ix] + ';">' + obj.params[5][ix] + '</span>');
		obj.__Show();
	}
	obj.OnSelect = function (val)
	{
		alert(val.id);
	}
}
FontStyleListSelect.prototype = new BXTBSelect;

function FontSizeListSelect()
{
	var obj = this;
	obj.className = 'FontSizeListSelect';
	obj.items = Array();
	obj.__Show = obj.Show;
	obj.Show = function ()
	{
		for(var ix=0; ix<obj.params[5].length; ix++)
			obj.AddRow(ix, '<span style="font-size:' + obj.params[5][ix] + ';">' + obj.params[5][ix] + '</span>', obj.params[5][ix]);
		obj.__Show();
	}

	FontSizeListSelect.prototype.OnSelect = function ()
	{
		var item_id = this.selectedItem;
		//this.pMainObj.executeCommand('FontSize', this.items[item_id].title);
		this.pMainObj.WrapSelectionWith('span', {'style': 'font-size:'+this.items[item_id].title+';'});
		return true;
	}
}

//**************************************************************************************/
function BXDialog()
{
	BXDialog.prototype._Create = function ()
	{
		if(!this.params || typeof(this.params)!="object")
			this.params = {};

		this.params.pMainObj = this.pMainObj;
		if(window.showModalDialog)
			window.showModalDialog('/bitrix/admin/fileman_editor_dialog.php?lang='+BXLang+'&site='+BXSite+'&name='+this.name+'&not_use_default='+this.not_use_default, this.params, "dialogWidth:"+this.width+"px;dialogHeight:"+this.height+"px;help:no;scroll:no;status:no;center:yes;");
		else
		{
			this.pWnd = window.open('/bitrix/admin/fileman_editor_dialog.php?lang='+BXLang+'&site='+BXSite+'&name='+this.name+'&not_use_default='+this.not_use_default, 'BXDialog'+Math.random.toString().substring(2), 'height='+this.height+',width='+this.width+',toolbar=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,modal=yes,alwaysRaised=yes,dialog=yes');
			this.pWnd.resizeTo(this.width, this.height);
			this.pWnd.moveTo((screen.width-this.width)/2, (screen.height-this.height)/2);
			this.pWnd.dialogArguments = this.params;

			addCustomElementEvent(window.top.parent, 'focus', this.CheckFocus, this);

			this.pWnd.focus();
		}
	}

	BXDialog.prototype.CheckFocus = function()
	{
		if(this.pWnd && !this.pWnd.closed)
		{
			this.pWnd.focus();
			return false;
		}
		else
		{
			delCustomElementEvent(window.top.parent, 'focus', this.CheckFocus);
		}
	}
}
