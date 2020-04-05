var phpVars;
if(!phpVars)
{
	phpVars = {
		ADMIN_THEME_ID: '.default',
		LANGUAGE_ID: 'en',
		FORMAT_DATE: 'DD.MM.YYYY',
		FORMAT_DATETIME: 'DD.MM.YYYY HH:MI:SS',
		opt_context_ctrl: false,
		cookiePrefix: 'BITRIX_SM',
		titlePrefix: '',
		bitrix_sessid: '',
		messHideMenu: '',
		messShowMenu: '',
		messHideButtons: '',
		messShowButtons: '',
		messFilterInactive: '',
		messFilterActive: '',
		messFilterLess: '',
		messLoading: 'Loading...',
		messMenuLoading: '',
		messMenuLoadingTitle: '',
		messNoData: '',
		messExpandTabs: '',
		messCollapseTabs: '',
		messPanelFixOn: '',
		messPanelFixOff: '',
		messPanelCollapse: '',
		messPanelExpand: '',
		messFavAddSucc: '',
		messFavAddErr: '',
		messFavDelSucc: '',
		messFavDelErr: ''
	};
}

function JCSplitter(params)
{
	this.params = params;

	this.Highlight = function(on)
	{
		var control = document.getElementById(this.params.control);
		var div = document.getElementById(this.params.divShown);
		if(div.style.display!="none")
			control.className = this.params.classShown+(on? 'sel':'');
		else
			control.className = this.params.classHidden+(on? 'sel':'');
	}

	this.Toggle = function()
	{
		var visible = jsUtils.ToggleDiv(this.params.divShown);
		jsUtils.ToggleDiv(this.params.divHidden);
		this.Highlight(false);
		document.getElementById(this.params.control).title = (visible? this.params.messHide : this.params.messShow);
		return visible;
	}
}

/************************************************/

function JCAdminMenu(sOpenedSections)
{
	var _this = this;
	this.sMenuSelected='';
	this.x = 0;
	this.divToResize = null;
	this.divToBound = null;
	this.toggle = false;
	this.oSections = {};
	this.request = new JCHttpRequest();

	var aSect = sOpenedSections.split(',');
	for(var i in aSect)
		this.oSections[aSect[i]] = true;

	this.verSplitter = new JCSplitter({
		control:'vdividercell',
		divShown:'menudiv', divHidden:'hiddenmenucontainer',
		messHide:phpVars.messHideMenu, messShow:phpVars.messShowMenu,
		classShown:'vdividerknob vdividerknobleft', classHidden:'vdividerknob vdividerknobright'
	});
	this.horSplitter = new JCSplitter({
		control:'hdividercell',
		divShown:'buttonscontainer', divHidden:'smbuttonscontainer',
		messHide:phpVars.messHideButtons, messShow:phpVars.messShowButtons,
		classShown:'hdividerknob hdividerknobup', classHidden:'hdividerknob hdividerknobdown'
	});

	this.verSplitterToggle = function()
	{
		var visible = this.verSplitter.Toggle();
		jsUserOptions.SaveOption('admin_menu', 'pos', 'ver', (visible? 'on':'off'));
	}

	this.horSplitterToggle = function()
	{
		var visible = this.horSplitter.Toggle();
		jsUserOptions.SaveOption('admin_menu', 'pos', 'hor', (visible? 'on':'off'));
	}

	this.ToggleMenu = function(menu_id, menu_text)
	{
		var div = document.getElementById(menu_id);
		if(div.style.display!="none")
			return;

		/*menu div*/
		if(this.sMenuSelected != "")
			document.getElementById(this.sMenuSelected).style.display = 'none';
		div.style.display = "block";

		/*button*/
		document.getElementById('menutitle').innerHTML = menu_text;

		document.getElementById('btn_'+this.sMenuSelected).className = 'button';
		document.getElementById('smbtn_'+this.sMenuSelected).className = 'smbutton';
		document.getElementById('btn_'+menu_id).className = 'button buttonsel';
		document.getElementById('smbtn_'+menu_id).className = 'smbutton smbuttonsel';

		this.sMenuSelected = menu_id;
	}

	this.StartDrag = function()
	{
		if(this.toggle)
			return;
		if(document.getElementById('menudiv').style.display == 'none')
			return;

		this.divToBound = document.getElementById("menu_min_width");
		this.divToResize = document.getElementById('menucontainer');
		this.x = this.divToResize.offsetWidth;

		jsUtils.addEvent(document, "mousemove", _this.ResizeMenu);
		document.onmouseup = this.StopDrag;

		var b = document.body;
		b.ondrag = jsUtils.False;
		b.onselectstart = jsUtils.False;
		b.style.MozUserSelect = 'none';
		b.style.cursor = 'e-resize';
	}

	this.StopDrag = function(e)
	{
		jsUtils.removeEvent(document, "mousemove", _this.ResizeMenu);
		document.onmouseup = null;

		var b = document.body;
		b.ondrag = null;
		b.onselectstart = null;
		b.style.MozUserSelect = '';
		b.style.cursor = '';

		if(window.onresize)
			window.onresize();

		jsUserOptions.SaveOption('admin_menu', 'pos', 'width', parseInt(_this.divToResize.style.width));
	}

	this.ResizeMenu = function(e)
	{
		var x = e.clientX + document.body.scrollLeft;
		if(	_this.x == x)
			return;

		var div = _this.divToResize;
		var mnu = _this.divToBound;

		if(x < mnu.offsetWidth)
		{
			div.style.width = mnu.offsetWidth+'px';
			_this.x = x;
			return;
		}

		div.style.width = div.offsetWidth+(x - _this.x)+'px';
		_this.x = x;
	}

	this.ToggleSection = function(cell, div_id, level)
	{
		if(jsUtils.ToggleDiv(div_id))
		{
			if(level <= 2)
				this.oSections[div_id] = true;
			cell.className='sign signminus';
		}
		else
		{
			this.oSections[div_id] = false;
			cell.className='sign signplus';
		}

		if(level <= 2)
		{
			var sect='';
			for(var i in this.oSections)
				if(this.oSections[i] == true)
					sect += (sect != ''? ',':'')+i;
			jsUserOptions.SaveOption('admin_menu', 'pos', 'sections', sect);
		}
	}

	this.ToggleDynSection = function(cell, module_id, div_id, level)
	{
		function MenuText(text)
		{
			var s = '';
			for(var i=0; i<level; i++)
				s += '<td><div class="menuindent"></div></td>\n';
			return(
				'<div class="menuline">'+
				'<table cellspacing="0">'+
				'	<tr>'+s+
				'		<td class="menutext menutext-loading">'+text+'</td>'+
				'	</tr>'+
				'</table>'+
				'</div>');
		}

		var div = document.getElementById(div_id);
		if(div.innerHTML == '')
		{
			div.innerHTML = MenuText(phpVars.messMenuLoading);

			this.request.Action = function(result)
			{
				result = jsUtils.trim(result);
				div.innerHTML = (result != ''? result : MenuText(phpVars.messNoData));
			}
			this.request.Send('/bitrix/admin/get_menu.php?lang='+phpVars.LANGUAGE_ID+'&admin_mnu_module_id='+module_id+'&admin_mnu_menu_id='+encodeURIComponent(div_id));
		}
		this.ToggleSection(cell, div_id, level);
	}
}



/***************************************/

