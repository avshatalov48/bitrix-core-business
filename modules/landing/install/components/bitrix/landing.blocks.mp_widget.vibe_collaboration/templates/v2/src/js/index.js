import { Messenger } from 'im.public';

export class VibeCollaborationV2 extends BX.Landing.Widget.Base
{
	constructor(element, options)
	{
		super(element);

		this.options = options;
		this.element = element;

		this.initialize();
	}

	initialize()
	{
		this.bindButtonEvents();
	}

	bindButtonEvents()
	{
		const buttonHandlers = {
			'.button-1': this.handleButton1Click.bind(this),
			'.button-2': this.handleButton2Click.bind(this),
			'.button-3': this.handleButton3Click.bind(this),
		};

		Object.entries(buttonHandlers).forEach(([selector, handler]) => {
			const button = this.element.querySelector(selector);
			if (button)
			{
				BX.bind(button, 'click', handler);
			}
		});
	}

	handleButton1Click(event)
	{
		event.preventDefault();
		BX.SidePanel.Instance.open(this.options.inviteDialogLink);
	}

	handleButton2Click(event)
	{
		event.preventDefault();
		(new BX.UI.FeaturePromoter({ code: 'limit_demo' })).show();
	}

	handleButton3Click(event)
	{
		event.preventDefault();
		Messenger.openChat(this.options.generalChatId);
	}
}