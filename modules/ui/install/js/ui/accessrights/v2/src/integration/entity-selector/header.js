import { Tag } from 'main.core';
import { BaseHeader } from 'ui.entity-selector';
import { BitrixVue } from 'ui.vue3';
import { Locator } from '../../components/section/value/popup-header/locator';

export class Header extends BaseHeader
{
	render(): HTMLElement
	{
		return this.#renderVueApp();
	}

	#renderVueApp(): HTMLElement
	{
		const container = Tag.render`<div style="padding: 20px 20px 0;"></div>`;

		const app = BitrixVue.createApp(Locator, {
			maxWidth: this.getDialog().getWidth(),
		});
		app.provide('section', this.getOption('section'));
		app.provide('right', this.getOption('right'));

		app.mount(container);

		return container;
	}
}
