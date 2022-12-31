import { Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import type { UploaderOptions, Uploader } from 'ui.uploader.core';
import { BitrixVue, VueCreateAppResult } from 'ui.vue3';

import VueUploaderAdapter from './vue-uploader-adapter';

/**
 * @memberof BX.UI.Uploader
 */
export default class VueUploaderWidget extends EventEmitter
{
	#vueAdapter: VueUploaderAdapter = null;
	#uploaderOptions: UploaderOptions = null;
	#widgetOptions = {};
	#vueApp = null;

	constructor(uploaderOptions: UploaderOptions, widgetOptions: {[key: string]: any} = {})
	{
		super();
		this.setEventNamespace('BX.UI.Uploader.Vue.Widget');

		this.#uploaderOptions = uploaderOptions;
		this.#widgetOptions = widgetOptions;
	}

	getRootComponent(): ?Function
	{
		return null;
	}

	getAdapter(): VueUploaderAdapter
	{
		if (this.#vueAdapter === null)
		{
			this.#vueAdapter = new VueUploaderAdapter(this.#uploaderOptions);
		}

		return this.#vueAdapter;
	}

	getUploader(): Uploader
	{
		return this.getAdapter().getUploader();
	}

	getVueApp(): VueCreateAppResult
	{
		if (this.#vueApp !== null)
		{
			return this.#vueApp;
		}

		this.#vueApp = BitrixVue.createApp(this.getRootComponent(), {
			uploaderOptions: this.#uploaderOptions,
			widgetOptions: this.#widgetOptions,
			uploaderAdapter: this.getAdapter(),
		});

		return this.#vueApp;
	}

	renderTo(node: HTMLElement): void
	{
		if (Type.isDomNode(node))
		{
			this.getVueApp().mount(node);
		}
	}
}
