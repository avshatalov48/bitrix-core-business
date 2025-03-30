import { AnalyticsOptions, sendData } from 'ui.analytics';

export type AnalyticContext = {
	withDiscount: boolean,
	popupType: 'WARNING' | 'FINAL',
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
			event: 'demo_activated',
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
		return this.context.popupType === 'WARNING'
			? 'pre_disconnection_alert'
			: 'post_disconnection_notice';
	}

	#getP1(): string
	{
		return `discount_${this.context.withDiscount ? 'Y' : 'N'}`;
	}
}
