import {Type} from "main.core";

;(function() {
	'use strict';

	BX.namespace('BX.Grid');

	/**
	 * BX.Grid.Row
	 * @param {BX.Main.Grid} parent
	 * @param {HtmlElement} node
	 * @constructor
	 */
	BX.Grid.Row = function(parent, node)
	{
		this.node = null;
		this.checkbox = null;
		this.sort = null;
		this.actions = null;
		this.settings = null;
		this.index = null;
		this.actionsButton = null;
		this.parent = null;
		this.depth = null;
		this.parentId = null;
		this.editData = null;
		this.custom = null;
		this.onElementClick = this.onElementClick.bind(this);
		this.init(parent, node);
		this.initElementsEvents();
	};

	//noinspection JSUnusedGlobalSymbols,JSUnusedGlobalSymbols
	BX.Grid.Row.prototype = {
		init: function(parent, node)
		{
			if (BX.type.isDomNode(node))
			{
				this.node = node;
				this.parent = parent;
				this.settings = new BX.Grid.Settings();
				this.bindNodes = [];

				if (this.isBodyChild())
				{
					this.bindNodes = [].slice.call(this.node.parentNode.querySelectorAll("tr[data-bind=\""+this.getId()+"\"]"));
					if (this.bindNodes.length)
					{
						this.node.addEventListener("mouseover", this.onMouseOver.bind(this));
						this.node.addEventListener("mouseleave", this.onMouseLeave.bind(this));
						this.bindNodes.forEach(function(row) {
							row.addEventListener("mouseover", this.onMouseOver.bind(this));
							row.addEventListener("mouseleave", this.onMouseLeave.bind(this));
							row.addEventListener("click", function() {
								if (this.isSelected())
								{
									this.unselect();
								}
								else
								{
									this.select();
								}
							}.bind(this));
						}, this);
					}
				}

				if (this.parent.getParam('ALLOW_CONTEXT_MENU'))
				{
					BX.bind(this.getNode(), 'contextmenu', BX.delegate(this._onRightClick, this));
				}
			}
		},

		onMouseOver: function()
		{
			this.node.classList.add("main-grid-row-over");
			this.bindNodes.forEach(function(row) {
				row.classList.add("main-grid-row-over");
			});
		},

		onMouseLeave: function()
		{
			this.node.classList.remove("main-grid-row-over");
			this.bindNodes.forEach(function(row) {
				row.classList.remove("main-grid-row-over");
			});
		},

		isCustom: function()
		{
			if (this.custom === null)
			{
				this.custom = BX.hasClass(this.getNode(), this.parent.settings.get('classRowCustom'));
			}

			return this.custom;
		},

		_onRightClick: function(event)
		{
			event.preventDefault();
			if (!this.isHeadChild())
			{
				this.showActionsMenu(event);
			}
		},

		getDefaultAction: function()
		{
			return BX.data(this.getNode(), 'default-action');
		},

		getEditorValue: function()
		{
			var self = this;
			var cells = this.getCells();
			var values = {};
			var cellValues;

			[].forEach.call(cells, function(current) {
				cellValues = self.getCellEditorValue(current);
				if (BX.type.isArray(cellValues))
				{
					cellValues.forEach(function(cellValue) {
						values[cellValue.NAME] = cellValue.VALUE !== undefined ? cellValue.VALUE : "";

						if (cellValue.hasOwnProperty("RAW_NAME") && cellValue.hasOwnProperty("RAW_VALUE"))
						{
							values[cellValue.NAME + "_custom"] = values[cellValue.NAME + "_custom"] || {};
							values[cellValue.NAME + "_custom"][cellValue.RAW_NAME] =
								values[cellValue.NAME + "_custom"][cellValue.RAW_NAME] || cellValue.RAW_VALUE;
						}
					});
				}
				else if (cellValues)
				{
					values[cellValues.NAME] = cellValues.VALUE !== undefined ? cellValues.VALUE : "";
				}
			});

			return values;
		},

		/**
		 * @deprecated
		 * @use this.getEditorValue()
		 */
		editGetValues: function()
		{
			return this.getEditorValue();
		},

		getCellEditorValue: function(cell)
		{
			var editor = BX.Grid.Utils.getByClass(cell, this.parent.settings.get('classEditor'), true);
			var result = null;

			if (BX.type.isDomNode(editor))
			{
				if (BX.hasClass(editor, 'main-grid-editor-checkbox'))
				{
					result = {
						'NAME': editor.getAttribute('name'),
						'VALUE': editor.checked ? 'Y' : 'N'
					};
				}
				else if(BX.hasClass(editor, 'main-grid-editor-custom'))
				{
					result = this.getCustomValue(editor);
				}
				else if(BX.hasClass(editor, 'main-grid-editor-money'))
				{
					result = this.getMoneyValue(editor);
				}
				else if(BX.hasClass(editor, 'main-ui-multi-select'))
				{
					result = this.getMultiSelectValues(editor);
				}
				else
				{
					result = this.getImageValue(editor);
				}
			}

			return result;
		},

		isEdit: function()
		{
			return BX.hasClass(this.getNode(), 'main-grid-row-edit');
		},

		hide: function()
		{
			BX.addClass(this.getNode(), this.parent.settings.get('classHide'));
		},

		show: function()
		{
			BX.Dom.attr(this.getNode(), 'hidden', null);
			BX.removeClass(this.getNode(), this.parent.settings.get('classHide'));
		},

		isShown: function()
		{
			return !BX.hasClass(this.getNode(), this.parent.settings.get('classHide'));
		},

		isNotCount: function()
		{
			return BX.hasClass(this.getNode(), this.parent.settings.get('classNotCount'));
		},

		getContentContainer: function(target)
		{
			if (BX.Type.isDomNode(target))
			{
				const cell = target.closest('.main-grid-cell');
				if (BX.Type.isDomNode(cell))
				{
					return cell.querySelector('.main-grid-cell-content');
				}
			}

			return target;
		},

		getContent: function(cell)
		{
			var container = this.getContentContainer(cell);
			var content;

			if (BX.type.isDomNode(container))
			{
				content = BX.html(container);
			}

			return content;
		},
		getMoneyValue: function(editor)
		{
			const result = [];
			const filteredValue = {
				PRICE: {},
				CURRENCY: {},
				HIDDEN: {},
			};
			const fieldName = editor.getAttribute('data-name');

			const inputs = [].slice.call(editor.querySelectorAll('input'));
			inputs.forEach(function(element) {
				result.push({
					NAME: fieldName,
					RAW_NAME: element.name,
					RAW_VALUE: element.value || '',
					VALUE: element.value || '',
				});

				if (element.classList.contains('main-grid-editor-money-price'))
				{
					filteredValue.PRICE = {
						NAME: element.name,
						VALUE: element.value,
					};
				}
				else if (element.type ===' hidden')
				{
					filteredValue.HIDDEN[element.name] = element.value;
				}
			});
			const currencySelector = editor.querySelector('.main-grid-editor-dropdown');
			if (currencySelector)
			{
				const currencyFieldName = currencySelector.getAttribute('name');
				if (BX.type.isNotEmptyString(currencyFieldName))
				{
					result.push({
						NAME: fieldName,
						RAW_NAME: currencyFieldName,
						RAW_VALUE: currencySelector.dataset.value || '',
						VALUE: currencySelector.dataset.value || '',
					});
					filteredValue.CURRENCY = {
						NAME: currencyFieldName,
						VALUE: currencySelector.dataset.value,
					};
				}
			}

			result.push({
				NAME: fieldName,
				VALUE: filteredValue,
			});
			return result;
		},
		getCustomValue: function(editor)
		{
			let map = new Map(), name = editor.getAttribute('data-name');
			let inputs = [].slice.call(editor.querySelectorAll('input, select, textarea'));
			inputs.forEach((element) => {
				if (element.name === '')
				{
					return;
				}
				if (element.hasAttribute('data-ignore-field'))
				{
					return;
				}

				let resultObject = {
					'NAME': name,
					'RAW_NAME': element.name,
					'RAW_VALUE': element.value,
					'VALUE': element.value
				};

				switch (element.tagName)
				{
					case 'SELECT':
						if (element.multiple)
						{
							let selectValues = [];
							element.querySelectorAll('option').forEach((option) => {
								if (option.selected)
								{
									selectValues.push(option.value);
								}
							});
							resultObject['RAW_VALUE'] = selectValues;
							resultObject['VALUE'] = selectValues;
							map.set(element.name, resultObject);
						}
						else
						{
							map.set(element.name, resultObject);
						}
						break;
					case 'INPUT':
						switch(element.type.toUpperCase())
						{
							case 'RADIO':
								if (element.checked)
								{
									map.set(element.name, resultObject);
								}
								break;
							case 'CHECKBOX':
								if (element.checked)
								{
									if (this.isMultipleCustomValue(element.name))
									{
										if (map.has(element.name))
										{
											resultObject = map.get(element.name);
											resultObject.RAW_VALUE.push(element.value);
											resultObject.VALUE.push(element.value);
										}
										else
										{
											resultObject.RAW_VALUE = [element.value];
											resultObject.VALUE = [element.value];
										}
									}
									map.set(element.name, resultObject);
								}
								break;
							case 'FILE':
								resultObject['RAW_VALUE'] = element.files[0];
								resultObject['VALUE'] = element.files[0];
								map.set(element.name, resultObject);
								break;
							default:
								if (this.isMultipleCustomValue(element.name))
								{
									if (map.has(element.name))
									{
										resultObject = map.get(element.name);
										resultObject.RAW_VALUE.push(element.value);
										resultObject.VALUE.push(element.value);
									}
									else
									{
										resultObject.RAW_VALUE = [element.value];
										resultObject.VALUE = [element.value];
									}
								}
								map.set(element.name, resultObject);
						}
						break;
					default:
						map.set(element.name, resultObject);
						break;
				}
			});

			let result = [];
			map.forEach((value) => {
				result.push(value);
			});

			return result;
		},

		isMultipleCustomValue: function(elementName: string): boolean
		{
			return elementName.length > 2
				&& elementName.lastIndexOf('[]') === elementName.length - 2;
		},

		getImageValue: function(editor)
		{
			var result = null;
			if (BX.hasClass(editor, 'main-grid-image-editor'))
			{
				var input = editor.querySelector('.main-grid-image-editor-file-input');

				if (input)
				{
					result = {
						'NAME': input.name,
						'VALUE': input.files[0]
					};
				}
				else
				{
					var fakeInput = editor.querySelector('.main-grid-image-editor-fake-file-input');

					if (fakeInput)
					{
						result = {
							'NAME': fakeInput.name,
							'VALUE': fakeInput.value
						};
					}
				}
			}
			else if (editor.value)
			{
				result = {
					'NAME': editor.getAttribute('name'),
					'VALUE': editor.value
				};
			}
			else
			{
				result = {
					'NAME': editor.getAttribute('name'),
					'VALUE': BX.data(editor, 'value')
				};
			}

			return result;
		},

		getMultiSelectValues: function(editor)
		{
			const value = JSON.parse(BX.data(editor, 'value'));
			return {
				'NAME': editor.getAttribute('name'),
				'VALUE': Type.isArrayFilled(value) ? value : ''
			};
		},

		/**
		 * @param {HTMLTableCellElement} cell
		 * @return {?HTMLElement}
		 */
		getEditorContainer: function(cell)
		{
			return BX.Grid.Utils.getByClass(cell, this.parent.settings.get('classEditorContainer'), true);
		},


		/**
		 * @return {HTMLElement}
		 */
		getCollapseButton: function()
		{
			if (!this.collapseButton)
			{
				this.collapseButton = BX.Grid.Utils.getByClass(this.getNode(), this.parent.settings.get('classCollapseButton'), true);
			}

			return this.collapseButton;
		},

		stateLoad: function()
		{
			BX.addClass(this.getNode(), this.parent.settings.get('classRowStateLoad'));
		},

		stateUnload: function()
		{
			BX.removeClass(this.getNode(), this.parent.settings.get('classRowStateLoad'));
		},

		stateExpand: function()
		{
			BX.addClass(this.getNode(), this.parent.settings.get('classRowStateExpand'));
		},

		stateCollapse: function()
		{
			BX.removeClass(this.getNode(), this.parent.settings.get('classRowStateExpand'));
		},

		getParentId: function()
		{
			if (this.parentId === null)
			{
				this.parentId = BX.data(this.getNode(), 'parent-id');

				if (typeof this.parentId !== 'undefined' && this.parentId !== null)
				{
					this.parentId = this.parentId.toString();
				}
			}

			return this.parentId;
		},


		/**
		 * @return {DOMStringMap}
		 */
		getDataset: function()
		{
			return this.getNode().dataset;
		},


		/**
		 * Gets row depth level
		 * @return {?number}
		 */
		getDepth: function()
		{
			if (this.depth === null)
			{
				this.depth = BX.data(this.getNode(), 'depth');
			}

			return this.depth;
		},


		/**
		 * Set row depth
		 * @param {number} depth
		 */
		setDepth: function(depth)
		{
			depth = parseInt(depth);

			if (BX.type.isNumber(depth))
			{
				var depthOffset = depth - parseInt(this.getDepth());
				var Rows = this.parent.getRows();

				this.getDataset().depth = depth;

				this.getShiftCells().forEach(function(cell) {
					BX.data(cell, 'depth', depth);
					BX.style(cell, 'padding-left', (depth * 20) + 'px');
				}, this);

				Rows.getRowsByParentId(this.getId(), true).forEach(function(row) {
					var childDepth = parseInt(depthOffset) + parseInt(row.getDepth());
					row.getDataset().depth = childDepth;
					row.getShiftCells().forEach(function(cell) {
						BX.data(cell, 'depth', childDepth);
						BX.style(cell, 'padding-left', (childDepth * 20) + 'px');
					});
				});
			}
		},


		/**
		 * Sets parent id
		 * @param {string|number} id
		 */
		setParentId: function(id)
		{
			this.getDataset()['parentId'] = id;
		},


		/**
		 * @return {HTMLTableRowElement}
		 */
		getShiftCells: function()
		{
			return BX.Grid.Utils.getBySelector(this.getNode(), 'td[data-shift="true"]');
		},

		showChildRows: function()
		{
			var rows = this.getChildren();
			var isCustom = this.isCustom();

			rows.forEach(function(row) {
				row.show();
				if (!isCustom && row.isExpand())
				{
					row.showChildRows();
				}
			});

			this.parent.updateCounterDisplayed();
			this.parent.updateCounterSelected();
			this.parent.adjustCheckAllCheckboxes();
			this.parent.adjustRows();
		},


		/**
		 * @return {BX.Grid.Row[]}
		 */
		getChildren: function()
		{
			var functionName = this.isCustom() ? 'getRowsByGroupId' : 'getRowsByParentId';
			var id = this.isCustom() ? this.getGroupId() : this.getId();
			return this.parent.getRows()[functionName](id, true);
		},

		hideChildRows: function()
		{
			var rows = this.getChildren();
			rows.forEach(function(row) { row.hide(); });
			this.parent.updateCounterDisplayed();
			this.parent.updateCounterSelected();
			this.parent.adjustCheckAllCheckboxes();
			this.parent.adjustRows();
		},

		isChildsLoaded: function()
		{
			if (!BX.type.isBoolean(this.childsLoaded))
			{
				this.childsLoaded = this.isCustom() || BX.data(this.getNode(), 'child-loaded') === 'true';
			}

			return this.childsLoaded;
		},

		expand: function()
		{
			var self = this;
			this.stateExpand();

			if (this.isChildsLoaded())
			{
				this.showChildRows();
			}
			else
			{
				this.stateLoad();
				this.loadChildRows(function(rows) {
					rows.reverse().forEach(function(current) {
						BX.insertAfter(current, self.getNode());
					});
					self.parent.getRows().reset();
					self.parent.bindOnRowEvents();

					if (self.parent.getParam('ALLOW_ROWS_SORT'))
					{
						self.parent.getRowsSortable().reinit();
					}

					if (self.parent.getParam('ALLOW_COLUMNS_SORT'))
					{
						self.parent.getColsSortable().reinit();
					}

					self.stateUnload();
					BX.data(self.getNode(), 'child-loaded', 'true');
					self.parent.updateCounterDisplayed();
					self.parent.updateCounterSelected();
					self.parent.adjustCheckAllCheckboxes();
				});
			}
		},

		collapse: function()
		{
			this.stateCollapse();
			this.hideChildRows();
		},

		isExpand: function()
		{
			return BX.hasClass(this.getNode(), this.parent.settings.get('classRowStateExpand'));
		},

		toggleChildRows: function()
		{
			if (!this.isExpand())
			{
				this.expand();
			}
			else
			{
				this.collapse();
			}
		},

		loadChildRows: function(callback)
		{
			if (BX.type.isFunction(callback))
			{
				var self = this;
				var depth = parseInt(this.getDepth());
				var action = this.parent.getUserOptions().getAction('GRID_GET_CHILD_ROWS');
				depth = BX.type.isNumber(depth) ? depth+1 : 1;
				this.parent.getData().request('', 'POST', {action: action, parent_id: this.getId(), depth: depth}, null, function() {
					var rows = this.getRowsByParentId(self.getId());
					callback.apply(null, [rows]);
				});
			}
		},

		update: function(data, url, callback)
		{
			data = !!data ? data : '';

			var action = this.parent.getUserOptions().getAction('GRID_UPDATE_ROW');
			var depth = this.getDepth();
			var id = this.getId();
			var parentId = this.getParentId();
			var rowData = {id: id, parentId: parentId, action: action, depth: depth, data: data};
			var self = this;

			this.stateLoad();
			this.parent.getData().request(url, 'POST', rowData, null, function() {
				var bodyRows = this.getBodyRows();
				self.parent.getUpdater().updateBodyRows(bodyRows);
				self.stateUnload();
				self.parent.getRows().reset();
				self.parent.getUpdater().updateFootRows(this.getFootRows());
				self.parent.getUpdater().updatePagination(this.getPagination());
				self.parent.getUpdater().updateMoreButton(this.getMoreButton());
				self.parent.getUpdater().updateCounterTotal(this.getCounterTotal());
				self.parent.bindOnRowEvents();
				self.parent.adjustEmptyTable(bodyRows);

				self.parent.bindOnMoreButtonEvents();
				self.parent.bindOnClickPaginationLinks();
				self.parent.updateCounterDisplayed();
				self.parent.updateCounterSelected();

				if (self.parent.getParam('ALLOW_COLUMNS_SORT'))
				{
					self.parent.colsSortable.reinit();
				}

				if (self.parent.getParam('ALLOW_ROWS_SORT'))
				{
					self.parent.rowsSortable.reinit();
				}

				BX.onCustomEvent(window, 'Grid::rowUpdated', [{id: id, data: data, grid: self.parent, response: this}]);
				BX.onCustomEvent(window, 'Grid::updated', [self.parent]);

				if (BX.type.isFunction(callback))
				{
					callback({id: id, data: data, grid: self.parent, response: this});
				}
			});
		},

		remove: function(data, url, callback)
		{
			data = !!data ? data : '';

			var action = this.parent.getUserOptions().getAction('GRID_DELETE_ROW');
			var depth = this.getDepth();
			var id = this.getId();
			var parentId = this.getParentId();
			var rowData = {id: id, parentId: parentId, action: action, depth: depth, data: data};
			var self = this;

			this.stateLoad();
			this.parent.getData().request(url, 'POST', rowData, null, function() {
				var bodyRows = this.getBodyRows();
				self.parent.getUpdater().updateBodyRows(bodyRows);
				self.stateUnload();
				self.parent.getRows().reset();
				self.parent.getUpdater().updateFootRows(this.getFootRows());
				self.parent.getUpdater().updatePagination(this.getPagination());
				self.parent.getUpdater().updateMoreButton(this.getMoreButton());
				self.parent.getUpdater().updateCounterTotal(this.getCounterTotal());
				self.parent.bindOnRowEvents();
				self.parent.adjustEmptyTable(bodyRows);

				self.parent.bindOnMoreButtonEvents();
				self.parent.bindOnClickPaginationLinks();
				self.parent.updateCounterDisplayed();
				self.parent.updateCounterSelected();

				if (self.parent.getParam('ALLOW_COLUMNS_SORT'))
				{
					self.parent.colsSortable.reinit();
				}

				if (self.parent.getParam('ALLOW_ROWS_SORT'))
				{
					self.parent.rowsSortable.reinit();
				}

				BX.onCustomEvent(window, 'Grid::rowRemoved', [{id: id, data: data, grid: self.parent, response: this}]);
				BX.onCustomEvent(window, 'Grid::updated', [self.parent]);

				if (BX.type.isFunction(callback))
				{
					callback({id: id, data: data, grid: self.parent, response: this});
				}
			});
		},

		editCancel: function()
		{
			var cells = this.getCells();
			var self = this;
			var editorContainer;

			[].forEach.call(cells, function(current) {
				editorContainer = self.getEditorContainer(current);

				if (BX.type.isDomNode(editorContainer))
				{
					BX.remove(self.getEditorContainer(current));
					BX.show(self.getContentContainer(current));
				}
			});

			BX.removeClass(this.getNode(), 'main-grid-row-edit');
		},

		getCellByIndex: function(index)
		{
			return this.getCells()[index];
		},

		getEditDataByCellIndex: function(index)
		{
			return eval(BX.data(this.getCellByIndex(index), 'edit'));
		},

		getCellNameByCellIndex: function(index)
		{
			return BX.data(this.getCellByIndex(index), 'name');
		},

		resetEditData: function()
		{
			this.editData = null;
		},

		setEditData: function(editData)
		{
			this.editData = editData;
		},

		getEditData: function()
		{
			if (this.editData === null)
			{
				var editableData = this.parent.getParam('EDITABLE_DATA');
				var rowId = this.getId();

				if (BX.type.isPlainObject(editableData) && rowId in editableData)
				{
					this.editData = editableData[rowId];
				}
				else
				{
					this.editData = {};
				}
			}

			return this.editData
		},

		getCellEditDataByCellIndex: function(cellIndex)
		{
			var editData = this.getEditData();
			var result = null;
			cellIndex = parseInt(cellIndex);

			if (BX.type.isNumber(cellIndex) && BX.type.isPlainObject(editData))
			{
				var columnEditData = this.parent.getRows().getHeadFirstChild().getEditDataByCellIndex(cellIndex);

				if (BX.type.isPlainObject(columnEditData))
				{
					result = columnEditData;
					result.VALUE = editData[columnEditData.NAME];
				}
			}

			return result;
		},

		edit: function()
		{
			var cells = this.getCells();
			var self = this;
			var editObject, editor, height, contentContainer;

			[].forEach.call(cells, function(current, index) {
				if (current.dataset.editable === 'true')
				{
					try {
						editObject = self.getCellEditDataByCellIndex(index);
					} catch (err) {
						throw new Error(err);
					}

					if (self.parent.getEditor().validateEditObject(editObject))
					{
						contentContainer = self.getContentContainer(current);
						height = BX.height(contentContainer);
						editor = self.parent.getEditor().getEditor(editObject, height);

						if (!self.getEditorContainer(current) && BX.type.isDomNode(editor))
						{
							current.appendChild(editor);
							BX.hide(contentContainer);
						}
					}
				}
			});

			BX.addClass(this.getNode(), 'main-grid-row-edit');
		},

		setDraggable: function(value)
		{
			if (!value)
			{
				BX.addClass(this.getNode(), this.parent.settings.get('classDisableDrag'));
				this.parent.getRowsSortable().unregister(this.getNode());
			}
			else
			{
				BX.removeClass(this.getNode(), this.parent.settings.get('classDisableDrag'));
				this.parent.getRowsSortable().register(this.getNode());
			}
		},

		isDraggable: function()
		{
			return !BX.hasClass(this.getNode(), this.parent.settings.get('classDisableDrag'));
		},

		getNode: function()
		{
			return this.node;
		},

		getIndex: function()
		{
			return this.getNode().rowIndex;
		},

		getId: function()
		{
			return String(BX.data(this.getNode(), 'id'));
		},

		getGroupId: function()
		{
			return (BX.data(this.getNode(), 'group-id')).toString();
		},

		getObserver: function()
		{
			return BX.Grid.observer;
		},

		getCheckbox: function()
		{
			if (!this.checkbox)
			{
				this.checkbox = BX.Grid.Utils.getByClass(this.getNode(), this.settings.get('classRowCheckbox'), true);
			}

			return this.checkbox;
		},

		getActionsMenu: function()
		{
			if (!this.actionsMenu)
			{
				var buttonRect = this.getActionsButton().getBoundingClientRect();

				this.actionsMenu = BX.PopupMenu.create(
					'main-grid-actions-menu-' + this.getId(),
					this.getActionsButton(),
					this.getMenuItems(),
					{
						'autoHide': true,
						'offsetTop': -((buttonRect.height / 2) + 26),
						'offsetLeft': 30,
						'angle': {
							'position': 'left',
							'offset': ((buttonRect.height / 2) - 8)
						},
						'events': {
							'onPopupClose': BX.delegate(this._onCloseMenu, this),
							'onPopupShow': BX.delegate(this._onPopupShow, this)
						}
					}
				);

				BX.addCustomEvent('Grid::updated', function() {
					if(this.actionsMenu)
					{
						this.actionsMenu.destroy();
						this.actionsMenu = null;
					}
				}.bind(this));

				BX.bind(this.actionsMenu.popupWindow.popupContainer, 'click', BX.delegate(function(event) {
					var actionsMenu = this.getActionsMenu();
					if (actionsMenu)
					{
						var target = BX.getEventTarget(event);
						var item = BX.findParent(target, {
							className: 'menu-popup-item'
						}, 10);

						if (!item || !item.dataset.preventCloseContextMenu)
						{
							actionsMenu.close();
						}
					}
				}, this));
			}

			return this.actionsMenu;
		},

		_onCloseMenu: function()
		{
		},

		_onPopupShow: function(popupMenu)
		{
			popupMenu.setBindElement(this.getActionsButton());
		},

		actionsMenuIsShown: function()
		{
			return this.getActionsMenu().popupWindow.isShown();
		},

		showActionsMenu: function(event)
		{
			BX.fireEvent(document.body, 'click');

			this.getActionsMenu().popupWindow.show();

			if (event)
			{
				this.getActionsMenu().popupWindow.popupContainer.style.top = ((event.pageY - 25) + BX.PopupWindow.getOption("offsetTop")) + "px";
				this.getActionsMenu().popupWindow.popupContainer.style.left = ((event.pageX + 20) + BX.PopupWindow.getOption("offsetLeft")) + "px";
			}
		},

		closeActionsMenu: function()
		{
			if (this.actionsMenu)
			{
				if (this.actionsMenu.popupWindow)
				{
					this.actionsMenu.popupWindow.close();
				}
			}
		},

		getMenuItems: function()
		{
			return this.getActions() || [];
		},

		getActions: function()
		{
			try {
				this.actions = this.actions || eval(BX.data(this.getActionsButton(), this.settings.get('dataActionsKey')));
			} catch (err) {
				this.actions = null;
			}

			return this.actions;
		},

		getActionsButton: function()
		{
			if (!this.actionsButton)
			{
				this.actionsButton = BX.Grid.Utils.getByClass(this.getNode(), this.settings.get('classRowActionButton'), true);
			}

			return this.actionsButton;
		},

		initSelect: function()
		{
			if (this.isSelected() && !BX.hasClass(this.getNode(), this.settings.get('classCheckedRow')))
			{
				BX.addClass(this.getNode(), this.settings.get('classCheckedRow'))
			}
		},

		getParentNode: function()
		{
			var result;

			try {
				result = (this.getNode()).parentNode;
			} catch (err) {
				result = null;
			}

			return result;
		},

		getParentNodeName: function()
		{
			var result;

			try {
				result = (this.getParentNode()).nodeName;
			} catch (err) {
				result = null;
			}

			return result;
		},

		isSelectable: function()
		{
			return !this.isEdit() || this.parent.getParam('ALLOW_EDIT_SELECTION');
		},

		select: function()
		{
			var checkbox;

			if (
				this.isSelectable()
				&& (this.parent.getParam('ADVANCED_EDIT_MODE') || !this.parent.getRows().hasEditable())
			)
			{
				checkbox = this.getCheckbox();

				if (checkbox)
				{
					if (!BX.data(checkbox, 'disabled'))
					{
						BX.addClass(this.getNode(), this.settings.get('classCheckedRow'));
						this.bindNodes.forEach(function(row) {
							BX.addClass(row, this.settings.get('classCheckedRow'));
						}, this);
						checkbox.checked = true;
					}
				}
			}
		},

		unselect: function()
		{
			if (this.isSelectable())
			{
				BX.removeClass(this.getNode(), this.settings.get('classCheckedRow'));
				this.bindNodes.forEach(function(row) {
					BX.removeClass(row, this.settings.get('classCheckedRow'));
				}, this);
				if (this.getCheckbox())
				{
					this.getCheckbox().checked = false;
				}
			}
		},

		getCells: function()
		{
			return this.getNode().cells;
		},

		isSelected: function()
		{
			return (
				(this.getCheckbox() && (this.getCheckbox()).checked) ||
				(BX.hasClass(this.getNode(), this.settings.get('classCheckedRow')))
			);
		},

		isHeadChild: function()
		{
			return (
				this.getParentNodeName() === 'THEAD' &&
				BX.hasClass(this.getNode(), this.settings.get('classHeadRow'))
			);
		},

		isBodyChild: function()
		{
			return (
				BX.hasClass(this.getNode(), this.settings.get('classBodyRow')) && !BX.hasClass(this.getNode(), this.settings.get('classEmptyRows'))
			);
		},

		isFootChild: function()
		{
			return (
				this.getParentNodeName() === 'TFOOT' &&
				BX.hasClass(this.getNode(), this.settings.get('classFootRow'))
			);
		},

		prependTo: function(target)
		{
			BX.Dom.prepend(this.getNode(), target);
		},

		appendTo: function(target)
		{
			BX.Dom.append(this.getNode(), target);
		},

		setId: function(id)
		{
			BX.Dom.attr(this.getNode(), 'data-id', id);
		},

		setActions: function(actions)
		{
			const actionCell = this.getNode().querySelector('.main-grid-cell-action');
			if (actionCell)
			{
				let actionButton = actionCell.querySelector('.main-grid-row-action-button');
				if (!actionButton)
				{
					actionButton = BX.Dom.create({
						tag: 'div',
						props: {className: 'main-grid-row-action-button'},
					});

					const container = this.getContentContainer(actionCell);
					BX.Dom.append(actionButton, container);
				}

				BX.Dom.attr(actionButton, {
					href: '#',
					'data-actions': actions,
				});

				this.actions = actions;

				if (this.actionsMenu)
				{
					this.actionsMenu.destroy();
					this.actionsMenu = null;
				}
			}
		},

		makeCountable: function()
		{
			BX.Dom.removeClass(this.getNode(), 'main-grid-not-count');
		},

		makeNotCountable: function()
		{
			BX.Dom.addClass(this.getNode(), 'main-grid-not-count');
		},

		getColumnOptions: function(columnId)
		{
			const columns = this.parent.getParam('COLUMNS_ALL');
			if (
				BX.Type.isPlainObject(columns)
				&& Reflect.has(columns, columnId)
			)
			{
				return columns[columnId];
			}

			return null;
		},

		setCellsContent: function(content)
		{
			const headRow = this.parent.getRows().getHeadFirstChild();

			[...this.getCells()].forEach((cell, cellIndex) => {
				const cellName = headRow.getCellNameByCellIndex(cellIndex);

				if (Reflect.has(content, cellName))
				{
					const columnOptions = this.getColumnOptions(cellName);
					const container = this.getContentContainer(cell);
					const cellContent = content[cellName];
					if (
						columnOptions.type === 'labels'
						&& BX.Type.isArray(cellContent)
					)
					{
						const labels = cellContent.map((labelOptions) => {
							const label = BX.Tag.render`
								<span class="ui-label ${labelOptions.color}"></span>
							`;

							if (labelOptions.light !== true)
							{
								BX.Dom.addClass(label, 'ui-label-fill');
							}

							if (BX.Type.isPlainObject(labelOptions.events))
							{
								if (Reflect.has(labelOptions.events, 'click'))
								{
									BX.Dom.addClass(label, 'ui-label-link');
								}

								this.bindOnEvents(label, labelOptions.events);
							}

							const labelContent = (() => {
								if (BX.Type.isStringFilled(labelOptions.html))
								{
									return labelOptions.html;
								}

								return labelOptions.text;
							})();

							const inner = BX.Tag.render`
								<span class="ui-label-inner">${labelContent}</span>
							`;

							BX.Dom.append(inner, label);

							if (BX.Type.isPlainObject(labelOptions.removeButton))
							{
								const button = (() => {
									if (labelOptions.removeButton.type === BX.Grid.Label.RemoveButtonType.INSIDE)
									{
										return BX.Tag.render`
											<span class="ui-label-icon"></span>	
										`;
									}

									return BX.Tag.render`
										<span class="main-grid-label-remove-button ${labelOptions.removeButton.type}"></span>	
									`;
								})();

								if (BX.Type.isPlainObject(labelOptions.removeButton.events))
								{
									this.bindOnEvents(button, labelOptions.removeButton.events);
								}

								BX.Dom.append(button, label);
							}

							return label;
						});

						const labelsContainer = BX.Tag.render`
							<div class="main-grid-labels">${labels}</div>
						`;

						BX.Dom.clean(container);
						const oldLabelsContainer = container.querySelector('.main-grid-labels');
						if (BX.Type.isDomNode(oldLabelsContainer))
						{
							BX.Dom.replace(oldLabelsContainer, labelsContainer);
						}
						else
						{
							BX.Dom.append(labelsContainer, container);
						}
					}
					else if (
						columnOptions.type === 'tags'
						&& BX.Type.isPlainObject(cellContent)
					)
					{
						const tags = cellContent.items.map((tagOptions) => {
							const tag = BX.Tag.render`
								<span class="main-grid-tag"></span>
							`;

							this.bindOnEvents(tag, tagOptions.events);

							if (tagOptions.active === true)
							{
								BX.Dom.addClass(tag, 'main-grid-tag-active');
							}

							const tagContent = (() => {
								if (BX.Type.isStringFilled(tagOptions.html))
								{
									return tagOptions.html;
								}

								return BX.Text.encode(tagOptions.text);
							})();

							const tagInner = BX.Tag.render`
								<span class="main-grid-tag-inner">${tagContent}</span>
							`;

							BX.Dom.append(tagInner, tag);

							if (tagOptions.active === true)
							{
								const removeButton = BX.Tag.render`
									<span class="main-grid-tag-remove"></span>
								`;

								BX.Dom.append(removeButton, tag);

								if (BX.Type.isPlainObject(tagOptions.removeButton))
								{
									this.bindOnEvents(removeButton, tagOptions.removeButton.events);
								}
							}

							return tag;
						});

						const tagsContainer = BX.Tag.render`
							<span class="main-grid-tags">${tags}</span>
						`;

						const addButton = BX.Tag.render`
							<span class="main-grid-tag-add"></span>
						`;
						if (BX.Type.isPlainObject(cellContent.addButton))
						{
							this.bindOnEvents(addButton, cellContent.addButton.events);
						}

						BX.Dom.append(addButton, tagsContainer);

						const oldTagsContainer = container.querySelector('.main-grid-tags');
						if (BX.Type.isDomNode(oldTagsContainer))
						{
							BX.Dom.replace(oldTagsContainer, tagsContainer);
						}
						else
						{
							BX.Dom.append(tagsContainer, container);
						}
					}
					else
					{
						BX.Runtime.html(container, cellContent);
					}
				}
			});
		},

		getCellById: function(id)
		{
			const headRow = this.parent.getRows().getHeadFirstChild();

			return [...this.getCells()].find((cell, index) => {
				return headRow.getCellNameByCellIndex(index) === id;
			});
		},

		isTemplate: function()
		{
			return this.isBodyChild() && /^template_[0-9]$/.test(this.getId());
		},

		enableAbsolutePosition: function()
		{
			const headCells = [...this.parent.getRows().getHeadFirstChild().getCells()];
			const cellsWidth = headCells.map((cell) => {
				return BX.Dom.style(cell, 'width');
			});

			const cells = this.getCells();
			cellsWidth.forEach((width, index) => {
				BX.Dom.style(cells[index], 'width', width);
			});

			BX.Dom.style(this.getNode(), 'position', 'absolute');
		},

		disableAbsolutePosition: function()
		{
			BX.Dom.style(this.getNode(), 'position', null);
		},

		getHeight: function()
		{
			return BX.Text.toNumber(BX.Dom.style(this.getNode(), 'height'));
		},

		setCellActions: function(cellActions)
		{
			Object.entries(cellActions).forEach(([cellId, actions]) => {
				const cell = this.getCellById(cellId);
				if (cell)
				{
					const inner = cell.querySelector('.main-grid-cell-inner');
					if (inner)
					{
						const container = (() => {
							const currentContainer = inner.querySelector('.main-grid-cell-content-actions');
							if (currentContainer)
							{
								BX.Dom.clean(currentContainer);
								return currentContainer;
							}

							const newContainer = BX.Tag.render`
								<div class="main-grid-cell-content-actions"></div>
							`;

							BX.Dom.append(newContainer, inner);

							return newContainer;
						})();

						if (BX.Type.isArrayFilled(actions))
						{
							actions.forEach((action) => {
								const actionClass = (() => {
									if (BX.Type.isArrayFilled(action.class))
									{
										return action.class.join(' ');
									}

									return action.class;
								})();

								const button = BX.Tag.render`
									<span class="main-grid-cell-content-action ${actionClass}"></span>
								`;

								if (BX.Type.isPlainObject(action.events))
								{
									this.bindOnEvents(button, action.events);
								}

								if (BX.Type.isPlainObject(action.attributes))
								{
									BX.Dom.attr(button, action.attributes);
								}

								BX.Dom.append(button, container);
							});
						}
					}
				}
			});
		},

		/**
		 * @private
		 */
		initElementsEvents: function()
		{
			const buttons = [
				...this.getNode().querySelectorAll('.main-grid-cell [data-events]'),
			];
			if (BX.Type.isArrayFilled(buttons))
			{
				buttons.forEach((button) => {
					const events = eval(BX.Dom.attr(button, 'data-events'));
					if (BX.Type.isPlainObject(events))
					{
						BX.Dom.attr(button, 'data-events', null);
						this.bindOnEvents(button, events);
					}
				});
			}
		},

		/**
		 * @private
		 * @param event
		 */
		onElementClick: function(event)
		{
			event.stopPropagation();
		},

		/**
		 * @private
		 */
		bindOnEvents: function(button, events)
		{
			if (
				BX.Type.isDomNode(button)
				&& BX.Type.isPlainObject(events)
			)
			{
				BX.Event.bind(button, 'click', this.onElementClick.bind(this));

				const target = (() => {
					const selector = BX.Dom.attr(button, 'data-target');
					if (selector)
					{
						return button.closest(selector);
					}

					return button;
				})();

				const event = new BX.Event.BaseEvent({
					data: {
						button,
						target,
						row: this,
					},
				});

				event.setTarget(target);

				Object.entries(events).forEach(([eventName, handler]) => {
					const preparedHandler = eval(handler);
					BX.Event.bind(button, eventName, preparedHandler.bind(null, event));
				});
			}
		},

		setCounters: function(counters)
		{
			if (BX.Type.isPlainObject(counters))
			{
				Object.entries(counters).forEach(([columnId, counter]) => {
					const cell = this.getCellById(columnId);
					if (BX.Type.isDomNode(cell))
					{
						const cellInner = cell.querySelector('.main-grid-cell-inner');
						const counterContainer = (() => {
							const container = cell.querySelector('.main-grid-cell-counter');
							if (BX.Type.isDomNode(container))
							{
								return container;
							}

							return BX.Tag.render`
								<span class="main-grid-cell-counter"></span>
							`;
						})();

						const uiCounter = (() => {
							const currentCounter = counterContainer.querySelector('.ui-counter');
							if (BX.Type.isDomNode(currentCounter))
							{
								return currentCounter;
							}

							const newCounter = BX.Tag.render`
								<span class="ui-counter"></span>
							`;

							BX.Dom.append(newCounter, counterContainer);

							return newCounter;
						})();

						if (BX.Type.isPlainObject(counter.events))
						{
							this.bindOnEvents(uiCounter, counter.events);
						}

						const counterInner = (() => {
							const currentInner = uiCounter.querySelector('.ui-counter-inner');
							if (BX.Type.isDomNode(currentInner))
							{
								return currentInner;
							}

							const newInner = BX.Tag.render`
								<span class="ui-counter-inner"></span>
							`;

							BX.Dom.append(newInner, uiCounter);

							return newInner;
						})();

						if (BX.Type.isStringFilled(counter.type))
						{
							Object.values(BX.Grid.Counters.Type).forEach((type) => {
								BX.Dom.removeClass(counterContainer, `main-grid-cell-counter-${type}`);
							});
							BX.Dom.addClass(counterContainer, `main-grid-cell-counter-${counter.type}`);
						}

						if (BX.Type.isStringFilled(counter.color))
						{
							Object.values(BX.Grid.Counters.Color).forEach((color) => {
								BX.Dom.removeClass(uiCounter, color);
							});
							BX.Dom.addClass(uiCounter, counter.color);
						}

						if (BX.Type.isStringFilled(counter.size))
						{
							Object.values(BX.Grid.Counters.Size).forEach((size) => {
								BX.Dom.removeClass(uiCounter, size);
							});
							BX.Dom.addClass(uiCounter, counter.size);
						}

						if (BX.Type.isStringFilled(counter.class))
						{
							BX.Dom.addClass(uiCounter, counter.class);
						}

						if (
							BX.Type.isStringFilled(counter.value)
							|| BX.Type.isNumber(counter.value)
						)
						{
							const currentValue = BX.Text.toNumber(counterInner.innerText);
							const value = BX.Text.toNumber(counter.value);

							if (value > 0)
							{
								if (value < 100)
								{
									counterInner.innerText = counter.value;
								}
								else
								{
									counterInner.innerText = '99+';
								}

								if (counter.animation !== false)
								{
									if (value !== currentValue)
									{
										if (value > currentValue)
										{
											BX.Dom.addClass(counterInner, 'ui-counter-plus');
										}
										else
										{
											BX.Dom.addClass(counterInner, 'ui-counter-minus');
										}
									}

									BX.Event.bindOnce(counterInner, 'animationend', (event) => {
										if (
											event.animationName === 'uiCounterPlus'
											|| event.animationName === 'uiCounterMinus'
										)
										{
											BX.Dom.removeClass(counterInner, ['ui-counter-plus', 'ui-counter-minus']);
										}
									});
								}
							}
						}

						if (BX.Text.toNumber(counter.value) > 0)
						{
							const align = counter.type === BX.Grid.Counters.Type.RIGHT ? 'right' : 'left';
							if (align === 'left')
							{
								BX.Dom.prepend(counterContainer, cellInner);
							}
							else if (align === 'right')
							{
								BX.Dom.append(counterContainer, cellInner);
							}
						}
						else
						{
							const leftAlignedClass = (
								`main-grid-cell-counter-${BX.Grid.Counters.Type.LEFT_ALIGNED}`
							);
							if (BX.Dom.hasClass(counterContainer, leftAlignedClass))
							{
								BX.remove(uiCounter);
							}
							else
							{
								BX.remove(counterContainer);
							}
						}
					}
				});
			}
		},
	};
})();
