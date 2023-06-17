;(function ()
{
	BX.namespace('BX.Main.User');
	if (BX.Main.User.Selector)
	{
		return;
	}

	/**
	 * UserSelector.
	 *
	 */
	function UserSelector (params)
	{
		this.caller = params.caller;
		this.container = BX(params.containerId);
		this.id = params.id;
		this.containerId = params.containerId;
		this.inputName = params.inputName;
		this.inputId = params.inputName;
		this.isInputMultiple = params.isInputMultiple;
		this.inputNode = (this.container ? this.container.querySelector('input[name="' + params.inputName + '"]') : null);
		this.useSymbolicId = params.useSymbolicId;
		this.openDialogWhenInit = !!params.openDialogWhenInit;

		this.selector = BX.UI.TileSelector.getById(this.id);
		if (!this.selector)
		{
			throw new Error('Tile selector `' + this.id + '` not found.');
		}
		this.searchInputNode = this.selector.getSearchInput();
		if (!this.searchInputNode.id)
		{
			this.searchInputNode.id = this.inputId + '-' + this.id + '-search-input'
		}
		this.lazyload = !!params.lazyload;

		BX.addCustomEvent(this.selector, this.selector.events.buttonSelect, this.openDialog.bind(this));
		BX.addCustomEvent(this.selector, this.selector.events.tileRemove, this.removeTile.bind(this));
		BX.addCustomEvent(this.selector, this.selector.events.tileClick, this.clickTile.bind(this));
		BX.Main.User.SelectorController.init(this);
	}
	UserSelector.prototype = {
		openDialog: function()
		{
			if (this.lazyload)
			{
				var initialized = false;

				if (BX.Main.selectorManagerV2)
				{
					var selectorInstance = BX.Main.selectorManagerV2.getById(this.id);
					if (
						selectorInstance
						&& selectorInstance.initialized
					)
					{
						BX.Main.User.SelectorController.open(this);
						initialized = true;
					}
				}

				if (!initialized)
				{
					BX.onCustomEvent("BX.Main.SelectorV2:initDialog", [ {
						selectorId: this.id,
						openDialogWhenInit: true
					}]);
				}
			}
			else
			{
				BX.Main.User.SelectorController.open(this);
			}
		},
		removeTile: function(tile)
		{
			this.unsetValue(tile.id);
		},
		clickTile: function(tile)
		{
			if (
				BX.type.isNotEmptyObject(tile.data)
				&& BX.type.isNotEmptyString(tile.data.url)
			)
			{
				if (
					BX.type.isNotEmptyString(tile.data.urlUseSlider)
					&& tile.data.urlUseSlider == 'Y'
					&& BX.type.isNotEmptyObject(BX.SidePanel)
				)
				{
					BX.SidePanel.Instance.open(tile.data.url);
				}
				else
				{
					window.open(tile.data.url, '_blank');
				}
			}
		},
		setUsers: function(list)
		{
			list = list || [];

			if (this.isInputMultiple)
			{
				this.addInputs(list);
			}
			else
			{
				this.inputNode.value = list.join(',');
				BX.fireEvent(this.inputNode, "change");
			}
		},
		getUsers: function()
		{
			if (
				!this.isInputMultiple
				&& !this.inputNode
			)
			{
				return [];
			}

			var list;
			if (this.isInputMultiple)
			{
				list = this.getInputs().map(function (inputNode) {
					return inputNode.value;
				});
			}
			else
			{
				list = this.inputNode.value.split(',');
			}

			if (!this.useSymbolicId)
			{
				return list.filter(function (id) {
					id = parseInt(id);
					return !!id;
				}).map(function (id) {
					return parseInt(id);
				});
			}
			else
			{
				return list.filter(function (id) {
					return (id.length > 0);
				});
			}
		},
		setValue: function(value)
		{
			if (!this.useSymbolicId)
			{
				if (/^\d+$/.test(value) !== true)
				{
					return;
				}
				value = parseInt(value);
			}

			if (this.selectOne)
			{
				this.setUsers([value]);
			}
			else
			{
				var list = this.getUsers();
				if (!BX.util.in_array(value, list))
				{
					list.push(value);
				}
				this.setUsers(list);
			}

		},
		unsetValue: function(value)
		{
			if (!this.useSymbolicId)
			{
				if (/^\d+$/.test(value) !== true)
				{
					return;
				}
				value = parseInt(value);
			}

			if (this.selectOne)
			{
				this.setUsers();
			}
			else
			{
				var jsonValue = false;
				if (BX.type.isNotEmptyObject(BX.Main.selectorManagerV2))
				{
					var mainSelectorInstance = BX.Main.selectorManagerV2.getById(this.id);
					if (mainSelectorInstance.getOption('returnJsonValue') == 'Y')
					{
						jsonValue = true;
					}
				}

				var list = this.getUsers().filter(function (id) {
					if (jsonValue)
					{
						var parsedItem = JSON.parse(id);
						if (BX.type.isNotEmptyObject(parsedItem))
						{
							id = parsedItem.id;
						}

					}
					return id !== value;
				});
				this.setUsers(list);
			}
		},
		addInput: function(value)
		{
			var inputNode = document.createElement('input');
			inputNode.type = 'hidden';
			inputNode.name = this.inputName;
			inputNode.value = value;
			this.container.appendChild(inputNode);
			BX.fireEvent(inputNode, "change");
			BX.Event.EventEmitter.emit(window, 'BX.Main.User.SelectorController:itemRendered', [{
				selectorId: this.id,
				value: value,
			}]);
		},
		addInputs: function(list)
		{
			this.removeInputs();
			list.forEach(function (value) {
				this.addInput(value);
			}, this);

			if (
				list.length <= 0
				&& this.isInputMultiple
			)
			{
				this.addInput('');
			}


		},
		getInputs: function()
		{
			return BX.convert.nodeListToArray(this.container.querySelectorAll('input[name="' + this.inputName + '"]'));
		},
		removeInputs: function()
		{
			this.getInputs().forEach(function (inputNode) {
				BX.fireEvent(inputNode, "change");
				BX.remove(inputNode);
			});
		}
	};


	var Controller = {
		list: [],
		init: function (userSelector)
		{
			this.list.push(userSelector);

			BX.onCustomEvent(window, 'BX.Main.User.SelectorController::init', [{
				id: userSelector.id,
				inputId: userSelector.searchInputNode.id,
				containerId: userSelector.containerId,
				openDialogWhenInit: userSelector.openDialogWhenInit
			}]);
		},
		open: function (userSelector)
		{
			if (userSelector.isOpen)
			{
				return;
			}

			if (BX.UI.SelectorManager)
			{
				// read selector data from the tiles
				var selectorInstance = BX.UI.SelectorManager.instances[userSelector.id];
				if (selectorInstance)
				{
					if (!userSelector.isInputMultiple)
					{
						selectorInstance.itemsSelected = {};
					}

					userSelector.getUsers().forEach(function (id) {

						var itemEntityId = null;

						for(var entityId in selectorInstance.entities)
						{
							if (
								selectorInstance.entities.hasOwnProperty(entityId)
								&& BX.type.isNotEmptyObject(selectorInstance.entities[entityId].items)
							)
							{
								if (BX.util.in_array(id, Object.keys(selectorInstance.entities[entityId].items)))
								{
									itemEntityId = entityId;
								}
							}
						}

						if (itemEntityId)
						{
							selectorInstance.itemsSelected[id] = itemEntityId.toLowerCase();
						}
					});

					selectorInstance.nodes.input = userSelector.selector.input;
					selectorInstance.nodes.tag = userSelector.selector.buttonSelect;
				}
			}

			userSelector.isOpen = true;

			BX.onCustomEvent(window, 'BX.Main.User.SelectorController::open', [{
				id: userSelector.id,
				inputId: userSelector.searchInputNode.id,
				containerId: userSelector.containerId,
				bindNode: userSelector.container
			}]);
		},
		formatName: function(params)
		{
			var result = '';
			var nameTemplate = (BX.type.isNotEmptyString(params.nameTemplate) ? BX.util.htmlspecialcharsback(BX.util.htmlspecialcharsback(params.nameTemplate)) : '#NAME#');

			if (BX.type.isNotEmptyObject(params.item))
			{
				var item = params.item;
				result = nameTemplate;
			}
			else
			{
				return result;
			}

			for (var field in item)
			{
				if (item.hasOwnProperty(field))
				{
					result = result.replace('#' + field.toUpperCase() + '#', BX.util.htmlspecialcharsback(item[field]));
				}
			}

			return result;
		},
		select: function (params)
		{
			var mainSelectorInstance = (BX.type.isNotEmptyObject(BX.Main.selectorManagerV2) ? BX.Main.selectorManagerV2.getById(params.selectorId) : null);

			var self = BX.Main.User.SelectorController;
			var userSelector = self.getUserSelector(params.selectorId);
			if (
				!userSelector
				|| !BX.type.isNotEmptyObject(params.item)
			)
			{
				return;
			}
			var entityId = userSelector.useSymbolicId ? params.item.id : params.item.entityId;
			if (
				BX.type.isNotEmptyObject(params.item.params)
				&& BX.type.isNotEmptyString(params.item.params.email)
				&& (
					!mainSelectorInstance
					|| mainSelectorInstance.getOption('returnJsonValue') != 'Y'
				)
			)
			{
				entityId = (BX.type.isNotEmptyString(params.prefix) ? params.prefix : 'UE') + entityId;
			}

			var jsonValue = false;
			if (
				mainSelectorInstance
				&& mainSelectorInstance.getOption('returnJsonValue') == 'Y'
			)
			{
				userSelector.setValue(JSON.stringify(params.item));
			}
			else
			{
				userSelector.setValue(entityId);
			}

			var data = {
				readonly: !!params.undeletable
			};
			if (BX.type.isNotEmptyString(params.entityType))
			{
				data.entityType = params.entityType;
			}
			if (BX.type.isNotEmptyString(params.item.url))
			{
				data.url = params.item.url;
			}
			if (BX.type.isNotEmptyString(params.item.urlUseSlider))
			{
				data.urlUseSlider = params.item.urlUseSlider;
			}
			if (
				BX.type.isNotEmptyString(params.item.isExtranet)
				&& params.item.isExtranet == 'Y'
			)
			{
				data.extranet = true;
			}
			if (
				BX.type.isNotEmptyString(params.item.isCrmEmail)
				&& params.item.isCrmEmail == 'Y'
			)
			{
				data.crmEmail = true;
			}
			if (BX.type.isNotEmptyString(params.state))
			{
				data.state = params.state;
			}

			userSelector.selector.addTile(self.formatName({
				item: params.item,
				nameTemplate: (mainSelectorInstance ? mainSelectorInstance.getOption('nameTemplate') : '#NAME# #LAST_NAME#')
			}), data, entityId);
			userSelector.selector.input.value = '';

			if (
				!userSelector.isInputMultiple
				|| !BX.type.isNotEmptyString(params.tab)
				|| params.tab != 'search'
			)
			{
				userSelector.selector.input.style.display = 'none';
				userSelector.selector.buttonSelect.style.display = '';
			}

			BX.onCustomEvent('BX.Main.User.SelectorController:select', [ {
				selectorId: params.selectorId,
				item: params.item,
				contextNode: userSelector.selector.context,
				containerId: userSelector.containerId,
				inputName: userSelector.inputName
			} ]);
		},
		unSelect: function (params)
		{
			var self = BX.Main.User.SelectorController;
			var userSelector = self.getUserSelector(params.selectorId);
			if (
				!userSelector
				|| !BX.type.isNotEmptyObject(params.item)
			)
			{
				return;
			}

			var entityId = userSelector.useSymbolicId ? params.item.id : params.item.entityId;
			userSelector.unsetValue(entityId);
			var tile = userSelector.selector.getTile(entityId);
			if (tile)
			{
				userSelector.selector.removeTile(tile);
			}

			if (BX.UI.SelectorManager)
			{
				var selectorInstance = BX.UI.SelectorManager.instances[params.selectorId];
				if (selectorInstance)
				{
					if (typeof selectorInstance.deleteSelectedItem == 'function') // compatibility
					{
						selectorInstance.deleteSelectedItem({
							itemId: params.item.id
						});
					}
					else
					{
						delete selectorInstance.itemsSelected[params.item.id];
					}
				}
			}

			if (
				!userSelector.isInputMultiple
				|| !BX.type.isNotEmptyString(params.tab)
				|| params.tab != 'search'
			)
			{
				userSelector.selector.input.style.display = 'none';
				userSelector.selector.buttonSelect.style.display = '';
			}

			BX.onCustomEvent('BX.Main.User.SelectorController:unSelect', [ {
				selectorId: params.selectorId,
				item: params.item,
				contextNode: userSelector.selector.context,
				containerId: userSelector.containerId,
				inputName: userSelector.inputName
			} ]);
		},
		openDialog: function (params)
		{
			var self = BX.Main.User.SelectorController;
			var userSelector = self.getUserSelector(params.selectorId);
			if (!userSelector)
			{
				return;
			}

			userSelector.isOpen = true;

			if (userSelector.selector)
			{
				userSelector.selector.input.style.display = '';
				userSelector.selector.buttonSelect.style.display = 'none';
				userSelector.selector.input.focus();
			}
		},
		closeDialog: function (params)
		{
			var self = BX.Main.User.SelectorController;
			var userSelector = self.getUserSelector(params.selectorId);
			if (!userSelector)
			{
				return;
			}

			userSelector.isOpen = false;

			if (userSelector.selector)
			{
				userSelector.selector.input.style.display = 'none';
				userSelector.selector.buttonSelect.style.display = '';
			}
		},
		openSearch: function (params)
		{
			var self = BX.Main.User.SelectorController;
			var userSelector = self.getUserSelector(params.selectorId);
			if (!userSelector)
			{
				return;
			}

			userSelector.isOpen = false;

			if (userSelector.selector)
			{
				userSelector.selector.input.style.display = '';
				userSelector.selector.buttonSelect.style.display = 'none';
			}
		},
		closeSearch: function (params)
		{
			var self = BX.Main.User.SelectorController;
			var userSelector = self.getUserSelector(params.selectorId);
			if (!userSelector)
			{
				return;
			}

			if (userSelector.selector)
			{
				var selectorInstance = BX.UI.SelectorManager.instances[params.selectorId];
				if (
					!selectorInstance
					|| !selectorInstance.closeByEmptySearchResult
				) // e.g. autohide
				{
					userSelector.selector.input.style.display = 'none';
					userSelector.selector.buttonSelect.style.display = '';
				}
			}
		},
		getUserSelector: function (id)
		{
			var userSelector = this.list.filter(function (selector) {
				return (
					selector.id === id
					&& (
						!selector.container
						|| document.body.contains(selector.container)
					)
				);
			});

			return userSelector[0];
		}
	};

	if (!BX.Main.User.SelectorController)
	{
		BX.Main.User.SelectorController = Controller;
	}

	BX.Main.User.Selector = UserSelector;

})(window);