;(function()
{
	const namespace = BX.namespace('BX.Mail.MailboxList');
	if (namespace.Manager)
	{
		return;
	}

	class Manager
	{
		constructor(params)
		{
			this.gridId = params.gridId;

			this.sliderMessageEvent = 'SidePanel.Slider:onMessage';
			this.mailboxSuccessEventId = 'mail-mailbox-config-success';

			this.bindEvents();
		}

		bindEvents()
		{
			BX.addCustomEvent(this.sliderMessageEvent, this.onSliderMessage.bind(this));
		}

		onSliderMessage(event)
		{
			if (event.getEventId() === this.mailboxSuccessEventId)
			{
				const grid = BX.Main.gridManager.getInstanceById(this.gridId);
				if (grid)
				{
					grid.reload();
				}
			}
		}

		unbindEvents()
		{
			BX.removeCustomEvent(this.sliderMessageEvent, this.onSliderMessage.bind(this));
		}

		destroy()
		{
			this.unbindEvents();
		}
	}

	namespace.Manager = Manager;
})();
