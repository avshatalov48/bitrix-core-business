function BxInterfaceGrid(table_id)
{
	this.oActions = {};
	this.oColsMeta = {};
	this.oColsNames = {};
	this.customNames = {};
	this.oEditData = {};
	this.oSaveData = {};
	this.oOptions = {};
	this.oVisibleCols = null;
	this.vars = {};
	this.menu = null;
	this.settingsMenu = [];
	this.filterMenu = [];
	this.checkBoxCount = 0;
	this.bColsChanged = false;
	this.bViewsChanged = false;
	this.oFilterRows = {};
	this.activeRow = null;

	var _this = this;
	this.table_id = table_id;

	this.InitTable = function()
	{
		var tbl = document.getElementById(this.table_id);
		if(!tbl || tbl.rows.length<1 || tbl.rows[0].cells.length<1)
			return;

		var i;
		var nCols = tbl.rows[0].cells.length;

		/*head row actions*/
		for(i=0; i<nCols; i++)
		{
			var j;
			for(j=0; j<2; j++)
			{
				var cell = tbl.rows[j].cells[i];

				cell.onmouseover = function(){_this.HighlightGutter(this, true)};
				cell.onmouseout = function(){_this.HighlightGutter(this, false)};
				if(j==1)
				{
					if(cell.className && (cell.className == 'bx-actions-col' || cell.className == 'bx-checkbox-col'))
						continue;

					//DD handlers
					if(this.vars.user_authorized)
					{
						cell.onbxdragstart = _this.DragStart;
						cell.onbxdragstop = _this.DragStop;
						cell.onbxdrag = _this.Drag;
						cell.onbxdraghout = function(){_this.HighlightGutter(this, false)};
						jsDD.registerObject(cell);

						cell.onbxdestdraghover = _this.DragHover;
						cell.onbxdestdraghout = _this.DragOut;
						cell.onbxdestdragfinish = _this.DragFinish;
						jsDD.registerDest(cell);
					}
				}
			}
		}

		var n = tbl.rows.length;
		for(i=0; i<n; i++)
		{
			var row = tbl.rows[i];

			if(row.className && row.className == 'bx-grid-footer')
				continue;

			/*first and last columns style classes*/
			row.cells[0].className += ' bx-left';
	 		row.cells[row.cells.length-1].className += ' bx-right';

			if(i>=2)
			{
				/*first column checkbox action*/
				var checkbox = row.cells[0].childNodes[0];
				if(checkbox && checkbox.tagName && checkbox.tagName.toUpperCase() == "INPUT" && checkbox.type.toUpperCase() == "CHECKBOX")
				{
					checkbox.onclick = function(){_this.SelectRow(this); _this.EnableActions()};
					jsUtils.addEvent(row, "click", _this.OnClickRow);
					this.checkBoxCount++;
				}

				/*rows mousover action*/
				row.onmouseover = function(){_this.HighlightRow(this, true)};
				row.onmouseout = function(){_this.HighlightRow(this, false)};

				if(i%2 == 0)
					row.className += ' bx-odd';
				else
					row.className += ' bx-even';
			}
			if(row.oncontextmenu)
				jsUtils.addEvent(row, "contextmenu", this.OnRowContext);
		}

		if(tbl.rows.length > 2)
		{
			tbl.rows[2].className += ' bx-top';
			var r = tbl.rows[tbl.rows.length-1];
			if(r.className && r.className == 'bx-grid-footer')
				r = tbl.rows[tbl.rows.length-2];
			r.className += ' bx-bottom';
		}
	};

	this.OnRowContext = function(e)
	{
		if(!_this.menu)
			return;

		if(!e)
			e = window.event;
		if(!phpVars.opt_context_ctrl && e.ctrlKey || phpVars.opt_context_ctrl && !e.ctrlKey)
			return;

		var targetElement;
		if(e.target)
			targetElement = e.target;
		else if(e.srcElement)
			targetElement = e.srcElement;

		//column context menu
		var el = targetElement;
		while(el && !(el.tagName && el.tagName.toUpperCase() == 'TD' && el.oncontextmenu))
			el = jsUtils.FindParentObject(el, "td");

		var col_menu = null;
		if(el && el.oncontextmenu)
		{
			col_menu = el.oncontextmenu();
			col_menu[col_menu.length] = {'SEPARATOR':true};
		}

		//row context menu
		el = targetElement;
		while(el && !(el.tagName && el.tagName.toUpperCase() == 'TR' && el.oncontextmenu))
			el = jsUtils.FindParentObject(el, "tr");

		var menu = _this.menu;
		menu.PopupHide();

		_this.activeRow = el;
		if(_this.activeRow && !BX.hasClass(el, 'bx-grid-gutter') && !BX.hasClass(el, 'bx-grid-head'))
			_this.activeRow.className += ' bx-active';

		menu.OnClose = function()
		{
			if(_this.activeRow)
			{
				_this.activeRow.className = _this.activeRow.className.replace(/\s*bx-active/i, '');
				_this.activeRow = null;
			}
			_this.SaveColumns();
		};

		//combined menu
		var menuItems = BX.util.array_merge(col_menu, el.oncontextmenu());
		if(menuItems.length == 0)
			return;
		menu.SetItems(menuItems);
		menu.BuildItems();

		var arScroll = jsUtils.GetWindowScrollPos();
		var x = e.clientX + arScroll.scrollLeft;
		var y = e.clientY + arScroll.scrollTop;
		var pos = {};
		pos['left'] = pos['right'] = x;
		pos['top'] = pos['bottom'] = y;

		menu.PopupShow(pos);

		e.returnValue = false;
		if(e.preventDefault)
			e.preventDefault();
	};

	this.ShowActionMenu = function(el, index)
	{
		_this.menu.PopupHide();

		_this.activeRow = jsUtils.FindParentObject(el, "tr");
		if(_this.activeRow)
			_this.activeRow.className += ' bx-active';

		_this.menu.ShowMenu(el, _this.oActions[index], false, false,
			function()
			{
				if(_this.activeRow)
				{
					_this.activeRow.className = _this.activeRow.className.replace(/\s*bx-active/i, '');
					_this.activeRow = null;
				}
			}
		);
	};

	this.HighlightGutter = function(cell, on)
	{
		var table = cell.parentNode.parentNode.parentNode;
		var gutter = table.rows[0].cells[cell.cellIndex];
		var bSorted = (gutter.className.indexOf('bx-sorted') != -1);
		if(on)
		{
			if(bSorted)
			{
				gutter.className += ' bx-over-sorted';
				cell.className += ' bx-over-sorted';
			}
			else
			{
				gutter.className += ' bx-over';
				cell.className += ' bx-over';
			}
		}
		else
		{
			if(bSorted)
			{
				gutter.className = gutter.className.replace(/\s*bx-over-sorted/i, '');
				cell.className = cell.className.replace(/\s*bx-over-sorted/i, '');
			}
			else
			{
				gutter.className = gutter.className.replace(/\s*bx-over/i, '');
				cell.className = cell.className.replace(/\s*bx-over/i, '');
			}
		}
	};

	this.HighlightRow = function(row, on)
	{
		if(on)
			row.className += ' bx-over';
		else
			row.className = row.className.replace(/\s*bx-over/i, '');
	};

	this.SelectRow = function(checkbox)
	{
		var row = checkbox.parentNode.parentNode;
		var tbl = row.parentNode.parentNode;
		var span = document.getElementById(tbl.id+'_selected_span');
		var selCount = parseInt(span.innerHTML);

		if(checkbox.checked)
		{
			row.className += ' bx-selected';
			selCount++;
		}
		else
		{
			row.className = row.className.replace(/\s*bx-selected/ig, '');
			selCount--;
		}
		span.innerHTML = selCount.toString();

		var checkAll = document.getElementById(tbl.id+'_check_all');

		if(selCount == this.checkBoxCount)
			checkAll.checked = true;
		else
			checkAll.checked = false;
	};

	this.OnClickRow = function(e)
	{
		if(!e)
			e = window.event;
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
	};

	this.SelectAllRows = function(checkbox)
	{
		var tbl = document.getElementById(this.table_id);
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
	};

	this.EnableActions = function()
	{
		var form = document.forms['form_'+this.table_id];
		if(!form) return;

		var bEnabled = this.IsActionEnabled();
		var bEnabledEdit = this.IsActionEnabled('edit');

		if(form.apply) form.apply.disabled = !bEnabled;
		var b = document.getElementById('edit_button_'+this.table_id);
		if(b) b.className = 'context-button icon action-edit-button'+(bEnabledEdit? '':'-dis');
		b = document.getElementById('delete_button_'+this.table_id);
		if(b) b.className = 'context-button icon action-delete-button'+(bEnabled? '':'-dis');
	};

	this.IsActionEnabled = function(action)
	{
		var form = document.forms['form_'+this.table_id];
		if(!form) return;

		var bChecked = false;
		var span = document.getElementById(this.table_id+'_selected_span');
		if(span && parseInt(span.innerHTML)>0)
			bChecked = true;

		var elAll = form['action_all_rows_'+this.table_id];
		if(action == 'edit')
			return !(elAll && elAll.checked) && bChecked;
		else
			return (elAll && elAll.checked) || bChecked;
	};

	this.SwitchActionButtons = function(bShow)
	{
		var buttonsTd = document.getElementById("bx_grid_"+this.table_id+"_action_buttons");
		var td = buttonsTd;
		while(td = jsUtils.FindNextSibling(td, 'td'))
			td.style.display = (bShow? 'none':'');
		buttonsTd.style.display = (bShow? '':'none');
	};

	this.ActionEdit = function(a)
	{
		if(this.IsActionEnabled('edit'))
		{
			var form = document.forms['form_'+this.table_id];
			if(!form)
				return;

			//show form buttons
			this.SwitchActionButtons(true);

			//go through rows and show inputs
			var ids = form['ID[]'];
			if(!ids.length)
				ids = new Array(ids);

			for(var i=0; i<ids.length; i++)
			{
				var el = ids[i];
				if(el.checked)
				{
					var tr = jsUtils.FindParentObject(el, "tr");
					BX.denyEvent(tr, 'dblclick');

					//go through columns
					var td = jsUtils.FindParentObject(el, "td");
					td = jsUtils.FindNextSibling(td, "td");
					if(td.className == 'bx-actions-col')
						td = jsUtils.FindNextSibling(td, "td");

					var row_id = el.value;
					this.oSaveData[row_id] = {};
					for(var col_id in this.oColsMeta)
					{
						if(this.oColsMeta[col_id].editable == true && this.oEditData[row_id][col_id] !== false)
						{
							this.oSaveData[row_id][col_id] = td.innerHTML;
							td.innerHTML = '';

							//insert controls
							var data = this.oEditData[row_id][col_id];
							var name = 'FIELDS['+row_id+']['+col_id+']';
							switch(this.oColsMeta[col_id].type)
							{
								case 'checkbox':
									td.appendChild(BX.create('INPUT', {'props': {
										'type':'hidden',
										'name':name,
										'value':'N'
									}}));
									td.appendChild(BX.create('INPUT', {'props': {
										'type':'checkbox',
										'name':name,
										'value':'Y',
										'checked':(data == 'Y'),
										'defaultChecked':(data == 'Y')
									}}));
									break;
								case 'list':
									var options = [];
									for(var list_val in this.oColsMeta[col_id].items)
									{
										options[options.length] = BX.create('OPTION', {
											'props': {'value':list_val, 'selected':(list_val == data)},
											'text': this.oColsMeta[col_id].items[list_val]}
										);
									}

									td.appendChild(BX.create('SELECT', {
										'props': {'name':name},
										'children': options
									}));
									break;
								case 'date':
									var span = BX.create('SPAN', {'style':{'whiteSpace':'nowrap'}});
									span.appendChild(BX.create('INPUT', {'props': {
										'type':'text',
										'name':name,
										'value':data,
										'size':(this.oColsMeta[col_id].size? this.oColsMeta[col_id].size : 10)
									}}));
									span.appendChild(BX.create('A', {
										'props': {
											'href':'javascript:void(0);',
											'title': this.vars.mess.calend_title
										},
										'html':'<img src="'+this.vars.calendar_image+'" alt="'+this.vars.mess.calend_title+'" class="calendar-icon" onclick="BX.calendar({node:this, field:\''+name+'\', bTime: true, currentTime: \''+this.vars.server_time+'\'});" onmouseover="this.className+=\' calendar-icon-hover\';" onmouseout="this.className = this.className.replace(/\s*calendar-icon-hover/ig, \'\');" border="0"/>'}));
									td.appendChild(span);
									break;
								default:
									var props = {
										'type':'text',
										'name':name,
										'value':data,
										'size':(this.oColsMeta[col_id].size? this.oColsMeta[col_id].size : 15)
									};
									if(this.oColsMeta[col_id].maxlength)
										props.maxLength = this.oColsMeta[col_id].maxlength;
									td.appendChild(BX.create('INPUT', {'props': props}));
									break;
							}
						}
						td = jsUtils.FindNextSibling(td, "td");
					}
				}
				el.disabled = true;
			}

			form.elements['action_button_'+this.table_id].value = 'edit';
		}
	};

	this.ActionCancel = function()
	{
		var form = document.forms['form_'+this.table_id];
		if(!form)
			return;

		//hide form buttons
		this.SwitchActionButtons(false);

		//go through rows and restore values
		var ids = form['ID[]'];
		if(!ids.length)
			ids = new Array(ids);

		for(var i=0; i<ids.length; i++)
		{
			var el = ids[i];
			if(el.checked)
			{
				var tr = jsUtils.FindParentObject(el, "tr");
				BX.allowEvent(tr, 'dblclick');

				//go through columns
				var td = jsUtils.FindParentObject(el, "td");
				td = jsUtils.FindNextSibling(td, "td");
				if(td.className == 'bx-actions-col')
					td = jsUtils.FindNextSibling(td, "td");

				var row_id = el.value;
				for(var col_id in this.oColsMeta)
				{
					if(this.oColsMeta[col_id].editable == true && this.oEditData[row_id][col_id] !== false)
						td.innerHTML = this.oSaveData[row_id][col_id];

					td = jsUtils.FindNextSibling(td, "td");
				}
			}
			el.disabled = false;
		}

		form.elements['action_button_'+this.table_id].value = '';
	};

	this.ActionDelete = function()
	{
		var form = document.forms['form_'+this.table_id];
		if(!form)
			return;

		form.elements['action_button_'+this.table_id].value = 'delete';

		BX.submit(form);
	};

	this.DeleteItem = function(field_id, message)
	{
		var checkbox = document.getElementById('ID_' + field_id);
		if(checkbox)
		{
			if(confirm(message))
			{
				var form = document.forms['form_'+this.table_id];
				if(!form)
					return;

				//go through rows and restore values
				var ids = form['ID[]'];
				if(!ids.length)
					ids = new Array(ids);

				for(var i=0; i<ids.length; i++)
				{
					ids[i].checked = false;
				}

				checkbox.checked = true;
				this.ActionDelete();
			}
		}
	};

	this.ForAllClick = function(el)
	{
		if(el.checked && !confirm(this.vars.mess.for_all_confirm))
		{
			el.checked=false;
			return;
		}

		//go through rows
		var ids = el.form['ID[]'];
		if(ids)
		{
			if(!ids.length)
				ids = new Array(ids);
			for(var i=0; i<ids.length; i++)
				ids[i].disabled = el.checked;
		}

		this.EnableActions();
	};

	this.Sort = function(url, by, sort_state, def_order, args)
	{
		var order;
		if(sort_state == '')
		{
			var e = null, bControl = false;
			if(args.length > 0)
				e = args[0];
			if(!e)
				e = window.event;
			if(e)
				bControl = e.ctrlKey;
			order = (bControl? (def_order == 'asc'? 'desc':'asc') : def_order);
		}
		else if(sort_state == 'asc')
			order = 'desc';
		else
			order = 'asc';

		url += order;

		BX.ajax.get('/bitrix/components'+_this.vars.component_path+'/settings.php?GRID_ID='+_this.table_id+'&action=savesort&by='+by+'&order='+order+'&sessid='+_this.vars.sessid, function(){_this.Reload(url)});
	};

	this.InitVisCols = function()
	{
		if(this.oVisibleCols == null)
		{
			this.oVisibleCols = {};
			for(var id in this.oColsMeta)
				this.oVisibleCols[id] = true;
		}
	};

	this.CheckColumn = function(column, menuItem)
	{
		var colMenu = this.menu.GetMenuByItemId(menuItem.id);
		var bShow = !(colMenu.GetItemInfo(menuItem).ICON == 'checked');
		colMenu.SetItemIcon(menuItem, (bShow? 'checked':''));

		this.InitVisCols();
		this.oVisibleCols[column] = bShow;
		this.bColsChanged = true;
	};

	this.HideColumn = function(column)
	{
		this.InitVisCols();
		this.oVisibleCols[column] = false;
		this.bColsChanged = true;
		this.SaveColumns();
	};

	this.ApplySaveColumns = function()
	{
		this.menu.PopupHide();
		this.SaveColumns();
	};

	this.SaveColumns = function(columns)
	{
		var sCols = '';
		if(columns)
		{
			sCols = columns
		}
		else
		{
			if(!_this.bColsChanged)
				return;

			for(var id in _this.oVisibleCols)
				if(_this.oVisibleCols[id])
					sCols += (sCols!=''? ',':'')+id;
		}
		BX.ajax.get('/bitrix/components'+_this.vars.component_path+'/settings.php?GRID_ID='+_this.table_id+'&action=showcolumns&columns='+sCols+'&sessid='+_this.vars.sessid, function(){_this.Reload()});
	};

	this.Reload = function(url)
	{
		jsDD.Disable();

		if(!url)
		{
			url = this.vars.current_url;
		}

		if(this.vars.ajax.AJAX_ID != '')
		{
			BX.ajax.insertToNode(url+(url.indexOf('?') == -1? '?':'&')+'bxajaxid='+this.vars.ajax.AJAX_ID, 'comp_'+this.vars.ajax.AJAX_ID);
		}
		else
		{
			window.location = url;
		}
	};

	this.SetTheme = function(menuItem, theme)
	{
		BX.loadCSS(this.vars.template_path+'/themes/'+theme+'/style.css');
		BX(_this.table_id).className = 'bx-interface-grid bx-interface-grid-theme-'+theme;

		var themeMenu = this.menu.GetMenuByItemId(menuItem.id);
		themeMenu.SetAllItemsIcon('');
		themeMenu.SetItemIcon(menuItem, 'checked');

		BX.ajax.get('/bitrix/components'+_this.vars.component_path+'/settings.php?GRID_ID='+_this.table_id+'&action=settheme&theme='+theme+'&sessid='+_this.vars.sessid);
	};

	this.SetView = function(view_id)
	{
		var filter_id = _this.oOptions.views[view_id].saved_filter;
		var func = (filter_id && _this.oOptions.filters[filter_id]?
			function(){_this.ApplyFilter(filter_id)} :
			function(){_this.Reload()});

		BX.ajax.get('/bitrix/components'+_this.vars.component_path+'/settings.php?GRID_ID='+_this.table_id+'&action=setview&view_id='+view_id+'&sessid='+_this.vars.sessid, func);
	};

	this.EditCurrentView = function()
	{
		this.ShowSettings(this.oOptions.views[this.oOptions.current_view],
			function()
			{
				_this.SaveSettings(_this.oOptions.current_view, true);
			}
		);
	};

	this.AddView = function()
	{
		var view_id = 'view_'+Math.round(Math.random()*1000000);

		var view = {};
		for(var i in this.oOptions.views[this.oOptions.current_view])
			view[i] = this.oOptions.views[this.oOptions.current_view][i];
		view.name = this.vars.mess.viewsNewView;

		this.ShowSettings(view,
			function()
			{
				var data = _this.SaveSettings(view_id);

				_this.oOptions.views[view_id] = {
					'name':data.name,
					'columns':data.columns,
					'sort_by':data.sort_by,
					'sort_order':data.sort_order,
					'page_size':data.page_size,
					'saved_filter':data.saved_filter,
					'custom_names': data.custom_names
				};
				_this.bViewsChanged = true;

				var form = document['views_'+_this.table_id];
				form.views_list.options[form.views_list.length] = new Option((data.name != ''? data.name:_this.vars.mess.viewsNoName), view_id, true, true);
			}
		);
	};

	this.EditView = function(view_id)
	{
		this.ShowSettings(this.oOptions.views[view_id],
			function()
			{
				var data = _this.SaveSettings(view_id);

				_this.oOptions.views[view_id] = {
					'name':data.name,
					'columns':data.columns,
					'sort_by':data.sort_by,
					'sort_order':data.sort_order,
					'page_size':data.page_size,
					'saved_filter':data.saved_filter,
					'custom_names': data.custom_names
				};
				_this.bViewsChanged = true;

				var form = document['views_'+_this.table_id];
				form.views_list.options[form.views_list.selectedIndex].text = (data.name != ''? data.name:_this.vars.mess.viewsNoName);
			}
		);
	};

	this.DeleteView = function(view_id)
	{
		if(!confirm(this.vars.mess.viewsDelete))
			return;

		var form = document['views_'+this.table_id];
		var index = form.views_list.selectedIndex;
		form.views_list.remove(index);
		form.views_list.selectedIndex = (index < form.views_list.length? index : form.views_list.length-1);

		this.bViewsChanged = true;

		BX.ajax.get('/bitrix/components'+this.vars.component_path+'/settings.php?GRID_ID='+this.table_id+'&action=delview&view_id='+view_id+'&sessid='+_this.vars.sessid);
	};

	this.ShowSettings = function(view, action)
	{
		var bCreated = false;
		if(!window['settingsDialog'+this.table_id])
		{
			window['settingsDialog'+this.table_id] = new BX.CDialog({
				'content':'<form name="settings_'+this.table_id+'"></form>',
				'title': this.vars.mess.settingsTitle,
				'width': this.vars.settingWndSize.width,
				'height': this.vars.settingWndSize.height,
				'resize_id': 'InterfaceGridSettingWnd'
			});
			bCreated = true;
		}

		window['settingsDialog'+this.table_id].ClearButtons();
		window['settingsDialog'+this.table_id].SetButtons([
			{
				'title': this.vars.mess.settingsSave,
				'action': function(){
					action();
					this.parentWindow.Close();
				}
			},
			BX.CDialog.prototype.btnCancel
		]);
		window['settingsDialog'+this.table_id].Show();

		var form = document['settings_'+this.table_id];

		if(bCreated)
			form.appendChild(BX('view_settings_'+this.table_id));

		this.customNames = (view.custom_names? view.custom_names : {});

		//name
		form.view_name.focus();
		form.view_name.value = view.name;

		//get visible columns
		var aVisCols = [];
		if(view.columns != '')
		{
			aVisCols = view.columns.split(',');
		}
		else
		{
			for(var i in this.oColsMeta)
				aVisCols[aVisCols.length] = i;
		}

		var oVisCols = {}, n;
		for(i=0, n=aVisCols.length; i<n; i++)
			oVisCols[aVisCols[i]] = true;

		//invisible cols
		jsSelectUtils.deleteAllOptions(form.view_all_cols);
		for(i in this.oColsNames)
		{
			if(!oVisCols[i])
			{
				var colName = (this.customNames[i]? this.customNames[i] : this.oColsNames[i]);
				form.view_all_cols.options[form.view_all_cols.length] = new Option(colName, i, false, false);
			}
		}

		//visible cols
		jsSelectUtils.deleteAllOptions(form.view_cols);
		for(i in oVisCols)
		{
			colName = (this.customNames[i]? this.customNames[i] : this.oColsNames[i]);
			form.view_cols.options[form.view_cols.length] = new Option(colName, i, false, false);
		}

		//sorting
		jsSelectUtils.selectOption(form.view_sort_by, view.sort_by);
		jsSelectUtils.selectOption(form.view_sort_order, view.sort_order);

		//page size
		jsSelectUtils.selectOption(form.view_page_size, view.page_size);

		//saved filter
		jsSelectUtils.deleteAllOptions(form.view_filters);
		form.view_filters.options[0] = new Option(this.vars.mess.viewsFilter, '');
		for(i in this.oOptions.filters)
			form.view_filters.options[form.view_filters.length] = new Option(this.oOptions.filters[i].name, i, (i == view.saved_filter), (i == view.saved_filter));

		//common options
		if(form.set_default_settings)
		{
			form.set_default_settings.checked = false;
			form.delete_users_settings.checked = false;
			form.delete_users_settings.disabled = true;
		}

		//init controls
		form.up_btn.disabled = form.down_btn.disabled = form.rename_btn.disabled = form.add_btn.disabled = form.del_btn.disabled = true;
	};

	this.RenameColumn = function()
	{
		var bCreated = false;
		if(!window['renameDialog'+this.table_id])
		{
			window['renameDialog'+this.table_id] = new BX.CDialog({
				'content':'<form name="rename_'+this.table_id+'"></form>',
				'title': this.vars.mess.renameTitle,
				'width': this.vars.renameWndSize.width,
				'height': this.vars.renameWndSize.height,
				'resize_id': 'InterfaceGridRenameWnd',
				'buttons': [
					{
						'title': this.vars.mess.settingsSave,
						'action': function()
						{
							var selectedCol = settingsForm.view_cols.value;
							var value = form.col_name.value;

							if(value.length > 0)
							{
								_this.customNames[selectedCol] = value;
							}
							else
							{
								value = _this.oColsNames[selectedCol];
								delete _this.customNames[selectedCol];
							}
							settingsForm.view_cols.options[settingsForm.view_cols.selectedIndex].text = value;

							this.parentWindow.Close();
						}
					},
					BX.CDialog.prototype.btnCancel
				]
			});
			bCreated = true;
		}

		window['renameDialog'+this.table_id].Show();

		var form = document['rename_'+this.table_id];
		var settingsForm = document['settings_'+this.table_id];

		if(bCreated)
			form.appendChild(BX('rename_column_'+this.table_id));

		var selectedCol = settingsForm.view_cols.value;

		form.col_name.focus();
		form.col_name_def.value = this.oColsNames[selectedCol];
		form.col_name.value = (this.customNames[selectedCol]? this.customNames[selectedCol] : this.oColsNames[selectedCol]);
	};

	this.SaveSettings = function(view_id, doReload)
	{
		var form = document['settings_'+this.table_id];

		var sCols = '';
		var n = form.view_cols.length;
		for(var i=0; i<n; i++)
			sCols += (sCols!=''? ',':'')+form.view_cols[i].value;

		var data = {
			'GRID_ID': this.table_id,
			'view_id': view_id,
			'action': 'savesettings',
			'sessid': this.vars.sessid,
			'name': form.view_name.value,
			'columns': sCols,
			'sort_by': form.view_sort_by.value,
			'sort_order': form.view_sort_order.value,
			'page_size': form.view_page_size.value,
			'saved_filter': form.view_filters.value,
			'custom_names': this.customNames
		};

		if(form.set_default_settings)
		{
			data.set_default_settings = (form.set_default_settings.checked? 'Y':'N');
			data.delete_users_settings = (form.delete_users_settings.checked? 'Y':'N');
		}
		
		var handler = null;
		if(doReload === true)
		{
			handler = function()
			{
				if(data.saved_filter && _this.oOptions.filters[data.saved_filter])
				{
					_this.ApplyFilter(data.saved_filter);
				}
				else
				{
					_this.Reload();
				}
			};
		}

		BX.ajax.post('/bitrix/components'+_this.vars.component_path+'/settings.php', data, handler);

		return data;
	};

	this.ReloadViews = function()
	{
		if(_this.bViewsChanged)
			_this.Reload();
	};

	this.ShowViews = function()
	{
		this.bViewsChanged = false;
		var bCreated = false;
		if(!window['viewsDialog'+this.table_id])
		{
			var applyBtn = new BX.CWindowButton({
				'title': this.vars.mess.viewsApply,
				'hint': this.vars.mess.viewsApplyTitle,
				'action': function(){
					var form = document['views_'+_this.table_id];
					if(form.views_list.selectedIndex != -1)
						_this.SetView(form.views_list.value);

					window['bxGrid_'+_this.table_id].bViewsChanged = false;
					this.parentWindow.Close();
				}
			});

			window['viewsDialog'+this.table_id] = new BX.CDialog({
				'content':'<form name="views_'+this.table_id+'"></form>',
				'title': this.vars.mess.viewsTitle,
				'buttons': [applyBtn, BX.CDialog.prototype.btnClose],
				'width': this.vars.viewsWndSize.width,
				'height': this.vars.viewsWndSize.height,
				'resize_id': 'InterfaceGridViewsWnd'
			});

			BX.addCustomEvent(window['viewsDialog'+this.table_id], 'onWindowUnRegister', this.ReloadViews);

			bCreated = true;
		}

		window['viewsDialog'+this.table_id].Show();

		var form = document['views_'+this.table_id];

		if(bCreated)
			form.appendChild(BX('views_list_'+this.table_id));
	};

	/* DD handlers */

	this.DragStart = function()
	{
		var div = document.body.appendChild(document.createElement("DIV"));
		div.style.position = 'absolute';
		div.style.zIndex = 10;
		div.className = 'bx-drag-object';
		div.innerHTML = this.innerHTML;
		div.style.width = this.clientWidth+'px';
		this.__dragCopyDiv = div;
		this.className += ' bx-drag-source';

		var arrowDiv = document.body.appendChild(document.createElement("DIV"));
		arrowDiv.style.position = 'absolute';
		arrowDiv.style.zIndex = 20;
		arrowDiv.className = 'bx-drag-arrow';
		this.__dragArrowDiv = arrowDiv;

		return true;
	};

	this.Drag = function(x, y)
	{
		var div = this.__dragCopyDiv;
		div.style.left = x+'px';
		div.style.top = y+'px';

		return true;
	};

	this.DragStop = function()
	{
		this.className = this.className.replace(/\s*bx-grid-drag-source/ig, "");

		this.__dragCopyDiv.parentNode.removeChild(this.__dragCopyDiv);
		this.__dragCopyDiv = null;

		this.__dragArrowDiv.parentNode.removeChild(this.__dragArrowDiv);
		this.__dragArrowDiv = null;

		return true;
	};

	this.DragHover = function(obDrag, x, y)
	{
		_this.HighlightGutter(this, true);
		this.className += ' bx-drag-over';

		var div = obDrag.__dragArrowDiv;
		var pos = jsUtils.GetRealPos(this);
		if(this.cellIndex <= obDrag.cellIndex)
			div.style.left = (pos['left']-6)+'px';
		else
			div.style.left = (pos['right']-6)+'px';
		div.style.top = (pos['top']-12)+'px';

		return true;
	};

	this.DragOut = function(obDrag, x, y)
	{
		_this.HighlightGutter(this, false);
		this.className = this.className.replace(/\s*bx-drag-over/ig, "");

		var div = obDrag.__dragArrowDiv;
		div.style.left = '-1000px';

		return true;
	};

	this.DragFinish = function(obDrag, x, y, e)
	{
		_this.HighlightGutter(this, false);
		this.className = this.className.replace(/\s*bx-drag-over/ig, "");

		//can't move to itself
		if(this == obDrag)
			return true;

		var tbl = BX(_this.table_id);
		var delta = 0;
		for(var i=0; i < 2; i++)
		{
			var cell = tbl.rows[1].cells[i];
			if(cell.className && (cell.className.indexOf('bx-actions-col') != -1 || cell.className.indexOf('bx-checkbox-col') != -1))
				delta ++;
		}

		var cols = [];
		for(var id in _this.oColsMeta)
			cols[cols.length] = id;

		var index_from = obDrag.cellIndex-delta;
		var index_to = this.cellIndex-delta;

		var tmp = cols[index_from];
		if(index_to < index_from)
		{
			for(i = index_from; i > index_to; i--)
				cols[i] = cols[i-1];
		}
		else
		{
			for(i = index_from; i < index_to; i++)
				cols[i] = cols[i+1];
		}
		cols[index_to] = tmp;

		var sCols = '';
		for(i=0; i<cols.length; i++)
			sCols += (sCols != ''? ',':'')+cols[i];

		_this.SaveColumns(sCols);
		return true;
	};

	/* Filter */

	this.InitFilter = function()
	{
		var row = BX('flt_header_'+this.table_id);
		if(row)
			jsUtils.addEvent(row, "contextmenu", this.OnRowContext);
	};

	this.SwitchFilterRow = function(row_id, menuItem)
	{
		if(menuItem)
		{
			var colMenu = this.menu.GetMenuByItemId(menuItem.id);
			colMenu.SetItemIcon(menuItem, (this.oFilterRows[row_id]? '':'checked'));
		}
		else
		{
			var mnu = this.filterMenu[0].MENU;
			for(var i=0; i<mnu.length; i++)
			{
				if(mnu[i].ID == 'flt_'+this.table_id+'_'+row_id)
				{
					mnu[i].ICONCLASS = (this.oFilterRows[row_id]? '':'checked');
					break;
				}
			}
		}

		var row = BX('flt_row_'+this.table_id+'_'+row_id);
		row.style.display = (this.oFilterRows[row_id]? 'none':'');
		this.oFilterRows[row_id] = (this.oFilterRows[row_id]? false:true);

		var a = BX('a_minmax_'+this.table_id);
		if(a && a.className.indexOf('bx-filter-max') != -1)
			this.SwitchFilter(a);

		this.SaveFilterRows();
	};

	this.SwitchFilterRows = function(on)
	{
		this.menu.PopupHide();

		var i=0;
		for(var id in this.oFilterRows)
		{
			i++;
			if(i == 1 && on == false)
				continue;
			this.oFilterRows[id] = on;
			var row = BX('flt_row_'+this.table_id+'_'+id);
			row.style.display = (on? '':'none');
		}

		var mnu = this.filterMenu[0].MENU;
		for(i=0; i<mnu.length; i++)
		{
			if(i == 0 && on == false)
				continue;
			if(mnu[i].SEPARATOR == true)
				break;
			mnu[i].ICONCLASS = (on? 'checked':'');
		}

		var a = BX('a_minmax_'+this.table_id);
		if(a && a.className.indexOf('bx-filter-max') != -1)
			this.SwitchFilter(a);

		this.SaveFilterRows();
	};

	this.SaveFilterRows = function()
	{
		var sRows = '';
		for(var id in this.oFilterRows)
			if(this.oFilterRows[id])
				sRows += (sRows!=''? ',':'')+id;

		BX.ajax.get('/bitrix/components'+this.vars.component_path+'/settings.php?GRID_ID='+this.table_id+'&action=filterrows&rows='+sRows+'&sessid='+this.vars.sessid);
	};

	this.SwitchFilter = function(a)
	{
		var on = (a.className.indexOf('bx-filter-min') != -1);
		a.className = (on? 'bx-filter-btn bx-filter-max' : 'bx-filter-btn bx-filter-min');
		a.title = (on? this.vars.mess.filterShow : this.vars.mess.filterHide);

		var row = BX('flt_content_'+this.table_id);
		row.style.display = (on? 'none':'');

		BX.ajax.get('/bitrix/components'+this.vars.component_path+'/settings.php?GRID_ID='+this.table_id+'&action=filterswitch&show='+(on? 'N':'Y')+'&sessid='+this.vars.sessid);
	};

	this.ClearFilter = function(form)
	{
		for(var i=0, n=form.elements.length; i<n; i++)
		{
			var el = form.elements[i];
			switch(el.type.toLowerCase())
			{
				case 'text':
				case 'textarea':
					el.value = '';
					break;
				case 'select-one':
					el.selectedIndex = 0;
					break;
				case 'select-multiple':
					for(var j=0, l=el.options.length; j<l; j++)
						el.options[j].selected = false;
					break;
				case 'checkbox':
					el.checked = false;
					break;
				default:
					break;
			}
			if(el.onchange)
				el.onchange();
		}

		BX.onCustomEvent(form, "onGridClearFilter", []);

		form.clear_filter.value = "Y";

		BX.submit(form);
	};

	this.ShowFilters = function()
	{
		var bCreated = false;
		if(!window['filtersDialog'+this.table_id])
		{
			var applyBtn = new BX.CWindowButton({
				'title': this.vars.mess.filtersApply,
				'hint': this.vars.mess.filtersApplyTitle,
				'action': function(){
					var form = document['filters_'+_this.table_id];
					if(form.filters_list.value)
						_this.ApplyFilter(form.filters_list.value);
					this.parentWindow.Close();
				}
			});

			window['filtersDialog'+this.table_id] = new BX.CDialog({
				'content':'<form name="filters_'+this.table_id+'"></form>',
				'title': this.vars.mess.filtersTitle,
				'buttons': [applyBtn, BX.CDialog.prototype.btnClose],
				'width': this.vars.filtersWndSize.width,
				'height': this.vars.filtersWndSize.height,
				'resize_id': 'InterfaceGridFiltersWnd'
			});

			bCreated = true;
		}

		window['filtersDialog'+this.table_id].Show();

		var form = document['filters_'+this.table_id];
		if(bCreated)
			form.appendChild(BX('filters_list_'+this.table_id));
	};

	this.AddFilter = function(fields)
	{
		if(!fields)
			fields = {};
		var filter_id = 'filter_'+Math.round(Math.random()*1000000);
		var filter = {'name':this.vars.mess.filtersNew, 'fields':fields};

		this.ShowFilterSettings(filter,
			function()
			{
				var data = _this.SaveFilter(filter_id);

				_this.oOptions.filters[filter_id] = {
					'name':data.name,
					'fields':data.fields
				};

				var form = document['filters_'+_this.table_id];
				form.filters_list.options[form.filters_list.length] = new Option((data.name != ''? data.name:_this.vars.mess.viewsNoName), filter_id, true, true);

				if(_this.filterMenu.length == 4) //no saved filters
					_this.filterMenu = BX.util.insertIntoArray(_this.filterMenu, 1, {'SEPARATOR':true});
				var mnuItem = {'ID': 'mnu_'+_this.table_id+'_'+filter_id, 'TEXT': BX.util.htmlspecialchars(data.name), 'TITLE': _this.vars.mess.ApplyTitle, 'ONCLICK':'bxGrid_'+_this.table_id+'.ApplyFilter(\''+filter_id+'\')'};
				_this.filterMenu = BX.util.insertIntoArray(_this.filterMenu, 2, mnuItem);
			}
		);
	};

	this.AddFilterAs = function()
	{
		var form = document.forms['filter_'+this.table_id];
		var fields = this.GetFilterFields(form);
		this.ShowFilters();
		this.AddFilter(fields);
	};

	this.EditFilter = function(filter_id)
	{
		this.ShowFilterSettings(this.oOptions.filters[filter_id],
			function()
			{
				var data = _this.SaveFilter(filter_id);

				_this.oOptions.filters[filter_id] = {
					'name':data.name,
					'fields':data.fields
				};

				var form = document['filters_'+_this.table_id];
				form.filters_list.options[form.filters_list.selectedIndex].text = (data.name != ''? data.name:_this.vars.mess.viewsNoName);

				for(var i=0, n=_this.filterMenu.length; i<n; i++)
				{
					if(_this.filterMenu[i].ID && _this.filterMenu[i].ID == 'mnu_'+_this.table_id+'_'+filter_id)
					{
						_this.filterMenu[i].TEXT = BX.util.htmlspecialchars(data.name);
						break;
					}
				}
			}
		);
	};

	this.DeleteFilter = function(filter_id)
	{
		if(!confirm(this.vars.mess.filtersDelete))
			return;

		var form = document['filters_'+this.table_id];
		var index = form.filters_list.selectedIndex;
		form.filters_list.remove(index);
		form.filters_list.selectedIndex = (index < form.filters_list.length? index : form.filters_list.length-1);

		for(var i=0, n=this.filterMenu.length; i<n; i++)
		{
			if(_this.filterMenu[i].ID && _this.filterMenu[i].ID == 'mnu_'+_this.table_id+'_'+filter_id)
			{
				this.filterMenu = BX.util.deleteFromArray(this.filterMenu, i);
				if(this.filterMenu.length == 5)
					this.filterMenu = BX.util.deleteFromArray(this.filterMenu, 1);
				break;
			}
		}

		delete this.oOptions.filters[filter_id];

		BX.ajax.get('/bitrix/components'+this.vars.component_path+'/settings.php?GRID_ID='+this.table_id+'&action=delfilter&filter_id='+filter_id+'&sessid='+_this.vars.sessid);
	};

	this.ShowFilterSettings = function(filter, action)
	{
		var bCreated = false;
		if(!window['filterSettingsDialog'+this.table_id])
		{
			window['filterSettingsDialog'+this.table_id] = new BX.CDialog({
				'content':'<form name="flt_settings_'+this.table_id+'"></form>',
				'title': this.vars.mess.filterSettingsTitle,
				'width': this.vars.filterSettingWndSize.width,
				'height': this.vars.filterSettingWndSize.height,
				'resize_id': 'InterfaceGridFilterSettingWnd'
			});
			bCreated = true;
		}

		window['filterSettingsDialog'+this.table_id].ClearButtons();
		window['filterSettingsDialog'+this.table_id].SetButtons([
			{
				'title': this.vars.mess.settingsSave,
				'action': function(){
					action();
					this.parentWindow.Close();
				}
			},
			BX.CDialog.prototype.btnCancel
		]);
		window['filterSettingsDialog'+this.table_id].Show();

		var form = document['flt_settings_'+this.table_id];

		if(bCreated)
			form.appendChild(BX('filter_settings_'+this.table_id));

		form.filter_name.focus();
		form.filter_name.value = filter.name;

		this.SetFilterFields(form, filter.fields);
	};

	this.SetFilterFields = function(form, fields)
	{
		for(var i=0, n = form.elements.length; i<n; i++)
		{
			var el = form.elements[i];

			if(el.name == 'filter_name')
				continue;

			var val = fields[el.name] || '';

			switch(el.type.toLowerCase())
			{
				case 'select-one':
				case 'text':
				case 'textarea':
					el.value = val;
					break;
				case 'radio':
				case 'checkbox':
					el.checked = (el.value == val);
					break;
				case 'select-multiple':
					var name = el.name.substr(0, el.name.length - 2);
					var bWasSelected = false;
					for(var j=0, l = el.options.length; j<l; j++)
					{
						var sel = (fields[name]? fields[name]['sel'+el.options[j].value] : null);
						el.options[j].selected = (el.options[j].value == sel);
						if(el.options[j].value == sel)
							bWasSelected = true;
					}
					if(!bWasSelected && el.options.length > 0 && el.options[0].value == '')
						el.options[0].selected = true;
					break;
				default:
					break;
			}
			if(el.onchange)
				el.onchange();
		}
	};

	this.GetFilterFields = function(form)
	{
		var fields = {};
		for(var i=0, n = form.elements.length; i<n; i++)
		{
			var el = form.elements[i];

			if(el.name == 'filter_name')
				continue;

			switch(el.type.toLowerCase())
			{
				case 'select-one':
				case 'text':
				case 'textarea':
					fields[el.name] = el.value;
					break;
				case 'radio':
				case 'checkbox':
					if(el.checked)
						fields[el.name] = el.value;
					break;
				case 'select-multiple':
					var name = el.name.substr(0, el.name.length - 2);
					fields[name] = {};
					for(var j=0, l = el.options.length; j<l; j++)
						if(el.options[j].selected)
							fields[name]['sel'+el.options[j].value] = el.options[j].value;
					break;
				default:
					break;
			}
		}
		return fields;
	};

	this.SaveFilter = function(filter_id)
	{
		var form = document['flt_settings_'+this.table_id];
		var data = {
			'GRID_ID': this.table_id,
			'filter_id': filter_id,
			'action': 'savefilter',
			'sessid': this.vars.sessid,
			'name': form.filter_name.value,
			'fields': this.GetFilterFields(form)
		};

		BX.ajax.post('/bitrix/components'+_this.vars.component_path+'/settings.php', data);

		return data;
	};

	this.ApplyFilter = function(filter_id)
	{
		var form = document.forms['filter_'+this.table_id];
		this.SetFilterFields(form, this.oOptions.filters[filter_id].fields);

		BX.submit(form);
	};

	this.OnDateChange = function(sel)
	{
		var bShowFrom=false, bShowTo=false, bShowHellip=false, bShowDays=false, bShowBr=false;

		if(sel.value == 'interval')
			bShowBr = bShowFrom = bShowTo = bShowHellip = true;
		else if(sel.value == 'before')
			bShowTo = true;
		else if(sel.value == 'after' || sel.value == 'exact')
			bShowFrom = true;
		else if(sel.value == 'days')
			bShowDays = true;

		BX.findNextSibling(sel, {'tag':'span', 'class':'bx-filter-from'}).style.display = (bShowFrom? '':'none');
		BX.findNextSibling(sel, {'tag':'span', 'class':'bx-filter-to'}).style.display = (bShowTo? '':'none');
		BX.findNextSibling(sel, {'tag':'span', 'class':'bx-filter-hellip'}).style.display = (bShowHellip? '':'none');
		BX.findNextSibling(sel, {'tag':'span', 'class':'bx-filter-days'}).style.display = (bShowDays? '':'none');
		var span = BX.findNextSibling(sel, {'tag':'span', 'class':'bx-filter-br'});
		if(span)
			span.style.display = (bShowBr? '':'none');
	};
}

