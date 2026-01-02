import { TileItem } from 'ui.uploader.tile-widget';
import { FileStatus } from 'ui.uploader.core';

import { BaseItem } from '../base-item';

import type { BitrixVueComponentProps } from 'ui.vue3';

// @vue/component
export const TileItemAdapter: BitrixVueComponentProps = {
	name: 'TileItemAdapter',

	components: {
		TileItem,
	},

	extends: BaseItem,

	provide: {
		uploader: {},
		widgetOptions: {
			showItemMenuButton: false,
		},
		emitter: {},
		adapter: {},
	},

	computed: {
		tileItem(): Object
		{
			return {
				name: this.file.name,
				extension: this.file.extension,
				isImage: this.file.isImage,
				previewUrl: this.file.isImage ? this.file.url : null,
				status: FileStatus.COMPLETE,
				customData: {},
			};
		},
	},

	// language=Vue
	template: `
		<TileItem :item="tileItem" :readonly="true" />
	`,
};