function JCAdminList(table_id)
{
	var _this = this;
	this.table_id = table_id;

	this.InitTable = function()
	{
		var tbl = document.getElementById(this.table_id);
		if(!tbl || tbl.rows.length<1 || tbl.rows[0].cells.length<1)
			return;

		var i;
		var nCols = tbl.rows[0].cells.length;
		var sortedIndex = -1;

		/*head row mousover action*/
		for(i=0; i<nCols; i++)
		{
			var j;
			var cell_sort = tbl.rows[1].cells[i];
			var sort_table = jsUtils.FindChildObject(cell_sort, "table", "sorting");

			for(j=0; j<2; j++)
			{
				var cell = tbl.rows[j].cells[i];

				cell.onmouseover = function(){_this.HighlightGutter(this, true)};
				cell.onmouseout = function(){_this.HighlightGutter(this, false)};

				/*expand sorting table behaviour on parent cell*/
				if(sort_table)
				{
					cell.onclick = sort_table.onclick;
					cell.title = sort_table.title;
					cell.style.cursor = "pointer";

					if(j == 0)
					{
						var cl = sort_table.rows[0].cells[1].className.toLowerCase();
						if(cl == "sign up" || cl == "sign down")
						{
							cell.className += ' sorted';
							sortedIndex = i;
						}
					}
				}
			}
			if(sort_table)
				sort_table.onclick = null;
		}

		var n = tbl.rows.length;
		for(i=0; i<n; i++)
		{
			var row = tbl.rows[i];

			/*first and last columns style classes*/
			row.cells[0].className += ' left';
			row.cells[row.cells.length-1].className += ' right';

			if(row.className && row.className == 'footer')
				continue;

			/*sorted column*/
			if(sortedIndex != -1 && sortedIndex < row.cells.length)
				row.cells[sortedIndex].className += ' sorted';

			if(i>=2)
			{
				/*first column checkbox action*/
				var checkbox = row.cells[0].childNodes[0];
				if(checkbox && checkbox.tagName && checkbox.tagName.toUpperCase() == "INPUT" && checkbox.type.toUpperCase() == "CHECKBOX")
				{
					checkbox.onclick = function(){_this.SelectRow(this); _this.EnableActions()};
					jsUtils.addEvent(row, "click", _this.OnClickRow);
				}

				/*rows mousover action*/
				row.onmouseover = function(){_this.HighlightRow(this, true)};
				row.onmouseout = function(){_this.HighlightRow(this, false)};

				if(i%2 == 0)
					row.className += ' odd';
				else
					row.className += ' even';

				if(row.oncontextmenu)
				{
					jsUtils.addEvent(row, "contextmenu",
						function(e)
						{
							if(!e) e = window.event;
							if(!phpVars.opt_context_ctrl && e.ctrlKey || phpVars.opt_context_ctrl && !e.ctrlKey)
								return;

							var targetElement;
							if(e.target) targetElement = e.target;
							else if(e.srcElement) targetElement = e.srcElement;

							while(targetElement && !targetElement.oncontextmenu)
								targetElement = jsUtils.FindParentObject(targetElement, "tr");

							var x = e.clientX + document.body.scrollLeft;
							var y = e.clientY + document.body.scrollTop;
							var pos = {};
							pos['left'] = pos['right'] = x;
							pos['top'] = pos['bottom'] = y;

							var menu = window[_this.table_id+"_menu"];
							menu.PopupHide();
							menu.SetItems(targetElement.oncontextmenu());
							menu.BuildItems();
							menu.PopupShow(pos);

							e.returnValue = false;
							if(e.preventDefault) e.preventDefault();
						}
					);
				}
			}
		}

		if(tbl.rows.length > 2)
		{
			tbl.rows[2].className += ' top';
			tbl.rows[tbl.rows.length-1].className += ' bottom';
		}
	}

	this.Destroy = function(bLast)
	{
		var tbl = document.getElementById(this.table_id);
		if(!tbl || tbl.rows.length<1 || tbl.rows[0].cells.length<1)
			return;

		var i;
		var nCols = tbl.rows[0].cells.length;
		for(i=0; i<nCols; i++)
		{
			var j;
			for(j=0; j<2; j++)
			{
				var cell = tbl.rows[j].cells[i];
				cell.onmouseover = null;
				cell.onmouseout = null;
				cell.onclick = null;
			}
		}
		var n = tbl.rows.length;
		for(i=0; i<n; i++)
		{
			var row = tbl.rows[i];
			var checkbox = row.cells[0].childNodes[0];
			if(checkbox && checkbox.onclick)
				checkbox.onclick = null;
			row.onmouseover = null;
			row.onmouseout = null;
			jsUtils.removeAllEvents(row);
		}
		if(bLast == true)
			_this = null;
	}

	this.HighlightGutter = function(cell, on)
	{
		var table = cell.parentNode.parentNode.parentNode;
		var gutter = table.rows[0].cells[cell.cellIndex];
		if(on)
			gutter.className += ' over';
		else
			gutter.className = gutter.className.replace(/\s*over/i, '');
	}

	this.HighlightRow = function(row, on)
	{
		if(on)
			row.className += ' over';
		else
			row.className = row.className.replace(/\s*over/i, '');
	}

	this.SelectRow = function(checkbox)
	{
		var row = checkbox.parentNode.parentNode;
		var tbl = row.parentNode.parentNode;
		var span = document.getElementById(tbl.id+'_selected_span');
		var selCount = parseInt(span.innerHTML);

		if(checkbox.checked)
		{
			row.className += ' selected';
			selCount++;
		}
		else
		{
			row.className = row.className.replace(/\s*selected/ig, '');
			selCount--;
		}
		span.innerHTML = selCount;

		var checkAll = document.getElementById(tbl.id+'_check_all');
		if(selCount == tbl.rows.length-2)
			checkAll.checked = true;
		else
			checkAll.checked = false;
	}

	this.OnClickRow = function(e)
	{
		if(!e)
			var e = window.event;
		if(!e.ctrlKey)
			return;
		var obj = (e.target? e.target : (e.srcElement? e.srcElement : null));
		if(!obj)
			return;
		if(!obj.parentNode.cells)
			return;
		var checkbox = obj.parentNode.cells[0].childNodes[0];
		if(checkbox && checkbox.tagName && checkbox.tagName.toUpperCase() == "INPUT" && checkbox.type.toUpperCase() == "CHECKBOX" && !checkbox.disabled)
		{
			checkbox.checked = !checkbox.checked;
			_this.SelectRow(checkbox);
		}
		_this.EnableActions();
	}

	this.SelectAllRows = function(checkbox)
	{
		var tbl = checkbox.parentNode.parentNode.parentNode.parentNode;
		var bChecked = checkbox.checked;
		var i;
		var n = tbl.rows.length;
		for(i=2; i<n; i++)
		{
			var box = tbl.rows[i].cells[0].childNodes[0];
			if(box && box.tagName && box.tagName.toUpperCase() == 'INPUT' && box.type.toUpperCase() == "CHECKBOX")
			{
				if(box.checked != bChecked && !box.disabled)
				{
					box.checked = bChecked;
					this.SelectRow(box);
				}
			}
		}
		this.EnableActions();
	}

	this.EnableActions = function()
	{
		var form = document.forms['form_'+this.table_id];
		if(!form) return;

		var bEnabled = this.IsActionEnabled();
		var bEnabledEdit = this.IsActionEnabled('edit');

		if(form.apply) form.apply.disabled = !bEnabled;
		var b = document.getElementById('action_edit_button');
		if(b) b.className = 'context-button icon action-edit-button'+(bEnabledEdit? '':'-dis');
		b = document.getElementById('action_delete_button');
		if(b) b.className = 'context-button icon action-delete-button'+(bEnabled? '':'-dis');
	}

	this.IsActionEnabled = function(action)
	{
		var form = document.forms['form_'+this.table_id];
		if(!form) return;

		var bChecked = false;
		var span = document.getElementById(this.table_id+'_selected_span');
		if(span && parseInt(span.innerHTML)>0)
			bChecked = true;

		if(action == 'edit')
			return !(form.action_target && form.action_target.checked) && bChecked;
		else
			return (form.action_target && form.action_target.checked) || bChecked;
	}

	this.SetActiveResult = function(callback, url)
	{
		CHttpRequest.Action = function(result)
		{
			CloseWaitWindow();
			_this.Destroy(false);
			document.getElementById(_this.table_id+"_result_div").innerHTML = result;
			_this.InitTable();
			jsAdminChain.AddItems(_this.table_id+"_navchain_div");
			if(callback)
				callback(url);
		}
	}

	this.GetAdminList = function(url, callback)
	{
		ShowWaitWindow();

		var re = new RegExp('&mode=list&table_id='+escape(_this.table_id), 'g');
		url = url.replace(re, '');

		var link = document.getElementById('navchain-link');
		if(link)
			link.href = url;

		if(url.indexOf('?')>=0)
			url += '&mode=list&table_id='+escape(_this.table_id);
		else
			url += '?&mode=list&table_id='+escape(_this.table_id);

		_this.SetActiveResult(callback, url);
		CHttpRequest.Send(url);
	}

	this.Sort = function(url, bCheckCtrl, args)
	{
		if(bCheckCtrl == true)
		{
			var e = null, bControl = false;
			if(args.length > 0)
				e = args[0];
			if(!e)
				e = window.event;
			if(e)
				bControl = e.ctrlKey;
			url += (bControl? 'desc':'asc');
		}
		this.GetAdminList(url);
	}

	this.PostAdminList = function(url)
	{
		if(url.indexOf('?')>=0)
			url += '&mode=frame&table_id='+escape(this.table_id);
		else
			url += '?mode=frame&table_id='+escape(this.table_id);

		var frm = document.getElementById('form_'+this.table_id);

		try{frm.action.act.parentNode.removeChild(frm.action);}catch(e){}

		frm.action = url;
		frm.onsubmit();
		frm.submit();
	}

	this.ShowSettings = function(url)
	{
		if(document.getElementById("settings_float_div"))
			return;

		CHttpRequest.Action = function(result)
		{
			CloseWaitWindow();

			if(result == '')
				return;

			var div = document.body.appendChild(document.createElement("DIV"));
			div.id = "settings_float_div";
			div.className = "settings-float-form";
			div.style.position = 'absolute';
			div.style.zIndex = 1000;
			div.innerHTML = result;

			var left = parseInt(document.body.scrollLeft + document.body.clientWidth/2 - div.offsetWidth/2);
			var top = parseInt(document.body.scrollTop + document.body.clientHeight/2 - div.offsetHeight/2);
			jsFloatDiv.Show(div, left, top);

			jsUtils.addEvent(document, "keypress", _this.SettingsOnKeyPress);
		}
		ShowWaitWindow();
		CHttpRequest.Send(url);
	}

	this.CloseSettings =  function()
	{
		jsUtils.removeEvent(document, "keypress", _this.SettingsOnKeyPress);
		var div = document.getElementById("settings_float_div");
		jsFloatDiv.Close(div);
		div.parentNode.removeChild(div);
	}

	this.SettingsOnKeyPress = function(e)
	{
		if(!e) e = window.event
		if(!e) return;
		if(e.keyCode == 27)
			_this.CloseSettings();
	}

	this.SaveSettings =  function()
	{
		ShowWaitWindow();

		var sCols='', sBy='', sOrder='', sPageSize='';

		var oSelect = document.list_settings.selected_columns;
		var n = oSelect.length;
		for(var i=0; i<n; i++)
			sCols += (sCols != ''? ',':'')+oSelect[i].value;

		oSelect = document.list_settings.order_field;
		if(oSelect)
			sBy = oSelect[oSelect.selectedIndex].value;

		oSelect = document.list_settings.order_direction;
		if(oSelect)
			sOrder = oSelect[oSelect.selectedIndex].value;

		oSelect = document.list_settings.nav_page_size;
		sPageSize = oSelect[oSelect.selectedIndex].value;

		var bCommon = (document.list_settings.set_default && document.list_settings.set_default.checked);

		jsUserOptions.SaveOption('list', this.table_id, 'columns', sCols, bCommon);
		jsUserOptions.SaveOption('list', this.table_id, 'by', sBy, bCommon);
		jsUserOptions.SaveOption('list', this.table_id, 'order', sOrder, bCommon);
		jsUserOptions.SaveOption('list', this.table_id, 'page_size', sPageSize, bCommon);

		var url = window.location.href;
		jsUserOptions.SendData(function(){_this.GetAdminList(url, _this.CloseSettings);});
	}

	this.DeleteSettings = function(bCommon)
	{
		ShowWaitWindow();
		var url = window.location.href;
		jsUserOptions.DeleteOption('list', this.table_id, bCommon, function(){_this.GetAdminList(url, _this.CloseSettings);});
	}
}

