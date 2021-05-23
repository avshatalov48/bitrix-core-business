;(function ()
{
	BX.namespace('BX.Sender.Call');
	if (BX.Sender.Call.Number)
	{
		return;
	}

	var Helper = BX.Sender.Helper;

	/**
	 * Selector.
	 *
	 */
	function Selector()
	{
	}
	Selector.prototype.init = function (params)
	{
		this.context = BX(params.containerId);
		this.providerSelectorNode = Helper.getNode('provider-selector', this.context);
		this.numberSelectorNode = Helper.getNode('number-selector', this.context);
		this.numberSelectorBlockNode = Helper.getNode('number-selector-block', this.context);
		this.selectedProvider = false;
		this.inputNode = Helper.getNode('number-input', this.context);
		this.list = params.list;

		var menu = this.list.map(function (item) {
			return {
				id: item.id,
				text: item.name,
				onclick: this.onProviderSelect.bind(this, item)
			};
		}, this);
		if (params.hasRest)
		{
			menu.push({delimiter: true}, {
				text: params.mess.marketplaceSendersList,
				href: '/marketplace/category/voximplant_infocalls/',
				target: '_blank'
			});
		}

		if (this.list.length > 0)
		{
			BX.bind(this.providerSelectorNode, 'click', this.showMenu.bind(this, this.providerSelectorNode, menu, 'main'));
			BX.bind(this.numberSelectorNode, 'click', this.showNumbersMenu.bind(this));

			var value = this.inputNode.value;
			var selectedProvider = false;
			var selectedNumber = false;
			if (value)
			{
				for (var providerIterator = 0; providerIterator< this.list.length; providerIterator++)
				{
					var item = this.list[providerIterator];
					if (item.numbers && item.numbers.length)
					{
						for (var i=0; i<item.numbers.length; i++)
						{
							if (value.toString() === item.numbers[i].id.toString())
							{
								selectedProvider = item;
								selectedNumber = item.numbers[i];
								break;
							}
						}
					}
					else if (value.toString() === item.id.toString())
					{
						selectedProvider = item;
					}
					if (selectedProvider)
						break;
				}
			}
			if (selectedProvider)
			{
				this.setProvider(selectedProvider, selectedNumber);
			}
			else
			{
				this.setProvider(this.list[0], false);
			}
		}
		else
		{
			var item = {
				'id': '',
				'name': this.providerSelectorNode.getAttribute('data-setup-name')
			};
			this.setProvider(item, false);
			var uri = this.providerSelectorNode.getAttribute('data-setup-uri');
			BX.bind(this.providerSelectorNode, 'click', function () {
				top.location.href = uri;
			});
		}
	};
	Selector.prototype.setProvider = function (provider, selectedNumber)
	{
		if (this.selectedProvider.id == provider.id)
		{
			return;
		}
		this.selectedProvider = provider;
		this.providerSelectorNode.textContent = provider.name;
		this.inputNode.value = provider.hasOwnProperty('id') ? provider.id : "";
		if (provider.numbers && provider.numbers.length) {
			this.showNumbersSelector(provider, selectedNumber);
		} else {
			this.hideNumbersSelector();
		}
	};
	Selector.prototype.setNumber = function (number)
	{
		this.numberSelectorNode.textContent = number.name;
		this.inputNode.value = number.id;
	};
	Selector.prototype.onProviderSelect = function (provider, e)
	{
		this.setProvider(provider, false);
		this.closeMenu();
	};
	Selector.prototype.showNumbersSelector = function (provider, selectedNumber)
	{
		BX.show(this.numberSelectorBlockNode);
		this.setNumber(selectedNumber ? selectedNumber : provider.numbers[0]);
	};
	Selector.prototype.hideNumbersSelector = function ()
	{
		BX.hide(this.numberSelectorBlockNode);
	};
	Selector.prototype.showNumbersMenu = function ()
	{
		if (!this.selectedProvider || !this.selectedProvider.numbers || !this.selectedProvider.numbers.length)
			return;

		var menu = this.selectedProvider.numbers.map(function (item) {
			return {
				id: item.id,
				text: item.name,
				onclick: this.onNumberSelect.bind(this, item)
			};
		}, this);
		this.destroyMenu('numbers');
		this.showMenu(this.numberSelectorNode, menu, 'numbers');
	};
	Selector.prototype.onNumberSelect = function (number, e)
	{
		this.setNumber(number);
		this.closeMenu();
	};
	Selector.prototype.showMenu = function (node, menuItems, popupId)
	{
		this.popup = this.createMenu(
			'sender-call-number-' + popupId,
			node,
			menuItems,
			{offsetLeft: 10}
		);
		this.popup.popupWindow.show();
	};
	Selector.prototype.createMenu = function (popupId, button, items, params)
	{
		params = params || {};
		return BX.PopupMenu.create(
			popupId,
			button,
			items,
			{
				autoHide: true,
				offsetLeft: params.offsetLeft ? params.offsetLeft : -21,
				offsetTop: params.offsetTop ? params.offsetTop : -3,
				angle:
				{
					position: "top",
					offset: 42
				}
			}
		);
	};
	Selector.prototype.closeMenu = function ()
	{
		if(this.popup && this.popup.popupWindow)
		{
			this.popup.popupWindow.close();
		}
	};
	Selector.prototype.destroyMenu = function (popupId)
	{
		BX.PopupMenu.destroy('sender-call-number-' + popupId);
	};

	BX.Sender.Call.Number = new Selector();

})(window);