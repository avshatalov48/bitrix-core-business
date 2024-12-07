import { Type } from 'main.core';
import { EventEmitter } from 'main.core.events';

import VueUploaderAdapter from './vue-uploader-adapter';

import type { BitrixVueComponentProps } from 'ui.vue3';

/**
 * @memberof BX.UI.Uploader
 */
export const VueUploaderComponent: BitrixVueComponentProps = {
	name: 'VueUploaderComponent',
	props: {
		uploaderOptions: {
			type: Object,
		},
		widgetOptions: {
			type: Object,
			default: {},
		},
		uploaderAdapter: {
			type: Object,
			default: null,
		},
	},
	data: (): Object<string, any> => ({
		items: [],
		uploaderError: null,
	}),
	provide(): Object<string, any> {
		return {
			uploader: this.uploader,
			adapter: this.adapter,
			widgetOptions: this.widgetOptions,
			emitter: this.emitter,
		};
	},
	beforeCreate(): void
	{
		if (this.uploaderAdapter === null)
		{
			this.hasOwnAdapter = true;

			const uploaderOptions = {
				...(Type.isPlainObject(this.customUploaderOptions) ? this.customUploaderOptions : {}),
				...this.uploaderOptions,
			};

			this.adapter = new VueUploaderAdapter(uploaderOptions);
		}
		else
		{
			this.hasOwnAdapter = false;
			this.adapter = this.uploaderAdapter;
		}

		this.uploader = this.adapter.getUploader();

		this.emitter = new EventEmitter(this, `BX.UI.Uploader.${this.$options.name}`);
		this.emitter.subscribeFromOptions(this.widgetOptions.events);
	},
	created(): void
	{
		this.items = this.adapter.getReactiveItems();
		this.uploaderError = this.adapter.getUploaderError();
	},
	unmounted(): void
	{
		if (this.hasOwnAdapter)
		{
			this.adapter.destroy();
			this.adapter = null;
			this.uploader = null;
		}
	},
};