/************************************************/

function TabControl(name, unique_name, aTabs)
{
	var _this = this;
	this.name = name;
	this.unique_name = unique_name;
	this.aTabs = aTabs;
	this.aTabsDisabled = {};
	this.bExpandTabs = false;

	this.AUTOSAVE = null;

	var auto_lnk = BX(this.name + '_autosave_link');
	if (auto_lnk)
	{
		auto_lnk.title = BX.message('AUTOSAVE_T');
		BX.addCustomEvent('onAutoSavePrepare', function (ob, h) {
			BX.bind(auto_lnk, 'click', BX.proxy(ob.Save, ob));
		});
		BX.addCustomEvent('onAutoSave', function() {
			auto_lnk.className = 'context-button bx-core-autosave bx-core-autosave-saving';
		});
		BX.addCustomEvent('onAutoSaveFinished', function(ob, t) {
			t = parseInt(t);
			if (!isNaN(t))
			{
				setTimeout(function() {
					auto_lnk.className = 'context-button bx-core-autosave bx-core-autosave-ready';
				}, 1000);
				auto_lnk.title = BX.message('AUTOSAVE_L').replace('#DATE#', BX.formatDate(new Date(t * 1000)));
			}
		});
		BX.addCustomEvent('onAutoSaveInit', function() {
			auto_lnk.className = 'context-button bx-core-autosave bx-core-autosave-edited';
		});
	}


	this.NextTab = function()
	{
		var SelectedTab = BX.findChild(document, {'className': 'tab-selected'}, true );
		//let's cut "tab_" and take tab name or tab_cont_
		if(SelectedTab)
			var CurrentTab=SelectedTab.id.substr(4);
		else
		{
			var SelectedTab = BX.findChild(document, {'className': 'tab-container-selected'}, true );
			var CurrentTab=SelectedTab.id.substr(9);
		}

		var NextTab="";

		for(var i=0; i<this.aTabs.length; i++)
			{
				if(CurrentTab==this.aTabs[i]["DIV"])
				{
					if(i>=(this.aTabs.length-1))
						NextTab=this.aTabs[0];
					else
						NextTab=this.aTabs[i+1];
				}
			}

		if(NextTab["DIV"])
			this.SelectTab(NextTab["DIV"]);
	}


	this.SelectTab = function(tab_id)
	{
		var div = document.getElementById(tab_id);
		if(div.style.display != 'none')
			return;

		for (var i = 0, cnt = this.aTabs.length; i < cnt; i++)
		{
			var tab = document.getElementById(this.aTabs[i]["DIV"])
			if(tab.style.display != 'none')
			{
				this.ShowTab(this.aTabs[i]["DIV"], false);
				tab.style.display = 'none';
				break;
			}
		}

		this.ShowTab(tab_id, true);
		div.style.display = 'block';

		document.getElementById(this.name+'_active_tab').value = tab_id;

		for (var i = 0, cnt = this.aTabs.length; i < cnt; i++)
			if(this.aTabs[i]["DIV"] == tab_id)
			{
				this.aTabs[i]["_ACTIVE"] = true;
				if(this.aTabs[i]["ONSELECT"])
					eval(this.aTabs[i]["ONSELECT"]);
				break;
			}
	}

	this.ShowTab = function(tab_id, on)
	{
		var sel = (on? '-selected':'');
		try{
		document.getElementById('tab_cont_'+tab_id).className = 'tab-container'+sel;
		document.getElementById('tab_left_'+tab_id).className = 'tab-left'+sel;
		document.getElementById('tab_'+tab_id).className = 'tab'+sel;
		if(tab_id != this.aTabs[this.aTabs.length-1]["DIV"])
			document.getElementById('tab_right_'+tab_id).className = 'tab-right'+sel;
		else
			document.getElementById('tab_right_'+tab_id).className = 'tab-right-last'+sel;
		}catch(e){}
	}

	this.HoverTab = function(tab_id, on)
	{
		var tab = document.getElementById('tab_'+tab_id);
		if(tab.className == 'tab-selected')
			return;

		document.getElementById('tab_left_'+tab_id).className = (on? 'tab-left-hover':'tab-left');
		tab.className = (on? 'tab-hover':'tab');
		var tab_right = document.getElementById('tab_right_'+tab_id);
		if(tab_id != this.aTabs[this.aTabs.length-1]["DIV"])
			tab_right.className = (on? 'tab-right-hover':'tab-right');
		else
			tab_right.className = (on? 'tab-right-last-hover':'tab-right-last');
	}

	this.InitEditTables = function()
	{
		for(var tab = 0, cnt = this.aTabs.length; tab < cnt; tab++)
		{
			var div = document.getElementById(this.aTabs[tab]["DIV"]);
			var tbl = jsUtils.FindChildObject(div.firstChild, 'table', 'edit-table');
			if(!tbl)
			{
				var tbl = jsUtils.FindChildObject(div, 'table', 'edit-table');
				if (!tbl)
					continue;
			}

			var n = tbl.rows.length;
			for(var i=0; i<n; i++)
				if(tbl.rows[i].cells.length > 1)
					tbl.rows[i].cells[0].className = 'field-name';
		}
	}

	this.DisableTab = function(tab_id)
	{
		this.aTabsDisabled[tab_id] = true;
		this.ShowDisabledTab(tab_id, true);
		if(this.bExpandTabs)
		{
			var div = document.getElementById(tab_id);
			div.style.display = 'none';
		}
	}

	this.EnableTab = function(tab_id)
	{
		this.aTabsDisabled[tab_id] = false;
		this.ShowDisabledTab(tab_id, this.bExpandTabs);
		if(this.bExpandTabs)
		{
			var div = document.getElementById(tab_id);
			div.style.display = 'block';
		}
	}

	this.ShowDisabledTab = function(tab_id, disabled)
	{
		var tab = document.getElementById('tab_cont_'+tab_id);
		if(disabled)
		{
			tab.className = 'tab-container-disabled';
			tab.onclick = null;
			tab.onmouseover = null;
			tab.onmouseout = null;
		}
		else
		{
			tab.className = 'tab-container';
			tab.onclick = function(){_this.SelectTab(tab_id);};
			tab.onmouseover = function(){_this.HoverTab(tab_id, true);};
			tab.onmouseout = function(){_this.HoverTab(tab_id, false);};
		}
	}

	this.Destroy = function()
	{
		//for(var i in this.aTabs)
		for(var i = 0, cnt = this.aTabs.length; i < cnt; i++)
		{
			var tab = document.getElementById('tab_cont_'+this.aTabs[i]["DIV"]);
			if (!tab)
				continue;
			tab.onclick = null;
			tab.onmouseover = null;
			tab.onmouseout = null;
		}
		_this = null;
	}

	this.ToggleTabs = function()
	{
		this.bExpandTabs = !this.bExpandTabs;

		var a = document.getElementById(this.name+'_expand_link');
		a.title = (this.bExpandTabs? phpVars.messCollapseTabs : phpVars.messExpandTabs);
		a.className = (this.bExpandTabs? a.className.replace(/\s*down/ig, ' up') : a.className.replace(/\s*up/ig, ' down'));

		for(var i in this.aTabs)
		{
			var tab_id = this.aTabs[i]["DIV"];
			this.ShowTab(tab_id, false);
			this.ShowDisabledTab(tab_id, (this.bExpandTabs || this.aTabsDisabled[tab_id]));
			var div = document.getElementById(tab_id);
			div.style.display = (this.bExpandTabs && !this.aTabsDisabled[tab_id]? 'block':'none');
			if(i > 0)
			{
				var tbl = jsUtils.FindChildObject(div.firstChild, 'table', 'edit-tab-title');
				if(this.bExpandTabs)
				{
					try{
						tbl.rows[0].style.display = 'table-row';
					}
					catch(e){
						tbl.rows[0].style.display = 'block';
					}
				}
				else
					tbl.rows[0].style.display = 'none';
			}
		}
		if(!this.bExpandTabs)
		{
			this.ShowTab(this.aTabs[0]["DIV"], true);
			var div = document.getElementById(this.aTabs[0]["DIV"]);
			div.style.display = 'block';
		}
		jsUserOptions.SaveOption('edit', this.unique_name, 'expand', (this.bExpandTabs? 'on': 'off'));

		jsUtils.onCustomEvent('OnToggleTabs');
	}

	this.ShowWarnings = function(form_name, warnings)
	{
		var form = document.forms[form_name];
		if(!form)
			return;
		for(var i in warnings)
		{
			var e = form.elements[warnings[i]['name']];
			if(!e)
				continue;

			var type = (e.type? e.type.toLowerCase():'');
			var bBefore = false;
			if(e.length > 1 && type != 'select-one' && type != 'select-multiple')
			{
				e = e[0];
				bBefore = true;
			}
			if(type == 'textarea' || type == 'select-multiple')
				bBefore = true;

			var td = e.parentNode;
			var img;
			if(bBefore)
			{
				img = td.insertBefore(new Image(), e);
				td.insertBefore(document.createElement("BR"), e);
			}
			else
			{
				img = td.insertBefore(new Image(), e.nextSibling);
				img.hspace = 2;
				img.vspace = 2;
				img.style.verticalAlign = 'bottom';
			}
			img.src = '/bitrix/themes/'+phpVars.ADMIN_THEME_ID+'/images/icon_warn.gif';
			img.title = warnings[i]['title'];
		}
	}

	this.ShowSettings = function(url)
	{
		if(document.getElementById("settings_float_div"))
			return;

		CHttpRequest.Action = function(result)
		{
			CloseWaitWindow();

			if(result == '')
				return;

			var div = document.body.appendChild(document.createElement("DIV"));
			div.id = "settings_float_div";
			div.className = "settings-float-form";
			div.style.position = 'absolute';
			div.style.zIndex = 1000;
			div.innerHTML = result;

			var left = parseInt(document.body.scrollLeft + document.body.clientWidth/2 - div.offsetWidth/2);
			var top = parseInt(document.body.scrollTop + document.body.clientHeight/2 - div.offsetHeight/2);
			jsFloatDiv.Show(div, left, top);

			jsUtils.addEvent(document, "keypress", _this.SettingsOnKeyPress);
		}
		ShowWaitWindow();
		CHttpRequest.Send(url);
	}

	this.CloseSettings =  function()
	{
		jsUtils.removeEvent(document, "keypress", _this.SettingsOnKeyPress);
		var div = document.getElementById("settings_float_div");
		jsFloatDiv.Close(div);
		div.parentNode.removeChild(div);
	}

	this.SettingsOnKeyPress = function(e)
	{
		if(!e) e = window.event
		if(!e) return;
		if(e.keyCode == 27)
			_this.CloseSettings();
	}

	this.SaveSettings =  function()
	{
		ShowWaitWindow();

		var sTabs='', s='';

		var oFieldsSelect;
		var oSelect = document.getElementById('selected_tabs');
		if(oSelect)
		{
			var k = oSelect.length;
			for(var i=0; i<k; i++)
			{
				s = oSelect[i].value + '--#--' + oSelect[i].text;
				oFieldsSelect = document.getElementById('selected_fields[' + oSelect[i].value + ']');
				if(oFieldsSelect)
				{
					var n = oFieldsSelect.length;
					for(var j=0; j<n; j++)
					{
						s += '--,--' + oFieldsSelect[j].value + '--#--' + jsUtils.trim(oFieldsSelect[j].text);
					}
				}
				sTabs += s + '--;--';
			}
		}

		var bCommon = (document.form_settings.set_default && document.form_settings.set_default.checked);

		var request = new JCHttpRequest;
		request.Action = function () {BX.reload()};

		var sParam = '';
		sParam += '&p[0][c]=form';
		sParam += '&p[0][n]='+encodeURIComponent(this.name);
		if(bCommon)
			sParam += '&p[0][d]=Y';
		sParam += '&p[0][v][tabs]=' + encodeURIComponent(sTabs);

		var options_url = '/bitrix/admin/user_options.php?lang='+phpVars.LANGUAGE_ID+'&sessid='+phpVars.bitrix_sessid;
		options_url += '&action=delete&c=form&n='+this.name+'_disabled';

		request.Post(options_url, sParam);
	}

	this.DeleteSettings = function(bCommon)
	{
		ShowWaitWindow();
		jsUserOptions.DeleteOption('form', this.name, bCommon, function () {BX.reload()});
	}

	this.DisableSettings = function()
	{
		var request = new JCHttpRequest;
		request.Action = function () {BX.reload()};
		var sParam = '';
		sParam += '&p[0][c]=form';
		sParam += '&p[0][n]='+encodeURIComponent(this.name+'_disabled');
		sParam += '&p[0][v][disabled]=Y';
		request.Send('/bitrix/admin/user_options.php?lang=' + phpVars.LANGUAGE_ID + sParam + '&sessid='+phpVars.bitrix_sessid);
	}

	this.EnableSettings = function()
	{
		var request = new JCHttpRequest;
		request.Action = function () {BX.reload()};
		var sParam = '';
		sParam += '&c=form';
		sParam += '&n='+encodeURIComponent(this.name)+'_disabled';
		sParam += '&action=delete';
		request.Send('/bitrix/admin/user_options.php?lang=' + phpVars.LANGUAGE_ID + sParam + '&sessid='+phpVars.bitrix_sessid);
	}
}

