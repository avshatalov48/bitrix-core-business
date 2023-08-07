import { Type } from 'main.core';
import { BaseEvent } from 'main.core.events';
import { VueUploaderComponent } from 'ui.uploader.vue';
import { TileWidgetSlot } from 'ui.uploader.tile-widget';

import { DropArea } from './drop-area';
import { TileList } from './tile-list';
import { ErrorPopup } from './error-popup';
import { DragOverMixin } from '../mixins/drag-over-mixin';

import type { UploaderFileInfo } from 'ui.uploader.core';
import type { BitrixVueComponentProps } from 'ui.vue3';
import type { PopupOptions } from 'main.popup';

/**
 * @memberof BX.UI.Uploader
 */
export const TileWidgetComponent: BitrixVueComponentProps = {
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
			isMounted: false,
			autoCollapse: false,
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
		TileWidgetSlot: () => TileWidgetSlot,
		slots(): TileWidgetSlot
		{
			const slots = Type.isPlainObject(this.widgetOptions.slots) ? this.widgetOptions.slots : {};

			return {
				[TileWidgetSlot.BEFORE_TILE_LIST]: slots[TileWidgetSlot.BEFORE_TILE_LIST],
				[TileWidgetSlot.AFTER_TILE_LIST]: slots[TileWidgetSlot.AFTER_TILE_LIST],
				[TileWidgetSlot.BEFORE_DROP_AREA]: slots[TileWidgetSlot.BEFORE_DROP_AREA],
				[TileWidgetSlot.AFTER_DROP_AREA]: slots[TileWidgetSlot.AFTER_DROP_AREA],
			}
		}
	},
	created(): void
	{
		this.autoCollapse =
			Type.isBoolean(this.widgetOptions.autoCollapse)
				? this.widgetOptions.autoCollapse
				: this.items.length > 0
		;

		// Current Items
		this.items.forEach(item => {
			item['tileWidgetData'] = {};
		});

		// New Items
		this.adapter.subscribe('Item:onBeforeAdd', (event: BaseEvent): void => {
			const item: UploaderFileInfo = event.getData().item;
			item['tileWidgetData'] = {};
		});

		this.adapter.subscribe('Item:onAdd', (event: BaseEvent): void => {
			this.uploaderError = null;
		});

		this.adapter.subscribe('Item:onRemove', (event: BaseEvent): void => {
			this.uploaderError = null;
		});
	},
	mounted(): void
	{
		this.uploader.assignDropzone(this.$refs.container);
		this.isMounted = true;
	},
	methods: {
		enableAutoCollapse(): void
		{
			this.autoCollapse = true;
		},

		disableAutoCollapse(): void
		{
			this.autoCollapse = false;
		},

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
			<component :is="slots[TileWidgetSlot.BEFORE_TILE_LIST]"></component>
			<TileList 
				v-if="items.length !== 0" 
				:items="items" 
				:auto-collapse="autoCollapse" 
				@onUnmount="this.autoCollapse = false"
			/>
			<component :is="slots[TileWidgetSlot.AFTER_TILE_LIST]"></component>
			<component :is="slots[TileWidgetSlot.BEFORE_DROP_AREA]"></component>
			<DropArea />
			<component :is="slots[TileWidgetSlot.AFTER_DROP_AREA]"></component>
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
