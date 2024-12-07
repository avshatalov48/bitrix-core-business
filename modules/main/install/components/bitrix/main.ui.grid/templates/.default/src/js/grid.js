(function() {
	'use strict';

	BX.namespace('BX.Main');

	/**
	 * @event Grid::ready
	 * @event Grid::columnMoved
	 * @event Grid::rowMoved
	 * @event Grid::pageSizeChanged
	 * @event Grid::optionsUpdated
	 * @event Grid::dataSorted
	 * @event Grid::thereSelectedRows
	 * @event Grid::allRowsSelected
	 * @event Grid::allRowsUnselected
	 * @event Grid::noSelectedRows
	 * @event Grid::updated
	 * @event Grid::headerPinned
	 * @event Grid::headerUnpinned
	 * @event Grid::beforeRequest
	 * @param {string} containerId
	 * @param {object} arParams
	 * @param {boolean} arParams.ALLOW_COLUMNS_SORT
	 * @param {boolean} arParams.ALLOW_ROWS_SORT
	 * @param {boolean} arParams.ALLOW_COLUMNS_RESIZE
	 * @param {boolean} arParams.SHOW_ROW_CHECKBOXES
	 * @param {boolean} arParams.ALLOW_HORIZONTAL_SCROLL
	 * @param {boolean} arParams.ALLOW_PIN_HEADER
	 * @param {boolean} arParams.SHOW_ACTION_PANEL
	 * @param {boolean} arParams.PRESERVE_HISTORY
	 * @param {boolean} arParams.ALLOW_CONTEXT_MENU
	 * @param {object} arParams.DEFAULT_COLUMNS
	 * @param {boolean} arParams.ENABLE_COLLAPSIBLE_ROWS
	 * @param {object} arParams.EDITABLE_DATA
	 * @param {string} arParams.SETTINGS_TITLE
	 * @param {string} arParams.APPLY_SETTINGS
	 * @param {string} arParams.CANCEL_SETTINGS
	 * @param {string} arParams.CONFIRM_APPLY
	 * @param {string} arParams.CONFIRM_CANCEL
	 * @param {string} arParams.CONFIRM_MESSAGE
	 * @param {string} arParams.CONFIRM_FOR_ALL_MESSAGE
	 * @param {string} arParams.CONFIRM_RESET_MESSAGE
	 * @param {object} arParams.COLUMNS_ALL_WITH_SECTIONS
	 * @param {boolean} arParams.ENABLE_FIELDS_SEARCH
	 * @param {object} arParams.CHECKBOX_LIST_OPTIONS
	 * @param {array} arParams.HEADERS_SECTIONS
	 * @param {string} arParams.RESET_DEFAULT
	 * @param {object} userOptions
	 * @param {object} userOptionsActions
	 * @param {object} userOptionsHandlerUrl
	 * @param {object} panelActions
	 * @param {object} panelTypes
	 * @param {object} editorTypes
	 * @param {object} messageTypes
	 * @constructor
	 */
	BX.Main.grid = function(
		containerId,
		arParams,
		userOptions,
		userOptionsActions,
		userOptionsHandlerUrl,
		panelActions,
		panelTypes,
		editorTypes,
		messageTypes,
	)
	{
		BX.Event.EventEmitter.makeObservable(this, 'BX.Main.Grid');
		this.settings = null;
		this.containerId = '';
		this.container = null;
		this.wrapper = null;
		this.fadeContainer = null;
		this.scrollContainer = null;
		this.pagination = null;
		this.moreButton = null;
		this.table = null;
		this.rows = null;
		this.history = false;
		this.userOptions = null;
		this.checkAll = null;
		this.sortable = null;
		this.updater = null;
		this.data = null;
		this.fader = null;
		this.editor = null;
		this.isEditMode = null;
		this.pinHeader = null;
		this.pinPanel = null;
		this.arParams = null;
		this.resize = null;
		this.editableRows = [];

		this.init(
			containerId,
			arParams,
			userOptions,
			userOptionsActions,
			userOptionsHandlerUrl,
			panelActions,
			panelTypes,
			editorTypes,
			messageTypes,
		);
	};

	BX.Main.grid.prototype = {
		init(containerId, arParams, userOptions, userOptionsActions, userOptionsHandlerUrl, panelActions, panelTypes, editorTypes, messageTypes)
		{
			this.baseUrl = window.location.pathname + window.location.search;
			this.container = BX(containerId);

			if (!BX.type.isNotEmptyString(containerId))
			{
				throw 'BX.Main.grid.init: parameter containerId is empty';
			}

			if (BX.type.isPlainObject(arParams))
			{
				this.arParams = arParams;
			}
			else
			{
				throw new TypeError('BX.Main.grid.init: arParams isn\'t object');
			}

			this.settings = new BX.Grid.Settings();
			this.containerId = containerId;
			this.userOptions = new BX.Grid.UserOptions(this, userOptions, userOptionsActions, userOptionsHandlerUrl);
			this.gridSettings = new BX.Grid.SettingsWindow.Manager(this);
			this.messages = new BX.Grid.Message(this, messageTypes);
			this.cache = new BX.Cache.MemoryCache();

			if (this.getParam('ALLOW_PIN_HEADER'))
			{
				this.pinHeader = new BX.Grid.PinHeader(this);
				BX.addCustomEvent(window, 'Grid::headerUpdated', BX.proxy(this.bindOnCheckAll, this));
			}

			this.bindOnCheckAll();

			if (this.getParam('ALLOW_HORIZONTAL_SCROLL'))
			{
				this.fader = new BX.Grid.Fader(this);
			}

			this.pageSize = new BX.Grid.Pagesize(this);
			this.editor = new BX.Grid.InlineEditor(this, editorTypes);

			if (this.getParam('SHOW_ACTION_PANEL'))
			{
				this.actionPanel = new BX.Grid.ActionPanel(this, panelActions, panelTypes);
				this.pinPanel = new BX.Grid.PinPanel(this);
			}

			this.isEditMode = false;

			if (!BX.type.isDomNode(this.getContainer()))
			{
				throw `BX.Main.grid.init: Failed to find container with id ${this.getContainerId()}`;
			}

			if (!BX.type.isDomNode(this.getTable()))
			{
				throw 'BX.Main.grid.init: Failed to find table';
			}

			this.bindOnRowEvents();

			if (this.getParam('ALLOW_COLUMNS_RESIZE'))
			{
				this.resize = new BX.Grid.Resize(this);
			}

			this.bindOnMoreButtonEvents();
			this.bindOnClickPaginationLinks();
			this.bindOnClickHeader();

			if (this.getParam('ALLOW_ROWS_SORT'))
			{
				this.initRowsDragAndDrop();
			}

			if (this.getParam('ALLOW_COLUMNS_SORT'))
			{
				this.initColsDragAndDrop();
			}

			this.getRows().initSelected();
			this.adjustEmptyTable(this.getRows().getSourceBodyChild());
			BX.onCustomEvent(this.getContainer(), 'Grid::ready', [this]);
			BX.addCustomEvent(window, 'Grid::unselectRow', BX.proxy(this._onUnselectRows, this));
			BX.addCustomEvent(window, 'Grid::unselectRows', BX.proxy(this._onUnselectRows, this));
			BX.addCustomEvent(window, 'Grid::allRowsUnselected', BX.proxy(this._onUnselectRows, this));
			BX.addCustomEvent(window, 'Grid::updated', BX.proxy(this._onGridUpdated, this));
			window.frames[this.getFrameId()].onresize = BX.throttle(this._onFrameResize, 20, this);

			if (this.getParam('ALLOW_STICKED_COLUMNS'))
			{
				this.initStickedColumns();
			}
		},

		destroy()
		{
			BX.removeCustomEvent(window, 'Grid::unselectRow', BX.proxy(this._onUnselectRows, this));
			BX.removeCustomEvent(window, 'Grid::unselectRows', BX.proxy(this._onUnselectRows, this));
			BX.removeCustomEvent(window, 'Grid::allRowsUnselected', BX.proxy(this._onUnselectRows, this));
			BX.removeCustomEvent(window, 'Grid::headerPinned', BX.proxy(this.bindOnCheckAll, this));
			BX.removeCustomEvent(window, 'Grid::updated', BX.proxy(this._onGridUpdated, this));
			this.getPinHeader() && this.getPinHeader().destroy();
			this.getFader() && this.getFader().destroy();
			this.getResize() && this.getResize().destroy();
			this.getColsSortable() && this.getColsSortable().destroy();
			this.getRowsSortable() && this.getRowsSortable().destroy();
			this.getSettingsWindow() && this.getSettingsWindow().destroy();
			this.getActionsPanel() && this.getActionsPanel().destroy();
			this.getPinPanel() && this.getPinPanel().destroy();
			this.getPageSize() && this.getPageSize().destroy();
		},

		_onFrameResize()
		{
			BX.onCustomEvent(window, 'Grid::resize', [this]);
		},

		_onGridUpdated()
		{
			this.initStickedColumns();
			this.adjustFadePosition(this.getFadeOffset());
		},

		/**
		 * @private
		 * @return {string}
		 */
		getFrameId()
		{
			return `main-grid-tmp-frame-${this.getContainerId()}`;
		},

		enableActionsPanel()
		{
			if (this.getParam('SHOW_ACTION_PANEL'))
			{
				const panel = this.getActionsPanel().getPanel();

				if (BX.type.isDomNode(panel))
				{
					BX.removeClass(panel, this.settings.get('classDisable'));
				}
			}
		},

		disableActionsPanel()
		{
			if (this.getParam('SHOW_ACTION_PANEL'))
			{
				const panel = this.getActionsPanel().getPanel();

				if (BX.type.isDomNode(panel))
				{
					BX.addClass(panel, this.settings.get('classDisable'));
				}
			}
		},

		getSettingsWindow()
		{
			return this.gridSettings;
		},

		_onUnselectRows()
		{
			const panel = this.getActionsPanel();
			let checkbox;

			if (panel instanceof BX.Grid.ActionPanel)
			{
				checkbox = panel.getForAllCheckbox();

				if (BX.type.isDomNode(checkbox))
				{
					checkbox.checked = null;
					this.disableForAllCounter();
				}
			}

			this.adjustCheckAllCheckboxes();
		},

		/**
		 * @return {boolean}
		 */
		isIE()
		{
			if (!BX.type.isBoolean(this.ie))
			{
				this.ie = BX.hasClass(document.documentElement, 'bx-ie');
			}

			return this.ie;
		},

		/**
		 * @return {boolean}
		 */
		isTouch()
		{
			if (!BX.type.isBoolean(this.touch))
			{
				this.touch = BX.hasClass(document.documentElement, 'bx-touch');
			}

			return this.touch;
		},

		/**
		 * @param {string} paramName
		 * @param {*} [defaultValue]
		 * @return {*}
		 */
		getParam(paramName, defaultValue)
		{
			if (defaultValue === undefined)
			{
				defaultValue = null;
			}

			return (this.arParams.hasOwnProperty(paramName) ? this.arParams[paramName] : defaultValue);
		},

		/**
		 * @return {HTMLElement[]}
		 */
		getCounterTotal()
		{
			return BX.Grid.Utils.getByClass(this.getContainer(), this.settings.get('classCounterTotal'), true);
		},

		getActionKey()
		{
			return (`action_button_${this.getId()}`);
		},

		/**
		 * @return {?BX.Grid.PinHeader}
		 */
		getPinHeader()
		{
			if (this.getParam('ALLOW_PIN_HEADER'))
			{
				this.pinHeader = this.pinHeader || new BX.Grid.PinHeader(this);
			}

			return this.pinHeader;
		},

		/**
		 * @return {BX.Grid.Resize}
		 */
		getResize()
		{
			if (!(this.resize instanceof BX.Grid.Resize) && this.getParam('ALLOW_COLUMNS_RESIZE'))
			{
				this.resize = new BX.Grid.Resize(this);
			}

			return this.resize;
		},

		confirmForAll(container)
		{
			let checkbox;
			const self = this;

			if (BX.type.isDomNode(container))
			{
				checkbox = BX.Grid.Utils.getByTag(container, 'input', true);
			}

			if (checkbox.checked)
			{
				this.getActionsPanel().confirmDialog(
					{ CONFIRM: true, CONFIRM_MESSAGE: this.arParams.CONFIRM_FOR_ALL_MESSAGE },
					() => {
						if (BX.type.isDomNode(checkbox))
						{
							checkbox.checked = true;
						}

						self.selectAllCheckAllCheckboxes();
						self.getRows().selectAll();
						self.enableForAllCounter();
						self.updateCounterDisplayed();
						self.updateCounterSelected();
						self.enableActionsPanel();
						self.adjustCheckAllCheckboxes();
						self.lastRowAction = null;
						BX.onCustomEvent(window, 'Grid::allRowsSelected', []);
					},
					() => {
						if (BX.type.isDomNode(checkbox))
						{
							checkbox.checked = null;
							self.disableForAllCounter();
							self.updateCounterDisplayed();
							self.updateCounterSelected();
							self.adjustCheckAllCheckboxes();
							self.lastRowAction = null;
						}
					},
				);
			}
			else
			{
				this.unselectAllCheckAllCheckboxes();
				this.adjustCheckAllCheckboxes();
				this.getRows().unselectAll();
				this.disableForAllCounter();
				this.updateCounterDisplayed();
				this.updateCounterSelected();
				this.disableActionsPanel();
				BX.onCustomEvent(window, 'Grid::allRowsUnselected', []);
			}
		},

		disableCheckAllCheckboxes()
		{
			this.getCheckAllCheckboxes().forEach((checkbox) => {
				checkbox.getNode().disabled = true;
			});
		},

		enableCheckAllCheckboxes()
		{
			this.getCheckAllCheckboxes().forEach((checkbox) => {
				checkbox.getNode().disabled = false;
			});
		},

		indeterminateCheckAllCheckboxes()
		{
			this.getCheckAllCheckboxes().forEach((checkbox) => {
				checkbox.getNode().indeterminate = true;
			});
		},

		determinateCheckAllCheckboxes()
		{
			this.getCheckAllCheckboxes().forEach((checkbox) => {
				checkbox.getNode().indeterminate = false;
			});
		},

		editSelected()
		{
			this.disableCheckAllCheckboxes();
			this.getRows().editSelected();

			if (this.getParam('ALLOW_PIN_HEADER'))
			{
				this.getPinHeader()._onGridUpdate();
			}

			BX.onCustomEvent(window, 'Grid::resize', [this]);
		},

		editSelectedSave()
		{
			const data = { FIELDS: this.getRows().getEditSelectedValues(true) };

			if (this.getParam('ALLOW_VALIDATE'))
			{
				this.tableFade();
				data[this.getActionKey()] = 'validate';
				this.getData().request('', 'POST', data, 'validate', (res) => {
					res = JSON.parse(res);

					if (res.messages.length > 0)
					{
						this.arParams.MESSAGES = res.messages;
						this.messages.show();

						const editButton = this.getActionsPanel().getButtons()
							.find((button) => {
								return button.id === 'grid_edit_button_control';
							});

						this.tableUnfade();
						BX.fireEvent(editButton, 'click');
					}
					else
					{
						data[this.getActionKey()] = 'edit';
						this.reloadTable('POST', data);
					}
				});

				return;
			}

			if (this.getParam('HANDLE_RESPONSE_ERRORS'))
			{
				data[this.getActionKey()] = 'edit';

				const self = this;
				this.tableFade();

				this.getData().request(
					'',
					'POST',
					data,
					'',
					function(res) {
						try
						{
							res = JSON.parse(res);
						}
						catch
						{
							res = { messages: [] };
						}

						if (res.messages.length > 0)
						{
							self.arParams.MESSAGES = res.messages;
							self.messages.show();

							const editButton = self.getActionsPanel().getButtons()
								.find((button) => {
									return button.id === 'grid_edit_button_control';
								});

							self.tableUnfade();
							BX.fireEvent(editButton, 'click');

							return;
						}

						self.getRows().reset();
						const bodyRows = this.getBodyRows();

						self.getUpdater().updateContainer(this.getContainer());
						self.getUpdater().updateHeadRows(this.getHeadRows());
						self.getUpdater().updateBodyRows(bodyRows);
						self.getUpdater().updateFootRows(this.getFootRows());
						self.getUpdater().updatePagination(this.getPagination());
						self.getUpdater().updateMoreButton(this.getMoreButton());
						self.getUpdater().updateCounterTotal(this.getCounterTotal());

						self.adjustEmptyTable(bodyRows);

						self.bindOnRowEvents();

						self.bindOnMoreButtonEvents();
						self.bindOnClickPaginationLinks();
						self.bindOnClickHeader();
						self.bindOnCheckAll();
						self.updateCounterDisplayed();
						self.updateCounterSelected();
						self.disableActionsPanel();
						self.disableForAllCounter();

						if (self.getParam('SHOW_ACTION_PANEL'))
						{
							self.getUpdater().updateGroupActions(this.getActionPanel());
						}

						if (self.getParam('ALLOW_COLUMNS_SORT'))
						{
							self.colsSortable.reinit();
						}

						if (self.getParam('ALLOW_ROWS_SORT'))
						{
							self.rowsSortable.reinit();
						}

						self.tableUnfade();

						BX.onCustomEvent(window, 'Grid::updated', [self]);
					},
					(res) => {
						const editButton = self.getActionsPanel().getButtons()
							.find((button) => {
								return button.id === 'grid_edit_button_control';
							});

						self.tableUnfade();
						BX.fireEvent(editButton, 'click');
					},
				);

				return;
			}

			data[this.getActionKey()] = 'edit';
			this.reloadTable('POST', data);
		},

		getForAllKey()
		{
			return `action_all_rows_${this.getId()}`;
		},

		updateRow(id, data, url, callback)
		{
			const row = this.getRows().getById(id);

			if (row instanceof BX.Grid.Row)
			{
				row.update(data, url, callback);
			}
		},

		removeRow(id, data, url, callback)
		{
			const row = this.getRows().getById(id);

			if (row instanceof BX.Grid.Row)
			{
				row.remove(data, url, callback);
			}
		},

		addRow(data, url, callback)
		{
			const action = this.getUserOptions().getAction('GRID_ADD_ROW');
			const rowData = { action, data };
			const self = this;

			this.tableFade();
			this.getData().request(url, 'POST', rowData, null, function() {
				const bodyRows = this.getBodyRows();
				self.getUpdater().updateBodyRows(bodyRows);
				self.tableUnfade();
				self.getRows().reset();
				self.getUpdater().updateFootRows(this.getFootRows());
				self.getUpdater().updatePagination(this.getPagination());
				self.getUpdater().updateMoreButton(this.getMoreButton());
				self.getUpdater().updateCounterTotal(this.getCounterTotal());
				self.bindOnRowEvents();
				self.adjustEmptyTable(bodyRows);

				self.bindOnMoreButtonEvents();
				self.bindOnClickPaginationLinks();
				self.updateCounterDisplayed();
				self.updateCounterSelected();

				if (self.getParam('ALLOW_COLUMNS_SORT'))
				{
					self.colsSortable.reinit();
				}

				if (self.getParam('ALLOW_ROWS_SORT'))
				{
					self.rowsSortable.reinit();
				}

				BX.onCustomEvent(window, 'Grid::rowAdded', [{ data, grid: self, response: this }]);
				BX.onCustomEvent(window, 'Grid::updated', [self]);

				if (BX.type.isFunction(callback))
				{
					callback({ data, grid: self, response: this });
				}
			});
		},

		editSelectedCancel()
		{
			this.getRows().editSelectedCancel();
			this.enableCheckAllCheckboxes();

			if (this.getParam('ALLOW_PIN_HEADER'))
			{
				this.getPinHeader()._onGridUpdate();
			}
		},

		removeSelected()
		{
			const data = { ID: this.getRows().getSelectedIds() };
			const values = this.getActionsPanel().getValues();
			data[this.getActionKey()] = 'delete';
			data[this.getForAllKey()] = this.getForAllKey() in values ? values[this.getForAllKey()] : 'N';
			this.reloadTable('POST', data);
		},

		sendSelected()
		{
			const values = this.getActionsPanel().getValues();
			const selectedRows = this.getRows().getSelectedIds();
			const data = {
				rows: selectedRows,
				controls: values,
			};

			this.reloadTable('POST', data);
		},

		sendRowAction(action, data)
		{
			if (!BX.type.isPlainObject(data))
			{
				data = {};
			}

			data[this.getActionKey()] = action;

			this.reloadTable('POST', data);
		},

		/**
		 * @return {?BX.Grid.ActionPanel}
		 */
		getActionsPanel()
		{
			return this.actionPanel;
		},

		getPinPanel()
		{
			return this.pinPanel;
		},

		getApplyButton()
		{
			return BX.Grid.Utils.getByClass(this.getContainer(), this.settings.get('classPanelButton'), true);
		},

		getEditor()
		{
			return this.editor;
		},

		reload(url)
		{
			this.reloadTable('GET', {}, null, url);
		},

		getPanels()
		{
			return BX.Grid.Utils.getByClass(this.getContainer(), this.settings.get('classPanels'), true);
		},

		getEmptyBlock()
		{
			return BX.Grid.Utils.getByClass(this.getContainer(), this.settings.get('classEmptyBlock'), true);
		},

		adjustEmptyTable(rows)
		{
			function adjustEmptyBlockPosition(event)
			{
				const target = event.currentTarget;
				BX.style(emptyBlock, 'transform', `translate3d(${BX.scrollLeft(target)}px, 0px, 0`);
			}

			const filteredRows = rows.filter((row) => {
				return (
					BX.Dom.attr(row, 'data-id') !== 'template_0'
					&& !BX.Dom.hasClass(row, 'main-grid-hide')
				);
			});

			if (
				!BX.hasClass(document.documentElement, 'bx-ie')
				&& filteredRows.length === 1
				&& BX.hasClass(filteredRows[0], this.settings.get('classEmptyRows'))
			)
			{
				const gridRect = BX.pos(this.getContainer());
				const scrollBottom = BX.scrollTop(window) + BX.height(window);
				const diff = gridRect.bottom - scrollBottom;
				const panelsHeight = BX.height(this.getPanels());
				var emptyBlock = this.getEmptyBlock();
				const containerWidth = BX.width(this.getContainer());

				if (containerWidth)
				{
					BX.width(emptyBlock, containerWidth);
				}

				BX.style(emptyBlock, 'transform', `translate3d(${BX.scrollLeft(this.getScrollContainer())}px, 0px, 0`);

				BX.unbind(this.getScrollContainer(), 'scroll', adjustEmptyBlockPosition);
				BX.bind(this.getScrollContainer(), 'scroll', adjustEmptyBlockPosition);

				let parent = this.getContainer();
				let paddingOffset = 0;

				while (parent = parent.parentElement)
				{
					const parentPaddingTop = parseFloat(BX.style(parent, 'padding-top'));
					const parentPaddingBottom = parseFloat(BX.style(parent, 'padding-bottom'));

					if (!isNaN(parentPaddingTop))
					{
						paddingOffset += parentPaddingTop;
					}

					if (!isNaN(parentPaddingBottom))
					{
						paddingOffset += parentPaddingBottom;
					}
				}

				if (diff > 0)
				{
					BX.style(this.getTable(), 'min-height', `${gridRect.height - diff - panelsHeight - paddingOffset}px`);
				}
				else if (Math.abs(diff) === scrollBottom)
				{
					// If the grid is hidden
					BX.style(this.getTable(), 'min-height', '');
				}
				else
				{
					BX.style(this.getTable(), 'min-height', `${gridRect.height + Math.abs(diff) - panelsHeight - paddingOffset}px`);
				}

				BX.Dom.addClass(this.getContainer(), 'main-grid-empty-stub');

				if (this.getCurrentPage() <= 1)
				{
					this.hidePanels();
				}
			}
			else
			{
				BX.style(this.getTable(), 'min-height', '');

				// Chrome hack for 0116845 bug. @todo refactoring
				BX.style(this.getTable(), 'height', '1px');
				requestAnimationFrame(() => {
					BX.style(this.getTable(), 'height', '1px');
				});

				this.showPanels();
				BX.Dom.removeClass(this.getContainer(), 'main-grid-empty-stub');
			}
		},

		reloadTable(method, data, callback, url)
		{
			let bodyRows;

			if (!BX.type.isNotEmptyString(method))
			{
				method = 'GET';
			}

			if (!BX.type.isPlainObject(data))
			{
				data = {};
			}

			const self = this;
			this.tableFade();

			if (!BX.type.isString(url))
			{
				url = '';
			}

			this.getData().request(url, method, data, '', function() {
				BX.onCustomEvent(window, 'BX.Main.Grid:onBeforeReload', [self]);
				self.getRows().reset();
				bodyRows = this.getBodyRows();

				self.getUpdater().updateContainer(this.getContainer());
				self.getUpdater().updateHeadRows(this.getHeadRows());
				self.getUpdater().updateBodyRows(bodyRows);
				self.getUpdater().updateFootRows(this.getFootRows());
				self.getUpdater().updatePagination(this.getPagination());
				self.getUpdater().updateMoreButton(this.getMoreButton());
				self.getUpdater().updateCounterTotal(this.getCounterTotal());

				self.adjustEmptyTable(bodyRows);

				self.bindOnRowEvents();

				self.bindOnMoreButtonEvents();
				self.bindOnClickPaginationLinks();
				self.bindOnClickHeader();
				self.bindOnCheckAll();
				self.updateCounterDisplayed();
				self.updateCounterSelected();
				self.disableActionsPanel();
				self.disableForAllCounter();

				if (self.getParam('SHOW_ACTION_PANEL'))
				{
					self.getUpdater().updateGroupActions(this.getActionPanel());
				}

				if (self.getParam('ALLOW_COLUMNS_SORT'))
				{
					self.colsSortable.reinit();
				}

				if (self.getParam('ALLOW_ROWS_SORT'))
				{
					self.rowsSortable.reinit();
				}

				self.tableUnfade();

				BX.onCustomEvent(window, 'Grid::updated', [self]);

				if (BX.type.isFunction(callback))
				{
					callback();
				}

				if (self.getParam('ALLOW_PIN_HEADER'))
				{
					self.getPinHeader()._onGridUpdate();
				}
			});
		},

		getGroupEditButton()
		{
			return BX.Grid.Utils.getByClass(this.getContainer(), this.settings.get('classGroupEditButton'), true);
		},

		getGroupDeleteButton()
		{
			return BX.Grid.Utils.getByClass(this.getContainer(), this.settings.get('classGroupDeleteButton'), true);
		},

		enableGroupActions()
		{
			const editButton = this.getGroupEditButton();
			const deleteButton = this.getGroupDeleteButton();

			if (BX.type.isDomNode(editButton))
			{
				BX.removeClass(editButton, this.settings.get('classGroupActionsDisabled'));
			}

			if (BX.type.isDomNode(deleteButton))
			{
				BX.removeClass(deleteButton, this.settings.get('classGroupActionsDisabled'));
			}
		},

		disableGroupActions()
		{
			const editButton = this.getGroupEditButton();
			const deleteButton = this.getGroupDeleteButton();

			if (BX.type.isDomNode(editButton))
			{
				BX.addClass(editButton, this.settings.get('classGroupActionsDisabled'));
			}

			if (BX.type.isDomNode(deleteButton))
			{
				BX.addClass(deleteButton, this.settings.get('classGroupActionsDisabled'));
			}
		},

		closeActionsMenu()
		{
			const rows = this.getRows().getRows();
			for (let i = 0, l = rows.length; i < l; i++)
			{
				rows[i].closeActionsMenu();
			}
		},

		getPageSize()
		{
			return this.pageSize;
		},

		/**
		 * @return {?BX.Grid.Fader}
		 */
		getFader()
		{
			return this.fader;
		},

		/**
		 * @return {BX.Grid.Data}
		 */
		getData()
		{
			this.data = this.data || new BX.Grid.Data(this);

			return this.data;
		},

		/**
		 * @return {BX.Grid.Updater}
		 */
		getUpdater()
		{
			this.updater = this.updater || new BX.Grid.Updater(this);

			return this.updater;
		},

		isSortableHeader(item)
		{
			return (
				BX.hasClass(item, this.settings.get('classHeaderSortable'))
			);
		},

		isNoSortableHeader(item)
		{
			return (
				BX.hasClass(item, this.settings.get('classHeaderNoSortable'))
			);
		},

		bindOnClickHeader()
		{
			const self = this;
			let cell;

			BX.bind(this.getContainer(), 'click', (event) => {
				cell = BX.findParent(event.target, { tag: 'th' }, true, false);

				if (cell && self.isSortableHeader(cell) && !self.preventSortableClick)
				{
					const onBeforeSortEvent = new BX.Event.BaseEvent({
						data: {
							grid: self,
							columnName: BX.data(cell, 'name'),
						},
					});
					BX.Event.EventEmitter.emit('BX.Main.grid:onBeforeSort', onBeforeSortEvent);
					if (onBeforeSortEvent.isDefaultPrevented())
					{
						return;
					}
					self.preventSortableClick = false;
					self._clickOnSortableHeader(cell, event);
				}
			});
		},

		enableEditMode()
		{
			this.isEditMode = true;
		},

		disableEditMode()
		{
			this.isEditMode = false;
		},

		isEditMode()
		{
			return this.isEditMode;
		},

		getColumnHeaderCellByName(name)
		{
			return BX.Grid.Utils.getBySelector(
				this.getContainer(),
				`#${this.getId()} th[data-name="${name}"]`,
				true,
			);
		},

		getColumnByName(name)
		{
			const columns = this.getParam('DEFAULT_COLUMNS');

			return Boolean(name) && name in columns ? columns[name] : null;
		},

		adjustIndex(index)
		{
			const fixedCells = this.getAllRows()[0]
				.querySelectorAll('.main-grid-fixed-column').length;

			return (index + fixedCells);
		},

		getColumnByIndex(index)
		{
			index = this.adjustIndex(index);

			return this.getAllRows()
				.reduce((accumulator, row) => {
					if (!row.classList.contains('main-grid-row-custom') && !row.classList.contains('main-grid-row-empty'))
					{
						accumulator.push(row.children[index]);
					}

					return accumulator;
				}, []);
		},

		getAllRows()
		{
			const rows = [].slice.call(this.getTable().rows);
			const fixedTable = this.getContainer().parentElement.querySelector('.main-grid-fixed-bar table');

			if (fixedTable)
			{
				rows.push(fixedTable.rows[0]);
			}

			return rows;
		},

		hasEmptyRow(): boolean
		{
			return this.getAllRows().some((row) => BX.hasClass(row, 'main-grid-row-empty'));
		},

		initStickedColumns()
		{
			if (this.hasEmptyRow())
			{
				return;
			}

			[].slice.call(this.getAllRows()[0].children).forEach(function(cell, index) {
				if (cell.classList.contains('main-grid-sticked-column'))
				{
					this.stickyColumnByIndex(index);
				}
			}, this);

			if (this.getParam('ALLOW_COLUMNS_RESIZE'))
			{
				this.getResize().destroy();
				this.getResize().init(this);
			}
		},

		setStickedColumns(columns)
		{
			if (BX.type.isArray(columns))
			{
				const options = this.getUserOptions();
				const actions = [
					{
						action: options.getAction('GRID_SET_STICKED_COLUMNS'),
						stickedColumns: columns,
					},
				];

				options.batch(actions, () => {
					this.reloadTable();
				});
			}
		},

		getStickedColumns()
		{
			const columns = [].slice.call(this.getHead().querySelectorAll('.main-grid-cell-head'));

			return columns.reduce((acc, column) => {
				if (
					BX.hasClass(column, 'main-grid-fixed-column')
					&& !BX.hasClass(column, 'main-grid-cell-checkbox')
					&& !BX.hasClass(column, 'main-grid-cell-action')
				)
				{
					acc.push(column.dataset.name);
				}

				return acc;
			}, []);
		},

		stickyColumnByIndex(index)
		{
			const column = this.getColumnByIndex(index);
			const cellWidth = column[0].clientWidth;

			const heights = column.map((cell) => {
				return BX.height(cell);
			});

			column.forEach(function(cell, cellIndex) {
				cell.style.minWidth = `${cellWidth}px`;
				cell.style.width = `${cellWidth}px`;
				cell.style.minHeight = `${heights[cellIndex]}px`;

				const clone = BX.clone(cell);

				const lastStickyCell = this.getLastStickyCellFromRowByIndex(cellIndex);

				if (lastStickyCell)
				{
					let lastStickyCellLeft = parseInt(BX.style(lastStickyCell, 'left'));
					let lastStickyCellWidth = parseInt(BX.style(lastStickyCell, 'width'));

					lastStickyCellLeft = isNaN(lastStickyCellLeft) ? 0 : lastStickyCellLeft;
					lastStickyCellWidth = isNaN(lastStickyCellWidth) ? 0 : lastStickyCellWidth;

					cell.style.left = `${lastStickyCellLeft + lastStickyCellWidth}px`;
				}

				cell.classList.add('main-grid-fixed-column');
				cell.classList.add('main-grid-cell-static');
				clone.classList.add('main-grid-cell-static');

				if (this.getColsSortable())
				{
					this.getColsSortable().unregister(cell);
					this.getColsSortable().unregister(clone);
				}

				BX.insertAfter(clone, cell);
			}, this);

			this.adjustFadePosition(this.getFadeOffset());
		},

		adjustFixedColumnsPosition()
		{
			const fixedCells = this.getAllRows()[0]
				.querySelectorAll('.main-grid-fixed-column').length;

			const columnsPosition = [].slice.call(this.getAllRows()[0].children)
				.reduce((accumulator, cell, index, columns) => {
					let cellLeft;
					let cellWidth;

					if (columns[index - 1] && columns[index - 1].classList.contains('main-grid-fixed-column'))
					{
						cellLeft = parseInt(BX.style(columns[index - 1], 'left'));
						cellWidth = parseInt(BX.style(columns[index - 1], 'width'));

						cellLeft = isNaN(cellLeft) ? 0 : cellLeft;
						cellWidth = isNaN(cellWidth) ? 0 : cellWidth;

						accumulator.push({ index: index + 1, left: (cellLeft + cellWidth) });
					}

					return accumulator;
				}, []);

			columnsPosition
				.forEach(function(item) {
					const column = this.getColumnByIndex(item.index - fixedCells);

					column.forEach((cell) => {
						if (item.index !== columnsPosition[columnsPosition.length - 1].index)
						{
							cell.style.left = `${item.left}px`;
						}
					});
				}, this);

			this.getAllRows()
				.forEach((row) => {
					const height = BX.height(row);
					const cells = [].slice.call(row.children);

					cells.forEach((cell) => {
						cell.style.minHeight = `${height}px`;
					});
				});
		},

		getLastStickyCellFromRowByIndex(index)
		{
			return [].slice.call(this.getAllRows()[index].children)
				.reduceRight((accumulator, cell) => {
					if (!accumulator && cell.classList.contains('main-grid-fixed-column'))
					{
						accumulator = cell;
					}

					return accumulator;
				}, null);
		},

		getFadeOffset()
		{
			let fadeOffset = 0;
			const lastStickyCell = this.getLastStickyCellFromRowByIndex(0);

			if (lastStickyCell)
			{
				let lastStickyCellLeft = parseInt(BX.style(lastStickyCell, 'left'));
				let lastStickyCellWidth = lastStickyCell.offsetWidth;

				lastStickyCellLeft = isNaN(lastStickyCellLeft) ? 0 : lastStickyCellLeft;
				lastStickyCellWidth = isNaN(lastStickyCellWidth) ? 0 : lastStickyCellWidth;

				fadeOffset = lastStickyCellLeft + lastStickyCellWidth;
			}

			return fadeOffset;
		},

		adjustFadePosition(offset)
		{
			const earLeft = this.getFader().getEarLeft();
			const shadowLeft = this.getFader().getShadowLeft();

			earLeft.style.left = `${offset}px`;
			shadowLeft.style.left = `${offset}px`;
		},

		/**
		 * @param {string|object} column
		 */
		sortByColumn(column)
		{
			let headerCell = null;
			let header = null;

			if (BX.type.isPlainObject(column))
			{
				header = column;
				header.sort_url = this.prepareSortUrl(column);
			}
			else
			{
				headerCell = this.getColumnHeaderCellByName(column);
				header = this.getColumnByName(column);
			}

			if (header && (Boolean(headerCell) && !BX.hasClass(headerCell, this.settings.get('classLoad')) || !headerCell))
			{
				Boolean(headerCell) && BX.addClass(headerCell, this.settings.get('classLoad'));
				this.tableFade();

				const self = this;

				this.getUserOptions().setSort(header.sort_by, header.sort_order, () => {
					self.getData().request(header.sort_url, null, null, 'sort', function() {
						self.rows = null;
						self.getUpdater().updateHeadRows(this.getHeadRows());
						self.getUpdater().updateBodyRows(this.getBodyRows());
						self.getUpdater().updatePagination(this.getPagination());
						self.getUpdater().updateMoreButton(this.getMoreButton());

						self.bindOnRowEvents();

						self.bindOnMoreButtonEvents();
						self.bindOnClickPaginationLinks();
						self.bindOnCheckAll();
						self.updateCounterDisplayed();
						self.updateCounterSelected();
						self.disableActionsPanel();
						self.disableForAllCounter();

						if (self.getParam('SHOW_ACTION_PANEL'))
						{
							self.getActionsPanel().resetForAllCheckbox();
						}

						if (self.getParam('ALLOW_ROWS_SORT'))
						{
							self.rowsSortable.reinit();
						}

						if (self.getParam('ALLOW_COLUMNS_SORT'))
						{
							self.colsSortable.reinit();
						}

						BX.onCustomEvent(window, 'BX.Main.grid:sort', [header, self]);
						BX.onCustomEvent(window, 'Grid::updated', [self]);
						self.tableUnfade();
					});
				});
			}
		},

		prepareSortUrl(header)
		{
			let url = window.location.toString();

			if ('sort_by' in header)
			{
				url = BX.util.add_url_param(url, { by: header.sort_by });
			}

			if ('sort_order' in header)
			{
				url = BX.util.add_url_param(url, { order: header.sort_order });
			}

			return url;
		},

		_clickOnSortableHeader(header, event)
		{
			event.preventDefault();

			this.sortByColumn(BX.data(header, 'name'));
		},

		getObserver()
		{
			return BX.Grid.observer;
		},

		initRowsDragAndDrop()
		{
			this.rowsSortable = new BX.Grid.RowsSortable(this);
		},

		initColsDragAndDrop()
		{
			this.colsSortable = new BX.Grid.ColsSortable(this);
		},

		/**
		 * @return {BX.Grid.RowsSortable}
		 */
		getRowsSortable()
		{
			return this.rowsSortable;
		},

		/**
		 * @return {BX.Grid.ColsSortable}
		 */
		getColsSortable()
		{
			return this.colsSortable;
		},

		getUserOptionsHandlerUrl()
		{
			return this.userOptionsHandlerUrl || '';
		},

		/**
		 * @return {BX.Grid.UserOptions}
		 */
		getUserOptions()
		{
			return this.userOptions;
		},

		getCheckAllCheckboxes()
		{
			const checkAllNodes = BX.Grid.Utils.getByClass(this.getContainer(), this.settings.get('classCheckAllCheckboxes'));

			return checkAllNodes.map((current) => {
				return new BX.Grid.Element(current);
			});
		},

		selectAllCheckAllCheckboxes()
		{
			this.getCheckAllCheckboxes().forEach((current) => {
				current.getNode().checked = true;
			});
		},

		unselectAllCheckAllCheckboxes()
		{
			this.getCheckAllCheckboxes().forEach((current) => {
				current.getNode().checked = false;
			});
		},

		adjustCheckAllCheckboxes()
		{
			const total = this.getRows().getBodyChild().filter((row) => {
				return row.isShown() && Boolean(row.getCheckbox());
			}).length;

			const selected = this.getRows().getSelected().filter((row) => {
				return row.isShown();
			}).length;

			if (total > 0 && selected > 0 && total === selected)
			{
				this.selectAllCheckAllCheckboxes();
			}
			else
			{
				this.unselectAllCheckAllCheckboxes();
			}

			if (selected > 0 && selected < total)
			{
				this.indeterminateCheckAllCheckboxes();
			}
			else
			{
				this.determinateCheckAllCheckboxes();
			}
		},

		bindOnCheckAll()
		{
			const self = this;

			this.getCheckAllCheckboxes().forEach((current) => {
				current.getObserver().add(
					current.getNode(),
					'change',
					self._clickOnCheckAll,
					self,
				);
			});
		},

		_clickOnCheckAll(event)
		{
			event.preventDefault();

			this.toggleSelectionAll();
			this.determinateCheckAllCheckboxes();
		},

		toggleSelectionAll()
		{
			if (!this.getRows().isAllSelected()
				&& (this.lastRowAction === 'select' || !this.lastRowAction))
			{
				this.getRows().selectAll();
				this.selectAllCheckAllCheckboxes();
				this.enableActionsPanel();
				BX.onCustomEvent(window, 'Grid::allRowsSelected', [this]);
			}
			else
			{
				this.getRows().unselectAll();
				this.unselectAllCheckAllCheckboxes();
				this.disableActionsPanel();
				BX.onCustomEvent(window, 'Grid::allRowsUnselected', [this]);
			}

			delete this.lastRowAction;

			this.updateCounterSelected();
		},

		bindOnClickPaginationLinks()
		{
			const self = this;

			this.getPagination().getLinks().forEach((current) => {
				current.getObserver().add(
					current.getNode(),
					'click',
					self._clickOnPaginationLink,
					self,
				);
			});
		},

		bindOnMoreButtonEvents()
		{
			const self = this;

			this.getMoreButton().getObserver().add(
				this.getMoreButton().getNode(),
				'click',
				self._clickOnMoreButton,
				self,
			);
		},

		bindOnRowEvents()
		{
			const observer = this.getObserver();
			const showCheckboxes = this.getParam('SHOW_ROW_CHECKBOXES');
			const enableCollapsibleRows = this.getParam('ENABLE_COLLAPSIBLE_ROWS');

			this.getRows().getBodyChild().forEach(function(current) {
				showCheckboxes && observer.add(current.getNode(), 'click', this._onClickOnRow, this);
				current.getDefaultAction() && observer.add(current.getNode(), 'dblclick', this._onRowDblclick, this);
				current.getActionsButton() && observer.add(current.getActionsButton(), 'click', this._clickOnRowActionsButton, this);
				enableCollapsibleRows && current.getCollapseButton() && observer.add(current.getCollapseButton(), 'click', this._onCollapseButtonClick, this);
			}, this);
		},

		_onCollapseButtonClick(event)
		{
			event.preventDefault();
			event.stopPropagation();

			const row = this.getRows().get(event.currentTarget);
			row.toggleChildRows();

			if (row.isCustom())
			{
				this.getUserOptions().setCollapsedGroups(this.getRows().getIdsCollapsedGroups());
			}
			else
			{
				this.getUserOptions().setExpandedRows(this.getRows().getIdsExpandedRows());
			}

			BX.fireEvent(document.body, 'click');
		},

		_clickOnRowActionsButton(event)
		{
			const row = this.getRows().get(event.target);
			event.preventDefault();

			if (row.actionsMenuIsShown())
			{
				row.closeActionsMenu();
			}
			else
			{
				row.showActionsMenu();
			}
		},

		_onRowDblclick(event)
		{
			event.preventDefault();
			const row = this.getRows().get(event.target);
			let defaultJs = '';

			if (!row.isEdit())
			{
				clearTimeout(this.clickTimer);
				this.clickPrevent = true;

				try
				{
					defaultJs = row.getDefaultAction();
					eval(defaultJs);
				}
				catch (err)
				{
					console.warn(err);
				}
			}
		},

		_onClickOnRow(event)
		{
			const clickDelay = 50;
			const selection = window.getSelection();

			if (event.target.nodeName === 'LABEL')
			{
				event.preventDefault();
			}

			if (event.shiftKey || selection.toString().length === 0)
			{
				if (event.shiftKey)
				{
					selection.removeAllRanges();
				}

				this.clickTimer = setTimeout(BX.delegate(function() {
					if (!this.clickPrevent)
					{
						clickActions.apply(this, [event]);
					}
					this.clickPrevent = false;
				}, this), clickDelay);
			}

			function clickActions(event)
			{
				let rows; let row; let containsNotSelected; let min; let max; let
					contentContainer;
				let isPrevent = true;

				if (event.target.nodeName !== 'A' && event.target.nodeName !== 'INPUT')
				{
					row = this.getRows().get(event.target);
					if (row)
					{
						contentContainer = row.getContentContainer(event.target);

						if (BX.type.isDomNode(contentContainer) && event.target.nodeName !== 'TD' && event.target !== contentContainer)
						{
							isPrevent = BX.data(contentContainer, 'prevent-default') === 'true';
						}

						if (isPrevent)
						{
							if (row.getCheckbox())
							{
								rows = [];

								this.currentIndex = 0;

								this.getRows().getRows().forEach(function(currentRow, index) {
									if (currentRow === row)
									{
										this.currentIndex = index;
									}
								}, this);

								this.lastIndex = this.lastIndex || this.currentIndex;

								if (event.shiftKey)
								{
									min = Math.min(this.currentIndex, this.lastIndex);
									max = Math.max(this.currentIndex, this.lastIndex);

									while (min <= max)
									{
										rows.push(this.getRows().getRows()[min]);
										min++;
									}

									containsNotSelected = rows.some((current) => {
										return !current.isSelected();
									});

									if (containsNotSelected)
									{
										rows.forEach((current) => {
											current.select();
										});
										this.lastRowAction = 'select';
										BX.onCustomEvent(window, 'Grid::selectRows', [rows, this]);
									}
									else
									{
										rows.forEach((current) => {
											current.unselect();
										});
										this.lastRowAction = 'unselect';
										BX.onCustomEvent(window, 'Grid::unselectRows', [rows, this]);
									}
								}
								else
									if (row.isSelected())
									{
										this.lastRowAction = 'unselect';
										row.unselect();
										BX.onCustomEvent(window, 'Grid::unselectRow', [row, this]);
									}
									else
									{
										this.lastRowAction = 'select';
										row.select();
										BX.onCustomEvent(window, 'Grid::selectRow', [row, this]);
									}

								this.updateCounterSelected();
								this.lastIndex = this.currentIndex;
							}

							this.adjustRows();
							this.adjustCheckAllCheckboxes();
						}
					}
				}
			}
		},

		adjustRows()
		{
			if (this.getRows().isSelected())
			{
				BX.onCustomEvent(window, 'Grid::thereSelectedRows', [this]);
				this.enableActionsPanel();
			}
			else
			{
				BX.onCustomEvent(window, 'Grid::noSelectedRows', []);
				this.disableActionsPanel();
			}
		},

		getPagination()
		{
			return new BX.Grid.Pagination(this);
		},

		getState()
		{
			return window.history.state;
		},

		tableFade()
		{
			BX.addClass(this.getTable(), this.settings.get('classTableFade'));
			this.getLoader().show();
			BX.onCustomEvent('Grid::disabled', [this]);
		},

		tableUnfade()
		{
			BX.removeClass(this.getTable(), this.settings.get('classTableFade'));
			this.getLoader().hide();
			BX.onCustomEvent('Grid::enabled', [this]);
		},

		_clickOnPaginationLink(event)
		{
			event.preventDefault();

			const self = this;
			const link = this.getPagination().getLink(event.target);

			if (!link.isLoad())
			{
				this.getUserOptions().resetExpandedRows();

				link.load();
				this.tableFade();

				this.getData().request(link.getLink(), null, null, 'pagination', function() {
					self.rows = null;
					self.getUpdater().updateBodyRows(this.getBodyRows());
					self.getUpdater().updateHeadRows(this.getHeadRows());
					self.getUpdater().updateMoreButton(this.getMoreButton());
					self.getUpdater().updatePagination(this.getPagination());

					self.bindOnRowEvents();
					self.bindOnMoreButtonEvents();
					self.bindOnClickPaginationLinks();
					self.bindOnCheckAll();
					self.updateCounterDisplayed();
					self.updateCounterSelected();
					self.disableActionsPanel();
					self.disableForAllCounter();

					if (self.getParam('SHOW_ACTION_PANEL'))
					{
						self.getActionsPanel().resetForAllCheckbox();
					}

					if (self.getParam('ALLOW_ROWS_SORT'))
					{
						self.rowsSortable.reinit();
					}

					if (self.getParam('ALLOW_COLUMNS_SORT'))
					{
						self.colsSortable.reinit();
					}

					link.unload();
					self.tableUnfade();

					BX.onCustomEvent(window, 'Grid::updated', [self]);
				});
			}
		},

		_clickOnMoreButton(event)
		{
			event.preventDefault();

			const self = this;
			const moreButton = this.getMoreButton();

			moreButton.load();

			this.getData().request(moreButton.getLink(), null, null, 'more', function() {
				self.getUpdater().appendBodyRows(this.getBodyRows());
				self.getUpdater().updateMoreButton(this.getMoreButton());
				self.getUpdater().updatePagination(this.getPagination());

				self.getRows().reset();
				self.bindOnRowEvents();

				self.bindOnMoreButtonEvents();
				self.bindOnClickPaginationLinks();
				self.bindOnCheckAll();
				self.updateCounterDisplayed();
				self.updateCounterSelected();

				if (self.getParam('ALLOW_PIN_HEADER'))
				{
					self.getPinHeader()._onGridUpdate();
				}

				if (self.getParam('ALLOW_ROWS_SORT'))
				{
					self.rowsSortable.reinit();
				}

				if (self.getParam('ALLOW_COLUMNS_SORT'))
				{
					self.colsSortable.reinit();
				}

				self.unselectAllCheckAllCheckboxes();

				BX.onCustomEvent(window, 'Grid::updated', [self]);
			});
		},

		getAjaxId()
		{
			return BX.data(
				this.getContainer(),
				this.settings.get('ajaxIdDataProp'),
			);
		},

		update(data, action)
		{
			let newRows; let newHeadRows; let newNavPanel; let thisBody; let thisHead; let
				thisNavPanel;

			if (!BX.type.isNotEmptyString(data))
			{
				return;
			}

			thisBody = BX.Grid.Utils.getByTag(this.getTable(), 'tbody', true);
			thisHead = BX.Grid.Utils.getByTag(this.getTable(), 'thead', true);
			thisNavPanel = BX.Grid.Utils.getByClass(this.getContainer(), this.settings.get('classNavPanel'), true);

			data = BX.create('div', { html: data });
			newHeadRows = BX.Grid.Utils.getByClass(data, this.settings.get('classHeadRow'));
			newRows = BX.Grid.Utils.getByClass(data, this.settings.get('classDataRows'));
			newNavPanel = BX.Grid.Utils.getByClass(data, this.settings.get('classNavPanel'), true);

			if (action === this.settings.get('updateActionMore'))
			{
				this.getRows().addRows(newRows);
				this.unselectAllCheckAllCheckboxes();
			}

			if (action === this.settings.get('updateActionPagination'))
			{
				BX.cleanNode(thisBody);
				this.getRows().addRows(newRows);
				this.unselectAllCheckAllCheckboxes();
			}

			if (action === this.settings.get('updateActionSort'))
			{
				BX.cleanNode(thisHead);
				BX.cleanNode(thisBody);
				thisHead.appendChild(newHeadRows[0]);
				this.getRows().addRows(newRows);
			}

			thisNavPanel.innerHTML = newNavPanel.innerHTML;

			this.bindOnRowEvents();

			this.bindOnMoreButtonEvents();
			this.bindOnClickPaginationLinks();
			this.bindOnClickHeader();
			this.bindOnCheckAll();
			this.updateCounterDisplayed();
			this.updateCounterSelected();
			this.sortable.reinit();
		},

		getCounterDisplayed()
		{
			return BX.Grid.Utils.getByClass(this.getContainer(), this.settings.get('classCounterDisplayed'));
		},

		getCounterSelected()
		{
			return BX.Grid.Utils.getByClass(this.getContainer(), this.settings.get('classCounterSelected'));
		},

		updateCounterDisplayed()
		{
			const counterDisplayed = this.getCounterDisplayed();
			let rows;

			if (BX.type.isArray(counterDisplayed))
			{
				rows = this.getRows();
				counterDisplayed.forEach((current) => {
					if (BX.type.isDomNode(current))
					{
						current.innerText = rows.getCountDisplayed();
					}
				});
			}
		},

		updateCounterSelected()
		{
			const counterSelected = this.getCounterSelected();
			let rows;

			if (BX.type.isArray(counterSelected))
			{
				rows = this.getRows();
				counterSelected.forEach((current) => {
					if (BX.type.isDomNode(current))
					{
						current.innerText = rows.getCountSelected();
					}
				});
			}
		},

		getContainerId()
		{
			return this.containerId;
		},

		getId()
		{
			// ID is equals to container Id
			return this.containerId;
		},

		getContainer()
		{
			return BX(this.getContainerId());
		},

		getCounter()
		{
			if (!this.counter)
			{
				this.counter = BX.Grid.Utils.getByClass(this.getContainer(), this.settings.get('classCounter'));
			}

			return this.counter;
		},

		enableForAllCounter()
		{
			const counter = this.getCounter();

			if (BX.type.isArray(counter))
			{
				counter.forEach(function(current) {
					BX.addClass(current, this.settings.get('classForAllCounterEnabled'));
				}, this);
			}
		},

		disableForAllCounter()
		{
			const counter = this.getCounter();

			if (BX.type.isArray(counter))
			{
				counter.forEach(function(current) {
					BX.removeClass(current, this.settings.get('classForAllCounterEnabled'));
				}, this);
			}
		},

		getScrollContainer()
		{
			if (!this.scrollContainer)
			{
				this.scrollContainer = BX.Grid.Utils.getByClass(this.getContainer(), this.settings.get('classScrollContainer'), true);
			}

			return this.scrollContainer;
		},

		getWrapper()
		{
			if (!this.wrapper)
			{
				this.wrapper = BX.Grid.Utils.getByClass(this.getContainer(), this.settings.get('classWrapper'), true);
			}

			return this.wrapper;
		},

		getFadeContainer()
		{
			if (!this.fadeContainer)
			{
				this.fadeContainer = BX.Grid.Utils.getByClass(this.getContainer(), this.settings.get('classFadeContainer'), true);
			}

			return this.fadeContainer;
		},

		getTable()
		{
			return BX.Grid.Utils.getByClass(this.getContainer(), this.settings.get('classTable'), true);
		},

		getHeaders()
		{
			return BX.Grid.Utils.getBySelector(this.getWrapper(), `.main-grid-header[data-relative="${this.getContainerId()}"]`);
		},

		getHead()
		{
			return BX.Grid.Utils.getByTag(this.getContainer(), 'thead', true);
		},

		getBody()
		{
			return BX.Grid.Utils.getByTag(this.getContainer(), 'tbody', true);
		},

		getFoot()
		{
			return BX.Grid.Utils.getByTag(this.getContainer(), 'tfoot', true);
		},

		/**
		 * @return {BX.Grid.Rows}
		 */
		getRows()
		{
			if (!(this.rows instanceof BX.Grid.Rows))
			{
				this.rows = new BX.Grid.Rows(this);
			}

			return this.rows;
		},

		getMoreButton()
		{
			const node = BX.Grid.Utils.getByClass(this.getContainer(), this.settings.get('classMoreButton'), true);

			return new BX.Grid.Element(node, this);
		},

		/**
		 * Gets loader instance
		 * @return {BX.Grid.Loader}
		 */
		getLoader()
		{
			if (!(this.loader instanceof BX.Grid.Loader))
			{
				this.loader = new BX.Grid.Loader(this);
			}

			return this.loader;
		},

		blockSorting()
		{
			const headerCells = BX.Grid.Utils.getByClass(
				this.getContainer(),
				this.settings.get('classHeadCell'),
			);

			headerCells.forEach(function(header) {
				if (this.isSortableHeader(header))
				{
					BX.removeClass(header, this.settings.get('classHeaderSortable'));
					BX.addClass(header, this.settings.get('classHeaderNoSortable'));
				}
			}, this);
		},

		unblockSorting()
		{
			const headerCells = BX.Grid.Utils.getByClass(
				this.getContainer(),
				this.settings.get('classHeadCell'),
			);

			headerCells.forEach(function(header) {
				if (this.isNoSortableHeader(header) && header.dataset.sortBy)
				{
					BX.addClass(header, this.settings.get('classHeaderSortable'));
					BX.removeClass(header, this.settings.get('classHeaderNoSortable'));
				}
			}, this);
		},

		confirmDialog(action, then, cancel)
		{
			let dialog; let popupContainer; let applyButton; let
				cancelButton;

			if ('CONFIRM' in action && action.CONFIRM)
			{
				action.CONFIRM_MESSAGE = action.CONFIRM_MESSAGE || this.arParams.CONFIRM_MESSAGE;
				action.CONFIRM_APPLY_BUTTON = action.CONFIRM_APPLY_BUTTON || this.arParams.CONFIRM_APPLY;
				action.CONFIRM_CANCEL_BUTTON = action.CONFIRM_CANCEL_BUTTON || this.arParams.CONFIRM_CANCEL;

				dialog = new BX.PopupWindow(
					`${this.getContainerId()}-confirm-dialog`,
					null,
					{
						content: `<div class="main-grid-confirm-content">${action.CONFIRM_MESSAGE}</div>`,
						titleBar: 'CONFIRM_TITLE' in action ? action.CONFIRM_TITLE : '',
						autoHide: false,
						zIndex: 9999,
						overlay: 0.4,
						offsetTop: -100,
						closeIcon: false,
						closeByEsc: true,
						events: {
							onClose()
							{
								BX.unbind(window, 'keydown', hotKey);
								dialog.destroy();
							},
						},
						buttons: [
							new BX.PopupWindowButton({
								text: action.CONFIRM_APPLY_BUTTON,
								id: `${this.getContainerId()}-confirm-dialog-apply-button`,
								events: {
									click()
									{
										BX.type.isFunction(then) ? then() : null;
										this.popupWindow.close();
										this.popupWindow.destroy();
										BX.onCustomEvent(window, 'Grid::confirmDialogApply', [this]);
										BX.unbind(window, 'keydown', hotKey);
									},
								},
							}),
							new BX.PopupWindowButtonLink({
								text: action.CONFIRM_CANCEL_BUTTON,
								id: `${this.getContainerId()}-confirm-dialog-cancel-button`,
								events: {
									click()
									{
										BX.type.isFunction(cancel) ? cancel() : null;
										this.popupWindow.close();
										this.popupWindow.destroy();
										BX.onCustomEvent(window, 'Grid::confirmDialogCancel', [this]);
										BX.unbind(window, 'keydown', hotKey);
									},
								},
							}),
						],
					},
				);

				if (!dialog.isShown())
				{
					dialog.show();
					popupContainer = dialog.popupContainer;
					BX.removeClass(popupContainer, this.settings.get('classCloseAnimation'));
					BX.addClass(popupContainer, this.settings.get('classShowAnimation'));
					applyButton = BX(`${this.getContainerId()}-confirm-dialog-apply-button`);
					cancelButton = BX(`${this.getContainerId()}-confirm-dialog-cancel-button`);

					BX.bind(window, 'keydown', hotKey);
				}
			}
			else
			{
				BX.type.isFunction(then) ? then() : null;
			}

			function hotKey(event)
			{
				if (event.code === 'Enter')
				{
					event.preventDefault();
					event.stopPropagation();
					BX.fireEvent(applyButton, 'click');
				}

				if (event.code === 'Escape')
				{
					event.preventDefault();
					event.stopPropagation();
					BX.fireEvent(cancelButton, 'click');
				}
			}
		},

		getCurrentPage()
		{
			const currentPage = parseInt(this.arParams.CURRENT_PAGE);
			if (BX.Type.isNumber(currentPage))
			{
				return currentPage;
			}

			return 0;
		},

		/**
		 * @private
		 * @return {Element | any}
		 */
		getEmptyStub()
		{
			return this.getTable().querySelector('.main-grid-row-empty');
		},

		/**
		 * @private
		 */
		showEmptyStub()
		{
			const stub = this.getEmptyStub();
			if (stub)
			{
				BX.Dom.attr(stub, 'hidden', null);
				BX.Dom.addClass(this.getContainer(), 'main-grid-empty-stub');
				if (this.getCurrentPage() <= 1)
				{
					this.hidePanels();
				}
			}
		},

		/**
		 * @private
		 */
		hideEmptyStub()
		{
			const stub = this.getEmptyStub();
			if (stub)
			{
				BX.Dom.attr(stub, 'hidden', true);
				BX.Dom.removeClass(this.getContainer(), 'main-grid-empty-stub');
				BX.Dom.style(this.getTable(), 'min-height', null);
				this.showPanels();
			}
		},

		/**
		 * @private
		 */
		showPanels()
		{
			BX.Dom.show(this.getPanels());
			if (this.getPanels().offsetHeight > 0)
			{
				BX.Dom.removeClass(this.getContainer(), 'main-grid-empty-footer');
			}
		},

		/**
		 * @private
		 */
		hidePanels()
		{
			BX.Dom.hide(this.getPanels());
			BX.Dom.addClass(this.getContainer(), 'main-grid-empty-footer');
		},

		/**
		 * @return {BX.Grid.Row}
		 */
		getTemplateRow()
		{
			const templateRow = BX.Runtime.clone(
				this.getRows().getBodyChild(true).find((row) => {
					return row.getId() === 'template_0';
				}),
			);
			const cloned = BX.Runtime.clone(templateRow.getNode());
			BX.Dom.prepend(cloned, this.getBody());

			const checkbox = cloned.querySelector('[type="checkbox"]');
			if (checkbox)
			{
				BX.Dom.attr(checkbox, 'disabled', null);
				BX.Dom.attr(checkbox, 'data-disabled', null);
			}

			return new BX.Grid.Row(this, cloned);
		},

		/**
		 * @private
		 * @return {{}[]}
		 */
		getRowEditorValue(withTemplate)
		{
			this.rows = null;

			return this.getRows().getSelected(withTemplate).map((row) => {
				return row.getEditorValue();
			});
		},

		/**
		 * @private
		 * @return {HTMLElement|HTMLBodyElement}
		 */
		getRowEditorActionPanel()
		{
			if (!this.rowEditorActionPanel)
			{
				this.rowEditorActionPanel = BX.Dom.create({
					tag: 'div',
					props: { className: 'main-ui-grid-row-editor-actions-panel' },
					children: [
						BX.Dom.create({
							tag: 'span',
							props: { className: 'ui-btn ui-btn-success' },
							text: this.arParams.SAVE_BUTTON_LABEL,
							events: {
								click: this.saveRows.bind(this),
							},
						}),
						BX.Dom.create({
							tag: 'span',
							props: { className: 'ui-btn ui-btn-link' },
							text: this.arParams.CANCEL_BUTTON_LABEL,
							events: {
								click: this.hideRowsEditor.bind(this),
							},
						}),
					],
				});
			}

			return this.rowEditorActionPanel;
		},

		/**
		 * @private
		 */
		showRowEditorActionsPanel()
		{
			const panel = this.getRowEditorActionPanel();
			BX.Dom.append(panel, this.actionPanel.getPanel());
		},

		/**
		 * @private
		 */
		hideRowEditorActionsPanel()
		{
			BX.Dom.remove(this.getRowEditorActionPanel());
		},

		/**
		 * @return {BX.Grid.Row}
		 */
		prependRowEditor()
		{
			return this.addRowEditor('prepend');
		},

		/**
		 * @return {BX.Grid.Row}
		 */
		appendRowEditor()
		{
			return this.addRowEditor('append');
		},

		/**
		 * @return {BX.Grid.Row}
		 */
		addRowEditor(direction = 'prepend')
		{
			BX.Dom.style(this.getTable(), 'min-height', null);
			const templateRow = this.getTemplateRow();
			this.editableRows.push(templateRow);

			if (direction === 'prepend')
			{
				templateRow.prependTo(this.getBody());
			}
			else
			{
				templateRow.appendTo(this.getBody());
			}

			templateRow.show();
			templateRow.select();
			templateRow.edit();

			this.getRows().reset();

			if (this.getParam('ALLOW_ROWS_SORT'))
			{
				this.rowsSortable.reinit();
			}

			if (this.getParam('ALLOW_COLUMNS_SORT'))
			{
				this.colsSortable.reinit();
			}

			this.hideEmptyStub();

			return templateRow;
		},

		hideRowsEditor()
		{
			this.editableRows.forEach((row) => {
				BX.Dom.remove(row.getNode());
			});
			this.editableRows = [];
		},

		saveRows()
		{
			const value = this.getRowEditorValue(true);

			this.emitAsync('onAddRowsAsync', { rows: value })
				.then((result) => {
					result.forEach((rowData, rowIndex) => {
						const row = this.editableRows[rowIndex];
						if (row)
						{
							row.editCancel();
							row.unselect();
							row.makeCountable();

							row.setId(rowData.id);
							row.setActions(rowData.actions);
							row.setCellsContent(rowData.columns);
						}
					});

					this.bindOnRowEvents();
					this.updateCounterDisplayed();
					this.updateCounterSelected();

					this.editableRows = [];
				});
		},

		getRealtime(): BX.Grid.Realtime
		{
			return this.cache.remember('realtime', () => {
				return new BX.Grid.Realtime({
					grid: this,
				});
			});
		},
	};
})();