/************************************************/

function ViewTabControl(aTabs)
{
	var _this = this;
	this.aTabs = aTabs;

	this.SelectTab = function(tab_id)
	{
		var div = document.getElementById(tab_id);
		if(div.style.display != 'none')
			return;

		for(var i in this.aTabs)
		{
			var tab_div = document.getElementById(this.aTabs[i]["DIV"]);
			if(tab_div.style.display != 'none')
			{
				var tab = document.getElementById('view_tab_'+this.aTabs[i]["DIV"]);
				tab.innerHTML = this.aTabs[i]["HTML"];
				tab.className = 'view-tab';
				this.ToggleDelimiter(tab, true);
				tab_div.style.display = 'none';
				break;
			}
		}

		var active_tab = document.getElementById('view_tab_'+tab_id);
		active_tab.className = 'view-tab view-tab-active';
		this.ToggleDelimiter(active_tab, false);
		div.style.display = 'block';

		this.RebuildTabs();

		for(var i in this.aTabs)
		{
			if(this.aTabs[i]["DIV"] == tab_id)
			{
				this.ReplaceAnchor(this.aTabs[i]);
				if(this.aTabs[i]["ONSELECT"])
					eval(this.aTabs[i]["ONSELECT"]);
				break;
			}
		}
	}

	this.ToggleDelimiter = function(tab, on)
	{
		var d;
		if((d = jsUtils.FindNextSibling(tab, 'div')) && d.className.indexOf('view-tab-delimiter') != -1)
			d.className = 'view-tab-delimiter'+(on? '':' view-tab-hide-delimiter');
		if((d = jsUtils.FindPreviousSibling(tab, 'div')) && d.className.indexOf('view-tab-delimiter') != -1)
			d.className = 'view-tab-delimiter'+(on? '':' view-tab-hide-delimiter');
	}

	this.DisableTab = function(tab_id)
	{
	}

	this.EnableTab = function(tab_id)
	{
	}

	this.ReplaceAnchor = function(tab)
	{
		var tab_div = document.getElementById('view_tab_'+tab["DIV"]);
		tab["HTML"] = tab_div.innerHTML;
		var a = jsUtils.FindChildObject(tab_div, "a");
		tab_div.innerHTML = a.innerHTML;
	}

	this.RebuildTabs = function()
	{
		var container = jsUtils.FindParentObject(document.getElementById('view_tab_'+_this.aTabs[0]["DIV"]), "div");
		var aPos = [0];
		var selectedIndex = -1;
		var prevTop = -1;
		var last;
		var n = container.childNodes.length;
		for(var i=0; i<n; i++)
		{
			var div = container.childNodes[i];
			if(!div.id)
				continue;

			if(prevTop > -1 && div.offsetTop > prevTop)
				aPos[aPos.length] = i;
			prevTop = div.offsetTop;

			if(selectedIndex == -1 && div.className.indexOf('view-tab-active') != -1)
				selectedIndex = aPos.length-1;
			last = div;
		}

		if(selectedIndex < aPos.length && selectedIndex > -1)
		{
			var aDiv = new Array();
			var div = container.childNodes[aPos[selectedIndex]];
			for(var i = aPos[selectedIndex]; i<aPos[selectedIndex+1]; i++)
			{
				aDiv[aDiv.length] = div;
				div = div.nextSibling;
			}
			if(aDiv.length > 0)
			{
				for(var i in aDiv)
					container.removeChild(aDiv[i]);

				while(last.nextSibling)
				{
					last = last.nextSibling;
					if(last.tagName && last.tagName.toUpperCase() == 'BR' && last.className && last.className == 'tab-break')
						break;
				}

				var br = document.createElement("BR");
				br.style.clear='both';
				container.insertBefore(br, last);

				for(var i in aDiv)
				{
					if(aDiv[i].tagName && aDiv[i].tagName.toUpperCase() == 'BR')
						continue;
					container.insertBefore(aDiv[i], last);
				}
			}
		}
	}

	this.Init = function()
	{
		if(this.aTabs.length == 0)
			return;
		for(var i in this.aTabs)
		{
			var div = document.getElementById(this.aTabs[i]["DIV"]);
			if(div.style.display != 'none')
			{
				this.ReplaceAnchor(this.aTabs[i]);
				this.ToggleDelimiter(document.getElementById('view_tab_'+this.aTabs[i]["DIV"]), false);
				break;
			}
		}
		setTimeout(this.RebuildTabs, 10);
		window.onresize = this.RebuildTabs;
	}

	this.Init();
}

/************************************************/

var jsAdminChain =
{
	_chain: '',

	AddItems: function(divId)
	{
		var main_chain = document.getElementById("main_navchain");
		if(!main_chain)
			return;

		if(this._chain == '')
			this._chain = main_chain.innerHTML;
		else
			main_chain.innerHTML = this._chain;

		var div = document.getElementById(divId);
		if(!div)
			return;

		main_chain.innerHTML += '<span class="adm-navchain-delimiter"></span>';
		main_chain.innerHTML += div.innerHTML;
	}
}

/************************************************/

function JCHttpRequest()
{
	this.Action = null; //function(result){}

	this._OnDataReady = function(result)
	{
		if(this.Action)
			this.Action(result);
	}

	this._CreateHttpObject = function()
	{
		var obj = null;
		if(window.XMLHttpRequest)
		{
			try {obj = new XMLHttpRequest();} catch(e){}
		}
        else if(window.ActiveXObject)
        {
            try {obj = new ActiveXObject("Microsoft.XMLHTTP");} catch(e){}
            if(!obj)
				try {obj = new ActiveXObject("Msxml2.XMLHTTP");} catch (e){}
        }
        return obj;
	}

	this._SetHandler = function(httpRequest)
	{
		var _this = this;
		httpRequest.onreadystatechange = function()
		{
			if(httpRequest.readyState == 4)
			{
//				try
				{
					var s = httpRequest.responseText;
					var code = [];
					var start, end;
					while((start = s.indexOf('<script>')) != -1)
					{
						var end = s.indexOf('</script>', start);
						if(end == -1)
							break;

						code[code.length] = s.substr(start+8, end-start-8);
						s = s.substr(0, start) + s.substr(end+9);
					}
					_this._OnDataReady(s);

					for(var i = 0, cnt = code.length; i < cnt; i++)
						if(code[i] != '')
							jsUtils.EvalGlobal(code[i]);
				}
/*
				catch (e)
				{
					var w = window.open("about:blank");
					w.document.write(httpRequest.responseText);
					w.document.close();
				}
*/
			}
		}
	}

	this.Send = function(url)
	{
		var httpRequest = this._CreateHttpObject();
		if(httpRequest)
		{
			httpRequest.open("GET", url, true);
			this._SetHandler(httpRequest);
			return httpRequest.send("");
		}
	}

	this.Post = function(url, data)
	{
		var httpRequest = this._CreateHttpObject();
		if(httpRequest)
		{
			httpRequest.open("POST", url, true);
			this._SetHandler(httpRequest);
			httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			return httpRequest.send(data);
		}
	}
}
var CHttpRequest = new JCHttpRequest();

