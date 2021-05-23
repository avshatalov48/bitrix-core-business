if (!SMupdateURL || SMupdateURL.length < 0)
	SMupdateURL = updateURL;

if (!window.XMLHttpRequest)
{
	var XMLHttpRequest = function()
	{
		try { return new ActiveXObject("MSXML3.XMLHTTP") } catch(e) {}
		try { return new ActiveXObject("MSXML2.XMLHTTP.3.0") } catch(e) {}
		try { return new ActiveXObject("MSXML2.XMLHTTP") } catch(e) {}
		try { return new ActiveXObject("Microsoft.XMLHTTP") } catch(e) {}
	}
}

var allMenuHolders = [];
function getMenuHolder(id)
{
	return allMenuHolders[id];
}


function BXMenu(menuHolderID)
{
	var _this = this;

	_this.menuHolderID = menuHolderID;

	allMenuHolders[_this.menuHolderID] = _this;

	_this.itemCols = Array();
	_this.colSource = false;
	_this.alreadyMoved = false;
	_this.ddMenuBlocked = false;
	_this.ddMenuOpen = false;
	_this.obTD = false;	
	_this.SettingsBlocked = false;
	_this.ddMenuHolderId = false;	
	
	_this.ActiveHolderPos = false;
	_this.ActiveButtonPos = false;

	_this.ClearButtonIsOver = false;
	
	_this.ShowHolder = function (id, ob)
	{
		if (_this.ddMenuBlocked)
			return;

		if (_this.ddMenuOpen)
			return;

		if(ob)
			_this.ActiveButtonPos = BX.pos(ob);
			
		var HolderDiv = BX(id)
		if (!HolderDiv)
			return;
			
		jsUtils.addEvent(document, "mousemove", _this.HolderCheckMouseMove);	
		HolderDiv.className = "ddmenu-active";
		
		_this.obTD = BX.findChild(ob, {tag: 'td'}, true);
		_this.obTD.className = 'bx-sm-feature-select';
		
		_this.ActiveHolderPos = BX.pos(HolderDiv);		
		_this.ddMenuOpen = true;
		_this.ddMenuHolderId = id;
	}

	_this.HolderCheckMouseMove = function(e)
	{	
		if(e && e.pageX)
			var current = {x: e.pageX, y: e.pageY};
		else
		{
			var scrollSize = BX.GetWindowScrollPos();
			var current = {x: event.clientX + scrollSize.scrollLeft, y: event.clientY + scrollSize.scrollTop};
		}

		if(
			(current.x >= parseInt(_this.ActiveHolderPos.left)) && (current.x <= parseInt(_this.ActiveHolderPos.right))
			&& (current.y >= parseInt(_this.ActiveHolderPos.top)) && (current.y <= parseInt(_this.ActiveHolderPos.bottom))
			)
		{
		}
		else
		{
			if (
				(current.x >= parseInt(_this.ActiveButtonPos.left)) && (current.x <= parseInt(_this.ActiveButtonPos.right))
			&& (current.y >= parseInt(_this.ActiveButtonPos.top)) && (current.y <= parseInt(_this.ActiveButtonPos.bottom))
			)
			{
			}
			else
			{
				jsUtils.addEvent(document, "mousemove", _this.HolderCheckMouseMoveOut);				
				setTimeout(function () {
					_this.HideHolder(_this.ddMenuHolderId)
				}, 500);
				_this.bHolderHide = true;
			}
		}
	
	}


	_this.HolderCheckMouseMoveOut = function(e)
	{	
		if(e && e.pageX)
			var current = {x: e.pageX, y: e.pageY};
		else
		{
			var scrollSize = BX.GetWindowScrollPos();
			var current = {x: event.clientX + scrollSize.scrollLeft, y: event.clientY + scrollSize.scrollTop};
		}

		if(
			(current.x >= parseInt(_this.ActiveHolderPos.left)) && (current.x <= parseInt(_this.ActiveHolderPos.right))
			&& (current.y >= parseInt(_this.ActiveHolderPos.top)) && (current.y <= parseInt(_this.ActiveHolderPos.bottom))
			)
		{
			jsUtils.removeEvent(document, "mousemove", _this.HolderCheckMouseMoveOut);		
			_this.bHolderHide = false;			
		}
		else
		{
			if (
				(current.x >= parseInt(_this.ActiveButtonPos.left)) && (current.x <= parseInt(_this.ActiveButtonPos.right))
			&& (current.y >= parseInt(_this.ActiveButtonPos.top)) && (current.y <= parseInt(_this.ActiveButtonPos.bottom))
			)
			{
				jsUtils.removeEvent(document, "mousemove", _this.HolderCheckMouseMoveOut);
				_this.bHolderHide = false;				
			}
		}
	
	}

	
	_this.HideHolder = function (id)
	{
		if (!_this.bHolderHide)
			return;
			
		if (_this.ddMenuBlocked)
			return;
	
		var HolderDiv = BX(id)
		if (!HolderDiv)
			return;
			
		jsUtils.removeEvent(document, "mousemove", _this.HolderCheckMouseMove);	
		jsUtils.removeEvent(document, "mousemove", _this.HolderCheckMouseMoveOut);
		HolderDiv.className = "ddmenu-inactive";
		_this.ddMenuOpen = false;
		_this.ddMenuHolderId = false;
		_this.bHolderHide = false;
		_this.obTD.className = 'bx-sm-feature-noselect';
	}
	

	_this.__MenuList = function()
	{
		_this.itemCols = Array();
		var MenuHolder = BX("MenuHolder_"+_this.menuHolderID).rows[0].cells;

		for(var i=0; i < MenuHolder.length; i++)
		{
			if(MenuHolder[i].id.substring(0, 1) == 's')
			{
				MenuHolder[i].realPos = BX.pos(MenuHolder[i]);
				_this.itemCols[_this.itemCols.length] = MenuHolder[i];
			}
		}

		if (bMenuAdd)
		{
			var MenuHolderAdd = BX("MenuHolderAdd_"+_this.menuHolderID).rows;

			for(i=0; i < MenuHolderAdd.length; i++)
			{
				if(MenuHolderAdd[i].cells[0].id.substring(0, 1) == 's')
				{
					var itemColsIndex = _this.itemCols.length;
					_this.itemCols[itemColsIndex] = MenuHolderAdd[i].cells[0].firstChild;
					_this.itemCols[itemColsIndex].realPos = BX.pos(MenuHolderAdd[i].cells[0]);
				}
			}
		}
	}

	_this.itemDrag = false;
	_this.mousePos = {x: 0, y: 0};
	_this.zind = 0;

	_this.t_swap = false;
	_this.d_swap = false;

	_this.DragStart = function(n, e)
	{
		_this.PreMoveCoords = false;

		if(e && e.pageX)
			_this.PreMoveCoords = {x: e.pageX, y: e.pageY};
		else
			_this.PreMoveCoords = {x: event.clientX + document.body.scrollLeft, y: event.clientY + document.body.scrollTop};

		_this.bDragInPreMove = false;
		
		jsUtils.addEvent(document.body, "mousemove", _this.onMousePreMove);
		jsUtils.addEvent(document.body, "mouseup", _this.onMousePreUp);

		_this.itemDrag = n;
	
		return false;
	}

	_this.onMousePreUp = function(e)
	{
		jsUtils.removeEvent(document.body, "mousemove", _this.onMousePreMove);
		jsUtils.removeEvent(document.body, "mouseup", _this.onMousePreUp);
	}
	
	_this.onMousePreMove = function(e)
	{
		if(e && e.pageX)
			var current = {x: e.pageX, y: e.pageY};
		else
		{
			var scrollSize = BX.GetWindowScrollPos();
			var current = {x: event.clientX + scrollSize.scrollLeft, y: event.clientY + scrollSize.scrollTop};
		}
		
		if (current.x >= (_this.PreMoveCoords.x-3) && current.x <= (_this.PreMoveCoords.x+3) && current.y >= (_this.PreMoveCoords.y-3) && current.y <= (_this.PreMoveCoords.y+3))
		{
		}
		else
		{
			jsUtils.removeEvent(document.body, "mousemove", _this.onMousePreMove);
			jsUtils.removeEvent(document.body, "mouseup", _this.onMousePreUp);

			jsUtils.addEvent(document.body, "mouseup", _this.onMouseUp);
			jsUtils.addEvent(document.body, "mousemove", _this.onMouseMove);
			
			_this.bDragInPreMove = true;
		}
		return false;
	}
	

	_this.onMouseMove = function(e)
	{
		if(_this.itemDrag == false)
			return;
			
		if (!_this.alreadyMoved)
		{
			var antiselect = BX("antiselect");
			
			if(antiselect)
			{
				antiselect.style.display = 'block';

			 	var windowSize = BX.GetWindowScrollSize();
				antiselect.style.width = windowSize.scrollWidth + "px";
				antiselect.style.height = windowSize.scrollHeight + "px";
				antiselect.style.opacity = 0.01;
				antiselect.style.filter = 'gray() alpha(opacity=01)';
			}

			_this.__MenuList();
			var t = BX('t'+_this.itemDrag);
			var tablePos = BX.pos(t);
			var d = BX('d'+_this.itemDrag);

			var rRealPos = BX.pos(t);		
			var center = rRealPos.left + (rRealPos.right - rRealPos.left)/2, center2 = rRealPos.top + (rRealPos.bottom - rRealPos.top)/2;
			for(var i=0; i<_this.itemCols.length; i++)
			{
				c = _this.itemCols[i].realPos;
				if(c.left <= center && c.right >= center && c.top <= center2 && c.bottom >= center2)
				{
					_this.colSource = _this.itemCols[i];
					break;
				}
			}
			d.style.display = 'block';

			t.style.position = 'absolute';
			t.style.width = d.offsetWidth+'px';
			t.style.height = d.offsetHeight+'px';
			t.style.left = tablePos["left"]+20+'px';
			t.style.top = tablePos["top"]+'px';
			t.style.border = '1px solid #777777';
			_this.zind = t.style.zIndex;
			t.style.zIndex = '10000';

			t.style.MozOpacity = 0.60;
			t.style.opacity = 0.60;
			t.style.overflow = 'hidden';
			t.style.filter = 'gray() alpha(opacity=60)';

//			var AddMenuHolder = BX("ddmenuaddholder");
//			AddMenuHolder.appendChild(t);
			document.body.appendChild(t);
				
			_this.mousePos.x = e.clientX + document.body.scrollLeft;
			_this.mousePos.y = e.clientY + document.body.scrollTop;
			
			if (_this.ddMenuOpen)
				_this.ddMenuBlocked = true;
			
			_this.alreadyMoved = true;
		}

		_this.bDragging = true;

		var t = BX('t'+_this.itemDrag);

		var x = e.clientX + document.body.scrollLeft;
		var y = e.clientY + document.body.scrollTop;

		t.style.left = parseInt(t.style.left) + x - _this.mousePos.x + 'px';
		t.style.top =  parseInt(t.style.top) + y - _this.mousePos.y + 'px';

		var rRealPos = BX.pos(t), c, el = false;
		var center = rRealPos.left + (rRealPos.right - rRealPos.left)/2, center2 = rRealPos.top + (rRealPos.bottom - rRealPos.top)/2;
		for(var i=0; i<_this.itemCols.length; i++)
		{
			c = _this.itemCols[i].realPos;
			if(c.left <= center && c.right >= center && c.top <= center2 && c.bottom >= center2)
			{
				el = true;
				break;
			}

		}

		if(el)
		{
			var d = BX('d'+_this.itemDrag);
			d.parentNode.removeChild(d);

			if(!_this.t_swap && _this.colSource && _this.colSource != _this.itemCols[i])
			{
				var n = _this.itemCols[i].childNodes.length;
				var child = false;

				for (var j=0; j<n; j++)
				{
					child = _this.itemCols[i].childNodes[j];
					if (child.id && child.id != 't'+_this.itemDrag && child.id.indexOf('d') != 0)
					{
						_this.t_swap = child;
						break;
					}
				}

				for (var j=0; j<n; j++)
				{
					child = _this.itemCols[i].childNodes[j];
					if (child.id && child.id.indexOf('d') == 0)
					{
						_this.d_swap = child;
						break;
					}
				}

				_this.colSource.appendChild(_this.t_swap);
				_this.colSource.appendChild(_this.d_swap);
				_this.t_swap = false;
				_this.d_swap = false;
				_this.colSource = _this.itemCols[i];
			}

			_this.itemCols[i].appendChild(d);
		}

		_this.mousePos.x = x;
		_this.mousePos.y = y;
	}

	_this.onMouseUp = function(e)
	{
	
		_this.bWasDraggedRecently = true;
		
		if(_this.itemDrag == false)
			return;
		
		setTimeout(function () {
			_this.bDragging = false;
		}, 50);

		var antiselect = BX("antiselect");
		
		if(antiselect)
			antiselect.style.display = 'none';

		var t = BX('t'+_this.itemDrag);

		t.style.MozOpacity = 1;
		t.style.opacity = 1;
		t.style.filter = '';
		t.style.position = 'static';
		t.style.border = '0px';
		t.style.width = '';
		t.style.height = '';
		t.style.zIndex = _this.zind;
		t.style.overflow = 'auto';
			
		var d = BX('d'+_this.itemDrag);
		d.style.display = 'none';

		t.parentNode.removeChild(t);
		d.parentNode.insertBefore(t, d);

		_this.itemDrag = false;
		_this.colSource = false;
		_this.t_swap = false;
		_this.ddMenuBlocked = false;
		_this.alreadyMoved = false;

		if (_this.ddMenuHolderId)
			setTimeout(function() { 
				_this.bHolderHide = true;
				_this.HideHolder(_this.ddMenuHolderId);
			}, 500);
		
		if(!_this.sendWait)
		{
			_this.sendWait = true;
			setTimeout("getMenuHolder('"+_this.menuHolderID+"').SendUpdatedInfo();", 1000);
		}
		
		jsUtils.removeEvent(document.body, "mousemove", _this.onMousePreMove);
		jsUtils.removeEvent(document.body, "mouseup", _this.onMousePreUp);

		jsUtils.removeEvent(document.body, "mousemove", _this.onMouseMove);
		jsUtils.removeEvent(document.body, "mouseup", _this.onMouseUp);

		_this.bDragInPreMove = false;
		
		setTimeout(function () {
			_this.bWasDraggedRecently = false;
		}, 500);	
		return BX.PreventDefault(e);
	}

	_this.GetPosString = function()
	{
		var MenuHolder = BX("MenuHolder_"+_this.menuHolderID).rows[0].cells;
		var i;
		var result = '', column=-1;
		for(i=0; i < MenuHolder.length; i++)
		{
			if(MenuHolder[i].id.substring(0, 1) == 's')
			{
				column++;
				childElements = MenuHolder[i].childNodes;
				for(el in childElements)
				{
					if(!childElements[el])
						continue;
					if(childElements[el].tagName && childElements[el].tagName.toUpperCase() == 'TABLE' && childElements[el].id.substring(0, 1) == 't')
					{
						result = result+'&POS['+column+']='+encodeURIComponent(childElements[el].id.substring(1));
						break;
					}
				}
			}
		}

		if (bMenuAdd)
		{
		
			var MenuHolderAdd = BX("MenuHolderAdd_"+_this.menuHolderID).rows;

			for(i=0; i < MenuHolderAdd.length; i++)
			{
				if(MenuHolderAdd[i].cells[0].id.substring(0, 1) == 's')
				{
					column++;
					childElements = MenuHolderAdd[i].cells[0].firstChild.childNodes;				
					
					for(el in childElements)
					{
						if(!childElements[el])
							continue;
						if(childElements[el].tagName && childElements[el].tagName.toUpperCase() == 'TABLE' && childElements[el].id.substring(0, 1) == 't')
						{
							result = result+'&POS['+column+']='+encodeURIComponent(childElements[el].id.substring(1));
							break;
						}
					}				
				}
			}		
		}
		
		return result;
	}


	_this.menuXmlHttpUpdate = new XMLHttpRequest();
	_this.sendWait = false;
	_this.SendUpdatedInfo = function(param)
	{
		param = param || "update_position";

		if (_this.menuXmlHttpUpdate.readyState % 4)
		{
			setTimeout("getMenuHolder('"+_this.menuHolderID+"').SendUpdatedInfo('"+param+"');", 500);
			return;
		}

		_this.sendWait = false;

		_this.menuXmlHttpUpdate.open("POST", SMupdateURL, true);
		_this.menuXmlHttpUpdate.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		_this.menuXmlHttpUpdate.onreadystatechange = function()
		{
			if(_this.menuXmlHttpUpdate.readyState == 4)
			{
				if(_this.menuXmlHttpUpdate.status == 200)
				{
					if(BX.util.trim(_this.menuXmlHttpUpdate.responseText).length > 0)
					{

					}
					if(param == 'clear_settings')
						window.location = window.location;
				}
				else
				{
					alert(langMenuError1);
				}
			}
		}

		_this.menuXmlHttpUpdate.send("menu_ajax="+_this.menuHolderID+"&menu_ajax_action=" + param + _this.GetPosString());
	}

	_this.Add = function(feature)
	{
		if(feature == false)
			return;

		var frm = BX("MenuHolderForm_" + _this.menuHolderID);
		frm["sm_action"].value = "add";
		frm["feature"].value = feature;
		frm["sessid"].value = BX.bitrix_sessid();
		
		BX.ajax.submit(frm);
	}

	
	_this.ClearButtonOver = function()
	{
		if (_this.ddMenuOpen)
			return false;
			
		BX('bx_sm_settings').className = 'bx-sm-feature-select';
	}
	
	_this.ClearButtonOut = function()
	{
		BX('bx_sm_settings').className = 'bx-sm-feature-noselect';
	}
	
	
	_this.ClearUserSettings = function()
	{
		if(!confirm(langMenuConfirm1))
			return;
	
		_this.SendUpdatedInfo('clear_settings');
	}

	_this.Delete = function(feature)
	{
		if(feature == false)
			return;

		if(!confirm(langMenuConfirm2))
			return;

		var frm = BX("MenuHolderForm_" + _this.menuHolderID);
		frm["sm_action"].value = "delete";
		frm["feature"].value = feature;
		frm["sessid"].value = BX.bitrix_sessid();
		
		BX.ajax.submit(frm);
	}

	_this.ShowSettings = function(id, t, feature)
	{
		if(feature == false)
			return;
	
		if (_this.ddMenuOpen)
			_this.ddMenuBlocked = true;

		t = t || 'get_settings';
		
		eval("var myDialogF = langMenuSettDialogTitle_"+feature);
		var myDialog = new BX.CDialog({title: langMenuSettDialogTitle1+myDialogF, content_url: SMupdateURL, content_post: "menu_ajax="+_this.menuHolderID+"&feature="+id+"&menu_ajax_action="+t, width: 550, height: 400, min_width: 500, min_height: 350 });
		myDialog.Show();
		BX.addCustomEvent(myDialog, 'onWindowUnRegister', function() { _this.ddMenuBlocked = false; })
		return false;
	}
	
	_this.ShowMenuSettings = function(t)
	{
		t = t || 'get_menu_settings';
		
		eval("var myDialogF = langMenuSettDialogTitle_global");
		var myDialog = new BX.CDialog({title: langMenuSettDialogTitle_global, content_url: SMupdateURL, content_post: "menu_ajax="+_this.menuHolderID+"&menu_ajax_action="+t, width: 550, height: 300, min_width: 500, min_height: 250 });
		myDialog.Show();
		return false;
	}	
}





