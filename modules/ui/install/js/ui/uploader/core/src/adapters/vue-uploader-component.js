import { Type } from 'main.core';
import VueUploaderAdapter from './vue-uploader-adapter';

/**
 * @memberof BX.UI.Uploader
 */
export const VueUploaderComponent = {
	name: 'VueUploaderComponent',
	props: {
		uploaderOptions: {
			type: Object
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
	data: () => ({
		items: [],
		uploaderError: null,
	}),
	provide() {
		return {
			uploader: this.uploader,
			adapter: this.adapter,
			widgetOptions: this.widgetOptions,
		}
	},
	beforeCreate()
	{
		this.adapter = this.uploaderAdapter === null ? new VueUploaderAdapter(this.uploaderOptions) : this.uploaderAdapter;
		this.uploader = this.adapter.getUploader();
	},
	created()
	{
		this.items = this.adapter.getItems();
		this.uploaderError = this.adapter.getUploaderError();
	},
	unmounted()
	{
		this.uploader.destroy();
	}
}