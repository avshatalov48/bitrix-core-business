import { AnalyticsOptions, sendData } from 'ui.analytics';
import PopupType from './type/popup-type';
import PopupCategory from './type/popup-category';

export type AnalyticContext = {
	withDiscount: boolean,
	popupType: PopupType,
	popupCategory: PopupCategory,
}

export class Analytic
{
	constructor(context: AnalyticContext)
	{
		this.context = context;
	}

	sendShow(): void
	{
		this.#send({
			tool: 'infohelper',
			category: 'market',
			event: 'show_popup',
		});
	}

	sendClickButton(button: string): void
	{
		this.#send({
			tool: 'infohelper',
			category: 'market',
			event: 'click_button',
			c_element: button,
		});
	}

	sendDemoActivated(): void
	{
		this.#send({
			tool: 'intranet',
			category: 'demo',
			event: 'market_demo_activated',
		});
	}

	#send(options: AnalyticsOptions): void
	{
		sendData({
			...options,
			type: this.#getType(),
			p1: this.#getP1(),
		});
	}

	#getType(): string
	{
		let type = this.context.popupType === PopupType.WARNING
			? 'pre_disconnection_alert'
			: 'post_disconnection_notice';

		if (this.context.popupCategory === PopupCategory.TRIAL)
		{
			type = `${type}_demo`;
		}

		return type;
	}

	#getP1(): string
	{
		return `discount_${this.context.withDiscount ? 'Y' : 'N'}`;
	}
}