/************************************************/

/***** DEPRECATED! Use BX.userOptions from core_ajax.js **********/
function JCUserOptions()
{
	var _this = this;
	this.options = null;
	this.bSend = false;
	this.request = new JCHttpRequest();

	this.GetParams = function()
	{
		if (BX && BX.userOptions)
		{
			_this.GetParams = BX.userOptions.__get;
			return _this.GetParams.apply(BX.userOptions, arguments);
		}

		var sParam = '';
		var n = -1;
		var prevParam = '';
		for(var i in _this.options)
		{
			var aOpt = _this.options[i];
			if(prevParam != aOpt[0]+'.'+aOpt[1])
			{
				n++;
				sParam += '&p['+n+'][c]='+encodeURIComponent(aOpt[0]);
				sParam += '&p['+n+'][n]='+encodeURIComponent(aOpt[1]);
				if(aOpt[4] == true)
					sParam += '&p['+n+'][d]=Y';
				prevParam = aOpt[0]+'.'+aOpt[1];
			}
			sParam += '&p['+n+'][v]['+encodeURIComponent(aOpt[2])+']='+encodeURIComponent(aOpt[3]);
		}

		return sParam.substr(1);
	}

	this.SaveOption = function(sCategory, sName, sValName, sVal, bCommon)
	{
		if (BX && BX.userOptions)
		{
			_this.SaveOption = BX.userOptions.save;
			return _this.SaveOption.apply(BX.userOptions, arguments);
		}

		if(!this.options)
			this.options = new Object();

		if(bCommon != true)
			bCommon = false;
		this.options[sCategory+'.'+sName+'.'+sValName] = [sCategory, sName, sValName, sVal, bCommon];

		var sParam = this.GetParams();
		if(sParam != '')
			document.cookie = phpVars.cookiePrefix+"_LAST_SETTINGS=" + sParam + "&sessid="+phpVars.bitrix_sessid+"; expires=Thu, 31 Dec 2020 23:59:59 GMT; path=/;";

		if(!this.bSend)
		{
			this.bSend = true;
			setTimeout(function(){_this.SendData(null)}, 5000);
		}
	}

	this.SendData = function(callback)
	{
		if (BX && BX.userOptions)
		{
			_this.SendData = BX.userOptions.send;
			return _this.SendData.apply(BX.userOptions, arguments);
		}

		var sParam = _this.GetParams();
		_this.options = null;
		_this.bSend = false;
		if(sParam != '')
		{
			document.cookie = phpVars.cookiePrefix+"_LAST_SETTINGS=; path=/;";
			_this.request.Action = callback;
			_this.request.Send('/bitrix/admin/user_options.php?'+sParam+'&sessid='+phpVars.bitrix_sessid);
		}
	}

	this.DeleteOption = function(sCategory, sName, bCommon, callback)
	{
		if (BX && BX.userOptions)
		{
			_this.DeleteOption = BX.userOptions.del;
			return _this.DeleteOption.apply(BX.userOptions, arguments);
		}

		_this.request.Action = callback;
		_this.request.Send('/bitrix/admin/user_options.php?action=delete&c='+sCategory+'&n='+sName+(bCommon == true? '&common=Y':'')+'&sessid='+phpVars.bitrix_sessid);
	}
}
var jsUserOptions = new JCUserOptions();

/************************************************/

function JCPanel()
{
	var _this = this;

	this.FixPanel = function()
	{
		var a = document.getElementById('admin_panel_fix_link');
		var panel = document.getElementById('bx_top_panel_container');
		var backDiv = document.getElementById('bx_top_panel_back');
		var bFixed = (panel.style.position == 'fixed' || panel.style.position == 'absolute');
		var bIE = jsUtils.IsIE();
		if(bIE)
		{
			try{panel.style.removeExpression("top");} catch(e) {bIE = false;}
		}
		if(bFixed)
		{
			a.title = phpVars.messPanelFixOn;
			a.className = 'fix-link fix-on';
			panel.style.position = '';
			backDiv.style.display = 'none';
			if(bIE)
			{
				panel.style.removeExpression("top");
				panel.style.removeExpression("left");
				panel.style.removeExpression("width");
				panel.style.width = '100%';

				var frame = document.getElementById("admin_panel_frame");
				if(frame)
					frame.style.visibility = 'hidden';
			}
		}
		else
		{
			this.ShowOn();
			if(bIE)
			{
				var frame = document.getElementById("admin_panel_frame");
				if(frame)
					frame.style.visibility = 'visible';
				else
					this.CreateFrame(panel);
			}
		}
		jsUserOptions.SaveOption('admin_panel', 'settings', 'fix', (bFixed? 'off':'on'));
	}

	this.ShowOn = function()
	{
		var a = document.getElementById('admin_panel_fix_link');
		var panel = document.getElementById('bx_top_panel_container');
		var backDiv = document.getElementById('bx_top_panel_back');
		var bIE = jsUtils.IsIE();
		if(bIE)
		{
			try{panel.style.setExpression("top", "0");} catch(e) {bIE = false;}
		}

		a.title = phpVars.messPanelFixOff;
		a.className = 'fix-link fix-off';
		panel.style.position = (bIE? 'absolute':'fixed');
		panel.style.left = '0px';
		panel.style.top = '0px';
		panel.style.zIndex = '1000';
		if(bIE)
		{
			if(document.body.currentStyle.backgroundImage == 'none')
			{
				document.body.style.backgroundImage = "url(/bitrix/images/1.gif)";
				document.body.style.backgroundAttachment = "fixed";
				document.body.style.backgroundRepeat = "no-repeat";
			}
			panel.style.setExpression("top", "eval((document.documentElement && document.documentElement.scrollTop) ? document.documentElement.scrollTop : document.body.scrollTop)");
			panel.style.setExpression("left", "eval((document.documentElement && document.documentElement.scrollLeft) ? document.documentElement.scrollLeft : document.body.scrollLeft)");
			panel.style.setExpression("width", "eval((document.documentElement && document.documentElement.clientWidth) ? document.documentElement.clientWidth : document.body.clientWidth)");
		}
		backDiv.style.height = panel.offsetHeight+'px';
		backDiv.style.display = 'block';
	}

	this.FixOn = function()
	{
		this.ShowOn();
		jsUtils.addEvent(window, "load", this.AdjustBackDiv);
	}

	this.AdjustBackDiv = function()
	{
		var panel = document.getElementById('bx_top_panel_container');
		var backDiv = document.getElementById('bx_top_panel_back');

		var bIE = jsUtils.IsIE();
		if(bIE)
		{
			try{backDiv.style.setExpression("height", "0");} catch(e) {bIE = false;}
		}

		backDiv.style.height = panel.offsetHeight+'px';

		if(bIE)
			_this.CreateFrame(panel);
	}

	this.CreateFrame = function(panel)
	{
		var frame = document.createElement("IFRAME");
		frame.src = "javascript:void(0)";
		frame.id = "admin_panel_frame";
		frame.style.position = 'absolute';
		frame.style.overflow = 'hidden';
		frame.style.zIndex = parseInt(panel.currentStyle.zIndex)-1;
		frame.style.height = panel.offsetHeight + "px";
		document.body.appendChild(frame);
		frame.style.setExpression("top", "eval(document.body.scrollTop)");
		frame.style.setExpression("left", "eval(document.body.scrollLeft)");
		frame.style.setExpression("width", "eval(document.body.clientWidth)");
		return frame;
	}

	this.IsFixed = function()
	{
		var panel = document.getElementById('bx_top_panel_container');
		return (panel && (panel.style.position == 'fixed' || panel.style.position == 'absolute'));
	}

	this.DisplayPanel = function(el)
	{
		var div = document.getElementById('bx_top_panel_splitter');
		if(div.style.display == 'none')
		{
			div.style.display = 'block';
			el.className = 'splitterknob';
			el.title = phpVars.messPanelCollapse;
			jsUserOptions.SaveOption('admin_panel', 'settings', 'collapsed', 'off');
		}
		else
		{
			div.style.display = 'none';
			el.className = 'splitterknob splitterknobdown';
			el.title = phpVars.messPanelExpand;
			jsUserOptions.SaveOption('admin_panel', 'settings', 'collapsed', 'on');
		}
		var panel = document.getElementById('bx_top_panel_container');
		var backDiv = document.getElementById('bx_top_panel_back');
		backDiv.style.height = panel.offsetHeight+'px';
		var frame = document.getElementById("admin_panel_frame");
		if(frame)
			frame.style.height = panel.offsetHeight + "px";
	}
}
var jsPanel = new JCPanel();

//***************************************************

