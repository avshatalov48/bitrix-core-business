;(function ()
{
	BX.namespace('BX.Sender.SMS');
	if (BX.Sender.SMS.Sender)
	{
		return;
	}

	var Helper = {
		changeDisplay: function (node, isShow)
		{
			if (!node)
			{
				return;
			}

			node.style.display = isShow ? '' : 'none';
		}
	};

	/**
	 * Sender.
	 *
	 */
	function Sender()
	{
		this.context = null;
		this.editor = null;
	}
	Sender.prototype.init = function (params)
	{
		this.context = BX(params.containerId);
		this.senderInputNode = this.context.querySelector('[data-role="sender-input"]');
		this.senderNode = this.context.querySelector('[data-role="sender-selector"]');
		this.fromContainerNode = this.context.querySelector('[data-role="from-container"]');
		this.setupNode = this.context.querySelector('[data-role="setup"]');
		this.fromNode = this.context.querySelector('[data-role="from-selector"]');

		this.manageUrl = params.manageUrl;
		this.senderId = params.senderId;
		this.list = params.list;

		var menuSenders = [];
		var prevFromRest = false;
		for (var i = 0; i < this.list.length; ++i)
		{
			var item = this.list[i];
			if (item.fromRest && !prevFromRest) {
				prevFromRest = item.fromRest;
				menuSenders.push({delimiter: true});
			}

			menuSenders.push({
				id: item.senderId + '.' + Math.random(),
				text: item.name,
				className: (!item.canUse ? 'sender-sms-popup-menu-item-disabled menu-popup-no-icon' : ''),
				onclick: this.onSenderSelect.bind(this, item)
			});
		}
		if (params.hasRest)
		{
			menuSenders.push({delimiter: true}, {
				text: params.mess.marketplaceSendersList,
				href: '/marketplace/category/crm_robot_sms/',
				target: '_blank'
			});
		}

		this.initSender();

		BX.bind(this.senderNode, 'click', this.showMenu.bind(this, this.senderNode, menuSenders, 'main'));
		BX.bind(this.fromNode, 'click', this.showFromMenu.bind(this));
	};
	Sender.prototype.initSender = function ()
	{
		if (this.getSender())
		{
			var sender = this.getSender();
			var filtered = this.list.filter(function (item) {
				var filtered = item.data.list.filter(function (fromItem) {
					return sender == fromItem.id;
				}, this);

				if (filtered.length > 0)
				{
					Helper.changeDisplay(this.fromContainerNode, !item.data.isHidden);
					this.setSender(filtered[0]);
					return true;
				}

				return false;
			}, this);

			if (filtered.length > 0)
			{
				return;
			}
		}

		var item = this.getItemById(this.senderId);
		if (!item && this.list.length > 0)
		{
			for (var i=0; i<this.list.length; i++)
			{
				if (this.list[i].canUse)
				{
					item = this.list[i];
					break;
				}
			}
		}
		if (item)
		{
			Helper.changeDisplay(this.fromContainerNode, !item.data.isHidden);
			this.setSender(item.data.list[0]);
		}
		else
		{
			this.showSetup(true);
		}
	};
	Sender.prototype.showSetup = function (isShow, link)
	{
		link = link || this.manageUrl;
		isShow = isShow || false;

		Helper.changeDisplay(this.setupNode, isShow);
		this.setupNode.href = link;
	};
	Sender.prototype.getItemById = function (senderId)
	{
		var list = this.list.filter(function (item) {
			return item.senderId == senderId;
		}, this);

		return (list.length === 0 ? null : list[0]);
	};
	Sender.prototype.onSenderSelect = function (item)
	{
		if (!item.canUse)
		{
			this.closeMenu();
			window.open(item.manageUrl);
			return;
		}
		this.senderNode.textContent = item.shortName;
		this.closeMenu();

		var showSelector = false;
		if (item.isConfigurable && !item.canUse)
		{
			showSelector = false;
		}
		else
		{
			showSelector = !item.data.isHidden;
		}

		Helper.changeDisplay(this.setupNode, false);
		Helper.changeDisplay(this.fromContainerNode, showSelector);

		this.senderId = item.senderId;
		this.setSender(item.data.list[0]);
	};
	Sender.prototype.setSender = function (sender)
	{
		this.fromNode.textContent = sender.name;
		this.senderInputNode.value = sender.id;
	};
	Sender.prototype.getSender = function ()
	{
		return this.senderInputNode.value;
	};
	Sender.prototype.onSelect = function (fromItem)
	{
		this.setSender(fromItem);
		this.closeMenu();
	};
	Sender.prototype.showFromMenu = function ()
	{
		var item = this.getItemById(this.senderId);
		if (!item)
		{
			return;
		}

		var menuItems = item.data.list.map(function (fromItem) {
			return {
				id: fromItem.id,
				text: fromItem.name,
				onclick: this.onSelect.bind(this, fromItem)
			};
		}, this);

		this.showMenu(this.fromNode, menuItems, this.senderId);
	};
	Sender.prototype.showMenu = function (node, menuItems, popupId)
	{
		this.popup = this.createMenu(
			'sender-sms-sender-' + popupId,
			node,
			menuItems
		);
		this.popup.popupWindow.show();
	};
	Sender.prototype.createMenu = function (popupId, button, items, params)
	{
		params = params || {};

		if(BX.PopupMenu.getMenuById(popupId) !== null)
		{
			return BX.PopupMenu.getMenuById(popupId);
		}

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
	Sender.prototype.closeMenu = function ()
	{
		if(this.popup && this.popup.popupWindow)
		{
			this.popup.popupWindow.close();
		}
	};


	BX.Sender.SMS.Sender = new Sender();

})(window);