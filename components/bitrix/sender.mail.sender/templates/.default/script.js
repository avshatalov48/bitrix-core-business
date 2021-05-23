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
		this.list = params.list;
		this.list = params.containerId;
		this.context = BX(params.containerId);
		this.default = params.default;

		this.input = Helper.getNode('mailbox-input', this.context);
		this.mailbox = Helper.getNode('mailbox', this.context);
		this.mailboxWrap = Helper.getNode('mailbox-wrap', this.context);
		BX.bind(this.mailboxWrap, 'click', this.showMenu.bind(this));

		this.param = {
			placeholder: '',
			required: true,
			callback: function(title, text) {
				this.setSelected(title);
			}.bind(this),
			settings:[],
			popupSettings:{
				maxHeight:350
			}
		};

		this.setCurrent(params.current);
	};
	Selector.prototype.setCurrent = function(current)
	{
		this.ifExists(current)? this.setSelected(current):
			this.setSelected(this.getFirstOrDefault());
	};
	Selector.prototype.getFirstOrDefault = function()
	{
		return (BXMainMailConfirm.getMailboxes().length > 0)?
			BXMainMailConfirm.getMailboxes()[0].formated: '';

	};
	Selector.prototype.setSelected = function(value)
	{
		if(BX.type.isNotEmptyString(value))
		{
			this.param.selected = value;
			this.input.value = value;
			this.mailbox.textContent = value;
		}
		else
		{
			this.input.value = '';
			this.mailbox.textContent = this.default;
		}
	};
	Selector.prototype.ifExists = function(value)
	{
		if(BX.type.isNotEmptyString(value) && BXMainMailConfirm.getMailboxes().length > 0)
		{
			for(var i in BXMainMailConfirm.getMailboxes())
			{
				if(BXMainMailConfirm.getMailboxes()[i].formated === value)
				{
					return true;
				}
			}
		}
		return false;
	};
	Selector.prototype.showMenu = function ()
	{
		BXMainMailConfirm.showList('sender-ui-mailbox-selector',this.mailbox,this.param);
	};
	BX.Sender.UI.Mailbox.Selector = new Selector();

})(window);