function JCDebugWindow()
{
	var _this = this;
	this.div_id = 'BX_DEBUG_WINDOW';
	this.div_current = null;
	this.div_detail_current = null;

	this.Show = function(info_id)
	{
		var div = document.getElementById(this.div_id);
		if(div)
		{
			div.style.display = 'block';
			var info_div = document.getElementById(info_id);
			if(info_div)
			{
				if(this.div_current)
					this.div_current.style.display = 'none';

				info_div.style.display = 'block';
				this.div_current = info_div;

				this.ShowDetails(info_id+'_1');
			}

			//var left = parseInt(document.body.scrollLeft + document.body.clientWidth/2 - div.offsetWidth/2);
			//var top = parseInt(document.body.scrollTop + document.body.clientHeight/2 - div.offsetHeight/2);

			var windowSize = jsUtils.GetWindowSize();

			var left = parseInt(windowSize["scrollLeft"] + windowSize["innerWidth"]/2 - div.offsetWidth/2);
			var top = parseInt(windowSize["scrollTop"] + windowSize["innerHeight"]/2 - div.offsetHeight/2);

			jsFloatDiv.Show(div, left, top);
			jsUtils.addEvent(document, "keypress", this.OnKeyPress);
		}
	}

	this.Close = function()
	{
		jsUtils.removeEvent(document, "keypress", this.OnKeyPress);
		var div = document.getElementById(this.div_id);
		jsFloatDiv.Close(div);
		div.style.display = 'none';
	}

	this.OnKeyPress = function(e)
	{
		if(!e) e = window.event
		if(!e) return;
		if(e.keyCode == 27)
			_this.Close();
	}

	this.ShowDetails = function(div_id)
	{
		var div = document.getElementById(div_id);
		if(div)
		{
			if(this.div_detail_current)
				this.div_detail_current.style.display = 'none';

			div.style.display = 'block';
			this.div_detail_current = div;
		}
	}
}
var jsDebugWindow = new JCDebugWindow();

//***************************************************

function ImgShw(ID, width, height, alt)
{
	var scroll = "no";
	var top=0, left=0;
	if(width > screen.width-10 || height > screen.height-28) scroll = "yes";
	if(height < screen.height-28) top = Math.floor((screen.height - height)/2-14);
	if(width < screen.width-10) left = Math.floor((screen.width - width)/2-5);
	width = Math.min(width, screen.width-10);
	height = Math.min(height, screen.height-28);
	var wnd = window.open("","","scrollbars="+scroll+",resizable=yes,width="+width+",height="+height+",left="+left+",top="+top);
	wnd.document.write(
		"<html><head>"+
		"<"+"script type=\"text/javascript\">"+
		"function KeyPress()"+
		"{"+
		"	if(window.event.keyCode == 27) "+
		"		window.close();"+
		"}"+
		"</"+"script>"+
		"<title></title></head>"+
		"<body topmargin=\"0\" leftmargin=\"0\" marginwidth=\"0\" marginheight=\"0\" onKeyPress=\"KeyPress()\">"+
		"<img src=\""+ID+"\" border=\"0\" alt=\""+alt+"\" />"+
		"</body></html>"
	);
	wnd.document.close();
}


var WizardWindow = {

	iframe : null,
	messLoading : phpVars.messLoading,
	currentDialog : null,
	currentFrame : null,
	isClosed : false,
	frameLoaded : false,
	//dialogs : {},

	Open : function(wizardName, sessid)
	{
		/*if (this.dialogs[wizardName])
		{
			this.currentDialog = this.dialogs[wizardName].dialog;
			this.currentFrame = this.dialogs[wizardName].frame;
			this.currentDialog.Show();
			return;
		}*/

		this.currentDialog = new BX.CWizardDialog({
			'width':'700',
			'height':'400',
			resizable: false
		});
		this.isClosed = false;
		this.frameLoaded = false;

		BX.addCustomEvent(this.currentDialog, "onBeforeWindowClose", BX.proxy(this.onBeforeWindowClose, this));

		var iframeID = Math.random();
		this.currentDialog.SetContent('<iframe class="content" style="background-color: transparent; height:400px;" allowtransparency="true" scrolling="no" id="wizard_iframe_' + iframeID + '" width="100%" src="/bitrix/admin/wizard_install.php?lang='+phpVars.LANGUAGE_ID+'&wizardName='+wizardName+'&bxsender=admin_wizard_dialog&sessid='+sessid+'" frameborder="0"></iframe>');
		this.currentDialog.Show();

		setTimeout(BX.proxy(function() { if (!this.frameLoaded) {this.ShowWaitWindow();} }, this), 400);
		this.currentFrame = BX("wizard_iframe_" + iframeID);
		BX.bind(this.currentFrame, "load", BX.proxy(this.OnFrameLoad, this));

		//this.dialogs[wizardName] = { dialog : this.currentDialog, frame : this.currentFrame };
	},

	OnFrameLoad : function()
	{
		this.frameLoaded = true;
		this.HideWaitWindow();

		var iframeWindow = this.currentFrame.contentWindow;

		var iframeDocument = null;
		if (this.currentFrame.contentDocument)
			iframeDocument = this.currentFrame.contentDocument;
		else
			iframeDocument = this.currentFrame.contentWindow.document;

		if(iframeWindow.focus)
			iframeWindow.focus();
		else
			iframeDocument.body.focus();
	},

	Close : function()
	{
		if (this.currentDialog)
		{
			this.isClosed = true;
			this.currentDialog.Close(true);
		}
	},

	ShowWaitWindow : function()
	{
		if (this.currentDialog && BX.type.isDomNode(this.currentDialog.PARTS.CONTENT))
		{
			var waiter = document.createElement("DIV");
			waiter.id = "__bx_wait_window";
			waiter.className = "";
			waiter.style.position = "absolute";
			waiter.style.left = "40%";
			waiter.style.top = "40%";
			waiter.style.zIndex = "3000";
			waiter.style.padding = "15px 10px 15px 35px";
			waiter.style.width = "auto";
			waiter.style.fontSize = "12px";
			waiter.style.borderRadius = "4px";
			waiter.style.boxShadow = "0 0 10px 1px #dfdfdf";
			waiter.style.border = "1px solid #DCE7ED";
			waiter.style.lineHeight = "9px";
			waiter.style.background = "#fff url(/bitrix/panel/main/images/waiter-white.gif) 3px center no-repeat";
			waiter.innerHTML = this.messLoading;

			this.currentDialog.PARTS.CONTENT.appendChild(waiter);
		}
	},

	HideWaitWindow : function()
	{
		var waiter = BX("__bx_wait_window");
		if (waiter && waiter.parentNode)
			waiter.parentNode.removeChild(waiter);
	},

	onBeforeWindowClose : function(dialog)
	{
		if (this.isClosed === false)
		{
			dialog.denyClose = !confirm(BX.message("ADMIN_WIZARD_EXIT_ALERT"));
		}
	}
};

//************************************************************

function JCStartMenu()
{
	var menuStart = null;
	var request = new JCHttpRequest();
	var _this = this;

	this.EvalMenu = function(result)
	{
		if(jsUtils.trim(result).length == 0)
			return;

		var menuItems;
        try
        {
			eval(result); // menuItems={'styles':[], 'items':[]}
        }
        catch(e)
        {
        }

		if(!menuItems)
			return false;

		//Applying styles
		var head = document.getElementsByTagName("HEAD");
		if(head && head[0])
		{
			var style = document.createElement("STYLE");
			head[0].appendChild(style);
			if(jsUtils.IsIE())
				document.styleSheets[document.styleSheets.length-1].cssText = menuItems['styles'];
			else
				style.appendChild(document.createTextNode(menuItems['styles']));
		}
		return menuItems;
	}

	this.ShowStartMenu = function(button, back_url)
	{
		var dPos = {'left':0, 'top':0, 'right':0, 'bottom':0};
		if(!menuStart || !menuStart.menuItems)
		{
			request.Action = function(result)
			{
				var menuItems = _this.EvalMenu(result);
				if(menuItems)
				{
					//show menu
					menuStart.PopupHide();
					menuStart.ShowMenu(button, menuItems['items'], jsPanel.IsFixed(), dPos);
				}
			}
			//create menu
			menuStart = new PopupMenu('panel_start_menu');
			menuStart.Create(1100);
			menuStart.ShowMenu(button, [{
				'TEXT':phpVars.messMenuLoading,
				'TITLE':phpVars.messMenuLoadingTitle,
				'ICONCLASS':'loading',
				'AUTOHIDE':false}], jsPanel.IsFixed(), dPos);
			request.Send('/bitrix/admin/get_start_menu.php?lang='+phpVars.LANGUAGE_ID+(back_url? '&back_url_pub='+encodeURIComponent(back_url):'')+'&sessid='+phpVars.bitrix_sessid);
		}
		else
		{
			menuStart.ShowMenu(button, null, jsPanel.IsFixed(), dPos);
		}
	}

	this.PreloadMenu = function(back_url)
	{
		if(!menuStart)
		{
			request.Action = function(result)
			{
				var menuItems = _this.EvalMenu(result);
				if(menuItems)
				{
					//show menu
					menuStart.SetItems(menuItems['items']);
					menuStart.BuildItems();
				}
			}
			//create menu
			menuStart = new PopupMenu('panel_start_menu');
			menuStart.Create(1100);
			request.Send('/bitrix/admin/get_start_menu.php?lang='+phpVars.LANGUAGE_ID+(back_url? '&back_url_pub='+encodeURIComponent(back_url):'')+'&sessid='+phpVars.bitrix_sessid);
		}
	}

	this.OpenDynMenu = function(menu, module_id, items_id, back_url)
	{
		request.Action = function(result)
		{
			if(jsUtils.trim(result).length == 0)
				return;

			var menuItems;
			eval(result); // menuItems={'items':[]}

			if(menu && menuItems)
			{
				var bVisible = menu.IsVisible();
				menu.PopupHide();
				menu.SetItems(menuItems['items']);
				menu.BuildItems();
				menu.parentMenu.ShowSubmenu(menu.parentItem, false, !bVisible);
			}
		}
		request.Send('/bitrix/admin/get_start_menu.php?mode=dynamic&lang='+phpVars.LANGUAGE_ID+'&admin_mnu_module_id='+encodeURIComponent(module_id)+'&admin_mnu_menu_id='+encodeURIComponent(items_id)+(back_url? '&back_url_pub='+encodeURIComponent(back_url):'')+'&sessid='+phpVars.bitrix_sessid);
	}

	this.OpenURL = function(item, arguments, url, back_url)
	{
		var itemInfo = menuStart.GetItemInfo(item);
		if(itemInfo)
		{
			request.Action = function(result){}
			request.Send('/bitrix/admin/get_start_menu.php?mode=save_recent&url='+encodeURIComponent(url)+'&text='+encodeURIComponent(itemInfo['TEXT'])+'&title='+encodeURIComponent(itemInfo['TITLE'])+'&icon='+itemInfo['ICON']+'&sessid='+phpVars.bitrix_sessid);
		}
		if(back_url)
			url += (url.indexOf('?')>=0? '&':'?')+'back_url_pub='+encodeURIComponent(back_url);
		jsUtils.Redirect(arguments, url);
	}
}
var jsStartMenu = new JCStartMenu();

