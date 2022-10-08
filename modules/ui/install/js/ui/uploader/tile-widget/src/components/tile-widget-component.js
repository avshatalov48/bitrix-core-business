import { BitrixVue } from 'ui.vue';
import { DropArea } from './drop-area';
import { TileList } from './tile-list';
import { ErrorPopup } from './error-popup';
import { DragOverMixin } from '../mixins/drag-over-mixin';
import { BaseEvent } from 'main.core.events';
import { UploaderStatus } from 'ui.uploader.core';

export const TileWidgetComponent = BitrixVue.localComponent('tile-uploader', {
	components: {
		DropArea,
		TileList,
		ErrorPopup,
	},
	mixins: [
		DragOverMixin,
	],
	props: {
		items: {
			type: Array,
			default: [],
		}
	},
	data()
	{
		return {
			uploaderError: null,
		}
	},
	computed: {
		errorPopupOptions()
		{
			return {
				bindElement: this.$refs.container,
				closeIcon: true,
				padding: 20,
				offsetLeft: 45,
				angle: true,
				darkMode: true,
				bindOptions: {
					position: 'top',
					forceTop: true,
				},
			};
		},
	},
	created()
	{
		this.$Bitrix.eventEmitter.subscribe('Uploader:onError', (event: BaseEvent) => {
			this.uploaderError = event.getData().error;
		});

		this.$Bitrix.eventEmitter.subscribe('Item:onAdd', (event: BaseEvent) => {
			this.uploaderError = null;
		});

		this.$Bitrix.eventEmitter.subscribe('Item:onRemove', (event: BaseEvent) => {
			this.uploaderError = null;
		});
	},
	mounted()
	{
		this.$root.getUploader().assignDropzone(this.$refs.container);
	},
	// language=Vue
	template: `
		<div class="ui-tile-uploader" ref="container" v-drop>
			<ErrorPopup 
				v-if="uploaderError"
				:alignArrow="false"
				:error="uploaderError"
				:popup-options="errorPopupOptions" 
				@onDestroy="uploaderError = null" 
			/>
			<template v-if="items.length === 0">
				<DropArea />
			</template>
			<template v-else>
				<TileList :items="items"></TileList>
				<DropArea />
			</template>
		</div>
	`
});
