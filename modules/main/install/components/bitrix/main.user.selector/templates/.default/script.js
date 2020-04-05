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
		this.inputNode = this.container.querySelector('input[name="' + params.inputName + '"]');
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
		BX.Main.User.SelectorController.init(this);
	}
	UserSelector.prototype = {
		openDialog: function()
		{
			if (this.lazyload)
			{
				BX.onCustomEvent("BX.Main.SelectorV2:initDialog", [ {
					selectorId: this.id,
					openDialogWhenInit: true
				}]);
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
				var list = this.getUsers().filter(function (id) {
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
			this.container.insertBefore(inputNode, this.container.firstElementChild);
		},
		addInputs: function(list)
		{
			this.removeInputs();
			list.forEach(function (value) {
				this.addInput(value);
			}, this);
		},
		getInputs: function()
		{
			return BX.convert.nodeListToArray(this.container.querySelectorAll('input[name="' + this.inputName + '"]'));
		},
		removeInputs: function()
		{
			this.getInputs().forEach(function (inputNode) {
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
		select: function (params)
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
			if (
				BX.type.isNotEmptyObject(params.item.params)
				&& BX.type.isNotEmptyString(params.item.params.email)
			)
			{
				entityId = 'UE' + entityId;
			}
			userSelector.setValue(entityId);

			var data = {
				readonly: !!params.undeletable
			};
			if (BX.type.isNotEmptyString(params.entityType))
			{
				data.entityType = params.entityType;
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

			userSelector.selector.addTile(params.item.name, data, entityId);
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
				contextNode: userSelector.selector.context
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

			BX.onCustomEvent('BX.Main.User.SelectorController:unSelect', [ {
				selectorId: params.selectorId,
				item: params.item,
				contextNode: userSelector.selector.context
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
		},
		getUserSelector: function (id)
		{
			var userSelector = this.list.filter(function (selector) {
				return selector.id === id;
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