//************************************************************
//Admin edit form functions

function OnAdd(id, params)
{
	var tabPrefix = 'cedit';
	if (!!params && typeof params === 'object')
	{
		if (!!params.tabPrefix)
			tabPrefix = params.tabPrefix;
	}
	var frm=document.form_settings;
	if(id == 'tabs_add')
	{
		var oSelect = document.getElementById('selected_tabs');
		if(oSelect)
		{
			var name = prompt(arFormEditMess.admin_lib_sett_tab_prompt, arFormEditMess.admin_lib_sett_tab_default_name);
			if(name && name.length > 0)
			{
				var n = oSelect.length;
				var c = 0;
				var found = true;
				while(found)
				{
					c++;
					found = false;
					for(var i=0; i<n; i++)
						if(oSelect[i].value == tabPrefix+c)
							found = true;
				}
				jsSelectUtils.addNewOption('selected_tabs', tabPrefix+c, name, false);
				var td = document.getElementById('selected_fields');
				var newSelect = document.createElement('SPAN');
				td.appendChild(newSelect);
				newSelect.innerHTML = '<select style="display:none" class="select" name="selected_fields['+tabPrefix + c + ']" id="selected_fields['+tabPrefix + c + ']" size="12" multiple onchange="Sync();"></select>';
				jsSelectUtils.selectOption('selected_tabs', tabPrefix+c);
			}
		}
	}
	if(id == 'tabs_copy')
	{
		var oSelectFrom = document.getElementById('available_tabs');
		var oSelectTo = document.getElementById('selected_tabs');
		if(oSelectFrom && oSelectTo)
		{
			var n = oSelectFrom.length;
			var k = oSelectTo.length;
			var c = 0;
			for(var i=0; i<n; i++)
				if(oSelectFrom[i].selected)
				{
					var found = false;
					for(var j=0; j<k; j++)
						if(oSelectTo[j].value == oSelectFrom[i].value)
							found = true;
					if(!found)
					{
						var td = document.getElementById('selected_fields');
						var newSelect = document.createElement('SPAN');
						var newID = 'selected_fields[' + oSelectFrom[i].value + ']';
						td.appendChild(newSelect);
						newSelect.innerHTML = '<select style="display:none" class="select" name="' + newID + '" id="' + newID + '" size="12" multiple onchange="Sync();"></select>';

						jsSelectUtils.addNewOption('selected_tabs', oSelectFrom[i].value, oSelectFrom[i].text, false);
						jsSelectUtils.selectAllOptions('available_fields');
						jsSelectUtils.addSelectedOptions(document.getElementById('available_fields'), newID);

						jsSelectUtils.selectOption('selected_tabs', oSelectFrom[i].value);

					}
				}
		}
	}
	if(id == 'fields_add')
	{
		var oSelect = document.getElementById('selected_tabs');
		var prefix = '';
		if(oSelect)
		{
			for(var i = 0; i < oSelect.length; i++)
				if(oSelect[i].selected)
					prefix = oSelect[i].value;
		}

		oSelect = GetFieldsActiveSelect();
		if(oSelect)
		{
			var name = prompt(arFormEditMess.admin_lib_sett_sec_prompt, arFormEditMess.admin_lib_sett_sec_default_name);
			if(name && name.length > 0)
			{
				var n = oSelect.length;
				var c = 0;
				var found = true;
				while(found)
				{
					c++;
					found = false;
					for(var i=0; i<n; i++)
						if(oSelect[i].value == prefix+'_csection'+c)
							found = true;
				}
				jsSelectUtils.addNewOption(oSelect.id, prefix+'_csection'+c, '--'+name, false);
				jsSelectUtils.selectOption(oSelect.id, prefix+'_csection'+c);
			}
		}
	}
	if(id == 'fields_copy')
	{
		var oSelectFrom = document.getElementById('available_fields');
		var oSelectTo = GetFieldsActiveSelect();
		if(oSelectFrom && oSelectTo && !oSelectTo.disabled)
		{
			//find last selected item in selected_fields
			var i, last = oSelectTo.length - 1;
			for(i = 0; i < oSelectTo.length; i++)
			{
				if(oSelectTo[i].selected)
					last = i;
			}
			//Delete all after last selected
			var tail = new Array;
			for(i = oSelectTo.length - 1; i > last; i--)
			{
				var newoption = new Option(oSelectTo[i].text, oSelectTo[i].value, false, false);
				newoption.innerHTML = oSelectTo[i].innerHTML;
				tail[tail.length] = newoption;
				oSelectTo.remove(i);
			}
			//Deselect all selected_fields
			for(i = 0; i < oSelectTo.length; i++)
				if(oSelectTo[i].selected)
					oSelectTo[i].selected = false;
			//Add new options
			var sel_count = 0, sel_value = '';
			for(i = 0; i < oSelectFrom.length; i++)
			{
				if(oSelectFrom[i].selected)
				{
					jsSelectUtils.addNewOption(oSelectTo.id, oSelectFrom[i].value, oSelectFrom[i].text, false);
					oSelectTo[oSelectTo.length - 1].selected = true;
					sel_count++;
					if(i < (oSelectFrom.length - 1))
						sel_value = oSelectFrom[i+1].value;
					else
						sel_value = '';
//					else if(i > 0)
//							sel_value = oSelectFrom[i-1].value;
				}
			}
			//Append selected_fields tail
			var n = oSelectTo.length;
			for(i = tail.length - 1; i >= 0; i--)
			{
				oSelectTo[n] = tail[i];
				n++;
			}
			if((sel_count == 1) && sel_value)
				jsSelectUtils.selectOption(oSelectFrom.id, sel_value);
		}
	}
	Sync();
}
function OnDelete(id)
{
	if(id == 'tabs_delete')
	{
		var selected_tabs = document.getElementById('selected_tabs');
		for(var i = 0; i < selected_tabs.length; i++)
		{
			if(selected_tabs[i].selected)
			{
				var selected_fields = document.getElementById('selected_fields[' + selected_tabs[i].value + ']');
				var p = selected_fields.parentNode;
				p.removeChild(selected_fields);
			}
		}

		jsSelectUtils.deleteSelectedOptions(selected_tabs.id);
		//For Opera deselect options
		jsSelectUtils.selectOption(selected_tabs.id, '');
	}
	if(id == 'fields_delete')
	{
		var selected_fields = GetFieldsActiveSelect();
		if(selected_fields)
		{
			jsSelectUtils.deleteSelectedOptions(selected_fields.id);
			//For Opera deselect options
			jsSelectUtils.selectOption(selected_fields.id, '');
		}
	}
	Sync();
}