function BXMenuItem(feature)
{
	var tracking = false;
	var ActiveItemPos = false;
	var ActiveButtonsPos = false;

	var _this = this;
	this.feature = feature;
	_this.bVertical = false;
	
	_this.StartTrackMouse = function(ob)
	{
	
		if (window.___BXMenu.itemDrag)
			return;
			
		_this.tracking = true;	
		_this.ActiveItemPos = BX.pos(ob);	
		
		BX.findParent(ob)
		
		var obDIV = BX.findParent(ob, {tag: 'div', property: {id: 'ddmenuadd'}}, true);
		if (obDIV)
			_this.bVertical = true;
		else
			_this.bVertical = false;
			
		jsUtils.addEvent(document, "mousemove", _this.CheckMouseMove);	
		setTimeout(function() { _this.ShowActionButtons() }, 500);
	}
	
	_this.CheckMouseMove = function(e)
	{	
		if(e && e.pageX)
			var current = {x: e.pageX, y: e.pageY};
		else
		{
			var scrollSize = BX.GetWindowScrollPos();
			var current = {x: event.clientX + scrollSize.scrollLeft, y: event.clientY + scrollSize.scrollTop};
		}
			
		if (_this.ActiveButtonsPos)
			if (_this.bVertical)
			{
				var delta_top = 0;
				var delta_right = parseInt(_this.ActiveButtonsPos.width);
			}
			else
			{
				var delta_top = parseInt(_this.ActiveButtonsPos.height);
				var delta_right = 0;
			}
		else
		{
			var delta_top = 0;
			var delta_right = 0;
		}
		
		if(
			(current.x >= parseInt(_this.ActiveItemPos.left)) && (current.x <= (parseInt(_this.ActiveItemPos.right) + delta_right))
			&& (current.y >= (parseInt(_this.ActiveItemPos.top) - delta_top)) && (current.y <= parseInt(_this.ActiveItemPos.bottom))
			)
		{
			// inside
		}
		else
		{
			jsUtils.removeEvent(document, "mousemove", _this.CheckMouseMove);			
			_this.HideActionButtons()
			_this.tracking = false;
		}
	
	}

	_this.ShowActionButtons = function()
	{
		if (_this.tracking)
		{
			var act = BX("act_"+_this.feature);
			if(act && act.style.display == 'block')
				return;
			act.style.display = 'block';
			_this.ActiveButtonsPos = BX.pos(act);
		}
	}
	
	_this.HideActionButtons = function()
	{
		var act = BX("act_"+_this.feature);
		
		if(act && act.style.display == 'none')
			return;
		act.style.display = 'none';
	}
	
}