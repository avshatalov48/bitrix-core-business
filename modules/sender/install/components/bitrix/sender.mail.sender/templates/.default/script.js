;(function (window)
{
	BX.namespace('BX.Sender.UI.Mailbox');
	if (BX.Sender.UI.Mailbox.Selector)
	{
		return;
	}

	var Helper = BX.Sender.Helper;

	/**
	 * Selector.
	 *
	 */
	function Selector(params)
	{
	}
	Selector.prototype.init = function (params)
	{
		this.mess = params.mess;
		this.list = params.list;
		this.context = BX(params.containerId);

		this.input = Helper.getNode('mailbox-input', this.context);
		this.mailbox = Helper.getNode('mailbox', this.context);
		BX.bind(this.mailbox, 'click', this.showMenu.bind(this));
	};
	Selector.prototype.onClick = function (item)
	{
		this.input.value = item.sender;
		this.mailbox.textContent = item.sender;
		this.popupMenu.close();
	};
	Selector.prototype.showAdd = function ()
	{
		BXMainMailConfirm.showForm(this.onAdd.bind(this));
		this.popupMenu.close();
	};
	Selector.prototype.onAdd = function (data, sender)
	{
		if (!sender)
		{
			return;
		}

		data.sender = sender;
		data.id = BX.util.hashCode(data.sender);
		this.list.push(data);
		this.popupMenu.addMenuItem(this.getMenuItem(data), 'new');
	};
	Selector.prototype.getMenuItem = function (item)
	{
		return {
			'id': item.id,
			'text': BX.util.htmlspecialchars(item.sender),
			'onclick': this.onClick.bind(this, item)
		};
	};
	Selector.prototype.showMenu = function ()
	{
		if (this.popupMenu)
		{
			this.popupMenu.show();
			return;
		}

		var items = this.list.map(this.getMenuItem, this);
		items.push({'delimiter': true});
		items.push({
			'id': 'new',
			'text': this.mess.addAddress,
			'onclick': this.showAdd.bind(this)
		});

		this.popupMenu = BX.PopupMenu.create(
			'sender-ui-mailbox-selector',
			this.mailbox,
			items,
			{
				autoHide: true,
				autoClose: true
			}
		);

		this.popupMenu.show();
	};


	BX.Sender.UI.Mailbox.Selector = new Selector();

})(window);