/* jshint ignore:start */
function BxUniversalGrid(table_id)
{
	"use strict";

	this.oColsMeta = {};
	this.oColsNames = {};
	this.customNames = {};
	this.columnsSizes = {};
	this.oEditData = {};
	this.oSaveData = {};
	this.oOptions = {};
	this.oVisibleCols = {};
	this.vars = {};
	this.menu = null;
	this.settingsMenu = [];
	this.filterMenu = [];
	this.checkBoxCount = 0;
	this.bColsChanged = false;
	this.bViewsChanged = false;
	this.oFilterRows = {};
	this.activeRow = null;
	this.hasActions = false;
	this.editMode = false;
	this.resizeMeta = {};
	this.table_id = table_id + '_table';
	this.grid_id = table_id;

	var _this = this;
	var Grid = null;
	var last_row = false;

	this.InitTable = function()
	{
		Grid = BX.Main.gridManager.getById(_this.grid_id).instance;
		this.checkBoxCount = 0;

		var table = Grid.getTable();

		if (!table || table.rows.length < 1 || table.rows[0].cells.length < 1)
		{
			return;
		}

		var cells = table.rows[0].cells;

		for (var i = 0; i < cells.length; i++)
		{
			if (BX.hasClass(cells[i], 'main-grid-cell-action') || BX.hasClass(cells[i], 'main-grid-cell-checkbox'))
			{
				cells[i].__fixed = true;
				continue;
			}

			var inode = BX.findChildByClassName(cells[i], 'main-grid-cell-head-container', false);
			BX.addClass(inode, 'main-grid-cell-head-dragable');
			BX.removeClass(cells[i], 'main-grid-cell-sortable');
		}

		this.initResizeMeta();
		this.toogleFader();
		BX.bind(window, 'resize', BX.delegate(this.toogleFader, this));
		BX.bind(table.parentNode, 'scroll', this.toogleFader);

		if (Grid.getParam('ALLOW_COLUMNS_RESIZE'))
		{
			for (var i = 0; i < cells.length; i++)
			{
				if (cells[i].__fixed)
				{
					continue;
				}

				var rhook = BX.findChildByClassName(cells[i], 'main-grid-resize-button');
				if (rhook)
				{
					rhook.onbxdragstart = _this.resizeColumnStart;
					rhook.onbxdragstop = _this.resizeColumnStop;
					rhook.onbxdrag = _this.resizeColumn;

					jsDD.registerObject(rhook);
				}
			}

			var registerPinnedTableButtons = function() {
				var table = Grid.getPinHeader().getFixedTable();
				var buttons = BX.findChild(table, {class: 'main-grid-resize-button'}, true, true);

				buttons.forEach(function(current) {
					current.onbxdragstart = _this.resizeColumnStart;
					current.onbxdragstop = _this.resizeColumnStop;
					current.onbxdrag = _this.resizeColumn;
					jsDD.registerObject(current);
				});
			};

			BX.addCustomEvent(window, 'Grid::headerPinned', registerPinnedTableButtons);
			BX.addCustomEvent(window, 'Grid::updated', registerPinnedTableButtons);
		}
	};

	//noinspection JSUnusedGlobalSymbols
	this.CheckColumn = function(name, menuItem)
	{
		var columns;
		var colMenu = this.menu.GetMenuByItemId(menuItem.id);
		var bShow = !(colMenu.GetItemInfo(menuItem).ICON == 'checked');
		colMenu.SetItemIcon(menuItem, (bShow? 'checked':''));


		if (name)
		{
			columns = Grid.getUserOptions().getCurrentOptions().columns.split(',');

			if (BX.type.isArray(columns) && BX.type.isNotEmptyString(name))
			{
				if (columns.some(function(current) { return current === name; }))
				{
					columns = columns.filter(function(current) {
						return current !== name;
					});

					BX.removeClass('menu_'+Grid.getContainerId()+'_columns_item_', 'checked');
				}
				else
				{
					columns.unshift(name);

					BX.addClass('menu_'+Grid.getContainerId()+'_columns_item_', 'checked');
				}
			}

			Grid.tableFade();

			Grid.getUserOptions().setColumns(columns, function() {
				Grid.reloadTable();
				BX.onCustomEvent(Grid.getContainer(), 'Grid::columnsChanged', [Grid]);
			});
		}


	};

	this.reinitColumnSize = function(column, temp)
	{
		var colId = this.GetColumnId(column);

		if (colId !== false)
		{
			var table  = BX(this.table_id);
			var twidth = table.offsetWidth;

			var cell = table.rows[0].cells[colId];
			var node = BX.findChildByClassName(cell, 'main-grid-cell-head-container', false);

			node.style.height = '';
			BX.removeClass(node, 'main-grid-cell-head-dragable');

			cell.style.width = '';
			cell.style.width = cell.offsetWidth+'px';

			BX.addClass(node, 'main-grid-cell-head-dragable');
			node.style.height = table.rows[0].cells[0].clientHeight+'px';

			if (temp)
			{
				cell.setAttribute('data-resize', 1);
			}
		}
	};

	this.initResizeMeta = function()
	{
		var table = Grid.getTable();
		var cells = table.rows[0].cells;

		this.resizeMeta.fixed = 0;
		this.resizeMeta.minPx = table.offsetWidth;

		var sizesChanged = false;
		for (var i = 0; i < cells.length; i++)
		{
			var width = BX.width(cells[i]);

			if (cells[i].__fixed)
			{
				this.resizeMeta.fixed += width;
				continue;
			}

			if (width > 0)
			{
				var name = cells[i].getAttribute('data-name');

				if (this.resizeMeta.columns[name] != width)
				{
					sizesChanged = true;
				}

				this.resizeMeta.columns[name] = width;
			}
		}

		if (sizesChanged)
		{
			this.resizeMeta.expand = 1;
		}
	};

	this.reinitResizeMeta = function()
	{
		var table = BX(_this.table_id);
		var cells = table.rows[0].cells;

		for (var i = 0; i < cells.length; i++)
		{
			if (cells[i].__fixed)
			{
				continue;
			}

			if (cells[i].offsetWidth > 0)
			{
				var name = cells[i].getAttribute('data-name');

				_this.resizeMeta.columns[name] = cells[i].offsetWidth;
			}
		}

		var twidth = table.offsetWidth;
		var pwidth = table.parentNode.clientWidth;

		_this.resizeMeta.minPx  = twidth;
		_this.resizeMeta.expand = twidth < pwidth ? (twidth / pwidth) : 1;

		_this.saveColumnsSizes();
	};

	this.toogleFader = function()
	{
		var Grid = BX.Main.gridManager.getById(_this.grid_id).instance;
		var table = Grid.getTable();
		var parent = Grid.getScrollContainer();

		if (table.offsetWidth > parent.clientWidth)
		{
			if (parent.scrollLeft > 0)
			{
				BX.addClass(parent.parentNode, 'main-grid-fade-left');
			}
			else
			{
				BX.removeClass(parent.parentNode, 'main-grid-fade-left');
			}

			if (table.offsetWidth > parent.scrollLeft+parent.clientWidth)
			{
				BX.addClass(parent.parentNode, 'main-grid-fade-right');
			}
			else
			{
				BX.removeClass(parent.parentNode, 'main-grid-fade-right');
			}
		}
		else
		{
			BX.removeClass(parent.parentNode, 'main-grid-fade-left');
			BX.removeClass(parent.parentNode, 'main-grid-fade-right');
		}
	};

	this.OnRowContext = function(e) {
		if (!_this.menu) {
			return;
		}

		if (!e) {
			e = window.event;
		}
		if (!phpVars.opt_context_ctrl && e.ctrlKey || phpVars.opt_context_ctrl && !e.ctrlKey) {
			return;
		}

		var targetElement;
		if (e.target) {
			targetElement = e.target;
		}
		else if (e.srcElement) {
			targetElement = e.srcElement;
		}

		//column context menu
		var el = targetElement;
		while (el && !(el.tagName && el.tagName.match(/(th|td)/i) && el.oncontextmenu)) {
			el = BX.findParent(el, {tagName: /(th|td)/i});
		}

		var col_menu = null;
		if (el && el.oncontextmenu) {
			col_menu = el.oncontextmenu();
			col_menu[col_menu.length] = {'SEPARATOR': true};
		}

		//row context menu
		el = targetElement;
		while (el && !(el.tagName && el.tagName.toUpperCase() === 'TR' && el.oncontextmenu)) {
			el = jsUtils.FindParentObject(el, "tr");
		}

		var menu = _this.menu;
		menu.PopupHide();

		_this.activeRow = el;
		if (_this.activeRow && !BX.hasClass(el, 'main-grid-row-head'))
		{
			_this.activeRow.className += ' active';
		}

		menu.OnClose = function()
		{
			if(_this.activeRow)
			{
				_this.activeRow.className = _this.activeRow.className.replace(/\s*active/i, '');
				_this.activeRow = null;
			}
		};

		//combined menu
		var menuItems = BX.util.array_merge(col_menu, el.oncontextmenu());
		if(menuItems.length == 0)
		{
			return;
		}

		menu.SetItems(menuItems);
		menu.BuildItems();

		var arScroll = jsUtils.GetWindowScrollPos();
		var x = e.clientX + arScroll.scrollLeft;
		var y = e.clientY + arScroll.scrollTop;
		var pos = {};
		pos.left = pos.right = x;
		pos.top = pos.bottom = y;

		menu.PopupShow(pos);

		e.returnValue = false;
		if(e.preventDefault)
		{
			e.preventDefault();
		}
	};

	this.ShowActionMenu = function(el, index)
	{
		_this.menu.PopupHide();

		_this.activeRow = jsUtils.FindParentObject(el, 'tr');
		if(_this.activeRow)
		{
			_this.activeRow.className += ' active';
		}

		var row = BX('datarow_'+this.table_id+'_'+index);
		var actionItems = BX.data(row, 'actions');

		if (row && actionItems)
		{
			var items = JSON.parse(actionItems);
			if (items && items.length > 0)
			{
				_this.menu.ShowMenu(el, items, false, false,
					function()
					{
						if(_this.activeRow)
						{
							_this.activeRow.className = _this.activeRow.className.replace(/\s*active/i, '');
							_this.activeRow = null;
						}
					}
				);
			}
		}

	};

	this.SelectRow = function(checkbox, e)
	{
		e = e ? e : window.event;

		var row = BX.findParent(checkbox, { className: 'main-grid-row' });
		var span = document.getElementById(this.table_id+'_selected_span');
		var selCount = parseInt(span.innerHTML);

		var rows = [row];

		if (e && e.shiftKey && last_row && last_row !== row)
		{
			var tbl = document.getElementById(this.table_id);

			for (var i = Math.min(last_row.rowIndex, row.rowIndex)+1; i < Math.max(last_row.rowIndex, row.rowIndex); i++)
			{
				rows.push(tbl.rows[i]);
			}

			if (BX.findChildByClassName(last_row.cells[0], 'main-grid-checkbox').checked)
			{
				rows.push(last_row);
			}
		}

		for (var i in rows)
		{
			var row_checkbox = BX.findChildByClassName(rows[i].cells[0], 'main-grid-checkbox');
			if (row_checkbox && (checkbox === row_checkbox || row_checkbox.checked !== checkbox.checked))
			{
				row_checkbox.checked = checkbox.checked;
				if (checkbox.checked)
				{
					BX.addClass(rows[i], 'main-grid-row-checked');
					selCount++;
				}
				else
				{
					BX.removeClass(rows[i], 'main-grid-row-checked');
					selCount--;
				}
			}
		}

		span.innerHTML = selCount.toString();

		var checkAll = BX(this.table_id+'_check_all');
		checkAll.checked = this.checkBoxCount > 0 && selCount === this.checkBoxCount;

		last_row = row;
	};

	this.SelectAllRows = function(checkbox) {};

	this.EnableActions = function()
	{
		var form = document.forms['form_'+this.table_id];
		if (!form) return;

		var bEnabled     = this.IsActionEnabled();
		var bEnabledEdit = this.IsActionEnabled('edit');

		var editButton   = BX('edit_button_'+this.table_id);
		var deleteButton = BX('delete_button_'+this.table_id);

		if (form.apply)
		{
			form.apply.disabled = !bEnabled;
			BX[bEnabled ? 'removeClass' : 'addClass'](form.apply, 'webform-button-disable');
		}
		if (editButton)
			BX[bEnabledEdit ? 'removeClass' : 'addClass'](editButton, 'main-grid-control-panel-action-icon-disable');
		if (deleteButton)
			BX[bEnabled ? 'removeClass' : 'addClass'](deleteButton, 'main-grid-control-panel-action-icon-disable');
	};

	/**
	 * @return {boolean}
	 */
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
		var buttons = BX('bx_grid_'+this.table_id+'_action_buttons');

		var div = buttons;
		while(div = jsUtils.FindNextSibling(div, 'div'))
			div.style.display = (bShow ? 'none' : '');

		buttons.style.display = bShow ? '' : 'none';
	};

	this.ActionEdit = function()
	{
		if(this.IsActionEnabled('edit'))
		{
			var form = document.forms['form_'+this.table_id];
			if(!form)
				return;

			this.editMode = true;

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
					if(BX.hasClass(td, 'main-grid-cell-action'))
						td = jsUtils.FindNextSibling(td, "td");

					var row_id = el.value;
					this.oSaveData[row_id] = {};
					for(var col_id in this.oVisibleCols)
					{
						if(this.oVisibleCols[col_id] == true && this.oColsMeta[col_id].editable == true && this.oEditData[row_id][col_id] !== false)
						{
							this.oSaveData[row_id][col_id] = td.innerHTML;
							td.innerHTML = '';

							//insert controls
							var data = this.oEditData[row_id][col_id];
							var name = 'FIELDS['+row_id+']['+col_id+']';
							var span = BX.create('SPAN', {'props': {'className': 'main-grid-cell-content'}});
							switch(this.oColsMeta[col_id].type)
							{
								case 'checkbox':
									span.appendChild(BX.create('INPUT', {'props': {
										'type': 'hidden',
										'name': name,
										'value': 'N'
									}}));
									span.appendChild(BX.create('INPUT', {'props': {
										'className': 'main-grid-cell-content-edit',
										'type': 'checkbox',
										'name': name,
										'value': 'Y',
										'checked': data == 'Y',
										'defaultChecked': data == 'Y'
									}}));
									break;
								case 'list':
									var options = [];
									for (var list_val in this.oColsMeta[col_id].items)
									{
										options[options.length] = BX.create('OPTION', {
											'props': {
												'value': list_val,
												'selected': list_val == data
											},
											'text': this.oColsMeta[col_id].items[list_val]
										});
									}

									span.appendChild(BX.create('SELECT', {
										'props': {
											'className': 'main-grid-cell-content-edit',
											'name': name
										},
										'children': options
									}));
									break;
								case 'date':
									var params = {
										'props': {
											'className': 'main-grid-cell-content-edit',
											'type': 'text',
											'name': name,
											'value': data,
											'size': this.oColsMeta[col_id].size ? this.oColsMeta[col_id].size : 20
										},
										'style': {'paddingRight': '20px'}
									};
									if (this.oColsMeta[col_id].size)
										params.props.size = this.oColsMeta[col_id].size;
									else
										params.style.width = '100%';

									span.appendChild(BX.create('INPUT', params));
									span.appendChild(BX.create('A', {
										'props': {
											'href':'javascript:void(0);',
											'title': this.vars.mess.calend_title
										},
										'style': {
											'border': 'none',
											'position': 'relative',
											'right': '22px'
										},
										'children': [
											BX.create('IMG', {'props': {
												'className': 'calendar-icon',
												'src': this.vars.calendar_image,
												'alt': this.vars.mess.calend_title,
												'onclick': (function(field) {
													return function() {
														BX.calendar({
															'node': this,
															'field': field,
															'bTime': true,
															'currentTime': _this.vars.server_time
														});
													};
												})(name),
												'onmouseover': function() {
													BX.addClass(this, 'calendar-icon-hover');
												},
												'onmouseout': function() {
													BX.removeClass(this, 'calendar-icon-hover');
												}
											}})
										]
									}));
									BX.addClass(span, 'main-grid-cell-text-line');
									break;
								case 'textarea':
									var params = {
										'props': {
											'className': 'main-grid-cell-content-edit',
											'name': name
										},
										'text': data
									};

									if (this.oColsMeta[col_id].cols)
										params.props.cols = this.oColsMeta[col_id].cols;
									else
										params.style = {'width': '100%'};

									if (this.oColsMeta[col_id].rows)
										params.props.rows = this.oColsMeta[col_id].rows;

									if (this.oColsMeta[col_id].maxlength)
										params.props.maxLength = this.oColsMeta[col_id].maxlength;

									span.appendChild(BX.create('TEXTAREA', params));
									break;
								case 'file':
									span.appendChild(BX.create('INPUT', {'props': {
										'className': 'main-grid-cell-content-edit',
										'type': 'file',
										'name': name
									}}));
									break;
								default:
									var params = {'props': {
										'className': 'main-grid-cell-content-edit',
										'type': 'text',
										'name': name,
										'value': data
									}};

									if (this.oColsMeta[col_id].size)
										params.props.size = this.oColsMeta[col_id].size;
									else
										params.style = {'width': '100%'};

									if (this.oColsMeta[col_id].maxlength)
										params.props.maxLength = this.oColsMeta[col_id].maxlength;

									span.appendChild(BX.create('INPUT', params));
									break;
							}

							td.appendChild(span);
						}
						td = jsUtils.FindNextSibling(td, "td");
					}
				}
				el.disabled = true;
			}

			BX(this.table_id+'_check_all').disabled = true;

			form.elements['action_button_'+this.table_id].value = 'edit';
		}
	};

	this.ActionCancel = function()
	{
		var form = document.forms['form_'+this.table_id];
		if(!form)
			return;

		this.editMode = false;

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
				if(BX.hasClass(td, 'main-grid-cell-action'))
					td = jsUtils.FindNextSibling(td, "td");

				var row_id = el.value;
				for(var col_id in this.oVisibleCols)
				{
					if(typeof this.oSaveData[row_id][col_id] != 'undefined')
						td.innerHTML = this.oSaveData[row_id][col_id];

					td = jsUtils.FindNextSibling(td, "td");
				}
			}
		}

		this.toggleCheckboxes();

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
			{
				if (ids[i].getAttribute('data-disabled'))
					continue;
				ids[i].disabled = el.checked;
			}
		}

		BX(this.table_id+'_check_all').disabled = el.checked;

		this.editMode = el.checked;

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
			order = (bControl? (def_order == 'acs'? 'desc':'asc') : def_order);
		}
		else if(sort_state == 'asc')
			order = 'desc';
		else
			order = 'asc';

		url += order;

		BX.ajax.get('/bitrix/components'+_this.vars.component_path+'/settings.ajax.php?GRID_ID='+_this.table_id+'&action=savesort&by='+by+'&order='+order+'&sessid='+_this.vars.sessid, function(){
			_this.Reload(url);
		});
	};

	/**
	 * @return {boolean}
	 */
	this.GetColumnId = function(column)
	{
		var colId = false;
		var tbl = BX(this.table_id);
		for (var i = 0; i < tbl.rows[0].cells.length; i++)
		{
			if (tbl.rows[0].cells[i].getAttribute('data-name') == column)
			{
				colId = i;
				break;
			}
		}

		return colId;
	};


	this.animation = function(node, props, duration, callback)
	{
		_this.animation.stop(node);

		var iteration = function()
		{
			node.__animation.step++;

			var animation = node.__animation;
			for (var i in animation.props)
			{
				var params = animation.props[i];
				node.style[i] = params.from*1 + (params.to-params.from) * animation.step / animation.steps + params.unit;
			}

			if (animation.step >= animation.steps)
				_this.animation.stop(node);
		};

		node.__animation = {
			'props': props,
			'steps': Math.round(duration/10),
			'step': 0,
			'callback': callback
		};

		node.__animation.interval = setInterval(iteration, duration/node.__animation.steps);
	};

	this.animation.stop = function(node)
	{
		if (node.__animation && node.__animation.interval)
		{
			node.__animation.interval = clearInterval(node.__animation.interval);
			if (node.__animation.callback)
				node.__animation.callback();
		}
	};

	this.toogleColumnCells = function(cid, show, fix)
	{
		var table = BX(_this.table_id);

		for (var i = 0; i < table.rows.length; i++)
		{
			var cells = table.rows[i].cells;
			if (cells[cid])
			{
				cells[cid].style.display = show ? '' : 'none';

				var content = BX.findChildByClassName(cells[cid], 'main-grid-cell-content', false);
				if (content && content.style)
					content.style.width = fix ? content.clientWidth+'px' : ''; // @TODO: clientWidth includes paddings
			}
		}
	};


	this.toggleCheckboxes = function()
	{
		var table = BX(this.table_id);

		for (var i = 1; i < table.rows.length; i++)
		{
			if (BX.hasClass(table.rows[i], 'main-grid-data-row'))
			{
				var checkbox = BX.findChildByClassName(table.rows[i].cells[0], 'main-grid-checkbox');
				var data_id = checkbox.value;

				var editable = false;

				if (this.hasActions)
				{
					if (!checkbox.getAttribute('data-disabled'))
						editable = true;
				}
				else if (typeof this.oEditData[data_id] != 'undefined')
				{
					for (var j in this.oVisibleCols)
					{
						if (this.oVisibleCols[j] && this.oColsMeta[j].editable && this.oEditData[data_id][j] !== false)
							editable = true;
					}
				}

				if (editable)
				{
					if (checkbox.getAttribute('data-disabled'))
						this.checkBoxCount++;
					checkbox.disabled = this.editMode;
					checkbox.removeAttribute('data-disabled');
				}
				else
				{
					if (!checkbox.getAttribute('data-disabled'))
						this.checkBoxCount--;
					checkbox.disabled = true;
					checkbox.setAttribute('data-disabled', 1);
				}
			}
		}

		if (this.checkBoxCount == 0)
		{
			BX(this.table_id+'_check_all').disabled = true;
			if (!this.editMode)
			{
				for (var i = 1; i < table.rows.length; i++)
				{
					if (BX.hasClass(table.rows[i], 'main-grid-data-row'))
					{
						var checkbox = BX.findChildByClassName(table.rows[i].cells[0], 'main-grid-checkbox');

						if (checkbox.checked)
						{
							checkbox.checked = false;
							this.SelectRow(checkbox);
						}
					}
				}
				BX(this.table_id+'_action_bar_fade').style.display = '';
			}
		}
		else
		{
			if (!this.editMode)
				BX(this.table_id+'_check_all').disabled = false;
			BX(this.table_id+'_action_bar_fade').style.display = 'none';
		}
	};

	this.SaveColumns = function(columns, callback)
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

		_this.oOptions.views[_this.oOptions.current_view].columns = sCols; // @TODO: sync

		if (_this.vars.user_authorized)
		{
			BX.ajax.get(
				'/bitrix/components'+_this.vars.component_path+'/settings.ajax.php',
				{
					GRID_ID: _this.table_id,
					action: 'showcolumns',
					columns: sCols,
					sessid: _this.vars.sessid
				},
				callback
			);
		}
	};

	this.saveColumnsSizes = function()
	{
		Grid.getUserOptions().setColumnSizes(_this.resizeMeta.columns, _this.resizeMeta.expand);
	};

	this.Reload = function(url)
	{
		var Grid = BX.Main.gridManager.getById(_this.grid_id).instance;
		var container = Grid.getScrollContainer();
		var request;

		jsDD.Disable();

		if(!url)
		{
			url = this.vars.current_url[this.vars.current_url.length-1];
		}

		if(this.vars.ajax.AJAX_ID != '')
		{
			request = BX.ajax.insertToNode(url+(url.indexOf('?') == -1? '?':'&')+'bxajaxid='+this.vars.ajax.AJAX_ID, 'comp_'+this.vars.ajax.AJAX_ID);
			request.onprogress = function()
			{
				_this.scrollContainerLeft = container.scrollLeft;
			};

			request.onload = function()
			{
				Grid.getScrollContainer().scrollLeft = _this.scrollContainerLeft;
				Grid.reloadTable();
			};
		}
		else
		{
			window.location = url;
		}
	};

	this.SetView = function(view_id)
	{
		var filter_id = _this.oOptions.views[view_id].saved_filter;
		var func = (filter_id && _this.oOptions.filters[filter_id]?
			function(){_this.ApplyFilter(filter_id)} :
			function(){_this.Reload()});

		BX.ajax.get('/bitrix/components'+_this.vars.component_path+'/settings.ajax.php?GRID_ID='+_this.table_id+'&action=setview&view_id='+view_id+'&sessid='+_this.vars.sessid, func);
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
					'custom_names': data.custom_names,
					'columns_sizes': data.columns_sizes
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
					'custom_names': data.custom_names,
					'columns_sizes': data.columns_sizes
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

		BX.ajax.get('/bitrix/components'+this.vars.component_path+'/settings.ajax.php?GRID_ID='+this.table_id+'&action=delview&view_id='+view_id+'&sessid='+_this.vars.sessid);
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
		this.columnsSizes = view.columns_sizes ? view.columns_sizes : {};

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
			for (var i in this.oVisibleCols)
			{
				if (this.oVisibleCols[i])
					aVisCols[aVisCols.length] = i;
			}
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
				var colName = this.customNames[i] ? (this.customNames[i]+' ('+this.oColsNames[i]+')') : this.oColsNames[i];
				form.view_all_cols.options[form.view_all_cols.length] = new Option(colName, i, false, false);
			}
		}

		//visible cols
		jsSelectUtils.deleteAllOptions(form.view_cols);
		for(i in oVisCols)
		{
			colName = this.customNames[i] ? (this.customNames[i]+' ('+this.oColsNames[i]+')') : this.oColsNames[i];
			form.view_cols.options[form.view_cols.length] = new Option(colName, i, false, false);
		}

		form.reset_columns_sizes.checked = false;

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
								value = value+' ('+_this.oColsNames[selectedCol]+')';
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
			'GRID_ID': this.grid_id,
			'view_id': view_id,
			'action': Grid.getUserOptions().getAction('GRID_SAVE_SETTINGS'),
			'sessid': this.vars.sessid,
			'name': form.view_name.value,
			'columns': sCols,
			'sort_by': form.view_sort_by.value,
			'sort_order': form.view_sort_order.value,
			'page_size': form.view_page_size.value,
			'saved_filter': form.view_filters.value,
			'custom_names': this.customNames,
			'columns_sizes': !form.reset_columns_sizes.checked ? this.columnsSizes : {}
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
					Grid.reloadTable();
				}
			};
		}

		BX.ajax.post('/bitrix/components'+_this.vars.component_path+'/settings.ajax.php', data, handler);

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
		_this.onDragStart(this);

		var Grid = BX.Main.gridManager.getById(_this.grid_id).instance;
		var container = Grid.getContainer();
		var table = Grid.getTable();
		var cells = Grid.getRows().getHeadChild()[0].getCells();
		var cellPosition = BX.pos(this);
		var containerPosition = BX.pos(container);
		var cellLeft = cellPosition.left - containerPosition.left;
		var node = BX.findChildByClassName(this, 'main-grid-cell-head-container', false);

		this.__cursor = BX.create('div', {props: {className: Grid.settings.get('classCursor')}});
		container.appendChild(this.__cursor);

		node.style.width = this.clientWidth+'px';
		node.style.height = this.clientHeight+'px';



		this.__dragNode = document.createElement('DIV');
		this.__dragNode.className = 'main-grid-cell-head-drag';
		this.__dragNode.appendChild(node);

		BX.style(this.__dragNode, 'left', cellLeft + 'px');
		table.parentNode.insertBefore(this.__dragNode, table);

		this.style.height = cellPosition.height+'px';
		this.innerHTML = '<span class="main-grid-cell-head-container" style="z-index: 25; "></span>';
		BX.addClass(this, 'main-grid-cell-head-drag-dest');

		this.__dragDest = this.cellIndex;
		this.__dragX = cellLeft-jsDD.start_x;

		this.__dragCells = [];
		for (var i = 0; i < cells.length; i++)
		{
			if (cells[i].__fixed) continue;

			var ipos = BX.pos(cells[i]);

			if (!ipos.width) continue;

			var inode = BX.findChildByClassName(cells[i], 'main-grid-cell-head-container', false);

			BX.addClass(cells[i], 'main-grid-cell-head-ondrag');
			this.__cursor.style.left = (ipos.left-containerPosition.left)+'px';

			if (i == this.cellIndex)
			{
				inode.style.height   = (ipos.height-2)+'px';
				this.__dragCells.ref = this.__dragCells.length;
			}

			this.__dragCells.push({ index: i, node: inode, offset: ipos.left-containerPosition.left, width: ipos.width, move: 0 });
		}

		if (this.__dragCells.length > 1 && this.__dragCells.ref == this.__dragCells.length-1)
		{
			var index = this.__dragCells[this.__dragCells.length-2].index;
			BX.addClass(cells[index], 'main-grid-cell-last');
		}
	};

	this.Drag = function(x)
	{
		var self = this;
		var Grid = BX.Main.gridManager.getById(_this.grid_id).instance;
		var container = Grid.getContainer();
		var dragX = (x+this.__dragX);
		var containerPosition = BX.pos(container);
		var dragDestNode, destNode, offset;

		this.__dragNode.style.left = (dragX + 'px');

		for (var i = this.__dragCells.length-1; i >= 0; i--)
		{
			if ((x-containerPosition.left) > this.__dragCells[i].offset)
			{
				this.__dest = this.__dragCells[i].index;
				break;
			}
		}

		dragDestNode = this.__dragCells.filter(function(current) {
			return current.index == self.__dragDest;
		})[0];

		destNode = this.__dragCells.filter(function(current) {
			return current.index == self.__dest;
		})[0];

		offset = destNode.offset;

		if (this.__dest > this.__dragDest)
		{
			offset = (destNode.offset + destNode.width);
		}

		dragDestNode.node.style.left = offset+'px';
		this.__cursor.style.left = offset+'px';
	};

	this.DragStop = function()
	{
		_this.onDragStop(this);

		var self = this;
		var Grid = BX.Main.gridManager.getById(_this.grid_id).instance;
		var rows = Grid.getRows().getList();
		var cells, dragCell, destCell, name, columns;

		var node = BX.findChildByClassName(this.__dragNode, Grid.settings.get('classCellHeadContainer'), false);
		this.innerHTML = '';
		this.appendChild(node);
		this.style.height = '';

		BX.removeClass(this, 'main-grid-cell-head-drag-dest');
		BX.remove(this.__cursor);
		BX.remove(this.__dragNode);

		rows.map(function(currentRow) {
			cells = currentRow.getCells();
			dragCell = cells[self.__dragDest];
			destCell = cells[self.__dest];

			if (currentRow.isHeadChild() || currentRow.isBodyChild() || currentRow.isFootChild())
			{
				if (self.__dest < self.__dragDest)
				{
					currentRow.getNode().insertBefore(dragCell, destCell);
				}

				if (self.__dest > self.__dragDest)
				{
					if (BX.type.isDomNode(destCell.nextElementSibling))
					{
						currentRow.getNode().insertBefore(dragCell, destCell.nextElementSibling);
					}
					else
					{
						currentRow.getNode().appendChild(dragCell);
					}
				}

				for (var i = 0; i < cells.length; i++)
				{
					var inode = BX.findChildByClassName(cells[i], Grid.settings.get('classCellHeadContainer'), false);
					if (inode && inode.style)
					{
						inode.style.width = '';
						inode.style.left  = '';
					}

					BX.removeClass(cells[i], Grid.settings.get('classCellHeadOndrag'));
				}
			}
		});

		if (this.__dest !== this.__dragDest)
		{
			cells = Grid.getRows().getHeadChild()[0].getCells();
			columns = _this.oVisibleCols;
			_this.oVisibleCols = {};

			[].forEach.call(cells, function(current) {
				name = BX.data(current, 'name');
				if (name)
				{
					_this.oVisibleCols[name] = columns[name];
				}
			});

			_this.bColsChanged = true;
			_this.SaveColumns();
		}
	};

	this.resizeColumnStart = function()
	{
		var cell = BX.findParent(this, { className: 'main-grid-cell-head' });
		var cells = Grid.getRows().getHeadFirstChild().getCells();
		var cellsKeys = Object.keys(cells);
		var cellContainer;
		_this.onDragStart(cell);
		this.__overlay = BX.create('div', {props: {className: 'main-grid-cell-overlay'}});
		BX.append(this.__overlay, cell);
		this.__resizeCell = cell.cellIndex;

		cellsKeys.forEach(function(key) {
			if (BX.hasClass(cells[key], 'main-grid-special-empty'))
			{
				BX.style(cells[key], 'width', '100%');
			}
			else
			{
				BX.width(cells[key], BX.width(cells[key]));
				cellContainer = BX.firstChild(cells[key]);
				BX.width(cellContainer, BX.width(cells[key]));
			}
		});
	};

	this.resizeColumn = function(x)
	{
		var table = BX(_this.table_id);
		var fixedTable = Grid.getPinHeader().getFixedTable();
		var cell = table.rows[0].cells[this.__resizeCell];
		var fixedCell, fixedCellContainer;

		var tpos = BX.pos(table);
		var cpos = BX.pos(cell);
		var cellContainer = BX.firstChild(cell);
		var cellAttrWidth = parseFloat(cell.style.width);
		var sX;


		x -= cpos.left;
		sX = x;

		if (cpos.width > cellAttrWidth)
		{
			x = cpos.width;
		}

		x = sX > x ? sX : x;

		if (x !== cpos.width)
		{
			cell.style.width = x+'px';
			cellContainer.style.width = x+'px';

			if (BX.type.isDomNode(fixedTable) && BX.type.isDomNode(fixedTable.rows[0]))
			{
				fixedCell = fixedTable.rows[0].cells[this.__resizeCell];
				fixedCellContainer = BX.firstChild(fixedCell);
				fixedCellContainer.style.width = x+'px';
				fixedCell.style.width = x+'px';
			}
		}


		_this.toogleFader();
	};

	this.resizeColumnStop = function()
	{
		var table = BX(_this.table_id);
		var cells = table.rows[0].cells;

		_this.reinitResizeMeta();
		_this.toogleFader();

		_this.onDragStop(cells[this.__resizeCell]);
		BX.remove(this.__resizeCell);
	};

	this.onDragStart = function(el)
	{
		if (el.getAttribute('onclick'))
		{
			el.setAttribute('data-onclick', el.getAttribute('onclick'));
			el.removeAttribute('onclick');
		}
	};

	this.onDragStop = function(el)
	{
		if (el.getAttribute('data-onclick'))
		{
			setTimeout(function() {
				el.setAttribute('onclick', el.getAttribute('data-onclick'));
				el.removeAttribute('data-onclick');
			}, 10);
		}
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

		BX.ajax.get('/bitrix/components'+this.vars.component_path+'/settings.ajax.php?GRID_ID='+this.table_id+'&action=filterrows&rows='+sRows+'&sessid='+this.vars.sessid);
	};

	this.SwitchFilter = function(a)
	{
		var on = (a.className.indexOf('bx-filter-min') != -1);
		a.className = (on? 'bx-filter-btn bx-filter-max' : 'bx-filter-btn bx-filter-min');
		a.title = (on? this.vars.mess.filterShow : this.vars.mess.filterHide);

		var row = BX('flt_content_'+this.table_id);
		row.style.display = (on? 'none':'');

		BX.ajax.get('/bitrix/components'+this.vars.component_path+'/settings.ajax.php?GRID_ID='+this.table_id+'&action=filterswitch&show='+(on? 'N':'Y')+'&sessid='+this.vars.sessid);
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

		BX.ajax.get('/bitrix/components'+this.vars.component_path+'/settings.ajax.php?GRID_ID='+this.table_id+'&action=delfilter&filter_id='+filter_id+'&sessid='+_this.vars.sessid);
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

		BX.ajax.post('/bitrix/components'+_this.vars.component_path+'/settings.ajax.php', data);

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

	this.loadColumn = function(column)
	{
		var table = BX(this.table_id);
		var colId = this.GetColumnId(column);
		var colHead = table.rows[0].cells[colId];

		colHead.removeAttribute('data-empty');
		if (colHead.getAttribute('data-resize'))
			jsDD.Disable();

		var results = [];
		var callback = function(json)
		{
			results.push(json);

			for (var i in json.edit)
			{
				if (typeof json.edit[i][column] != 'undefined')
				{
					if (typeof _this.oEditData[i] == 'undefined')
						_this.oEditData[i] = {};
					_this.oEditData[i][column] = json.edit[i][column];
				}
			}

			if (results.length < _this.vars.current_url.length)
				return;

			for (var i in results)
			{
				for (var j in results[i].data)
				{
					var cell = BX('datarow_'+_this.table_id+'_'+j).cells[colId];
					BX.findChildByClassName(cell, 'main-grid-cell-content').innerHTML = results[i].data[j][column];
				}
			}

			if (colHead.getAttribute('data-resize'))
			{
				colHead.removeAttribute('data-resize');

				var twidth    = table.offsetWidth;
				var widthFrom = colHead.offsetWidth;

				_this.animation.stop(colHead);

				if (colHead.offsetWidth > 0)
				{
					_this.reinitColumnSize(column);

					var widthTo = colHead.offsetWidth;
					colHead.style.width = widthFrom+'px';

					_this.toogleColumnCells(colId, true, true);

					var cellProps = {'width': {'from': widthFrom, 'to': widthTo, 'unit': 'px'}};
					_this.animation(colHead, cellProps, 100, function()
					{
						_this.toogleColumnCells(colId, true, false);

						_this.reinitResizeMeta();
						_this.toogleFader();
					});
				}

				jsDD.Enable();
			}

			_this.toggleCheckboxes();
		};

		for (var i in this.vars.current_url)
		{
			BX.ajax({
				url: this.vars.current_url[i],
				method: 'GET',
				dataType: 'json',
				headers: [
					{
						name: 'X-Ajax-Grid-UID',
						value: this.vars.ajax.GRID_AJAX_UID
					},
					{
						name: 'X-Ajax-Grid-Req',
						value: JSON.stringify({
							action: 'showcolumn',
							column: column
						})
					}
				],
				onsuccess: callback,
				onfailure: function() {
					colHead.setAttribute('data-empty', 1);
					jsDD.Enable();
				}
			});
		}
	};


	this.getData = function(url, callback)
	{
		BX.ajax({
			url: url + (url.indexOf('?') !== -1 ? '&' : '?') + 'bxajaxid=' + this.vars.ajax.AJAX_ID,
			method: 'GET',
			dataType: 'html',
			headers: [
				{
					name: 'X-Ajax-Grid-UID',
					value: this.vars.ajax.GRID_AJAX_UID
				},
				{
					name: 'X-Ajax-Grid-Req',
					value: JSON.stringify({
						action: 'showpage',
						columns: this.oVisibleCols
					})
				}
			],
			processData: true,
			scriptsRunFirst: true,
			onsuccess: function(data)
			{
				callback(data);
			}
		});
	};

}
/* jshint ignore:end */