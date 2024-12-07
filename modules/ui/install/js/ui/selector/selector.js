(function() {

var BX = window.BX;

BX.namespace('BX.UI');

if (!!BX.UI.Selector)
{
	return;
}

BX.UI.Selector = function(params)
{
	this.statuses = {
		searchWaiterEnabled: false
	};
	this.manager = BX.UI.SelectorManager;
	this.id = (BX.type.isNotEmptyString(params.id) ? params.id : null);
	this.fieldName = (BX.type.isNotEmptyString(params.fieldName) ? params.fieldName : null);
	this.tabs = {
		list: {},
		selected: null
	};
	this.dialogGroups = {};
	this.entities = (BX.type.isNotEmptyObject(params.entities) ? params.entities : {});
	this.networkItems = {};
	this.sortData = {};
	this.itemsSelected = (BX.type.isNotEmptyObject(params.itemsSelected) ? params.itemsSelected : {}); // obItemsSelected
	this.itemsUndeletable = (BX.type.isNotEmptyObject(params.itemsUndeletable) ? params.itemsUndeletable : []);
	this.options = (BX.type.isNotEmptyObject(params.options) ? params.options : {});

	this.bindOptions = (BX.type.isNotEmptyObject(params.bindOptions) ? params.bindOptions : {});
	this.bindOptions.forceBindPosition = true;

	this.popups = {
		container: null,
		main: null, // popupWindow
		search: null,
		inviteEmailUser: null // inviteEmailUserWindow
	};
	this.nodes = {
		input: (BX.type.isDomNode(params.input) ? params.input : null),
		inputBox: (BX.type.isDomNode(params.inputBox) ? params.inputBox : null),
		inputItemsContainer: (BX.type.isDomNode(params.inputItemsContainer) ? params.inputItemsContainer : null),
		tag: (BX.type.isDomNode(params.tag) ? params.tag : null),
		containerContentsContainer: null,
		searchContentsContainer: null,
		contentWaiter: null, // bx-lm-box-waiter-content-text
		searchWaiter: null
	};
	this.cursors = {}; // obCursorPosition
	this.result = {
		search: []
	}; // obResult
	this.resultChanged = {}; // bResultMoved
	this.tmpSearchResult = {
		client: [],
		ajax: []
	}; // tmpSearchResult
	this.callback = (BX.type.isNotEmptyObject(params.callback) ? params.callback : {});
	this.callbackBefore = (BX.type.isNotEmptyObject(params.callbackBefore) ? params.callbackBefore : {});
	this.searchXhr = null;
	this.searchRequestId = null;
	this.timeouts = {
		search: null
	};

	this.dialogNodes = {
		tabsContainer: null,
		contentsContainer: null
	};

	this.treeItemLoaded = {};

	this.clientDBSearchResult = {
		users: {}
	}; // oDbUserSearchResult
	this.ajaxSearchResult = {
		users: {}
	}; // oAjaxUserSearchResult
	this.containerSearchResult = {};

	this.searchInstance = null;
	this.navigationInstance = null;
	this.renderInstance = null;

	this.postponeSearch = false;
	this.closeByEmptySearchResult = false;

	var searchOptions = this.getOption('search');
 	if (
		BX.type.isNotEmptyString(searchOptions.useClientDatabase)
		&& searchOptions.useClientDatabase == 'Y'
	)
	{
		BX.onCustomEvent('BX.UI.SelectorManager:initClientDatabase', []);
	}
};

BX.UI.Selector.create = function(params)
{
	var selectorInstance = new BX.UI.Selector(params);
	BX.UI.SelectorManager.instances[params.id] = selectorInstance;

	BX.onCustomEvent('BX.UI.SelectorManager:onCreate', [ params.id ]);

	return selectorInstance;
};

BX.UI.Selector.prototype.getSearchInstance = function()
{
	if (this.searchInstance === null)
	{
		this.searchInstance = BX.UI.Selector.Search.create({
			selectorInstance: this
		});
	}
	return this.searchInstance;
};

BX.UI.Selector.prototype.getNavigationInstance = function()
{
	if (this.navigationInstance === null)
	{
		this.navigationInstance = BX.UI.Selector.Navigation.create({
			selectorInstance: this
		});
	}

	return this.navigationInstance;
};

BX.UI.Selector.prototype.getRenderInstance = function()
{
	if (this.renderInstance === null)
	{
		this.renderInstance = BX.UI.Selector.Render.create({
			selectorInstance: this
		});
	}

	return this.renderInstance;
};

BX.UI.Selector.prototype.getPopupBind = function()
{
	return (
		BX.type.isNotEmptyObject(this.bindOptions.position)
			? this.bindOptions.position
			: this.bindOptions.node
	);
};

BX.UI.Selector.prototype.getPopupZIndex = function()
{
	return (
		BX.type.isNotEmptyObject(this.bindOptions)
		&& typeof this.bindOptions.zIndex != 'undefined'
			? this.bindOptions.zIndex
			: 1200
	);
};

BX.UI.Selector.prototype.openDialog = function()
{
	var popupBind = this.getPopupBind();
	if (
		BX.type.isDomNode(popupBind)
		&& !document.body.contains(popupBind)
	)
	{
		return;
	}

	var isPromiseReturned = false;
	var promise = null;

	if (typeof this.callbackBefore.openDialog == 'function')
	{
		if (BX.type.isNotEmptyObject(this.callbackBefore.context))
		{
			promise = this.callbackBefore.openDialog.bind(this.callbackBefore.context)();
			isPromiseReturned =
				promise &&
				(
					Object.prototype.toString.call(promise) === "[object Promise]" ||
					promise.toString() === "[object BX.Promise]"
				)
			;
		}
	}

	if (!isPromiseReturned)
	{
		promise = Promise.resolve();
	}

	promise.then(
		this.openDialogPromiseFulfilled.bind(this),
		this.openDialogPromiseRejected.bind(this)
	);
};

BX.UI.Selector.prototype.openDialogPromiseFulfilled = function(result)
{
	var popupBind = this.getPopupBind();

	if (
		BX.type.isDomNode(popupBind)
		&& !document.body.contains(popupBind)
	)
	{
		return;
	}

	if (this.getOption('useContainer') == 'Y') // obUseContainer
	{
		if (!this.openContainer())
		{
			return false;
		}

		BX.cleanNode(this.nodes.containerContentsContainer);
		this.nodes.containerContentsContainer.appendChild(this.getDialogContent());

		var entityType = null;

		for (var itemId in this.itemsSelected)
		{
			if (this.itemsSelected.hasOwnProperty(itemId))
			{
				entityType = this.manager.convertEntityType(this.itemsSelected[itemId]);
				if (this.callback.select)
				{
					this.callback.select({
						item: this.entities[entityType].items[itemId],
						entityType: entityType,
						selectorId: this.id,
						state: 'init'
					})
				}
			}
		}

		this.popups.container.setAngle({});
		this.popups.container.setBindElement(popupBind);
		this.popups.container.show();
	}
	else
	{
		const id = `bx-selector-dialog-${this.id}`;
		const popup = BX.Main.PopupManager.getPopupById(id);
		popup?.destroy();

		this.popups.main = new BX.PopupWindow({
			id,
			bindElement: popupBind,
			autoHide: (this.getOption('popupAutoHide') != 'N'),
			zIndex: this.getPopupZIndex(),
			className: this.getRenderInstance().class.popup,
			offsetLeft: this.bindOptions.offsetLeft,
			offsetTop: this.bindOptions.offsetTop,
			bindOptions: this.bindOptions,
			cacheable: false,
			closeByEsc: true,
			closeIcon: (
				this.getOption('showCloseIcon') == 'Y'
					? {
						top: '12px',
						right: '15px'
					}
					: false
			),
			lightShadow: true,
			events: {
				onPopupShow: function() {
					if (
						this.manager.statuses.allowSendEvent
						&& this.callback.openDialog
					)
					{
						this.callback.openDialog({
							selectorId: this.id
						});
					}

					if (
						this.popups.inviteEmailUser
						&& this.popups.inviteEmailUser.isShown()
					)
					{
						this.popups.inviteEmailUser.close();
					}
				}.bind(this),
				onPopupDestroy : function() {
					this.popups.main = null;

					if (
						this.manager.statuses.allowSendEvent
						&& this.callback.closeDialog
					)
					{
						this.callback.closeDialog({
							selectorId: this.id
						});
					}
				}.bind(this)
			},
			content: this.getDialogContent()
		});

		this.popups.main.setAngle({});
		this.popups.main.show();
	}

	if (this.getOption('enableLast') != 'N')
	{
		this.tabs.selected = 'last';
	}

	if (
		this.getOption('enableLast') == 'N'
		&& this.getOption('enableSonetgroups') != 'Y'
		&& this.getOption('enableDepartments') == 'Y'
	)
	{
		this.switchTab({
			code: 'departments'
		});

		if (this.getOption('useContainer') == 'Y')
		{
			this.popups.container.adjustPosition();
		}
		else
		{
			this.popups.main.adjustPosition();
		}
	}

	this.getNavigationInstance().hoverFirstItem({
		tab: this.tabs.selected
	});
};

BX.UI.Selector.prototype.openDialogPromiseRejected = function(reason)
{
	this.callback.closeDialog({
		selectorId: this.id
	});
};

BX.UI.Selector.prototype.openContainer = function()
{
	if(this.popups.container)
	{
		this.popups.container.destroy();
	}

	this.popups.container = new BX.PopupWindow({
		id: 'bx-selector-dialog-' + this.id + '-container',
		bindElement: this.getPopupBind(),
		autoHide: (this.getOption('popupAutoHide') != 'N'),
		zIndex: this.getPopupZIndex(),
		className: this.getRenderInstance().class.popup,
		offsetLeft: this.bindOptions.offsetLeft,
		offsetTop: this.bindOptions.offsetTop,
		bindOptions: this.bindOptions,
		cacheable: false,
		closeByEsc: true,
		closeIcon: (
			this.getOption('showCloseIcon') == 'Y'
				? {
					top: 0,
					right: 0
				}
				: false
		),
		lightShadow: true,
		events: {
			onPopupShow: function() {
				if (
					this.manager.statuses.allowSendEvent
					&& this.callback.openDialog
				)
				{
					this.callback.openDialog({
						selectorId: this.id
					});
				}

				if (
					this.popups.inviteEmailUser
					&& this.popups.inviteEmailUser.isShown()
				)
				{
					this.popups.inviteEmailUser.close();
				}

			}.bind(this),
			onPopupDestroy : function() {
				this.popups.container = null;
				if (this.manager.statuses.allowSendEvent)
				{
					if (this.callback.closeDialog)
					{
						this.callback.closeDialog({
							selectorId: this.id
						});
					}
					if (this.callback.closeSearch)
					{
						this.callback.closeSearch({
							selectorId: this.id
						});
					}
				}
			}.bind(this)
		},
		content: this.getContainerContent()
	});

//	this.popups.container.setAngle({});
//	this.popups.container.show();

	return true;
};

BX.UI.Selector.prototype.openSearch = function(params)
{
	this.manager.statuses.allowSendEvent = false;
	if (this.popups.main != null)
	{
		this.popups.main.close();
	}
	this.manager.statuses.allowSendEvent = true;

	if (this.popups.search != null)
	{
		this.popups.search.close();
		return false;
	}

	if (this.getOption('useContainer') == 'Y') // obUseContainer
	{
		this.containerSearchResult = params.itemsList;

		if (this.nodes.searchContent)
		{
			BX.cleanNode(this.nodes.searchContent);
			var contentCollection = this.buildContentCollection({
				type: 'search',
				items: params.itemsList
			});
			for (i = 0; i < contentCollection.length; i++)
			{
				this.nodes.searchContent.appendChild(contentCollection[i]);
			}
		}
		else
		{
			BX.cleanNode(this.nodes.searchContent);
			BX(this.nodes.searchContent).appendChild(this.getSearchContent({
				itemsList: params.itemsList
			}));
		}

		this.switchTab({
			code: 'search'
		});

		this.popups.container.setAngle({});
	}
	else
	{
		this.popups.search = new BX.PopupWindow({
			id: 'bx-selector-dialog-' + this.id + '-search',
			bindElement: this.getPopupBind(),
			autoHide: true,
			zIndex: this.getPopupZIndex(),
			className: this.getRenderInstance().class.popup,
			offsetLeft: this.bindOptions.offsetLeft,
			offsetTop: this.bindOptions.offsetTop,
			bindOptions: this.bindOptions,
			cacheable: false,
			closeByEsc: true,
			closeIcon: false,
			lightShadow: true,
			events: {
				onPopupShow: function() {
					if (
						this.manager.statuses.allowSendEvent
						&& this.callback.openSearch
					)
					{
						this.callback.openSearch({
							selectorId: this.id
						});
					}

					if (
						this.popups.inviteEmailUser
						&& this.popups.inviteEmailUser.isShown()
					)
					{
						this.popups.inviteEmailUser.close();
					}

				}.bind(this),
				onPopupDestroy : function() {
					this.popups.search = null;
					this.getSearchInstance().abortSearchRequest();
					if (
						this.manager.statuses.allowSendEvent
						&& this.callback.closeSearch
					)
					{
						this.callback.closeSearch({
							selectorId: this.id
						});
					}
				}.bind(this)
			},
			content: this.getSearchContent({
				itemsList: params.itemsList
			})
		});

		this.popups.search.setAngle({});
		this.popups.search.show();
	}

	this.getNavigationInstance().hoverFirstItem({
		tab: 'search'
	});
};

BX.UI.Selector.prototype.getDialogContent = function()
{
	this.dialogNodes.tabsContainer = BX.create('DIV', {
		props: {
			className: this.getRenderInstance().class.tabsContainer
		}
	});

	var
		tab = null,
		tabIndex = 0,
		tabSort = [];

	for (var code in this.tabs.list)
	{
		if (this.tabs.list.hasOwnProperty(code))
		{
			tab = this.tabs.list[code];
			tabSort.push({
				code: code,
				value: (typeof tab['sort'] != 'undefined' && parseInt(tab['sort']) > 0 ? parseInt(tab['sort']) : 100)
			});
		}
	}

	if (
		this.getOption('useContainer') == 'Y'
		&& !BX.type.isNotEmptyObject(this.tabs.list['search'])
	)
	{
		this.tabs.list['search'] = {
			name: BX.message('MAIN_UI_SELECTOR_SEARCH_TAB_TITLE')
		};
		tabSort.push({
			code: 'search',
			value: 10000
		});
	}

	tabSort.sort(function(a, b) {
		if (a.value < b.value)
		{
			return -1;
		}
		if (a.value > b.value)
		{
			return 1;
		}
		return 0;
	});

	var contentsNodesList = [];
	var tabNode = null;

	for (var i = 0; i < tabSort.length; i++)
	{
		code = tabSort[i].code;
		tab = this.tabs.list[code];

		if (tabIndex === 0)
		{
			this.tabs.selected = code;
		}

		tabNode = BX.create('A', {
			attrs: {
				hidefocus: 'true',
				'data-code': code
			},
			props: {
				className: this.getRenderInstance().class.tab + ' ' + this.getRenderInstance().class.tabLast + ' ' + (tabIndex == 0 ? this.getRenderInstance().class.tabSelected : '')
			},
			events: {
				click: function (e) {
					this.switchTab({
						code: e.target.getAttribute('data-code')
					});
					e.stopPropagation();
					return e.preventDefault();
				}.bind(this)
			},
			html: tab.name
		});

		this.dialogNodes.tabsContainer.appendChild(tabNode);

		contentsNodesList.push(this.buildContentNode({
			type: code
		}));

		tabIndex++;
	}

	this.dialogNodes.contentsContainer = BX.create('DIV', { // id: 'bx-lm-box-last-content'
		props: {
			className: this.getRenderInstance().class.tabsContentContainer + ' ' + this.getRenderInstance().class.tabsContentContainerWindow
		},
		children: [
			BX.create('TABLE', {
				props: {
					className: this.getRenderInstance().class.tabsContentContainerTable
				},
				children: [
					BX.create('TR', {
						children: [
							BX.create('TD', {
								props: {
									className: this.getRenderInstance().class.tabsContentContainerCell
								},
								children: contentsNodesList
							})
						]
					})
				]
			})
		]
	});

	var windowClass = this.getOption('windowClass');

	return BX.create('DIV', {
		style: {
			minWidth: '650px',
			paddingBottom: '8px'
		},
		props: {
			className: this.getRenderInstance().class.boxCommon + ' ' + this.getRenderInstance().class.boxContainer + ' ' + this.getRenderInstance().class.boxContainerVertical + ' ' + (windowClass ? windowClass : this.getRenderInstance().class.boxDefault)
		},
		children: [
			this.dialogNodes.tabsContainer,
			this.dialogNodes.contentsContainer
		]
	});
};

BX.UI.Selector.prototype.getContainerContent = function()
{
	var
		windowClass = this.getOption('windowClass'),
		i = null;

	this.nodes.containerContentsContainer = BX.create('div', {
		props: {
			className: this.getRenderInstance().class.containerContent
		}
	});

	this.nodes.inputItemsContainer = BX.create('SPAN', {
		attrs: {
			id: 'bx-dest-internal-item'
		}
	});

	var result = BX.create('DIV', {
		children: [
			BX.create('DIV', {
				props: {
					className: this.getRenderInstance().class.boxCommon + ' ' + this.getRenderInstance().class.boxContainer + ' ' + this.getRenderInstance().class.boxContainerVertical + ' ' + (windowClass ? windowClass : this.getRenderInstance().class.boxDefault)
				},
				style: {
					minWidth: '650px',
					paddingBottom: '8px',
					overflow: 'hidden'
				},
				children: [
					BX.create('DIV', {
						props: {
							className: this.getRenderInstance().class.containerSearchBlock
						},
						children: [
							BX.create('DIV', {
								props: {
									className: this.getRenderInstance().class.containerSearchBlockCell
								},
								children: [
									this.nodes.inputItemsContainer,
									BX.create('SPAN', {
										attrs: {
											id: "bx-dest-internal-input-box",
										},
										style: {
											display: 'inline-block'
										},
										props: {
											className: this.getRenderInstance().class.containerSearchBlockInputBox
										},
										children: [
											this.getSearchInput()
										]
									})
								],
								events: {
									click: function(e) {
										BX.focus(this.nodes.input);
										return e.preventDefault();
									}.bind(this)
								}
							})
						]
					}),
					this.nodes.containerContentsContainer
				]
			})
		]
	});

	BX.bind(this.nodes.input, 'keydown', function(e) { this.getSearchInstance().beforeSearchHandler({ event: e }); }.bind(this));
	BX.bind(this.nodes.input, 'keyup', function(e) { this.getSearchInstance().searchHandler({ event: e }); }.bind(this));
	BX.bind(this.nodes.input, 'paste', function(e) { this.getSearchInstance().searchHandler({ event: e }); }.bind(this));

	BX.defer(BX.focus)(this.nodes.input);

	return result;
};

BX.UI.Selector.prototype.getSearchInput = function()
{
	this.nodes.input = BX.create('INPUT', {
		attrs: {
			type: "text"
		},
		props: {
			className: this.getRenderInstance().class.containerSearchBlockInput
		}
	});

	return this.nodes.input;
};


BX.UI.Selector.prototype.getSearchContent = function(params)
{
	this.nodes.searchContentsContainer = BX.create('DIV', {
		attrs: {
//			id: 'bx-lm-box-search-content'
		},
		props: {
			className: this.getRenderInstance().class.tabsContentContainer + ' ' + this.getRenderInstance().class.tabsContentContainerWindow
		},
		children: [
			BX.create('TABLE', {
				props: {
					className: this.getRenderInstance().class.tabsContentContainerTable
				},
				children: [
					BX.create('TR', {
						children: [
							BX.create('TD', {
								props: {
									className: this.getRenderInstance().class.tabsContentContainerCell
								},
								children: [
									this.buildContentNode({
										type: 'search',
										items: params.itemsList
									})
								]
							})
						]
					})
				]
			})
		]
	});

	var windowClass = this.getOption('windowClass');

	return BX.create('DIV', {
		style: {
			minWidth: '650px',
			paddingBottom: '8px'
		},
		props: {
			className: this.getRenderInstance().class.boxCommon + ' ' + this.getRenderInstance().class.boxContainer + ' ' + this.getRenderInstance().class.boxContainerVertical + ' ' + (windowClass ? windowClass : this.getRenderInstance().class.boxDefault)
		},
		children: [
			this.nodes.searchContentsContainer,
			(this.getOption('useContainer') == 'Y' ? null : this.getSearchInstance().buildSearchWaiter())
		]
	});
};

BX.UI.Selector.prototype.switchTab = function(params)
{
	var code = (BX.type.isNotEmptyString(params.code) ? params.code : null);
	if (!code)
	{
		return;
	}

	var
		i = null,
		j = null;

	this.tabs.selected = code;

	var nodes = BX.findChildren(this.dialogNodes.tabsContainer, { className: this.getRenderInstance().class.tab }, true);
	if (nodes)
	{
		for (i = 0; i < nodes.length; i++)
		{
			if (nodes[i].getAttribute('data-code') == code)
			{
				nodes[i].classList.add(this.getRenderInstance().class.tabSelected);
			}
			else
			{
				nodes[i].classList.remove(this.getRenderInstance().class.tabSelected);
			}
		}
	}

	nodes = BX.findChildren(this.dialogNodes.contentsContainer, { className: this.getRenderInstance().class.tabContent }, true);
	if (nodes)
	{
		for (i = 0; i < nodes.length; i++)
		{
			if (nodes[i].getAttribute('data-code') == code)
			{
				BX.cleanNode(nodes[i]);

				var contentCollection = this.buildContentCollection({
					type: code,
					items: (code == 'search' ? this.containerSearchResult : null)
				});
				for (j = 0; j < contentCollection.length; j++)
				{
					nodes[i].appendChild(contentCollection[j]);
				}

				nodes[i].classList.add(this.getRenderInstance().class.tabContentSelected);
			}
			else
			{
				nodes[i].classList.remove(this.getRenderInstance().class.tabContentSelected);
			}
		}
	}

	this.getNavigationInstance().hoverFirstItem({
		tab: code
	});

	if (this.getOption('focusInputOnSwitchTab') != 'N')
	{
		BX.focus(this.input);
	}


	var popup = (this.getOption('useContainer') == 'Y' ? this.popups.container : this.popups.main);

	setTimeout(function () {
		popup.bindOptions.forceTop = true;
		popup.bindOptions.position = this.getPopupPosition(popup);
		popup.adjustPosition();
		popup.bindOptions.forceTop = false;
	}.bind(this), 0);
};

BX.UI.Selector.prototype.getPopupPosition = function(popup)
{
	var
		popupPos = BX.pos(popup.getPopupContainer(), false),
		bindElementPos = BX.pos(popup.bindElement, false);

	return (
		popupPos.top < bindElementPos.top
			? 'top'
			: 'bottom'
	);
};

BX.UI.Selector.prototype.setOption = function(optionId, value, entityType)
{
	if (!BX.type.isNotEmptyString(entityType))
	{
		this.options[optionId] = value;
	}
	else
	{
		entityType = entityType.toUpperCase();
		if (BX.type.isNotEmptyObject(this.entities[entityType]))
		{
			if (!BX.type.isNotEmptyObject(this.entities[entityType].options))
			{
				this.entities[entityType].options = {};
			}
			this.entities[entityType].options[optionId] = value;
		}
	}
};

BX.UI.Selector.prototype.getOption = function(optionId, entityType)
{
	if (!BX.type.isNotEmptyString(entityType))
	{
		return (
			typeof this.options[optionId] != 'undefined'
				? this.options[optionId]
				: null
		);
	}
	else
	{
		entityType = entityType.toUpperCase();
		return (
			BX.type.isNotEmptyObject(this.entities[entityType])
			&& BX.type.isNotEmptyObject(this.entities[entityType].options)
			&& BX.type.isNotEmptyString(this.entities[entityType].options[optionId])
				? this.entities[entityType].options[optionId]
				: null
		);
	}
};

BX.UI.Selector.prototype.buildContentNode = function(params)
{
	var type = params.type;
	var tabsListToFill = [ this.tabs.selected ];

	if (this.getOption('useContainer') != 'Y')
	{
		tabsListToFill.push('search');
	}

	var contentCollection = [];

	if (BX.util.in_array(type, tabsListToFill))
	{
		contentCollection = this.buildContentCollection(params);
	}

	var result = BX.create('DIV', {
		attrs: {
			'data-code': type
		},
		props: {
			className: this.getRenderInstance().class.tabContent + ' ' + this.getRenderInstance().class.tabContentPrefix + type + ' ' + (BX.util.in_array(type, [ this.tabs.selected, 'search' ]) ? this.getRenderInstance().class.tabContentSelected : '')
		},
		children: contentCollection
	});

	if (type == 'search')
	{
		this.nodes.searchContent = result;
	}

	return result;
};

BX.UI.Selector.prototype.getItemsCodeList = function(params)
{
	var
		type = params.type,
		result = null;

	if (type == 'last')
	{
		result = this.getLastItems();
	}
	else if (type == 'search')
	{
		result = params.items;
	}
	else
	{
		result = this.getEntityItems(this.entities[type.toUpperCase()]);
	}

	return result;

};

BX.UI.Selector.prototype.buildContentCollection = function(params)
{
	var
		type = params.type,
		result = [],
		dialogGroup = null,
		groupNode = null,
		i = null;

	var itemsCodeList = this.getItemsCodeList(params);

	var useDialogGroups = BX.util.in_array(type, [ 'last', 'search' ]);
	if (useDialogGroups)
	{
		var dialogGroupsSort = [];

		for (var groupCode in this.dialogGroups)
		{
			if (this.dialogGroups.hasOwnProperty(groupCode))
			{
				dialogGroup = this.dialogGroups[groupCode];
				dialogGroupsSort.push({
					code: groupCode,
					value: (typeof dialogGroup.SORT != 'undefined' && parseInt(dialogGroup.SORT) > 0 ? parseInt(dialogGroup.SORT) : 100)
				});
			}
		}

		dialogGroupsSort.sort(function(a, b) {
			if (a.value < b.value)
			{
				return -1;
			}
			if (a.value > b.value)
			{
				return 1;
			}
			return 0;
		});

		var navData = {
			tab: type,
			group: 0
		};

		for (i=0; i < dialogGroupsSort.length; i++)
		{
			groupCode = dialogGroupsSort[i].code;

			groupNode = this.drawItemsGroup({
				groupCode: groupCode,
				itemsCodeList: itemsCodeList,
				descLessMode: (
					BX.type.isNotEmptyString(this.dialogGroups[groupCode].DESC_LESS_MODE)
					&& this.dialogGroups[groupCode].DESC_LESS_MODE == 'Y'
				),
				navData: navData
			});
			if (groupNode)
			{
				result.push(groupNode);
			}
		}
	}
	else if (BX.type.isNotEmptyObject(this.entities[type.toUpperCase()]))
	{
		var drawParams = {
			type: type,
			itemsCodeList: itemsCodeList,
			descLessMode: (
				BX.type.isNotEmptyObject(this.entities[type.toUpperCase()].additionalData)
				&& BX.type.isNotEmptyString(this.entities[type.toUpperCase()].additionalData.DESC_LESS_MODE)
				&& this.entities[type.toUpperCase()].additionalData.DESC_LESS_MODE == 'Y'
			)
		};

		if (
			BX.type.isNotEmptyObject(this.entities[type.toUpperCase()].additionalData)
			&& BX.type.isNotEmptyString(this.entities[type.toUpperCase()].additionalData.TYPE)
			&& this.entities[type.toUpperCase()].additionalData.TYPE == 'tree'
		)
		{
			var tabNodesCollection = this.drawItemsTreeTabNode(drawParams);
			for (i = 0; i < tabNodesCollection.length; i++)
			{
				result.push(tabNodesCollection[i]);
			}
		}
		else
		{
			var tabNode = this.drawItemsTab(drawParams);
			if (tabNode)
			{
				result.push(tabNode);
			}
		}
	}

	var emptyResult = false;

	if (result.length <= 0)
	{
		if (type == 'search')
		{
			emptyResult = true;

			this.nodes.contentWaiter = BX.create('SPAN', {
				props: {
					className: this.getRenderInstance().class.groupBoxContent
				},
				html: BX.message('MAIN_UI_SELECTOR_STUB_PLEASE_WAIT') // : 'MAIN_UI_SELECTOR_STUB_EMPTY_LIST')
			});

			var groupBox = BX.create('SPAN', {
				props: {
					className: this.getRenderInstance().class.groupBox + ' ' + this.getRenderInstance().class.groupBoxSearch
				},
				children: [
					this.nodes.contentWaiter
				]
			});

			if (
				this.getOption('useContainer') == 'Y'
				&& this.nodes.input
				&& !BX.type.isNotEmptyString(this.nodes.input.value)
			)
			{
				this.nodes.contentWaiter.style.display = 'none';
			}

			result.push(groupBox);
		}
		else
		{
			result.push(BX.create('SPAN', {
				props: {
					className: this.getRenderInstance().class.groupBox + ' ' + this.getRenderInstance().class.groupBoxSearch
				},
				children: [
					BX.create('SPAN', {
						props: {
							className: this.getRenderInstance().class.groupBoxContent
						},
						html: BX.message('MAIN_UI_SELECTOR_STUB_EMPTY_LIST')
					})
				]
			}));
		}
	}

	if (typeof this.result[type] != 'undefined')
	{
		this.cursors[type] = {
			firstItem: (!emptyResult ? this.result[type][0][0][0] : null), // obSearchFirstElement
			currentItem: (!emptyResult ? this.result[type][0][0][0] : null), // obCurrentElement.search
			position: { // obCursorPosition.search
				group: 0,
				row: 0,
				column: 0
			}
		};
	}

	return result;
};

BX.UI.Selector.prototype.getLastItems = function()
{
	var result = {};

	for (var entityCode in this.entities)
	{
		if (
			this.entities.hasOwnProperty(entityCode)
			&& BX.type.isArray(this.entities[entityCode].itemsLast)
		)
		{
			if (typeof result[entityCode] == 'undefined')
			{
				result[entityCode] = [];
			}

			for (var i=0; i < this.entities[entityCode].itemsLast.length; i++)
			{
				if (BX.util.in_array(this.entities[entityCode].itemsLast[i], result[entityCode]))
				{
					continue;
				}

				result[entityCode].push(this.entities[entityCode].itemsLast[i]);
			}
		}
	}

	return result;
};

BX.UI.Selector.prototype.getEntityItems = function(entityData)
{
	var result = [];

	if (!BX.type.isNotEmptyObject(entityData))
	{
		return result;
	}

	for (var entityCode in entityData.items)
	{
		if (entityData.items.hasOwnProperty(entityCode))
		{
			if (
				BX.util.in_array(entityCode, result)
				|| (
					BX.type.isNotEmptyString(entityData.items[entityCode].selectable)
					&& entityData.items[entityCode].selectable == 'N'
				)
			)
			{
				continue;
			}

			result.push(entityCode);
		}
	}

	return result;
};

BX.UI.Selector.prototype.drawItemsGroup = function(params)
{
	var result = null;

	var groupCode = params.groupCode;
	var itemsCodeList = params.itemsCodeList;

	if (
		!groupCode
		|| !BX.type.isNotEmptyObject(this.dialogGroups[groupCode])
	)
	{
		return result;
	}

	var
		groupContentNodeList = [],
		key, itemKey, entityType, item = null;

	if (BX.type.isNotEmptyObject(this.dialogGroups[groupCode].TYPE_LIST))
	{
		var itemsToDraw = [];
		for (key in this.dialogGroups[groupCode].TYPE_LIST)
		{
			if (this.dialogGroups[groupCode].TYPE_LIST.hasOwnProperty(key))
			{
				entityType = this.dialogGroups[groupCode].TYPE_LIST[key];
				if (BX.type.isNotEmptyObject(itemsCodeList[entityType]))
				{
					for (itemKey in itemsCodeList[entityType])
					{
						if (itemsCodeList[entityType].hasOwnProperty(itemKey))
						{
							item = {
								entityType: entityType,
								itemCode: itemsCodeList[entityType][itemKey]
							};
							if (BX.type.isNotEmptyObject(this.sortData[item.itemCode]))
							{
								item.sort = this.sortData[item.itemCode];
							}

							itemsToDraw.push(item);
						}
					}
				}
			}
		}

		itemsToDraw.sort(function(a, b) {
			if (
				typeof a.sort == 'undefined'
				&& typeof b.sort == 'undefined'
			)
			{
				return 0;
			}
			else if (
				typeof a.sort != 'undefined'
				&& typeof b.sort == 'undefined'
			)
			{
				return -1;
			}
			else if (
				typeof a.sort == 'undefined'
				&& typeof b.sort != 'undefined'
			)
			{
				return 1;
			}
			else
			{
				if (
					typeof a.sort.Y != 'undefined'
					&& typeof b.sort.Y == 'undefined'
				)
				{
					return -1;
				}
				else if (
					typeof a.sort.Y == 'undefined'
					&& typeof b.sort.Y != 'undefined'
				)
				{
					return 1;
				}
				else if (
					typeof a.sort.Y != 'undefined'
					&& typeof b.sort.Y != 'undefined'
				)
				{
					if (parseInt(a.sort.Y) > parseInt(b.sort.Y))
					{
						return -1;
					}
					else if (parseInt(a.sort.Y) < parseInt(b.sort.Y))
					{
						return 1;
					}
					else
					{
						return 0;
					}
				}
				else
				{
					if (parseInt(a.sort.N) > parseInt(b.sort.N))
					{
						return -1;
					}
					else if (parseInt(a.sort.N) < parseInt(b.sort.N))
					{
						return 1;
					}
					else
					{
						return 0;
					}
				}
			}
		});

		var
			drawResult = null,
			found = false,
			row = 0,
			column = 0;

		for (var i = 0; i < itemsToDraw.length; i++)
		{
			if (i == 0)
			{
				if (typeof this.result[params.navData.tab] == 'undefined')
				{
					this.result[params.navData.tab] = [];
				}
				this.result[params.navData.tab][params.navData.group] = [];
				found = true;
			}
			if (column == 2)
			{
				column = 0;
				row++;
			}

			if (typeof this.result[params.navData.tab][params.navData.group][row] == 'undefined')
			{
				this.result[params.navData.tab][params.navData.group][row] = [];
			}

			drawResult = this.drawItem(itemsToDraw[i]);
			if (drawResult)
			{
				this.result[params.navData.tab][params.navData.group][row][column] = {
					entityType: itemsToDraw[i].entityType,
					itemCode: itemsToDraw[i].itemCode
				};
				groupContentNodeList.push(drawResult);
				column++;
			}
		}

		if (found)
		{
			params.navData.group++;
		}
	}

	if (groupContentNodeList.length > 0)
	{
		result = BX.create('SPAN', {
			props: {
				className: this.getRenderInstance().class.groupBox + ' ' + this.getRenderInstance().class.groupBoxPrefix + groupCode
			},
			children: [
				BX.create('SPAN', {
					props: {
						className: this.getRenderInstance().class.groupBoxName
					},
					html: this.dialogGroups[groupCode].TITLE
				}),
				BX.create('SPAN', {
					props: {
						className: this.getRenderInstance().class.groupBoxContent
					},
					children: groupContentNodeList
				})
			]
		});
	}

	return result;
};

BX.UI.Selector.prototype.drawItemsTab = function(params)
{
	var type = params.type;
	var itemsCodeList = params.itemsCodeList;

	var
		groupContentNodeList = [],
		i = 0,
		row = 0,
		column = 0,
		item = null;

	if (BX.type.isNotEmptyObject(itemsCodeList))
	{
		for (var itemKey in itemsCodeList)
		{
			if (itemsCodeList.hasOwnProperty(itemKey))
			{
				if (i == 0)
				{
					this.result[params.type] = [];
					this.result[params.type][0] = [];
				}
				if (column == 2)
				{
					column = 0;
					row++;
				}

				if (typeof this.result[params.type][0][row] == 'undefined')
				{
					this.result[params.type][0][row] = [];
				}

				item = {
					entityType: type.toUpperCase(),
					itemCode: itemsCodeList[itemKey]
				};
				this.result[params.type][0][row][column] = item;
				groupContentNodeList.push(this.drawItem(item));
				column++;
			}
			i++;
		}
	}

	return BX.create('SPAN', {
		props: {
			className: this.getRenderInstance().class.groupBox + ' ' + this.getRenderInstance().class.groupBoxPrefix + type
		},
		children: [
			BX.create('SPAN', {
				props: {
					className: this.getRenderInstance().class.groupBoxContent
				},
				children: groupContentNodeList
			})
		]
	});
};

BX.UI.Selector.prototype.drawItemsTreeTabNode = function(params)
{
	var result = [];

	var
		type = (BX.type.isNotEmptyString(params.type) ? params.type : false),
		relation = (params.relation != 'undefined' ? params.relation : false),
		categoryId = (params.categoryId != 'undefined' ? params.categoryId : false),
		categoryOpened = params.categoryOpened,
		firstRelation = false,
		itemCode = null,
		category = null,
		i = null;

	if (
		!relation
		&& BX.type.isNotEmptyObject(this.entities[type.toUpperCase() + '_RELATION'])
		&& BX.type.isNotEmptyObject(this.entities[type.toUpperCase() + '_RELATION'].items)
	) // root
	{
		relation = this.entities[type.toUpperCase() + '_RELATION'].items;
		firstRelation = true;
	}

	if (!relation)
	{
		return result;
	}

	var selectText = (
		BX.type.isNotEmptyObject(this.entities[type.toUpperCase()].additionalData)
		&& BX.type.isNotEmptyString(this.entities[type.toUpperCase()].additionalData.SELECT_TEXT)
			? this.entities[type.toUpperCase()].additionalData.SELECT_TEXT
			: BX.message('MAIN_UI_SELECTOR_SELECT_TEXT')
	);
	var selectFlatText = (
		BX.type.isNotEmptyObject(this.entities[type.toUpperCase()].additionalData)
		&& BX.type.isNotEmptyString(this.entities[type.toUpperCase()].additionalData.SELECT_FLAT_TEXT)
			? this.entities[type.toUpperCase()].additionalData.SELECT_FLAT_TEXT
			: BX.message('MAIN_UI_SELECTOR_SELECT_FLAT_TEXT')
	);

	var
		branchNode = null,
		leavesContainer = null,
		checkBoxNode = null,
		nodeChildren = null;

	for (itemCode in relation)
	{
		if (
			relation.hasOwnProperty(itemCode)
			&& relation[itemCode].type == 'category'
		)
		{
			category = this.entities[type.toUpperCase()].items[relation[itemCode].id];
			firstRelation = (firstRelation && category.id != 'EX');

			branchNode = BX.create('DIV', {
				props: {
					className: this.getRenderInstance().class.treeBranch + ' ' + (firstRelation ? this.getRenderInstance().class.treeBranchOpened : '')
				},
				children: [
					BX.create('A', {
						attrs: {
							href: '#' + category.id,
							hidefocus: 'true',
							'data-entity-id': category.entityId
						},
						props: {
							className: this.getRenderInstance().class.treeBranchInner
						},
						events: {
							click: function(e) {
								this.openTreeItem({
									treeItemNode: e.currentTarget.parentNode,
									entityType: type,
									categoryId: e.currentTarget.getAttribute('data-entity-id')
								});
								e.stopPropagation();
								return e.preventDefault();
							}.bind(this)
						},
						children: [
							BX.create('DIV', {
								props: {
									className: this.getRenderInstance().class.treeBranchArrow
								}
							}),
							BX.create('DIV', {
								props: {
									className: this.getRenderInstance().class.treeBranchText
								},
								html: category.name
							})
						]
					})
				]
			});

			result.push(branchNode);

			checkBoxNode = (
				BX.type.isNotEmptyObject(this.entities[type.toUpperCase()].additionalData)
				&& BX.type.isNotEmptyString(this.entities[type.toUpperCase()].additionalData.ALLOW_SELECT)
				&& this.entities[type.toUpperCase()].additionalData.ALLOW_SELECT == 'Y'
				&& !firstRelation
				&& category.id != 'EX'
					? BX.create('A', {
						attrs: {
							hidefocus: 'true',
							href: '#' + relation[itemCode].id,
							'data-item-id': relation[itemCode].id,
							'data-entity-type': type.toUpperCase()
						},
						props: {
							className: this.getRenderInstance().class.treeBranchCheckBox + ' ' + (typeof this.itemsSelected[relation[itemCode].id] != 'undefined' ? this.getRenderInstance().class.treeBranchCheckBoxSelected : '') + ' ' + this.getRenderInstance().class.itemElement
						},
						events: {
							click: function(e) {
								this.selectItem({
									itemId: e.currentTarget.getAttribute('data-item-id'),
									entityType: e.currentTarget.getAttribute('data-entity-type'),
									itemNode: e.currentTarget,
									className: this.getRenderInstance().class.treeBranchCheckBoxSelected
								}); // 'department',  relation[itemCode].id, 'department'
								e.stopPropagation();
								return e.preventDefault();
							}.bind(this)
						},
						children: [
							BX.create('SPAN', {
								props: {
									className: this.getRenderInstance().class.treeBranchCheckBoxInner
								},
								children: [
									BX.create('DIV', {
										props: {
											className: this.getRenderInstance().class.treeBranchCheckBoxArrow
										}
									}),
									BX.create('DIV', {
										attrs: {
											rel: category.name + ': ' + selectText
										},
										props: {
											className: this.getRenderInstance().class.treeBranchCheckBoxText
										},
										html: selectText
									})
								]
							})
						]
					})
					: null
			);

			nodeChildren = [
				checkBoxNode
			];

			if (
				BX.type.isNotEmptyObject(this.entities[type.toUpperCase()].additionalData)
				&& BX.type.isNotEmptyString(this.entities[type.toUpperCase()].additionalData.ALLOW_FLAT)
				&& this.entities[type.toUpperCase()].additionalData.ALLOW_FLAT == 'Y'
//				&& !firstRelation
				&& category.id != 'EX'
				&& BX.type.isNotEmptyString(this.entities[type.toUpperCase()].items[relation[itemCode].id].idFlat)
			)
			{
				checkBoxNode = BX.create('A', {
					attrs: {
						hidefocus: 'true',
						href: '#' + this.entities[type.toUpperCase()].items[relation[itemCode].id].idFlat,
						'data-item-id': this.entities[type.toUpperCase()].items[relation[itemCode].id].idFlat,
						'data-entity-type': type.toUpperCase()
					},
					props: {
						className: this.getRenderInstance().class.treeBranchCheckBox + ' ' + (typeof this.itemsSelected[relation[itemCode].id] != 'undefined' ? this.getRenderInstance().class.treeBranchCheckBoxSelected : '') + ' ' + this.getRenderInstance().class.itemElement
					},
					events: {
						click: function(e) {
							this.selectItem({
								itemId: e.currentTarget.getAttribute('data-item-id'),
								entityType: e.currentTarget.getAttribute('data-entity-type'),
								itemNode: e.currentTarget,
								className: this.getRenderInstance().class.treeBranchCheckBoxSelected
							}); // 'department',  relation[itemCode].id, 'department'
							e.stopPropagation();
							return e.preventDefault();
						}.bind(this)
					},
					children: [
						BX.create('SPAN', {
							props: {
								className: this.getRenderInstance().class.treeBranchCheckBoxInner
							},
							children: [
								BX.create('DIV', {
									props: {
										className: this.getRenderInstance().class.treeBranchCheckBoxArrow
									}
								}),
								BX.create('DIV', {
									attrs: {
										rel: category.name + ': ' + selectFlatText
									},
									props: {
										className: this.getRenderInstance().class.treeBranchCheckBoxText
									},
									html: selectFlatText
								})
							]
						})
					]
				});

				nodeChildren.push(checkBoxNode);
			}

			var subParams = BX.clone(params);
			subParams.relation = relation[itemCode].items;
			subParams.categoryId = category.entityId;
			subParams.categoryOpened = firstRelation;

			var subResult = this.drawItemsTreeTabNode(subParams);

			if (subResult.length > 0)
			{
				for (i=0; i < subResult.length; i++)
				{
					nodeChildren.push(subResult[i]);
				}
			}

			leavesContainer = BX.create('DIV', {
				props: {
					className: this.getRenderInstance().class.treeBranchLeavesContainer + ' ' + (firstRelation ? this.getRenderInstance().class.treeBranchLeavesContainerOpened : '')
				},
				children: nodeChildren
			});

			result.push(leavesContainer);
		}
	}

	if (categoryId)
	{
		var
			relationNodesCollection = [],
			leafItem = null,
			leavesCount = 0;

		for (i in relation)
		{
			if (
				relation.hasOwnProperty(i)
				&& relation[i].type == this.entities[type.toUpperCase()].additionalData.RELATION_ENTITY_TYPE
			)
			{
				leafItem = this.entities[relation[i].type].items[relation[i].id];
				if (!leafItem)
				{
					continue;
				}

				relationNodesCollection.push(this.drawTreeLeafItem({
					entityType: relation[i].type,
					item: leafItem
				}));
				leavesCount++;
			}
		}

		if (leavesCount <= 0)
		{
			if (
				!BX.type.isNotEmptyObject(this.treeItemLoaded[type])
				|| !this.treeItemLoaded[type][categoryId]
			)
			{
				relationNodesCollection.push(BX.create('DIV', {
					props: {
						className: this.getRenderInstance().class.treeBranchLeavesWaiter
					},
					html: BX.message('MAIN_UI_SELECTOR_PLEASE_WAIT')
				}));
			}

			if (categoryOpened)
			{
				this.getTreeItemRelation({
					entityType: type,
					categoryId: categoryId
				});
			}
		}

		result.push(BX.create('DIV', {
			attrs: {
				id: 'bx-lm-category-relation-' + categoryId
			},
			props: {
				className: this.getRenderInstance().class.treeLeavesList
			},
			children: relationNodesCollection
		}));
	}

	return result;
};

BX.UI.Selector.prototype.drawTreeLeafItem = function(params)
{
	var
		entityType = params.entityType,
		leafItem = params.item;

	var activeClass = (
		typeof this.itemsSelected[leafItem.id] != 'undefined'
			? this.getRenderInstance().class.treeLeafSelected
			: ''
	);

	return BX.create('A', {
		attrs: {
			href: '#' + leafItem.id,
			rel: leafItem.id,
			hidefocus: 'true',
			'data-item-id': leafItem.id,
			'data-entity-type': entityType
		},
		props: {
			className: this.getRenderInstance().class.treeLeaf + ' ' + activeClass + ' ' + this.getRenderInstance().class.itemElement
		},
		events: {
			click: function(e)
			{
				this.selectItem({
					itemNode: e.currentTarget,
					itemId: e.currentTarget.getAttribute('data-item-id'),
					entityType: e.currentTarget.getAttribute('data-entity-type'),
					className: this.getRenderInstance().class.treeLeafSelected
				});
				e.stopPropagation();
				return e.preventDefault();
			}.bind(this)
		},
		children: [
			BX.create('DIV', {
				props: {
					className: this.getRenderInstance().class.treeLeafInfo
				},
				children: [
					BX.create('DIV', {
						props: {
							className: this.getRenderInstance().class.treeLeafName
						},
						html: leafItem.name
					}),
					BX.create('DIV', {
						props: {
							className: this.getRenderInstance().class.treeLeafDescription
						},
						html: leafItem.desc
					})
				]
			}),
			BX.create('DIV', {
				attrs: {
					style: (leafItem.avatar ? "background:url('" + encodeURI(leafItem.avatar) + "') no-repeat center center; background-size: cover;" : "")
				},
				props: {
					className: this.getRenderInstance().class.treeLeafAvatar
				}
			})
		]
	});
};


BX.UI.Selector.prototype.openTreeItem = function(params)
{
	var
		treeItemNode = BX.type.isDomNode(params.treeItemNode) ? params.treeItemNode : null,
		categoryId = params.categoryId,
		entityType = params.entityType;

	var doOpen = !BX.hasClass(treeItemNode, this.getRenderInstance().class.treeBranchOpened);

	BX.toggleClass(treeItemNode, this.getRenderInstance().class.treeBranchOpened);

	var nextDiv = BX.findNextSibling(treeItemNode, {
		tagName: "div"
	});

	if (BX.hasClass(nextDiv, this.getRenderInstance().class.treeBranchLeavesContainer))
	{
		BX.toggleClass(nextDiv, this.getRenderInstance().class.treeBranchLeavesContainerOpened);
	}

	if (doOpen)
	{
		this.getTreeItemRelation({
			entityType: entityType,
			categoryId: categoryId
		});
	}

	return false;
};

BX.UI.Selector.prototype.getTreeItemRelation = function(params)
{
	var categoryId = params.categoryId; // departmentId

	if (!BX.type.isUndefined(this.treeItemLoaded[categoryId]))
	{
		return false;
	}

	params.callback = this.getTreeItemRelationCallback.bind(this);
	params.entityType = params.entityType.toUpperCase();
	params.selectorId = this.id;
	params.allowSearchSelf = (BX.type.isNotEmptyObject(this.entities['USERS']) && this.entities['USERS'].options.allowSearchSelf);

	BX.onCustomEvent(this, 'BX.UI.SelectorManager:getTreeItemRelation', [ params ]);
};

BX.UI.Selector.prototype.getTreeItemRelationCallback = function(params)
{
	if (
		typeof params.selectorInstanceId == 'undefined'
		|| this.id != params.selectorInstanceId
	)
	{
		return;
	}

	var
		entityType = params.entityType,
		categoryId = params.categoryId,
		data = params.data,
		relationItems = {},
		leafEntityType = null;

	if (categoryId != 'EX')
	{
		categoryId = parseInt(categoryId);
	}

	if (typeof this.treeItemLoaded[entityType] == 'undefined')
	{
		this.treeItemLoaded[entityType] = {};
	}

	this.treeItemLoaded[entityType][categoryId] = true;

	var relationItem = BX.util.object_search_key(
		(categoryId == 'EX' ? categoryId : this.entities[entityType.toUpperCase()].additionalData.PREFIX + categoryId),
		this.entities[entityType.toUpperCase() + '_RELATION'].items
	);

	if (
		BX.type.isNotEmptyObject(this.entities[entityType.toUpperCase()])
		&& BX.type.isNotEmptyObject(this.entities[entityType.toUpperCase()].additionalData)
		&& BX.type.isNotEmptyString(this.entities[entityType.toUpperCase()].additionalData.RELATION_ENTITY_TYPE)
	)
	{
		leafEntityType = this.entities[entityType.toUpperCase()].additionalData.RELATION_ENTITY_TYPE;
		relationItems = data[leafEntityType]; // data.USERS
	}

	// clean leaves
	var i = null;
	if (BX.type.isNotEmptyObject(relationItem.items))
	{
		for(i in relationItem.items)
		{
			if (!relationItem.items.hasOwnProperty(i))
			{
				continue;
			}

			if (relationItem.items[i].type == leafEntityType)
			{
				delete(relationItem.items[i]);
			}
		}
	}

	BX.cleanNode(BX('bx-lm-category-relation-' + categoryId));

	// add leaves
	for(i in relationItems)
	{
		if (
			relationItems.hasOwnProperty(i)
			&& BX.type.isNotEmptyObject(this.entities[leafEntityType])
		)
		{
			if (!BX.type.isNotEmptyObject(this.entities[leafEntityType].items[i]))
			{
				this.entities[leafEntityType].items[i] = relationItems[i];
			}

			if (
				BX('bx-lm-category-relation-' + categoryId)
				&& !relationItem.items[i]
			)
			{
				relationItem.items[i] = {
					id: i,
					type: leafEntityType
				};

				BX('bx-lm-category-relation-' + categoryId).appendChild(this.drawTreeLeafItem({
					entityType: leafEntityType,
					item: this.entities[leafEntityType].items[i]
				}));
			}
		}
	}

	if (this.popups.container)
	{
		this.popups.container.adjustPosition();
	}
	if (this.popups.main)
	{
		this.popups.main.adjustPosition();
	}
};

BX.UI.Selector.prototype.drawItem = function(params)
{
	var result = null;
	var entityType = (BX.type.isNotEmptyString(params.entityType) ? params.entityType : null);
	var itemCode = (BX.type.isNotEmptyString(params.itemCode) ? params.itemCode : null);

	if (
		!entityType
		|| !itemCode
	)
	{
		return result;
	}


	var item = (
		BX.type.isNotEmptyObject(this.entities[entityType])
		&& BX.type.isNotEmptyObject(this.entities[entityType].items)
		&& BX.type.isNotEmptyObject(this.entities[entityType].items[itemCode])
			? this.entities[entityType].items[itemCode]
			: null
	);

	if (!item)
	{
		return result;
	}

	var
		itemName = item.name,
		itemDesc = (BX.type.isNotEmptyString(item.desc) ? item.desc : '');

	if(
		this.getOption('emailDescMode') != 'Y'
		&& BX.type.isNotEmptyString(item.showEmail)
		&& item.showEmail == 'Y'
		&& BX.type.isNotEmptyString(item.email)
	)
	{
		itemName += ' (' + item.email + ')';
	}

	var showDescription = BX.type.isNotEmptyString(item.desc);
	showDescription = params.descLessMode && params.descLessMode == true ? false : showDescription;
	showDescription = showDescription || item.showDesc;

	var emailDescMode = (typeof params.emailDescMode != 'undefined' && params.emailDescMode == true);
	if (emailDescMode === true)
	{
		showDescription = true;
	}

	var avatarNode = null;

	if (BX.type.isNotEmptyString(item.avatar))
	{
		avatarNode = BX.create('DIV', {
			props: {
				className: this.getRenderInstance().class.itemAvatar
			},
			children: [
				BX.create('IMG', {
					attrs: {
						src: encodeURI(item.avatar),
						'bx-lm-item-id': item.id,
						'bx-lm-item-type': entityType.toLowerCase()
					},
					props: {
						className: this.getRenderInstance().class.itemAvatarImage
					},
					events: {
						error: function() {
							BX.onCustomEvent('removeClientDbObject', [ BX.UI.SelectorManager, this.getAttribute('bx-lm-item-id'), this.getAttribute('bx-lm-item-type') ]);
							BX.cleanNode(this, true);
						}
					}
				}),
				BX.create('SPAN', {
					props: {
						className: this.getRenderInstance().class.itemAvatarStatus
					}
				})
			]
		});
	}
	else
	{
		avatarNode = BX.create('DIV', {
			props: {
				className: this.getRenderInstance().class.itemAvatar + ' ' + (item.iconCustom ? this.getRenderInstance().class.itemAvatarCustom : '')
			},
			html: (item.iconCustom ? item.iconCustom : '')
		});
	}

	return BX.create('A', {
		attrs: {
			id: this.getItemNodeId({ entityType: entityType, itemId: item.id }),
			hidefocus: 'true',
			rel: item.id,
			'data-entity-type': entityType
		},
		props: {
			className: (
				this.getRenderInstance().class.item + ' ' +
				this.getRenderInstance().class.itemElement + ' ' +
				( typeof this.itemsSelected[item.id] != 'undefined' ? this.getRenderInstance().class.itemSelected : '') + ' ' +
				(params.itemHover ? this.getRenderInstance().class.itemHover : '') + ' ' + // when the first element in
				(showDescription ? this.getRenderInstance().class.itemShowDescriptionMode : '') + ' ' +
				(params.className ? ' ' + params.className : '') + ' ' +
				this.getRenderInstance().class.itemElementTypePrefix + entityType.toLowerCase()  + ' ' +
				(this.getOption('avatarLessMode') == 'Y' ? this.getRenderInstance().class.itemAvatarlessMode : '') + ' ' +
				(
					(BX.type.isNotEmptyString(item.isExtranet) && item.isExtranet == 'Y')
					|| (BX.type.isNotEmptyString(item.isNetwork) && item.isNetwork == 'Y')
						? this.getRenderInstance().class.itemElementExtranet
						: ''
				) + ' ' +
				(
					BX.type.isNotEmptyString(item.isCrmEmail)
					&& item.isCrmEmail == 'Y'
						? this.getRenderInstance().class.itemElementCrmEmail
						: ''
				) + ' ' +
				(
					BX.type.isNotEmptyString(item.isEmail)
					&& item.isEmail == 'Y'
						? this.getRenderInstance().class.itemElementEmail
						: ''
				) + ' ' +
				(
					entityType.toLowerCase() == 'users'
					&& this.getOption('showVacations') == 'Y'
					&& BX.type.isNotEmptyObject(this.entities[entityType].additionalData)
					&& BX.type.isNotEmptyObject(this.entities[entityType].additionalData['USERS_VACATION'])
					&& BX.type.isNotEmptyString(this.entities[entityType].additionalData['USERS_VACATION'][item.entityId])
						? this.getRenderInstance().class.itemElementVacation
						: ''
				)
			)
		},
		events: {
			click: function(e)
			{
				this.selectItem({
					entityType: e.currentTarget.getAttribute('data-entity-type'),
					itemNode: e.currentTarget,
					itemId: item.id
				});
				e.stopPropagation();
				return e.preventDefault();
			}.bind(this)
		},
		children: [
			avatarNode,
			BX.create('DIV', {
				props: {
					className: this.getRenderInstance().class.itemSpace
				}
			}),
			BX.create('DIV', {
				props: {
					className: this.getRenderInstance().class.itemInfo
				},
				children: [
					BX.create('DIV', {
						props: {
							className: this.getRenderInstance().class.itemName
						},
						html: itemName
					}),
					(
						showDescription
							? BX.create('DIV', {
								props: {
									className: this.getRenderInstance().class.itemDescription
								},
								html: itemDesc
							})
							: null
					)
				]
			})
		]
	});
};

BX.UI.Selector.prototype.isDialogOpen = function() // isOpenDialog
{
	return (
		this.popups.main != null
		|| this.popups.container != null
	);
};

BX.UI.Selector.prototype.isContainerOpen = function() // isOpenContainer
{
	return (this.popups.container != null);
};

BX.UI.Selector.prototype.isSearchOpen = function() // isOpenDialog
{
	return (
		this.popups.search != null
		|| this.popups.container != null
	);
};

BX.UI.Selector.prototype.closeDialog = function(params)
{
	var silent = (BX.type.isNotEmptyObject(params) && !!params.silent);

	if (this.popups.main != null)
	{
		if (silent)
		{
			this.popups.main.destroy();
		}
		else
		{
			this.popups.main.close();
		}
	}
	else if (this.popups.container != null)
	{
		if (silent)
		{
			this.popups.container.destroy();
		}
		else
		{
			this.popups.container.close();
		}
	}

	BX.onCustomEvent('BX.UI.SelectorManager:onDialogClose', [ this ]);
	return true;
};

BX.UI.Selector.prototype.closeSearch = function()
{
	if (this.popups.search)
	{
		this.popups.search.close();
	}
	else if (this.popups.container)
	{
		this.popups.container.close();
	}

	this.closeByEmptySearchResult = false;

	return true;
};

BX.UI.Selector.prototype.closeAllPopups = function()
{
	for (var code in this.popups)
	{
		if (!this.popups.hasOwnProperty(code))
		{
			continue;
		}

		if (this.popups[code])
		{
			this.popups[code].close();
		}
	}
};

BX.UI.Selector.prototype.getItemNodeId = function(params)
{
	return (this.id + '_' + (this.tabs.selected ? this.tabs.selected : '') + '_' + params.entityType + '_' + params.itemId).toLowerCase()
};

BX.UI.Selector.prototype.getAdditionalEntitiesData = function()
{
	var result = {};

	for (var entityType in this.entities) // group
	{
		if (!this.entities.hasOwnProperty(entityType))
		{
			continue;
		}

		result[entityType] = {};

		if (BX.type.isNotEmptyObject(this.entities[entityType].additionalData))
		{
			result[entityType] = this.entities[entityType].additionalData;
		}
	}

	return result;
};

BX.UI.Selector.prototype.setTagTitle = function() // BXfpSetLinkName
{
	if (BX.type.isDomNode(this.nodes.tag))
	{
		if (
			Object.keys(this.itemsSelected).length <= 0
			&& this.getOption('tagLink1')
		)
		{
			this.nodes.tag.innerHTML = this.getOption('tagLink1');
		}
		else if (
			Object.keys(this.itemsSelected).length > 0
			&& this.getOption('tagLink2')
		)
		{
			this.nodes.tag.innerHTML = this.getOption('tagLink2');
		}
	}
};

BX.UI.Selector.prototype.selectItem = function(params)
{
	var itemId = params.itemId,
		entityType = params.entityType, // type
		itemNode = params.itemNode, // element
		className = (BX.type.isNotEmptyString(params.className) ? params.className : this.getRenderInstance().class.itemSelected), // template
		tab = (BX.type.isNotEmptyString(params.tab) ? params.tab : ''); // template

	if (!BX.type.isNotEmptyString(itemId))
	{
		return false;
	}

	var isPromiseReturned = false;
	var promise = null;

	if (typeof this.callbackBefore.select == 'function')
	{
		if (BX.type.isNotEmptyObject(this.callbackBefore.context))
		{
			promise = this.callbackBefore.select.bind(this.callbackBefore.context)(itemId);
			isPromiseReturned =
				promise &&
				(
					Object.prototype.toString.call(promise) === "[object Promise]" ||
					promise.toString() === "[object BX.Promise]"
				)
			;
		}
	}

	if (!isPromiseReturned)
	{
		promise = Promise.resolve();
	}

	promise.then(
		function(result)
		{
			this.selectItemPromiseFulfilled({
				itemId: itemId,
				entityType: entityType,
				itemNode: itemNode,
				className: className,
				tab: tab
			});
		}.bind(this),
		function(reason)
		{
			this.selectItemPromiseRejected({
				itemId: itemId,
				entityType: entityType,
				itemNode: itemNode,
				className: className,
				tab: tab
			});
		}.bind(this)
	);
};

BX.UI.Selector.prototype.selectItemPromiseFulfilled = function(data)
{
	var
		itemId = data.itemId,
		entityType = data.entityType,
		itemNode = data.itemNode,
		className = data.className,
		tab = data.tab;

	if (this.getOption('focusInputOnSelectItem') != 'N')
	{
		BX.focus(this.input);
	}

	if (typeof this.itemsSelected[itemId] != 'undefined')
	{
		return this.unselectItem({
			itemNode: itemNode,
			itemId: itemId,
			entityType: entityType,
			className: className
		});
	}
	else
	{
		if (this.getOption('multiple') != 'Y')
		{
			this.itemsSelected = {};
		}
		this.itemsSelected[itemId] = entityType.toLowerCase();
	}

	if (!BX.type.isArray(this.entities[entityType].itemsLast))
	{
		this.entities[entityType].itemsLast = [];
	}

	if (!BX.util.in_array(itemId, this.entities[entityType].itemsLast))
	{
		this.entities[entityType].itemsLast.push(itemId);
	}

	BX.addClass(itemNode, className); // changeItemClass

	BX.onCustomEvent('BX.UI.Selector:onSelectItem', [ {
		selectorId: this.id,
		itemId: itemId
	} ]);

	BX.onCustomEvent('BX.UI.Selector:onChange', [{selectorId: this.id}]);

	if (this.callback.select)
	{
		this.callback.select({
			item: this.entities[entityType].items[itemId],
			entityType: entityType,
			selectorId: this.id,
			state: 'select',
			tab: tab
		});
	}

	if (this.popups.search)
	{
		this.popups.search.close();
	}

	if (
		this.getOption('multiple') != 'Y'
		&& this.getOption('preventCloseAfterSelect') != 'Y'
	)
	{
		if (this.popups.container)
		{
			this.popups.container.close();
		}
		if (this.popups.main)
		{
			this.popups.main.close();
		}
	}

	this.getSearchInstance().abortSearchRequest();
};

BX.UI.Selector.prototype.selectItemPromiseRejected = function(data)
{
};

BX.UI.Selector.prototype.deleteSelectedItem = function(params)
{
	var itemId = params.itemId;

	if (!BX.type.isNotEmptyString(itemId))
	{
		return false;
	}

	if (this.popups.main)
	{
		var itemNodes = BX.findChildren(this.popups.main.popupContainer, { attrs: { rel: itemId} }, true);
		if (itemNodes)
		{
			for (var i = 0; i < itemNodes.length; i++)
			{
				BX.removeClass(itemNodes[i], this.getRenderInstance().class.itemSelected);
				BX.removeClass(itemNodes[i], this.getRenderInstance().class.treeLeafSelected);
			}
		}
	}

	BX.onCustomEvent('BX.UI.Selector:onChange', [{selectorId: this.id}]);

	delete this.itemsSelected[itemId];
};

BX.UI.Selector.prototype.unselectItem = function(params)
{
	var
		itemId = params.itemId,
		entityType = params.entityType, // type
		itemNode = params.itemNode, // element
		className = (BX.type.isNotEmptyString(params.className) ? params.className : this.getRenderInstance().class.itemSelected); // template

	if (!BX.type.isNotEmptyString(itemId))
	{
		return false;
	}

	if (
		(
			!BX.type.isNotEmptyString(params.mode)
			|| params.mode != 'reinit'
		)
		&& (
			typeof this.itemsSelected[itemId] == 'undefined'
			|| BX.util.in_array(itemId, this.itemsUndeletable)
		)
	)
	{
		return false;
	}
	else
	{
		delete this.itemsSelected[itemId];
	}

	BX.removeClass(itemNode, className); // changeItemClass

	if (this.callback.unSelect)
	{
		this.callback.unSelect({
			item: this.entities[entityType].items[itemId],
			entityType: entityType,
			selectorId: this.id
		});
	}

	if (
		this.getOption('multiple') != 'Y'
		&& this.getOption('preventCloseAfterSelect') != 'Y'
	)
	{
		if (this.popups.container)
		{
			this.popups.container.close();
		}
		if (this.popups.main)
		{
			this.popups.main.close();
		}
		if (this.popups.search)
		{
			this.popups.search.close();
		}
	}

	return false;
};

BX.UI.Selector.prototype.deleteLastItem = function()
{
	var lastId = false;
	for (var itemId in this.itemsSelected)
	{
		if (this.itemsSelected.hasOwnProperty(itemId))
		{
			lastId = itemId;
		}
	}

	if (
		lastId
		&& !BX.util.in_array(lastId, this.itemsUndeletable)
	)
	{
		var entityType = this.itemsSelected[lastId];

		delete this.itemsSelected[lastId];

		if (this.callback.unSelect)
		{
			this.callback.unSelect({
				item: this.entities[entityType.toUpperCase()].items[lastId],
				entityType: entityType.toUpperCase(),
				selectorId: this.id
			});
		}
	}
};

BX.UI.Selector.prototype.reinit = function() // reInit
{
	var entityType = null;

	if (this.callback.select)
	{
		for (var itemId in this.itemsSelected)
		{
			if (this.itemsSelected.hasOwnProperty(itemId))
			{
				entityType = this.itemsSelected[itemId];
				if (
					BX.type.isNotEmptyObject(this.entities[entityType.toUpperCase()])
					&& BX.type.isNotEmptyObject(this.entities[entityType.toUpperCase()].items)
					&& BX.type.isNotEmptyObject(this.entities[entityType.toUpperCase()].items[itemId])
				)
				{
					this.callback.select({
						item: this.entities[entityType.toUpperCase()].items[itemId],
						entityType: entityType,
						selectorId: this.id,
						state: 'init'
					});
				}
			}
		}
	}
};

BX.UI.Selector.prototype.getItemsSelectedSorted = function() // reInit
{
	var
		result = [],
		entityType = null;

	for (var itemId in this.itemsSelected)
	{
		if (this.itemsSelected.hasOwnProperty(itemId))
		{
			entityType = this.itemsSelected[itemId];
			result.push({
				itemId: itemId,
				entityType: entityType,
				sort: (
					BX.type.isNotEmptyObject(this.entities)
					&& BX.type.isNotEmptyObject(this.entities[entityType.toUpperCase()])
					&& BX.type.isNotEmptyObject(this.entities[entityType.toUpperCase()].additionalData)
					&& typeof this.entities[entityType.toUpperCase()].additionalData.SORT_SELECTED != 'undefined'
						? parseInt(this.entities[entityType.toUpperCase()].additionalData.SORT_SELECTED)
						: 100
				)
			});
		}
	}

	result.sort(function(a, b) {
		if (a.sort < b.sort)
		{
			return -1;
		}
		if (a.sort > b.sort)
		{
			return 1;
		}
		return 0;
	});

	return result;


};
})();
