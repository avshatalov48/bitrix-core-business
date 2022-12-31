import { DropArea } from './drop-area';
import { TileList } from './tile-list';
import { ErrorPopup } from './error-popup';
import { DragOverMixin } from '../mixins/drag-over-mixin';
import { BaseEvent } from 'main.core.events';
import type { PopupOptions } from 'main.popup';
import { VueUploaderComponent } from 'ui.uploader.core';

/**
 * @memberof BX.UI.Uploader
 */
export const TileWidgetComponent = {
	name: 'TileWidget',
	extends: VueUploaderComponent,
	components: {
		DropArea,
		TileList,
		ErrorPopup,
	},
	mixins: [
		DragOverMixin,
	],
	data() {
		return {
			isMounted: false
		}
	},
	computed: {
		errorPopupOptions(): PopupOptions
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
		this.adapter.subscribe('Item:onAdd', (event: BaseEvent) => {
			this.uploaderError = null;
		});

		this.adapter.subscribe('Item:onRemove', (event: BaseEvent) => {
			this.uploaderError = null;
		});
	},
	mounted()
	{
		this.uploader.assignDropzone(this.$refs.container);
		this.isMounted = true;
	},
	methods: {
		handlePopupDestroy(error)
		{
			if (this.uploaderError === error)
			{
				this.uploaderError = null;
			}
		}
	},
	// language=Vue
	template: `
		<div class="ui-tile-uploader" ref="container" v-drop>
			<template v-if="items.length === 0">
				<DropArea />
			</template>
			<template v-else>
				<TileList :items="items"></TileList>
				<DropArea />
			</template>
		</div>
		<ErrorPopup
			v-if="uploaderError && isMounted"
			:alignArrow="false"
			:error="uploaderError"
			:popup-options="errorPopupOptions"
			@onDestroy="handlePopupDestroy"
		/>
	`
};