function Sync()
{
	var i,j,n,found;
	var available_tabs = document.getElementById('available_tabs');
	var available_fields = document.getElementById('available_fields');
	var selected_tabs = document.getElementById('selected_tabs');

	//1 available_tabs
	//1.1 Save selection
	var available_tabs_selection = '';
	for(i = 0; i < available_tabs.length; i++)
		if(available_tabs[i].selected)
			available_tabs_selection = available_tabs[i].value;
	//2 available_fields
	//2.1 Save selection
	var available_fields_selection = new Object;
	for(i = 0; i < available_fields.length; i++)
	{
		if(available_fields[i].selected)
			available_fields_selection[available_fields[i].value] = available_fields[i].value;
	}
	//2.2 Clear list
	jsSelectUtils.selectAllOptions(available_fields.id);
	jsSelectUtils.deleteSelectedOptions(available_fields.id);
	//2.3 Fill list with fields missed
	if(available_tabs_selection)
	{
		var all_selected_fields = new Object;
		for(i = 0; i < selected_tabs.length; i++)
		{
			var selected_fields = document.getElementById('selected_fields[' + selected_tabs[i].value + ']');
			for(j = 0; j < selected_fields.length; j++)
				all_selected_fields[selected_fields[j].value] = selected_fields[j].value;
		}
		n = 0;
		for(available_field in arSystemTabsFields[available_tabs_selection])
		{
			if(!all_selected_fields[available_field])
			{
				var newoption = new Option(arSystemFields[available_field], available_field, false, false);
				available_fields.options[n] = newoption;
				available_fields.options[n].innerHTML = arSystemFields[available_field];
				n++;
			}
		}
		//2.4 Set selection
		for(i = 0; i < available_fields.length; i++)
			if(available_fields_selection[available_fields[i].value])
				available_fields[i].selected = true;
	}

	//3 selected_tabs

	//4 selected_fields
	found = false;
	for(i = 0; i < selected_tabs.length; i++)
	{
		var selected_fields = document.getElementById('selected_fields[' + selected_tabs[i].value + ']');
		if(selected_tabs[i].selected)
		{
			selected_fields.style.display = 'block';
			found = true;
		}
		else
		{
			selected_fields.style.display = 'none';
		}
	}
	if(found)
		document.getElementById('selected_fields[undef]').style.display = 'none';
	else
		document.getElementById('selected_fields[undef]').style.display = 'block';

	//5 disable and enable buttons
	//5.0 calculate selections counters
	var selected_tabs_count = 0;
	for(i = 0; i < selected_tabs.length; i++)
		if(selected_tabs[i].selected)
			selected_tabs_count++;
	var available_tabs_count = 0;
	for(i = 0; i < available_tabs.length; i++)
		if(available_tabs[i].selected)
			available_tabs_count++;
	//tabs_delete enabled if selected_tabs have selection
	document.getElementById('tabs_delete').disabled = selected_tabs_count <= 0;
	//tabs_copy enabled if available_tabs have selection and this selection does not exists in
	//		selected fields
	if(available_tabs_count <= 0)
	{
		document.getElementById('tabs_copy').disabled = true;
	}
	else
	{
		found = false;
		for(i = 0; i < selected_tabs.length; i++)
			if(selected_tabs[i].value == available_tabs_selection)
				found = true;
		document.getElementById('tabs_copy').disabled = found;
	}
	//tabs_up enabled if selected_tabs have selection
	document.getElementById('tabs_up').disabled = selected_tabs_count <= 0;
	//tabs_down enabled if selected_tabs have selection
	document.getElementById('tabs_down').disabled = selected_tabs_count <= 0;
	//tabs_rename enabled if selected_tabs have one item selected
	document.getElementById('tabs_rename').disabled = selected_tabs_count != 1;
	//tabs_add always selected
	document.getElementById('tabs_add').disabled = false;

	var selected_fields_count = 0;
	for(i = 0; i < selected_tabs.length; i++)
	{
		if(selected_tabs[i].selected)
		{
			var selected_fields = document.getElementById('selected_fields[' + selected_tabs[i].value + ']');
			for(j = 0; j < selected_fields.length; j++)
				if(selected_fields[j].selected)
					selected_fields_count++;
		}
	}
	var available_fields_count = 0;
	for(i = 0; i < available_fields.length; i++)
		if(available_fields[i].selected)
			available_fields_count++;
	//fields_delete enabled if selected_fields have selection
	document.getElementById('fields_delete').disabled = selected_fields_count <= 0;
	//fields_copy enabled if available_fields have selection and at least one tab selected
	document.getElementById('fields_copy').disabled = available_fields_count <= 0 || selected_tabs_count <= 0;
	//fields_up enabled if selected_fields have selection
	document.getElementById('fields_up').disabled = selected_fields_count <= 0;
	//fields_down enabled if selected_fields have selection
	document.getElementById('fields_down').disabled = selected_fields_count <= 0;
	//fields_rename enabled if selected_fields have one item selected
	document.getElementById('fields_rename').disabled = selected_fields_count != 1;
	//fields_add enabled if selected_tabs have one item selected
	document.getElementById('fields_add').disabled = selected_tabs_count != 1;

	var arFields = new Object;
	for(var name in arSystemFields)
		arFields[name] = arSystemFields[name];
	for(i = 0; i < selected_tabs.length; i++)
	{
		selected_fields = document.getElementById('selected_fields[' + selected_tabs[i].value + ']');
		for(j = 0; j < selected_fields.length; j++)
			delete arFields[selected_fields[j].value];
	}
	var absentRequiredFields = [];
	for(var name in arFields)
	{
		if(arFields[name].substring(0,1) === "*")
		{
			absentRequiredFields.push(arFields[name].substring(1));
		}
	}
	var save_button = document.getElementById('save_settings'),
		saveErrorMessage = document.getElementById('save_settings_error'),
		absentFieldList = document.getElementById('absent_required_fields');
	if (absentRequiredFields.length > 0)
	{
		absentFieldList.innerHTML = absentRequiredFields.join('<br>');
		saveErrorMessage.style.display = 'block';
		save_button.disabled = true;
	}
	else
	{
		absentFieldList.innerHTML = '';
		saveErrorMessage.style.display = 'none';
		save_button.disabled = false;
	}
}

function SyncAvailableFields()
{
	var oSelect = document.getElementById('available_tabs');
	if(oSelect)
	{
		var k = oSelect.length;
		for(var i=0; i<k; i++)
		{
			oFieldsSelect = document.getElementById('available_fields');
			if(oFieldsSelect)
			{
				jsSelectUtils.selectAllOptions(oFieldsSelect.id);
				jsSelectUtils.deleteSelectedOptions(oFieldsSelect.id);
				if(oSelect[i].selected)
				{
					var n = 0;
					for(var field_id in arSystemTabsFields[oSelect[i].value])
					{
						var newoption = new Option(arSystemFields[field_id], field_id, false, false);
						oFieldsSelect.options[n]=newoption;
						oFieldsSelect.options[n].innerHTML = arSystemFields[field_id];
						n++;
					}
				}
			}
		}
	}
}

function GetFieldsActiveSelect()
{
	var oFieldsSelect;
	var oSelect = document.getElementById('selected_tabs');
	if(oSelect)
	{
		var k = oSelect.length;
		for(var i=0; i<k; i++)
		{
			oFieldsSelect = document.getElementById('selected_fields[' + oSelect[i].value + ']');
			if(oFieldsSelect && oFieldsSelect.style.display == 'block')
				return oFieldsSelect;
		}
	}
	return false;
}

function OnRename(id)
{
	var frm=document.form_settings;
	if(id == 'tabs_rename')
	{
		var oSelect = document.getElementById('selected_tabs');
		if(oSelect)
		{
			var n = oSelect.length;
			var c = 0;
			var choice = '';
			for(var i=0; i<n; i++)
			{
				if(oSelect[i].selected)
				{
					c++;
					if(!choice)
						choice = oSelect[i].text;
				}
			}
			if(c == 1)
			{
				var name = prompt(arFormEditMess.admin_lib_sett_tab_rename, choice);
				if(name && name.length > 0)
				{
					for(var i=0; i<n; i++)
						if(oSelect[i].selected)
						{
							oSelect[i].text = name;
							break;
						}
				}
			}
		}
	}
	if(id == 'fields_rename')
	{
		var oSelect = GetFieldsActiveSelect();
		if(oSelect)
		{
			var n = oSelect.length;
			var c = 0;
			var choice = '';
			for(var i=0; i<n; i++)
			{
				if(oSelect[i].selected)
				{
					c++;
					if(!choice)
						choice = oSelect[i].innerHTML;
				}
			}
			if(c == 1)
			{
				var prefix = '';
				if(choice.substring(0, 2) == '--')
				{
					choice = choice.substring(2);
					prefix = '--';
				}
				else
				{
					if(choice.substring(0, 1) == '*')
					{
						choice = choice.substring(1);
						prefix = '*';
					}
					else
					{
						if(choice.substring(0, 12) == '&nbsp;&nbsp;')
						{
							choice = choice.substring(12);
							prefix = '&nbsp;&nbsp;';
						}
						else
						{
							while(choice.substring(0, 2) == '\xA0\xA0' || choice.substring(0, 2) == '\xC2\xA0')
							{
								choice = choice.substring(2);
								prefix = '&nbsp;&nbsp;';
							}
						}
					}
				}
				var name = prompt(arFormEditMess.admin_lib_sett_sec_rename, choice);
				if(name && name.length > 0)
				{
					for(var i=0; i<n; i++)
						if(oSelect[i].selected)
						{
							if(prefix == '&nbsp;&nbsp;')
							{
								oSelect[i].text = name;
								oSelect[i].innerHTML = '&nbsp;&nbsp;' + oSelect[i].innerHTML;
							}
							else
							{
								oSelect[i].text = prefix + name;
							}
							break;
						}
				}
			}
		}
	}
}
function FieldsUpAndDown(direction)
{
	var oSelect = GetFieldsActiveSelect();
	if(oSelect)
	{
		if(direction == 'up')
			jsSelectUtils.moveOptionsUp(oSelect);
		else
			jsSelectUtils.moveOptionsDown(oSelect);
	}
}

function exportSettingsToPhp(oEvent, formId)
{
	if (oEvent.ctrlKey || BX.browser.IsMac() && oEvent.altKey)
	{
		var mess = [];
		var mess_id = '';
		var php = "CAdminFormSettings::setTabsArray('"+formId+"', array(<br>";
		var oSelect = document.getElementById('selected_tabs');
		if(oSelect)
		{
			var k = oSelect.length;
			for(var i=0; i<k; i++)
			{
				mess_id = "_" + oSelect[i].value + "_TAB_TITLE";
				mess[mess.length] = {id: mess_id, val: oSelect[i].text};
				php += "\t'" + oSelect[i].value + "' => array(<br>";
				php += "\t\t'TAB' => GetMessage('" + mess_id + "'),<br>";
				php += "\t\t'FIELDS' => array(<br>";
				oFieldsSelect = document.getElementById('selected_fields[' + oSelect[i].value + ']');
				if(oFieldsSelect)
				{
					var n = oFieldsSelect.length;
					for(var j=0; j<n; j++)
					{
						mess_id = "_" + oFieldsSelect[j].value;
						mess[mess.length] = {id: mess_id, val: jsUtils.trim(oFieldsSelect[j].text)};
						php += "\t\t\t'" + oFieldsSelect[j].value + "' => GetMessage('" + mess_id + "'),<br>";
					}
				}
				php += "\t\t),<br>";
				php += "\t),<br>"
			}
		}
		php += '), true);<br>';

		for (var i = 0; i < mess.length; i++)
		{
			php += "$MESS[\""+mess[i].id+"\"]=\""+mess[i].val+"\";<br>";
		}

		var popup = new BX.CDialog({
			content: '<pre>' + php + '</pre>',
			buttons: [BX.CDialog.btnOK, BX.CDialog.btnCancel],
			width: 640,
			height: 480
		});
		popup.Show();
	}

}
