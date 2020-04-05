;(function ()
{
	BX.namespace('BX.Sender.UI');
	if (BX.Sender.UI.UserSelector)
	{
		return;
	}

	/**
	 * Tile.
	 *
	 */
	function UserSelector (params)
	{
		this.caller = params.caller;
		this.ajaxAction = new BX.AjaxAction(params.actionUri);
		this.container = BX(params.containerId);
		this.id = params.id;
		this.containerId = params.containerId;
		this.inputId = params.inputName;
		this.inputNode = this.container.querySelector('input[name="' + params.inputName + '"]');

		this.selector = BX.Sender.UI.TileSelector.getById(this.id);
		if (!this.selector)
		{
			throw new Error('Tile selector `' + this.id + '` not found.');
		}
		this.searchInputNode = this.selector.getSearchInput();
		if (!this.searchInputNode.id)
		{
			this.searchInputNode.id = this.inputId + '-search-input'
		}

		BX.addCustomEvent(this.selector, this.selector.events.buttonSelect, this.openDialog.bind(this));
		BX.addCustomEvent(this.selector, this.selector.events.tileRemove, this.removeTile.bind(this));

		BX.Sender.UI.UserSelectorController.init(this);
	}
	UserSelector.prototype = {
		openDialog: function()
		{
			BX.Sender.UI.UserSelectorController.open(this);
		},
		removeTile: function(tile)
		{
			this.unsetValue(tile.id);
		},
		setUsers: function(list)
		{
			list = list || [];
			this.inputNode.value = list.join(',');
		},
		getUsers: function()
		{
			if (!this.inputNode)
			{
				return [];
			}

			var list = this.inputNode.value.split(',');
			return list.filter(function (id) {
				id = parseInt(id);
				return !!id;
			}).map(function (id) {
				return parseInt(id);
			});
		},
		setValue: function(value)
		{
			if (/^\d+$/.test(value) !== true)
			{
				return;
			}

			value = parseInt(value);
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
			if (/^\d+$/.test(value) !== true)
			{
				return;
			}

			value = parseInt(value);
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
		}
	};

	var Controller = {
		list: [],
		init: function (userSelector)
		{
			this.list.push(userSelector);
			BX.onCustomEvent(window, 'BX.Sender.UI.UserSelectorController::init', [{
				id: userSelector.id,
				inputId: userSelector.searchInputNode.id,
				containerId: userSelector.containerId,
				openDialogWhenInit: false
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
				var name = userSelector.id;

				var selectorInstance = BX.UI.SelectorManager.instances[userSelector.id];
				if (selectorInstance)
				{
					selectorInstance.itemsSelected = {};
					userSelector.getUsers().forEach(function (id) {
						selectorInstance.itemsSelected[id] = 'users';
					});
					selectorInstance.nodes.input = userSelector.selector.input;
					selectorInstance.nodes.tag = userSelector.selector.buttonSelect;
				}
			}

			userSelector.isOpen = true;
			BX.onCustomEvent(window, 'BX.Sender.UI.UserSelectorController::open', [{
				id: userSelector.id,
				inputId: userSelector.searchInputNode.id,
				containerId: userSelector.containerId,
				bindNode: userSelector.container
			}]);
		},
		select: function (params)
		{
			var self = BX.Sender.UI.UserSelectorController;
			var userSelector = self.getUserSelector(params.selectorId);
			if (!userSelector)
			{
				return;
			}

			userSelector.setValue(params.item.entityId);
			userSelector.selector.addTile(params.item.name, {}, params.item.entityId);
		},
		unSelect: function (params)
		{
			var self = BX.Sender.UI.UserSelectorController;
			var userSelector = self.getUserSelector(params.selectorId);
			if (!userSelector)
			{
				return;
			}

			userSelector.unsetValue(params.item.entityId);
			var tile = userSelector.selector.getTile(params.item.entityId);
			userSelector.selector.removeTile(tile);
		},
		closeDialog: function (params)
		{
			var self = BX.Sender.UI.UserSelectorController;
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
			var self = BX.Sender.UI.UserSelectorController;
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
			var self = BX.Sender.UI.UserSelectorController;
			var userSelector = self.getUserSelector(params.selectorId);
			if (!userSelector)
			{
				return;
			}

			userSelector.isOpen = false;

			if (userSelector.selector)
			{
				userSelector.selector.input.value = '';
				userSelector.selector.input.style.display = 'none';
				userSelector.selector.buttonSelect.style.display = '';
			}
		},
		getUserSelector: function (id)
		{
			var userSelector = this.list.filter(function (selector) {
				return selector.id === id;
			});

			return userSelector[0];
		}
	};

	if (!BX.Sender.UI.UserSelectorController)
	{
		BX.Sender.UI.UserSelectorController = Controller;
	}

	BX.Sender.UI.UserSelector = UserSelector;

})(window);