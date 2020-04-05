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
		this.selectorNode = Helper.getNode('number-selector', this.context);
		this.inputNode = Helper.getNode('number-input', this.context);
		this.list = params.list;

		var menu = this.list.map(function (item) {
			return {
				id: item.id,
				text: item.name,
				onclick: this.onSelect.bind(this, item)
			};
		}, this);

		if (this.list.length > 0)
		{
			BX.bind(this.selectorNode, 'click', this.showMenu.bind(this, this.selectorNode, menu, 'main'));

			var value = this.inputNode.value;
			value = value ? value : this.list[0].id;
			var filtered = this.list.filter(function (item) {
				return value.toString() === item.id.toString();
			});
			var number = filtered.length > 0 ? filtered[0] : this.list[0];
			this.setNumber(number);
		}
		else
		{
			var item = {
				'id': '',
				'name': this.selectorNode.getAttribute('data-setup-name')
			};
			this.setNumber(item);
			var uri = this.selectorNode.getAttribute('data-setup-uri');
			BX.bind(this.selectorNode, 'click', function () {
				top.location.href = uri;
			});
		}
	};
	Selector.prototype.setNumber = function (number)
	{
		this.selectorNode.textContent = number.name;
		this.inputNode.value = number.id;
	};
	Selector.prototype.onSelect = function (number, e)
	{
		this.setNumber(number);
		this.closeMenu();
	};
	Selector.prototype.showMenu = function (node, menuItems, popupId)
	{
		this.popup = this.createMenu(
			'sender-call-number-' + popupId,
			node,
			menuItems
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
				},
				events:
				{
					onPopupClose : BX.delegate(this.onPopupClose, this)
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


	BX.Sender.Call.Number = new Selector();

})(window);