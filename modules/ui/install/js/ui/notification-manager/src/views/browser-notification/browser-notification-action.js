import { Type } from 'main.core';
import { UI } from 'ui.notification';
import { Button, ButtonOptions } from 'ui.buttons';

export default class BrowserNotificationAction extends UI.Notification.Action
{
	static BASE_BUTTON_CLASS = 'ui-notification-manager-browser-button';
	static TYPE_ACCEPT: string = 'accept';

	constructor(balloon, options)
	{
		super(balloon, options);

		this.setButtonClass(options.buttonType);
	}

	getContainer(): HTMLElement
	{
		if (this.container !== null)
		{
			return this.container;
		}

		let buttonOptions: ButtonOptions = {
			text: this.getTitle(),
		}

		if (Type.isFunction(this.events.click))
		{
			buttonOptions.onclick = (button: Button, event: Event) => {
				event.stopPropagation();

				this.events.click(button, event);
			}
		}

		const button = new Button(buttonOptions);

		button.removeClass('ui-btn');
		button.addClass(BrowserNotificationAction.BASE_BUTTON_CLASS);
		button.addClass(this.getButtonClass());

		this.container = button.getContainer();

		return this.container;
	}

	static getButtonTypes()
	{
		return [
			BrowserNotificationAction.TYPE_ACCEPT,
		];
	}

	static isSupportedButtonType(buttonType: string)
	{
		return BrowserNotificationAction.getButtonTypes().includes(buttonType);
	}

	setButtonClass(buttonType: ?string)
	{
		this.buttonClass =
			BrowserNotificationAction.isSupportedButtonType(buttonType)
				? BrowserNotificationAction.BASE_BUTTON_CLASS + '-' + buttonType
				: ''
		;
	}

	getButtonClass()
	{
		return this.buttonClass;
	}
}
