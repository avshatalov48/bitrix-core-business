;(function() {
	'use strict';

	BX.namespace('BX.Main');

	BX.Main.selectorManagerV2 = {
		getById: function (id)
		{
			if (typeof this.controls[id] != 'undefined')
			{
				return this.controls[id];
			}

			return null;
		},
		controls: {},
		loadedEntities: {}
	};

	/**
	 * General selector class
	 */
	BX.Main.SelectorV2 = function()
	{
		this.initialized = false;
		this.blockInit = false;

		this.id = "";
		this.apiVersion = 2;
		this.fieldName = null;
		this.inputId = null;
		this.input = null;
		this.tagId = null;
		this.tag = null;
		this.options = null;
		this.callback = null;
		this.callbackBefore = null;
		this.items = null;
		this.entities = null;
		this.mainPopupWindow = null;
		this.entitiesSet = [
			'users', 'emails', 'crmemails', 'groups', 'sonetgroups', 'department', 'departmentRelation', 'contacts', 'companies', 'leads', 'deals'
		];
		this.entityTypes = {};
		this.auxObject = null;
		this.selectorInstance = null;

		this.eventOpenBinded = false;
	};

	BX.Main.SelectorV2.controls = {};

	BX.Main.SelectorV2.create = function(params)
	{
		if(
			typeof params.id == 'undefined'
			|| !params.id
		)
		{
			params.id = BX.util.hashCode(Math.random().toString());
		}
		else if (typeof BX.Main.selectorManagerV2.controls[params.id] != 'undefined')
		{
			var control = BX.Main.selectorManagerV2.controls[params.id];
			if (control.bindNode && !document.body.contains(control.bindNode))
			{
				delete BX.Main.selectorManagerV2.controls[params.id];
			}
			else
			{
				return BX.Main.selectorManagerV2.controls[params.id];
			}
		}

		var self = new BX.Main.SelectorV2();
		BX.Main.selectorManagerV2.controls[params.id] = self;
		self.init(params);

		return self;
	};

	BX.Main.SelectorV2.proxyCallback = function(callback, data)
	{
		callback(data);
	};

	BX.Main.SelectorV2.prototype = {
		init: function(params)
		{
			this.id = params.id;
			this.apiVersion = (params.apiVersion && parseInt(params.apiVersion) >= 2 ? parseInt(params.apiVersion) : 2);
			this.fieldName = (params.fieldName ? params.fieldName : null);
			this.inputId = (params.inputId ? params.inputId : null);
			this.input = (params.inputId && BX(params.inputId) ? BX(params.inputId) : null);
			this.inputBox = (params.inputBoxId && BX(params.inputBoxId) ? BX(params.inputBoxId) : null);
			this.inputItemsContainer = (params.inputContainerId && BX(params.inputContainerId) ? BX(params.inputContainerId) : null);
			this.containerNode = (params.containerId && BX(params.containerId) ? BX(params.containerId) : null);
			this.bindNode = (params.bindId && BX(params.bindId) ? BX(params.bindId) : this.containerNode);
			this.tagId = (params.tagId ? params.tagId : null);
			this.tag = (params.tagId && BX(params.tagId) ? BX(params.tagId) : null);
			this.openDialogWhenInit = (typeof params.openDialogWhenInit == 'undefined' || !!params.openDialogWhenInit);

			this.options = params.options || {};
			this.callback = params.callback || null;
			this.callbackBefore = params.callbackBefore || null;

			this.items = params.items || null;
			this.entities = params.entities || null;
			this.entityTypes = {};

			BX.onCustomEvent('BX.Main.SelectorV2:onGetEntityTypes', [ {
				selector: this
			} ]);

			this.selectorInstance = BX.UI.Selector.create({
				id: this.id,
				fieldName: this.fieldName,
				type: 'xxx', // we need a description of the type, both frontend and backend
				pathToAjax: (BX.type.isNotEmptyString(params.pathToAjax) ? params.pathToAjax : null),
				input: this.input || null,
				tag: this.tag || null,
				inputBox: this.inputBox || null,
				inputItemsContainer: this.inputItemsContainer || null,
				entities: BX.clone(this.entityTypes),
				itemsSelected: BX.clone(this.items.selected) || {}, // obItemsSelected
				itemsUndeletable: BX.clone(this.items.undeletable) || [],
				options: {
					multiple: this.getOption('multiple'),
					useSearch: this.getOption('useSearch'),
					useContainer: (this.getOption('useContainer') == 'Y' ? 'Y' : 'N'),
					userNameTemplate: this.getOption('userNameTemplate'),
					siteDepartmentId: this.getOption('siteDepartmentId'), // siteDepartmentID
					showCloseIcon: 'Y',
					popupAutoHide: (this.getOption('popupAutoHide') == 'N' ? 'N' : 'Y'),
					last: {
						disable: (this.getOption('disableLast') == 'Y' ? 'Y' : 'N'), // lastTabDisable
					},
					search: {
						useAjax: (this.getOption('sendAjaxSearch') != 'N' ? 'Y' : 'N'), // sendAjaxSearch
						useClientDatabase: (this.getOption('useClientDatabase') == 'Y' ? 'Y' : 'N'), // useClientDatabase
					},
					focusInputOnSelectItem: (this.getOption('focusInputOnSelectItem') != 'N' ? 'Y' : 'N'), // BX.SocNetLogDestination:onBeforeSelectItemFocus
					focusInputOnSwitchTab: (this.getOption('focusInputOnSwitchTab') != 'N' ? 'Y' : 'N'), // BX.SocNetLogDestination:onBeforeSwitchTabFocus
					isCrmFeed: (this.getOption('isCrmFeed') == 'Y' ? 'Y' : 'N'), // isCrmFeed
					tagLink1: this.getOption('tagLink1'),
					tagLink2: this.getOption('tagLink2')
				},
				bindOptions: {
					node: this.bindNode,
					offsetTop: 5,
					offsetLeft: 15,
					zIndex: (parseInt(this.getOption('popupZIndex')) > 0 ? parseInt(this.getOption('popupZIndex')) : 1200)
				},
				callback: this.callback,
				callbackBefore: this.callbackBefore
			});

			BX.addCustomEvent('BX.UI.SelectorManager:getTreeItemRelation', function(params) {
				if (this.id == params.selectorId)
				{
					this.getTreeItemRelation(params);
				}
			}.bind(this));

			BX.addCustomEvent('BX.UI.SelectorManager:loadAll', function(params) {
				this.loadAll(params);
			}.bind(this));

			BX.addCustomEvent("BX.Main.SelectorV2:initDialog", function(params)
			{
				if (this.id == params.selectorId)
				{
					if (!!params.openDialogWhenInit)
					{
						this.openDialogWhenInit = true;
					}
					this.initDialog();
				}
			}.bind(this));

			BX.addCustomEvent("BX.Main.SelectorV2:reInitDialog", function(params) {
				if (this.id == params.selectorId)
				{
					this.initialized = false;
					this.items.selected = (BX.type.isNotEmptyObject(params.selectedItems) ? params.selectedItems : {});
					this.items.undeletable = (BX.type.isNotEmptyObject(params.undeletableItems) ? params.undeletableItems : {});

					var
						selectorInstance = BX.UI.SelectorManager.instances[params.selectorId],
						entityType = null,
						fullList = null,
						i = 0;

					if (selectorInstance)
					{
						for (var itemId in selectorInstance.itemsSelected)
						{
							if (selectorInstance.itemsSelected.hasOwnProperty(itemId))
							{
								entityType = selectorInstance.itemsSelected[itemId].toUpperCase();
								fullList = BX.UI.SelectorManager.getEntityTypeFullList(entityType);

								for (i=0; i < fullList.length; i++)
								{
									if (BX.type.isNotEmptyObject(selectorInstance.entities[fullList[i]]))
									{
										selectorInstance.unselectItem({
											itemNode: (
												BX.type.isNotEmptyObject(selectorInstance.entities[fullList[i]].items)
												&& BX.type.isNotEmptyObject(selectorInstance.entities[fullList[i]].items[itemId])
													? selectorInstance.entities[fullList[i]].items[itemId]
													: false
											),
											itemId: itemId,
											entityType: fullList[i],
											mode: 'reinit'
										});
									}
								}
							}
						}

						selectorInstance.itemsSelected = this.items.selected;
						selectorInstance.itemsUndeletable = this.items.undeletable;
					}

					this.initDialog();
				}
			}.bind(this));

			BX.addCustomEvent('BX.UI.SelectorManager:searchRequest', function(params) {
				if (this.id == params.selectorInstance.id)
				{
					params.selectorInstance.timeouts.search = setTimeout(function() {
						this.searchRequest(params);
					}.bind(this), 1000);
				}
			}.bind(this));

			BX.addCustomEvent('BX.UI.Selector:onSelectItem', function(params) {
				if (
					this.apiVersion >= 3
					&& BX.type.isNotEmptyObject(params)
					&& this.id == params.selectorId
					&& BX.type.isNotEmptyString(params.itemId)
				)
				{
					this.saveDestination({
						itemId: params.itemId
					});
				}
			}.bind(this));

			if (!BX.type.isNotEmptyString(this.getOption('lheName')))
			{
				var LHENode = BX('div' + this.getOption('lheName'));
				if (LHENode)
				{
					BX.addCustomEvent(LHENode, 'OnShowLHE', function(show) {
						if (!show)
						{
							this.selectorInstance.closeAllPopups();
						}
					}.bind(this));
				}
			}

			if (
				this.getOption('useContainer') != 'Y'
				&& this.input
			)
			{
				if (
					this.getOption('lazyLoad') != 'Y'
					|| BX.type.isNotEmptyObject(this.items.selected)
				)
				{
					if (BX.type.isNotEmptyObject(this.items.selected))
					{
						this.openDialogWhenInit = false;
					}
					this.initDialog()
				}

				if (this.tag)
				{
					BX.bind(this.tag, "focus", BX.delegate(function(e) {
						this.initDialog({
							realParams: true,
							bByFocusEvent: true
						});
						return e.preventDefault();
					}, this));

					this.selectorInstance.setTagTitle();
				}

				BX.bind(this.input, "keydown", function(event) {
					this.selectorInstance.getSearchInstance().beforeSearchHandler({ event: event });
				}.bind(this));

				BX.bind(this.input, "bxchange", function(event) {
					setTimeout(function() {
						this.selectorInstance.getSearchInstance().searchHandler({
							event: event,
							tagInputName: params.tagId
						});
					}.bind(this), 0); // because onpaste event is fired before actual value change
				}.bind(this));

				this.input.setAttribute('data-bxchangehandler', 'Y');
			}
			else if(
				this.getOption('useContainer') == 'Y'
				&& (
					this.getOption('lazyLoad') != 'Y'
					|| this.getOption('useContainer') == 'Y'
				)
			)
			{
				this.initDialog();
			}

			if (this.items.hidden)
			{
				for (var ii in this.items.hidden)
				{
					if (this.items.hidden.hasOwnProperty(ii))
					{
						this.callback.select.apply(
							{
								id: (typeof this.items.hidden[ii]["PREFIX"] != 'undefined' ? this.items.hidden[ii]["PREFIX"] : 'SG') + this.items.hidden[ii]["ID"],
								name: this.items.hidden[ii]["NAME"]
							},
							(typeof this.items.hidden[ii]["TYPE"] != 'undefined' ? this.items.hidden[ii]["TYPE"] : 'sonetgroups'),
							'',
							true,
							'',
							'init'
						);
					}
				}
			}
		},

		show: function()
		{
			this.initDialog();
		},

		initDialog: function(openDialogParams)
		{
			if (
				typeof openDialogParams == 'undefined'
				|| typeof openDialogParams.realParams == 'undefined'
			)
			{
				openDialogParams = null;
			}

			if (this.blockInit)
			{
				return;
			}

			var eventParams = {
				id : this.id
			};

			if (!this.initialized)
			{
				BX.onCustomEvent(window, 'BX.Main.SelectorV2:beforeInitDialog', [ eventParams ]);
			}

			setTimeout(BX.delegate(function() {
				if (
					typeof eventParams.blockInit == 'undefined'
					|| eventParams.blockInit !== true
				)
				{
					if (this.initialized)
					{
						if (
							!this.mainPopupWindow
							|| !this.mainPopupWindow.isShown()
						)
						{
							if (this.input)
							{
								this.input.style.display = 'inline-block';
							}

							if (this.inputBox)
							{
								this.inputBox.style.display = 'inline-block';
							}

							this.openDialog(openDialogParams);
						}
					}
					else
					{
						this.getData(BX.delegate(function(data) {
							if (!!this.openDialogWhenInit)
							{
								if (this.input)
								{
									this.input.style.display = 'inline-block';
								}

								if (this.inputBox)
								{
									this.inputBox.style.display = 'inline-block';
								}

								this.openDialog(openDialogParams);
							}

							BX.onCustomEvent(window, 'BX.Main.SelectorV2:afterInitDialog', [ {
								id: this.id
							} ]);

							if (
								typeof this.options.eventOpen != 'undefined'
								&& !this.eventOpenBinded
							)
							{
								this.eventOpenBinded = true;
								BX.addCustomEvent(window, this.options.eventOpen, function(params) {

									if (
										typeof params.id == 'undefined'
										|| params.id != this.id
									)
									{
										return;
									}

									if (
										this.options.multiple != 'Y'
										&& this.inputItemsContainer
										&& this.inputItemsContainer.children.length > 0
									)
									{
										return;
									}

									if (
										this.mainPopupWindow
										&& this.mainPopupWindow.isShown()
									)
									{
										return;
									}

									if (this.input)
									{
										this.input.style.display = 'inline-block';
									}

									if (this.inputBox)
									{
										this.inputBox.style.display = 'inline-block';
									}

									var bindNode = (
										(
											BX.type.isNotEmptyObject(params)
											&& params.bindNode
										)
											? params.bindNode
											: this.bindNode
									);

									var bindPosition = (
										(
											BX.type.isNotEmptyObject(params)
											&& BX.type.isNotEmptyObject(params.bindPosition)
										)
											? params.bindPosition
											: false
									);

									if (bindNode)
									{
										var inputNode = BX.findChild(bindNode, {
											tagName : "input",
											attr : {
												type: "text"
											}
										}, true);

										if (inputNode)
										{
											this.selectorInstance.nodes.input = inputNode;

											var tagNode = (this.tag || BX(params.tagId));
											if (tagNode)
											{
												this.selectorInstance.nodes.tag = tagNode;
											}

											if (inputNode.getAttribute('data-bxchangehandler') !== 'Y')
											{
												BX.bind(inputNode, "keydown", function(event) {
													this.selectorInstance.getSearchInstance().beforeSearchHandler({ event: event });
												}.bind(this));

												BX.bind(inputNode, "bxchange", function(event) {
													this.selectorInstance.getSearchInstance().searchHandler({
														event: event
													});
												}.bind(this));

												inputNode.setAttribute('data-bxchangehandler', 'Y');
											}
										}
									}

									if (
										bindPosition
										|| bindNode
									)
									{
										if (!this.initialized)
										{
											this.selectorInstance.itemsSelected = {};
										}

										if (typeof params.value != 'undefined')
										{
											this.selectorInstance.itemsSelected = params.value;
										}

										this.openDialog({
											bindNode: bindNode,
											bindPosition: bindPosition
										});
									}

								}.bind(this));
							}
						}, this));
					}
				}
			}, this), 1);
		},

		openDialog: function(openDialogParams)
		{
			if (BX.type.isNotEmptyObject(openDialogParams))
			{
				if (typeof openDialogParams.bindNode != 'undefined')
				{
					this.selectorInstance.bindOptions.node = openDialogParams.bindNode;
				}
				if (typeof openDialogParams.bindPosition != 'undefined')
				{
					this.selectorInstance.bindOptions.position = openDialogParams.bindPosition;
				}
			}

			this.selectorInstance.openDialog();
			this.mainPopupWindow = (this.getOption('useContainer') == 'Y' ? this.selectorInstance.popups.container : this.selectorInstance.popups.main);
		},

		closeDialog: function()
		{
			this.selectorInstance.closeDialog();
		},

		getData: function(callback)
		{
			this.blockInit = true;
			BX.onCustomEvent('BX.Main.SelectorV2:onGetDataStart', [ this.id ]);

			BX.ajax.runComponentAction('bitrix:main.ui.selector', 'getData', {
				mode: 'ajax',
				data: {
					options: this.options,
					entityTypes: this.entityTypes,
					selectedItems: this.items.selected || {},
				}
			}).then(function (response) {
				if (BX.type.isNotEmptyObject(response.data))
				{
					this.blockInit = false;
					BX.onCustomEvent('BX.Main.SelectorV2:onGetDataFinish', [ this.id ]);
					this.addData(response.data, callback);
					this.initialized = true;
				}
			}.bind(this), function (response) {
				this.blockInit = false;
				BX.onCustomEvent('BX.Main.SelectorV2:onGetDataFinish', [ this.id ]);
			}.bind(this));
		},

		loadAll: function(params)
		{
			var
				entity = (BX.type.isNotEmptyString(params.entity) ? params.entity.toUpperCase() : 'USERS');

			if (
				BX.type.isNotEmptyObject(params)
				&& BX.type.isFunction(params.callback)
				&& !BX.Main.selectorManagerV2.loadedEntities[entity]
			)
			{
				BX.Main.selectorManagerV2.loadedEntities[entity] = true;

				BX.ajax.runComponentAction('bitrix:main.ui.selector', 'loadAll', {
					mode: 'ajax',
					data: {
						entityType: entity
					}
				}).then(function (response) {
					if (BX.type.isNotEmptyObject(response.data))
					{
						for (var entityType in response.data)
						{
							if (response.data.hasOwnProperty(entityType))
							{
								BX.onCustomEvent('onFinderAjaxLoadAll', [ response.data[entityType], BX.UI.SelectorManager, entityType.toLowerCase() ]);
							}
						}
						params.callback();
					}
				}.bind(this), function (response) {
				}.bind(this));
			}
		},

		searchRequest: function(params)
		{
			this.blockInit = true;
			if (this.selectorInstance.timeouts.search)
			{
				clearTimeout(this.selectorInstance.timeouts.search);
			}

			var
				d = new Date();
			this.selectorInstance.searchRequestId = d.getTime();

			BX.ajax.runComponentAction('bitrix:main.ui.selector', 'doSearch', {
				mode: 'ajax',
				data: {
					searchString: params.searchString,
					searchStringConverted: (
						BX.message('LANGUAGE_ID') == 'ru'
						&& BX.correctText
							? BX.correctText(params.searchString)
							: ''
					),
					currentTimestamp: this.selectorInstance.searchRequestId,
					options: this.options,
					entityTypes: this.entityTypes,
					additionalData: (BX.type.isNotEmptyObject(params.additionalData) ? params.additionalData : {})
				},
				onrequeststart: function(xhr) {
					this.selectorInstance.searchXhr = xhr;
				}.bind(this)
			}).then(function (response) {
				this.blockInit = false;
				if (
					BX.type.isNotEmptyObject(response.data)
					&& response.data.currentTimestamp
					&& response.data.currentTimestamp == this.selectorInstance.searchRequestId
					&& BX.type.isNotEmptyObject(params.callback)
					&& BX.type.isFunction(params.callback.success)
				)
				{
					params.callback.success(response.data, {
						searchString: params.searchString,
						searchStringOriginal: (BX.type.isNotEmptyObject(params.searchStringOriginal) ? params.searchStringOriginal : params.searchString),
					});
				}
			}.bind(this), function (response) {
				this.blockInit = false;
				if (
					BX.type.isNotEmptyObject(params.callback)
					&& BX.type.isFunction(params.callback.failure)
				)
				{
					params.callback.failure(BX.type.isNotEmptyObject(response.data) ? response.data : {});
				}
			}.bind(this));
		},

		getTreeItemRelation: function(params)
		{
			BX.ajax.runComponentAction('bitrix:main.ui.selector', 'getTreeItemRelation', {
				mode: 'ajax',
				data: {
					entityType: params.entityType,
					categoryId: params.categoryId,
					allowSearchSelf: params.allowSearchSelf,
				}
			}).then(function (response) {
				if (
					BX.type.isNotEmptyObject(response.data)
					&& typeof params.callback != 'undefined'
				)
				{
					params.callback({
						selectorInstanceId: this.selectorInstance.id,
						entityType: params.entityType,
						categoryId: params.categoryId,
						data: response.data
					});
				}
			}.bind(this), function (response) {});
		},

		addData: function(data, callback)
		{
			BX.onCustomEvent('BX.Main.SelectorV2:onAddData', [ this.id, data ]);

			if (
				typeof data.ENTITIES.CRM != 'undefined'
				&& typeof data.ENTITIES.CRM.ITEMS_LAST != 'undefined'
				&& BX.type.isNotEmptyObject(data.ENTITIES.CRM.ITEMS_LAST)
			)
			{
//				BX.SocNetLogDestination.obCrmFeed[this.id] = true;
			}

			callback.apply(this, data);
		},

		getId: function()
		{
			return this.id;
		},
		getOption: function(optionId)
		{
			return (
				typeof this.options[optionId] != 'undefined'
					? this.options[optionId]
					: null
			);
		},

		saveDestination: function(params)
		{
			if (
				BX.type.isNotEmptyObject(params)
				&& BX.type.isNotEmptyString(params.itemId)
			)
			{
				var context = this.getOption('context');
				if (BX.type.isNotEmptyString(context))
				{
					BX.ajax.runComponentAction('bitrix:main.ui.selector', 'saveDestination', {
						mode: 'ajax',
						data: {
							context: context,
							itemId: params.itemId
						}
					}).then(
						function (response) {},
						function (response) {}
					);
				}
			}
		}
	};
})();
