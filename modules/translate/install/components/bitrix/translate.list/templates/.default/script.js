;(function ()
{
	'use strict';

	BX.namespace('BX.Translate');
	if (BX.Translate.PathList)
	{
		return;
	}

	var PathList = function ()
	{
		this.actionMode = '';
		this.tabId = '';
		this.gridId = '';
		this.filterId = '';
		this.mode = '';
		this.relUrl = '';
		this.messages = {};
		this.viewMode = [];
		this.defaults = {};
	};

	PathList.prototype.VIEW_MODE = {
		CountPhrases: "CountPhrases",
		CountFiles: "CountFiles",
		UntranslatedPhrases: "UntranslatedPhrases",
		UntranslatedFiles: "UntranslatedFiles",
		HideEmptyFolders: "HideEmptyFolders",
		ShowDiffLinks: "ShowDiffLinks"
	};

	PathList.prototype.STYLES = {
		gridLink: 'translate-link-grid',
		editLink: 'translate-link-edit',
		startIndexLink: 'translate-start-indexing',
		menuItem: 'translate-menu-item',
		menuItemChecked: 'menu-popup-item-accept'
	};

	PathList.prototype.ACTIONS = {
		fileList: 'FILE_LIST',
		searchFile: 'SEARCH_FILE',
		searchPhrase: 'SEARCH_PHRASE'
	};

	/**
	 * @param {Object} param
	 * @param {String} [param.relUrl]
	 * @param {Object} [param.defaults]
	 * @param {string} [param.defaults.startingPath]
	 * @param {string} [param.defaults.CODE_ENTRY]
	 * @param {string} [param.defaults.PHRASE_ENTRY]
	 * @param {Array} [param.defaults.initFolders]
	 * @param {String} [param.tabId]
	 * @param {String} [param.gridId]
	 * @param {String} [param.filterId]
	 * @param {Object} [param.styles]
	 * @param {String} [param.styles.GridLink]
	 * @param {String} [param.styles.EditLink]
	 * @param {String} [param.styles.MenuItemChecked]
	 * @param {String} [param.viewMode]
	 * @param {String} [param.mode]
	 * @param {Object} [param.messages]
	 * @param {Array} [param.extraMenuItems]
	 * @constructor
	 */
	PathList.prototype.init = function (param)
	{
		param = param || {};
		param.defaults = param.defaults || {};

		if (!BX.type.isNotEmptyString(param.relUrl))
			throw "BX.Translate.PathList: 'relUrl' parameter missing.";

		if (!BX.type.isNotEmptyString(param.defaults.startingPath))
			throw "BX.Translate.PathList: 'defaults.startingPath' parameter missing.";

		if (!BX.type.isNotEmptyString(param.tabId))
			throw "BX.Translate.PathList: 'tabId' parameter missing.";

		if (!BX.type.isNotEmptyString(param.gridId))
			throw "BX.Translate.PathList: 'gridId' parameter missing.";

		if (!BX.type.isNotEmptyString(param.filterId))
			throw "BX.Translate.PathList: 'filterId' parameter missing.";

		if (BX.type.isNotEmptyString(param.mode))
		{
			this.mode = param.mode;
		}

		if (BX.type.isNotEmptyString(param.actionMode))
		{
			this.actionMode = param.actionMode;
		}
		else
		{
			this.actionMode = this.ACTIONS.fileList;
		}

		this.defaults = param.defaults;
		this.relUrl = param.relUrl;
		this.tabId = param.tabId;
		this.gridId = param.gridId;
		this.filterId = param.filterId;

		if (BX.type.isArray(param.styles))
		{
			this.STYLES = BX.mergeEx(this.STYLES, param.styles);
		}

		if (BX.type.isArray(param.viewMode))
		{
			this.viewMode = param.viewMode;
		}
		else
		{
			this.viewMode.push(this.VIEW_MODE.CountPhrases);
		}

		if (BX.type.isPlainObject(param.messages))
		{
			this.messages = param.messages;
		}

		if (BX.type.isArray(param.extraMenuItems))
		{
			extraMenuItems = param.extraMenuItems;
		}

		setTimeout(BX.proxy(this.loadGridParams, this), 100);
		setTimeout(BX.proxy(this.initGridLinks, this), 120);

		BX.addCustomEvent('Grid::updated', BX.delegate(this.initGridLinks, this));
		BX.addCustomEvent('Grid::updated', BX.delegate(this.loadGridParams, this));

		BX.addCustomEvent('BX.Main.Filter:beforeApply', BX.delegate(this.filterBeforeApply, this));

		BX.addCustomEvent('onAjaxFailure', BX.delegate(function(errType, status, config){
			if (errType == 'auth')
			{
				if (typeof(this) == "object" && typeof(this.filterId) != "undefined")
				{
					BX.UI.Notification.Center.notify({
						content: this.getMessage("AuthError")
					});
					top.location = top.location.href;
				}
			}
			else if (errType == 'status')
			{
				if (typeof(config) == "object" && typeof(config.xhr) == "object" && config.xhr instanceof XMLHttpRequest)
				{
					try
					{
						var data = JSON.parse(config.xhr.responseText);
						if (BX.type.isPlainObject(data))
						{
							if (data.status === 'error')
							{
								if (data.errors[0])
								{
									BX.UI.Notification.Center.notify({
										content: data.errors[0].message
									});
								}
							}
						}
					}
					catch (err){}
				}
			}
		}, this));

		BX.Event.EventEmitter.subscribe(BX.UI.StepProcessing.ProcessEvent.BeforeRequest, BX.delegate(function(event){
			/** @type {BX.Main.Event.BaseEvent} event */
			var process = event.data.process ? event.data.process : {};
			var params = event.data.actionData ? event.data.actionData : {};

			/** @type {BX.UI.StepProcessing.Process} process */
			if (process instanceof BX.UI.StepProcessing.Process)
			{
				process.setParam('path', this.getCurrentPath());
				process.method = 'POST';
			}
			if (params instanceof FormData)
			{
				params.append('path', this.getCurrentPath());
				params.append('tabId', this.tabId);
				params.append('AJAX_CALL', 'Y');
				if(this.mode == 'admin')
				{
					params.append('admin_section', 'Y');
					params.append('lang', BX.message('LANGUAGE_ID'));
				}
			}
			else
			{
				params['path'] = this.getCurrentPath();
				params['tabId'] = this.tabId;
				params['AJAX_CALL'] = 'Y';
				if(this.mode == 'admin')
				{
					params['admin_section'] = 'Y';
					params['lang'] = BX.message('LANGUAGE_ID');
				}
			}
		}, this));

		var nodeViewMode = BX('bx-translate-mode-menu-view-anchor');
		if(nodeViewMode)
		{
			BX.bind(nodeViewMode, 'click', BX.proxy(this.showViewModeMenu, this));
		}

		var nodeInitFolder = BX('bx-translate-init-folder');
		if(nodeInitFolder)
		{
			BX.bind(nodeInitFolder, 'click', BX.proxy(this.showInitFolderMenu, this));
		}

		var nodeExtraMenu = BX('bx-translate-extra-menu-anchor');
		if(nodeExtraMenu)
		{
			BX.bind(nodeExtraMenu, 'click', BX.proxy(this.showExtraMenu, this));
		}

		BX.addCustomEvent('Grid::beforeRequest', BX.delegate(this.onBeforeGridRequest, this));

		BX.bind(window, 'popstate', BX.proxy(this.onPopState, this));
		this.replaceAddressLink(null);
	};

	/**
	 */
	PathList.prototype.setActionMode = function (actionMode)
	{
		if (BX.type.isNotEmptyString(actionMode))
		{
			this.actionMode = actionMode;
		}
		return this;
	};

	/**
	 */
	PathList.prototype.getActionMode = function ()
	{
		return this.actionMode;
	};

	/**
	 * @return {String}
	 */
	PathList.prototype.getMessage = function (name)
	{
		return BX.type.isNotEmptyString(this.messages[name]) ? this.messages[name] : "";
	};


	//region Filter

	/** @param {BX.Main.Filter} */
	var filter;

	/**
	 * @return {BX.Main.Filter}
	 */
	PathList.prototype.getFilter = function ()
	{
		if (typeof(filter) !== "object" || !filter instanceof BX.Main.Filter)
		{
			if (this.filterId !== "" && typeof(BX.Main.filterManager) !== "undefined")
			{
				filter = BX.Main.filterManager.getById(this.filterId);
			}
		}
		if (typeof(filter) === "object" && filter instanceof BX.Main.Filter)
		{
			return filter;
		}

		return null;
	};

	/**
	 * @param {String} filterId
	 * @param {Object} params
	 * @param {BX.Main.Filter} filterInstance
	 * @param {BX.Promise} filterPromise
	 * @return {BX.Main.Filter}
	 */
	PathList.prototype.filterBeforeApply = function (filterId, params, filterInstance, filterPromise)
	{
		if (filterId == this.filterId)
		{
			var path, url;
			if (params.action == 'clear')
			{

				path = this.defaults.startingPath;
				var inp = this.getFilter().getSearch().getInput();
				inp.value = this.defaults.startingPath;
			}
			else if (params.action == 'apply')
			{
				var values = this.getFilter().getFilterFieldsValues();
				path = values.FIND;
				path = path.replace(/[\\]+/ig, '/');
				if (BX.type.isNotEmptyString(values.PHRASE_TEXT) && !BX.type.isNotEmptyString(values.LANGUAGE_ID))
				{
					values.LANGUAGE_ID = BX.message('LANGUAGE_ID');
				}
				if (BX.type.isNotEmptyString(values.PHRASE_TEXT) && !BX.type.isNotEmptyString(values.PHRASE_ENTRY))
				{
					if (this.defaults.PHRASE_ENTRY)
						values.PHRASE_ENTRY = this.defaults.PHRASE_ENTRY;
				}

				if (BX.type.isNotEmptyString(values.PHRASE_CODE) && !BX.type.isNotEmptyString(values.CODE_ENTRY))
				{
					if (this.defaults.CODE_ENTRY)
						values.CODE_ENTRY = this.defaults.CODE_ENTRY;
				}

				this.getFilter().getApi().setFields(values);
			}

			url = this.addLinkParam(this.getCurrentUrl(), 'path', path);
			url = this.addLinkParam(url, 'tabId', this.tabId);
			this.replaceAddressLink(url, path, values);
		}
	};

	/**
	 * @param {string} link
	 * @param {string} name
	 * @param {string} value
	 * @return {string}
	 */
	PathList.prototype.addLinkParam = function(link, name, value)
	{
		if(!link.length)
		{
			return '?' + name + '=' + value;
		}
		link = BX.Uri.removeParam(link, name);
		if(link.indexOf('?') != -1)
		{
			return link + '&' + name + '=' + value;
		}
		return link + '?' + name + '=' + value;
	};

	/**
	 * @param {string} url
	 * @param {string} path
	 * @param {Object} filter
	 */
	PathList.prototype.replaceAddressLink = function(url, path, filter)
	{
		if ('history' in window)
		{
			if (typeof (window.history.pushState) === "function")
			{
				path = path || this.getFilter().getSearch().getSearchString();
				path = path.replace(/[\\]+/ig, '/');

				filter = filter || this.getFilter().getFilterFieldsValues();
				var state = {"path": path, "filter": filter};
				if (url)
				{
					url = this.addLinkParam(url, 'tabId', this.tabId);
					window.history.pushState(state, null, url);
				}
				else
				{
					url = this.getCurrentUrl();
					url = this.addLinkParam(url, 'tabId', this.tabId);
					window.history.replaceState(state, null, url);
				}
			}
		}
	};

	PathList.prototype.getCurrentUrl = function ()
	{
		return window.location.protocol + "//" + window.location.hostname + (window.location.port != '' ? ':' + window.location.port : '') +
			window.location.pathname + window.location.search;
	};

	PathList.prototype.onPopState = function (event)
	{
		var state = event.state || window.history.state;
		if (!state || !state.path || !state.filter)
		{
			window.location.reload();
		}
	};

	//endregion


	//region Grid

	/** @param {BX.Main.grid} */
	var grid;

	/**
	 * @return {BX.Main.grid}
	 */
	PathList.prototype.getGrid = function ()
	{
		if (typeof(grid) !== "object" || typeof(grid.instance) !== "object" || !grid.instance instanceof BX.Main.grid)
		{
			if (this.gridId !== "" && BX(this.gridId) && typeof(BX.Main.gridManager) !== "undefined")
			{
				grid = BX.Main.gridManager.getById(this.gridId);
			}
		}
		if (typeof(grid) === "object" && typeof(grid.instance) === "object" && grid.instance instanceof BX.Main.grid)
		{
			return grid.instance;
		}

		return null;
	};

	/**
	 * @param {BX.Grid.Data} gridData
	 * @param {Object} requestParams
	 * @param {Object} requestParams.data
	 * @param {String} requestParams.url
	 */
	PathList.prototype.onBeforeGridRequest = function(gridData, requestParams)
	{
		if (requestParams.method == 'POST')
		{
			if (!BX.type.isPlainObject(requestParams.data))
			{
				requestParams.data = {};
			}
			requestParams.data.viewMode = this.viewMode.join(',');
			requestParams.data.tabId = this.tabId;
			requestParams.data.path = this.getCurrentPath();
			requestParams.data.AJAX_CALL = 'Y';
			if (this.mode == 'admin')
			{
				requestParams.data.admin_section = 'Y';
				requestParams.data.lang = BX.message('LANGUAGE_ID');
			}
		}
		else
		{
			requestParams.url = BX.Uri.removeParam(requestParams.url, ['viewMode', 'tabId', 'path']);

			requestParams.url = BX.Uri.addParam(requestParams.url, {
				viewMode: this.viewMode.join(','),
				tabId: this.tabId,
				path: this.getCurrentPath()
			});
			if (this.mode == 'admin')
			{
				requestParams.url = BX.Uri.addParam(requestParams.url, {
					admin_section: 'Y',
					lang: BX.message('LANGUAGE_ID')
				});
			}
		}
	};

	PathList.prototype.reloadGrid = function ()
	{
		if(this.getGrid())
		{
			this.getGrid().reload();
		}
	};

	PathList.prototype.loadGrid = function (url, params)
	{
		if(this.getGrid())
		{
			this.toggleGridLoader(true);
			if (!BX.type.isNotEmptyString(url))
			{
				url = this.relUrl;
			}
			if (BX.type.isPlainObject(params))
			{
				this.getGrid().reloadTable('POST', params, BX.proxy(this.initGridLinks, this), url);
			}
			else
			{
				this.getGrid().reloadTable('GET', {}, BX.proxy(this.initGridLinks, this), url);
			}
		}
	};

	PathList.prototype.getGridRow = function (rowId)
	{
		return this.getGrid().getRows().getById('' + rowId);
	};

	PathList.prototype.markGridRowWait = function (rowIds)
	{
		for(var row, i = 0; i < rowIds.length; i++)
		{
			row = this.getGridRow(rowIds[i]);
			if (row)
			{
				row.getNode().style.opacity = 0.5;
			}
		}
	};

	PathList.prototype.markGridRowNormal = function (rowIds)
	{
		for(var row, i = 0; i < rowIds.length; i++)
		{
			row = this.getGridRow(rowIds[i]);
			if (row)
			{
				row.getNode().style.opacity = 1;
			}
		}
	};

	PathList.prototype.removeGridRow = function (rowIds)
	{
		for(var row, i = 0; i < rowIds.length; i++)
		{
			row = this.getGridRow(rowIds[i]);
			if (row)
			{
				row.getNode().remove();
			}
		}
	};

	PathList.prototype.loadGridParams = function()
	{
		var grid = BX("bx-translate-list-params");
		if (grid)
		{
			if ('dataset' in grid)
			{
				if ('actionmode' in grid.dataset)
				{
					this.actionMode = grid.dataset.actionmode;
					this.tabId = grid.dataset.tabid;
				}
			}
		}
	};

	PathList.prototype.initGridLinks = function()
	{
		var grid = this.getGrid();
		if(grid)
		{
			var gridLinks = grid.getContainer().querySelectorAll('.' + this.STYLES.gridLink);
			for (var i = 0; i < gridLinks.length; i++)
			{
				BX.bind(gridLinks[i], 'click', BX.proxy(this.linkGridClick, this));
				BX.bind(gridLinks[i], 'mousedown', BX.proxy(this.linkGridClick, this));
			}

			gridLinks = grid.getContainer().querySelectorAll('.' + this.STYLES.startIndexLink);
			for (i = 0; i < gridLinks.length; i++)
			{
				BX.bind(gridLinks[i], 'click', function () {
					BX.UI.StepProcessing.ProcessManager.get('index').showDialog()
				});
			}
		}
	};


	PathList.prototype.linkGridClick = function (event)
	{
		var withModifier = event.ctrlKey || event.shiftKey || event.metaKey;
		var isLeftClick = (BX.getEventButton(event) === BX.MSLEFT);
		if (isLeftClick)
		{
			var pathLink, url, row, path;
			if (BX.hasClass(event.currentTarget, this.STYLES.gridLink))
			{
				pathLink = event.currentTarget;
			}
			else
			{
				pathLink = event.currentTarget.closest('.' + this.STYLES.gridLink);
			}
			if (pathLink)
			{
				url = pathLink.href;
				if (BX.type.isNotEmptyString(url))
				{
					if (withModifier)
					{
						window.open(url);
					}
					else
					{
						if (this.getFilter())
						{
							row = pathLink.closest('.main-grid-row[data-id]');
							path = BX.data(row, 'path');
							if (BX.type.isNotEmptyString(path))
							{
								this.getFilter().getSearch().input.value = path;
							}
						}

						this.getFilter().getApi().apply();
					}
				}
			}
		}
		return !isLeftClick;
	};


	PathList.prototype.rowGridClick = function (params)
	{
		var pathLink, fileLink, url, row, data;

		row = this.getGrid().getRows().getById(params.rowId);

		if (params.action === 'FILE_LIST')
		{
			if (row.node)
			{
				pathLink = row.node.querySelector('.' + this.STYLES.gridLink);
			}
			if (pathLink)
			{
				url = pathLink.href;
				if (BX.type.isNotEmptyString(url))
				{
					if (this.getFilter())
					{
						data = row.getDataset();
						if (BX.type.isNotEmptyString(data.path))
						{
							this.getFilter().getSearch().input.value = data.path;
						}
					}

					this.getFilter().getApi().apply();
				}
			}

		}
		else if (params.action === 'EDIT')
		{
			if (row.node)
			{
				fileLink = row.node.querySelector('.' + this.STYLES.editLink);
			}
			if (fileLink)
			{
				url = fileLink.href;
				if (BX.type.isNotEmptyString(url))
				{

					window.location.href = url;
					//todo: open slider here
				}
			}
		}
	};


	PathList.prototype.toggleGridLoader = function (isShow)
	{
		var grid = this.getGrid();
		if(grid)
		{
			if (isShow) {
				grid.tableFade();
			} else {
				grid.tableUnfade();
			}
		}
	};

	PathList.prototype.sendGridAction = function (action, id)
	{
		this.toggleGridLoader(true);
	};

	PathList.prototype.remove = function (id)
	{
		this.sendGridAction('remove', id);
	};

	//endregion


	/**
	 * @return {String}
	 */
	PathList.prototype.getCurrentPath = function ()
	{
		var inp = this.getFilter().getSearch().getInput(),
			path = BX.type.isNotEmptyString(inp.value) ? inp.value : this.defaults.startingPath;

		path = path.replace(/[\\]+/ig, '/');
		if (inp.value !== path)
		{
			inp.value = path;
		}

		return path;
	};

	/**
	 * @param {String} path
	 */
	PathList.prototype.setPath = function (path)
	{
		if (BX.type.isNotEmptyString(path))
		{
			if (this.getFilter())
			{
				this.getFilter().getSearch().input.value = path;
				this.getFilter().getApi().apply();
			}
		}
	};



	//region Mode View Menu

	/** @type {BX.PopupMenuWindow} modeViewPopup */
	var modeViewPopup;

	PathList.prototype.showViewModeMenu = function (event)
	{
		var node = event.currentTarget;
		if (!modeViewPopup)
		{
			modeViewPopup = new BX.PopupMenuWindow(
				'translate-view-mode-menu',
				node,
				[
					{
						'id': this.VIEW_MODE.CountPhrases,
						'text': this.getMessage('ViewModeMenuCountPhrases'),
						'className': this.STYLES.menuItem + ' translate-view-mode-counter ' +
							(this.viewMode.indexOf(this.VIEW_MODE.CountPhrases) >= 0 ? this.STYLES.menuItemChecked : ''),
						'onclick': this.setViewMode.bind(this, this.VIEW_MODE.CountPhrases,
							{'fellowClass': 'translate-view-mode-counter', 'title': this.getMessage('ViewModeTitleCountPhrases')})
					},
					{
						'id': this.VIEW_MODE.CountFiles,
						'text': this.getMessage('ViewModeMenuCountFiles'),
						'className': this.STYLES.menuItem + ' translate-view-mode-counter ' +
							(this.viewMode.indexOf(this.VIEW_MODE.CountFiles) >= 0 ? this.STYLES.menuItemChecked : ''),
						'onclick': this.setViewMode.bind(this, this.VIEW_MODE.CountFiles,
							{'fellowClass': 'translate-view-mode-counter', 'title': this.getMessage('ViewModeTitleCountFiles')})
					},
					{
						'id': this.VIEW_MODE.UntranslatedPhrases,
						'text': this.getMessage('ViewModeMenuUntranslatedPhrases'),
						'className': this.STYLES.menuItem + ' translate-view-mode-counter ' +
							(this.viewMode.indexOf(this.VIEW_MODE.UntranslatedPhrases) >= 0 ? this.STYLES.menuItemChecked : ''),
						'onclick': this.setViewMode.bind(this, this.VIEW_MODE.UntranslatedPhrases,
							{'fellowClass': 'translate-view-mode-counter', 'title': this.getMessage('ViewModeTitleUntranslatedPhrases')})
					},
					{
						'id': this.VIEW_MODE.UntranslatedFiles,
						'text': this.getMessage('ViewModeMenuUntranslatedFiles'),
						'className': this.STYLES.menuItem + ' translate-view-mode-counter ' +
							(this.viewMode.indexOf(this.VIEW_MODE.UntranslatedFiles) >= 0 ? this.STYLES.menuItemChecked : ''),
						'onclick': this.setViewMode.bind(this, this.VIEW_MODE.UntranslatedFiles,
							{'fellowClass': 'translate-view-mode-counter', 'title': this.getMessage('ViewModeTitleUntranslatedFiles')})
					},
					{'delimiter': true},
					{
						'id': this.VIEW_MODE.HideEmptyFolders,
						'text': this.getMessage('ViewModeMenuHideEmptyFolders'),
						'className': this.STYLES.menuItem + ' translate-view-mode-emptiness ' +
							(this.viewMode.indexOf(this.VIEW_MODE.HideEmptyFolders) >= 0 ? this.STYLES.menuItemChecked : ''),
						'onclick': this.setViewMode.bind(this, this.VIEW_MODE.HideEmptyFolders)
					},
					{
						'id': this.VIEW_MODE.ShowDiffLinks,
						'text': this.getMessage('ViewModeMenuShowDiffLinks'),
						'className': this.STYLES.menuItem + ' translate-view-mode-difflinks ' +
							(this.viewMode.indexOf(this.VIEW_MODE.ShowDiffLinks) >= 0 ? this.STYLES.menuItemChecked : ''),
						'onclick': this.setViewMode.bind(this, this.VIEW_MODE.ShowDiffLinks)
					}
				],
				{
					autoHide: true,
					autoClose: true,
					closeByEsc: true
				}
			);
		}

		modeViewPopup.bindElement = node;
		modeViewPopup.show();
	};

	PathList.prototype.setViewMode = function (mode, filter)
	{
		filter = filter || {};
		var radioMode = BX.type.isNotEmptyString(filter.fellowClass), wasChanged = false;
		var replaceTitle = BX.type.isNotEmptyString(filter.title);
		var inx, item, items = modeViewPopup.getMenuItems();

		if(!Array.prototype.removeVal)
		{
			Array.prototype.removeVal = function (val) {
				var ind = this.indexOf(val);
				if (ind !== -1) this.splice(ind, 1);
			};
		}


		if (radioMode)
		{
			if (this.viewMode.indexOf(mode) < 0)
			{
				wasChanged = true;

				for (inx in items)
				{
					if (!items.hasOwnProperty(inx)) continue;
					item = items[inx];

					if (!BX.type.isNotEmptyString(item.className))
					{
						continue;
					}
					if (item.className.indexOf(filter.fellowClass) < 0)
					{
						continue;
					}

					this.viewMode.removeVal(item.id);
				}

				this.viewMode.push(mode);
			}
		}
		else
		{
			wasChanged = true;
			if (this.viewMode.indexOf(mode) < 0)
			{
				this.viewMode.push(mode);
			}
			else
			{
				this.viewMode.removeVal(mode);
			}
		}

		for (inx in items)
		{
			if(!items.hasOwnProperty(inx)) continue;
			item = items[inx];

			if (this.viewMode.indexOf(item.id) < 0)
			{
				BX.removeClass(item.layout.item, this.STYLES.menuItemChecked);
			}
			else
			{
				BX.addClass(item.layout.item, this.STYLES.menuItemChecked);
			}
		}

		if (replaceTitle)
		{
			modeViewPopup.bindElement.innerHTML = filter.title;
		}

		if (wasChanged)
		{
			setTimeout(BX.delegate(function () {
				this.getFilter().getApi().apply();
			}, this), 200);
		}

		if (modeViewPopup)
		{
			modeViewPopup.close();
		}
	};

	//endregion


	//region Extra Menu

	/** @type {BX.PopupMenuWindow} extraMenuPopup */
	var extraMenuPopup;
	/** @type {Array} extraMenuItems */
	var extraMenuItems;

	PathList.prototype.showExtraMenu = function (event)
	{
		var node = event.currentTarget;
		if (!extraMenuPopup)
		{
			extraMenuPopup = new BX.PopupMenuWindow(
				'translate-extra-menu',
				node,
				extraMenuItems,
				{
					autoHide: true,
					autoClose: true,
					closeByEsc: true
				}
			);
		}

		extraMenuPopup.bindElement = node;
		extraMenuPopup.show();
	};

	//endregion


	//region Init Folder Menu

	/** @type {BX.PopupMenuWindow} initFolderMenuPopup */
	var initFolderMenuPopup;

	PathList.prototype.showInitFolderMenu = function (event)
	{
		var node = event.currentTarget;
		if (!initFolderMenuPopup)
		{
			var initFolderMenuItems = [];
			for (var i = 0; i < this.defaults.initFolders.length; i++)
			{
				initFolderMenuItems.push({
					"id": "translate-init-folder-" + i,
					"text": this.defaults.initFolders[i],
					'className': this.STYLES.menuItem + ' ' + (this.defaults.startingPath === this.defaults.initFolders[i] ? this.STYLES.menuItemChecked : ''),
					'onclick': this.setInitFolder.bind(this, this.defaults.initFolders[i])
				});
			}

			initFolderMenuPopup = new BX.PopupMenuWindow(
				'translate-init-folder-menu',
				node,
				initFolderMenuItems,
				{
					autoHide: true,
					autoClose: true,
					closeByEsc: true
				}
			);
		}

		initFolderMenuPopup.bindElement = node;
		initFolderMenuPopup.show();
	};

	PathList.prototype.setInitFolder = function (path)
	{
		var inx, item, items = initFolderMenuPopup.getMenuItems();
		for (inx in items)
		{
			if(!items.hasOwnProperty(inx)) continue;
			item = items[inx];

			if (item.text === path)
			{
				BX.addClass(item.layout.item, this.STYLES.menuItemChecked);
				//initFolderMenuPopup.bindElement.innerHTML = item.text;
			}
			else
			{
				BX.removeClass(item.layout.item, this.STYLES.menuItemChecked);
			}
		}
		if (initFolderMenuPopup)
		{
			initFolderMenuPopup.close();
		}

		this.setPath(path);
	};
	//endregion


	//region groupActionMenu

	/** @type {Object} groupActionMenuItems */
	var groupActionMenuItems = {};

	/**
	 * @param {string} id
	 * @param {function} payLoad
	 */
	PathList.prototype.addGroupAction = function (id, payLoad)
	{
		groupActionMenuItems[id] = payLoad;
	};

	PathList.prototype.callGroupAction = function ()
	{
		var grid = this.getGrid(),
			actionPanel = grid.getActionsPanel(),
			selectedIds = actionPanel.getSelectedIds(),
			actions = actionPanel.getValues(),
			currentGroupAction = actions.action_button,
			rows = grid.getRows(),
			pathList = [], codeList = [], row, rowData;


		if(selectedIds.length > 0)
		{
			for(var i in selectedIds)
			{
				row = rows.getById(selectedIds[i]);
				if (row)
				{
					rowData = row.getDataset();
					if (rowData)
					{
						if ('path' in rowData)
						{
							pathList.push(rowData.path);
						}
						if ('code' in rowData)
						{
							codeList.push(rowData.code);
						}
					}
				}
			}

			if (typeof(groupActionMenuItems[currentGroupAction]) === "function")
			{
				groupActionMenuItems[currentGroupAction].apply(this, [pathList, codeList]);
			}
		}
	};

	//endregion

	/**
	 * @param {BX.Main.ui.select} select
	 * @param {Object} data
	 * @param {String} fieldName
	 * @param {Array} groupValues
	 */
	PathList.prototype.radioOnMultiple = function (select, data, fieldName, groupValues) {
		var filter = this.getFilter(),
			control = select.input.closest('.main-ui-control.main-ui-multi-select'),
			filterApi = filter.getApi();

		if(!BX.isParentForNode(filter.getFilter(), select.input))
		{
			return;
		}
		if (control)
		{
			if (BX.data(control, 'name') == fieldName)
			{
				var values, newValues;
				newValues = filter.getFilterFieldsValues();
				values = BX.clone(newValues[fieldName]);
				newValues[fieldName] = {};
				for (var g in groupValues)
				{
					if (!groupValues.hasOwnProperty(g)) continue;
					if (groupValues[g].indexOf(data.VALUE) >= 0)
					{
						for (var v in values)
						{
							if (!values.hasOwnProperty(v)) continue;
							if (groupValues[g].indexOf(values[v]) >= 0)
							{
								if (data.VALUE != values[v])
								{
									delete values[v];
								}
							}
						}
					}
				}
				newValues[fieldName] = values;
				filterApi.setFields(newValues);
			}
		}
	};

	BX.Translate.PathList = new PathList();

})(window);