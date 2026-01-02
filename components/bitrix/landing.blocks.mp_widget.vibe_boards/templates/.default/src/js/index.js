export class VibeBoards extends BX.Landing.Widget.Base
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
}