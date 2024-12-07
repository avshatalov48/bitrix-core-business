import { Dom, Runtime, Tag } from 'main.core';
import { InventoryCardBox } from './card-box';
import { BitrixVue } from 'ui.vue3';

import './style.css';

export class EnableWizard
{
	#params: Object;
	#analytics: Object;

	#template: HTMLElement;

	constructor(params, analytics)
	{
		this.#params = params;
		this.#analytics = analytics;
	}

	getCardTemplate(): HTMLElement
	{
		const app = this;

		if (!this.#template)
		{
			this.#template = Tag.render`<div id="inventory-management-card-wrap"></div>`;

			BitrixVue.createApp(
				{
					...InventoryCardBox,
					beforeCreate()
					{
						this.$bitrix.Application.set(app);
					},
				},
				this.#params,
			).mount(this.#template);
		}

		return this.#template;
	}

	render(node: HTMLElement): void
	{
		Dom.append(this.getCardTemplate(), node);
	}

	sendOpenedEvent()
	{
		this.#sendEvent({
			...this.#analytics,
			event: 'opened',
		});
	}

	sendStep2ProceededEvent(mode: string)
	{
		this.#sendEvent({
			...this.#analytics,
			event: 'step2_proceeded',
			p2: `choose_${mode}`,
		});
	}

	sendEnableProceededEvent(mode: string)
	{
		this.#sendEvent({
			...this.#analytics,
			event: 'enable_proceeded',
			p2: `choose_${mode}`,
		});
	}

	sendEnableDoneEvent(mode: string, status: string)
	{
		this.#sendEvent({
			...this.#analytics,
			event: 'enable_done',
			status,
			p2: `choose_${mode}`,
		});
	}

	#sendEvent(data: Object)
	{
		Runtime.loadExtension('ui.analytics')
			.then((exports) => {
				const { sendData } = exports;

				sendData(data);
			});
	}
}
