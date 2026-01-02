import { Button } from 'ui.buttons';
import { EventEmitter } from 'main.core.events';
import { Analytic} from '../analytic';

export class MarketPopupButton extends EventEmitter
{
	constructor(options: {
		text: string,
		onSuccess: fn,
		analytic: Analytic,
	})
	{
		super();
		this.setEventNamespace('BX.Rest.MarketExpired.Button');

		this.text = options.text;
		this.onSuccess = options.onSuccess;
	}

	render(): HTMLElement
	{
		return this.getButton().render();
	}

	getButtonConfig(): Object
	{
		return {};
	}

	onClick(): void
	{
		this.onSuccess?.();
	}

	getButton(): Button
	{
		this.button ??= new Button({
			...this.getButtonConfig(),
			className: 'rest-market-expired-popup__button',
			text: this.text,
			onclick: this.onClick.bind(this),
		});

		return this.button;
	}
}
