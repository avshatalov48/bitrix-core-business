import { Text, Loc } from 'main.core';
import { Popup, PopupOptions } from 'main.popup';
import { CloseButton } from 'ui.buttons';

import { FileStatus, FileOrigin, UploaderStatus } from 'ui.uploader.core';
import { VueUploaderComponent } from 'ui.uploader.vue';
import { TileList, ErrorPopup, DragOverMixin } from 'ui.uploader.tile-widget';

import { StackUpload } from './stack-upload';
import { StackLoad } from './stack-load';
import { StackPreview } from './stack-preview';
import { StackDropArea } from './stack-drop-area';

import type { BaseEvent } from 'main.core.events';

const isItemLoading = item => item.status === FileStatus.LOADING;

/**
 * @memberof BX.UI.Uploader
 */
export const StackWidgetComponent = {
	name: 'StackWidget',
	extends: VueUploaderComponent,
	components: {
		TileList,
		ErrorPopup,
		StackUpload,
		StackLoad,
		StackPreview,
		StackDropArea,
	},
	mixins: [
		DragOverMixin,
	],
	data: () => ({
		popupContentId: null,
		queueItems: [],
		enableAnimation: true,
		dragMode: false,
		isMounted: false,
	}),
	computed: {
		containerClasses(): Array
		{
			return [
				{
					'--multiple': this.uploader.isMultiple(),
					'--only-images': this.uploader.shouldAcceptOnlyImages(),
					'--many-items': this.items.length > 1,
				},
				`--${this.widgetOptions.size}`
			];
		},
		currentComponent()
		{
			if (this.items.length === 0 || this.dragOver)
			{
				if (this.dragOver)
				{
					this.dragMode = true;
				}

				return StackDropArea;
			}

			if (this.queueItems.length > 0)
			{
				return StackUpload;
			}

			if (this.items.some(isItemLoading))
			{
				return StackLoad;
			}

			return StackPreview;
		},
		currentComponentProps()
		{
			if (this.currentComponent === StackDropArea || this.currentComponent === StackLoad)
			{
				return {};
			}
			else if (this.currentComponent === StackUpload)
			{
				return {
					items: this.items,
					queueItems: this.queueItems,
				};
			}
			else if (this.currentComponent === StackPreview)
			{
				return {
					items: this.items,
				}
			}
		},
		error()
		{
			if (this.uploaderError)
			{
				return this.uploaderError;
			}
			else if (this.errorsCount > 0)
			{
				return Loc.getMessage('STACK_WIDGET_FILE_UPLOAD_ERROR');
			}

			return null;
		},
		errorsCount(): number
		{
			return this.items.reduce((errors, item) => {
				if (item.status === FileStatus.LOAD_FAILED || item.status === FileStatus.UPLOAD_FAILED)
				{
					return errors + 1;
				}
				else
				{
					return errors;
				}
			}, 0);
		},
		errorPopupOptions(): PopupOptions
		{
			return {
				bindElement: this.$refs.item,
				bindOptions: {
					position: 'top',
				},
				darkMode: true,
				offsetTop: 3,
				background: '#d2000d',
				contentBackground: 'transparent',
				contentColor: 'white',
				padding: this.uploaderError === null ? 10 : 20,
				closeIcon: this.uploaderError !== null,
			};
		},
	},
	watch: {
		currentComponent(newValue, oldValue): void
		{
			if (this.dragOver)
			{
				this.enableAnimation = false;
			}
			else if (oldValue === StackDropArea && this.dragMode)
			{
				this.enableAnimation = false;
			}
			else if (oldValue === StackPreview)
			{
				this.enableAnimation = false;
			}
			else
			{
				this.dragMode = false;
				this.enableAnimation = true;
			}
		},
		items: {
			handler() {
				if (this.items.length === 0 && this.popup)
				{
					this.popup.close();
				}
			},
			deep: true,
		}
	},
	created()
	{
		this.popup = null;

		this.adapter.subscribe('Uploader:onUploadStart', () => {
			this.items.forEach(item => {
				if (item.origin === FileOrigin.CLIENT && item.queued !== true)
				{
					item.queued = true;
					this.queueItems.push(item);
				}
			})
		});

		this.adapter.subscribe('Uploader:onUploadComplete', () => {
			this.queueItems = [];
		});

		this.adapter.subscribe('Item:onAdd', (event: BaseEvent) => {
			this.uploaderError = null;
			if (this.uploader.getStatus() === UploaderStatus.STARTED)
			{
				const item = event.getData().item;
				item.queued = true;
				this.queueItems.push(event.getData().item);
			}
		});

		this.adapter.subscribe('Item:onRemove', (event: BaseEvent) => {
			this.uploaderError = null;
			const item = event.getData().item;
			const position = this.queueItems.indexOf(item);
			if (position >= 0)
			{
				this.queueItems.splice(position, 1);
			}
		});
	},
	mounted()
	{
		this.uploader.assignBrowse(this.$refs['add-button']);
		this.isMounted = true;
	},
	methods: {
		showPopup()
		{
			if (!this.popup)
			{
				const id = 'stack-uploader-' + Text.getRandom().toLowerCase();
				this.popup = new Popup({
					width: 750,
					height: 400,
					draggable: true,
					titleBar: Loc.getMessage('STACK_WIDGET_POPUP_TITLE'),
					content: `<div id="${id}"></div>`,
					cacheable: false,
					closeIcon: true,
					closeByEsc: true,
					resizable: true,
					minWidth: 450,
					minHeight: 300,
					events: {
						onDestroy: () => {
							this.popup = null;
							this.popupContentId = null;
						},
					},
					buttons: [
						new CloseButton({ onclick: () => this.popup.close() }),
					]
				});

				this.popupContentId = `#${id}`;
			}

			this.popup.show();
		},
		abortUpload()
		{
			const items = Array.from(this.queueItems);
			this.queueItems = [];

			items.forEach(item => {
				this.uploader.removeFile(item.id);
			});
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
		<div class="ui-uploader-stack-widget" :class="containerClasses" v-drop>
			<Teleport v-if="popupContentId !== null" :to="popupContentId">
				<TileList :items="items" />
			</Teleport>
			<div class="ui-uploader-stack-item" ref="item">
				<transition
					:leave-active-class="enableAnimation ? 'ui-uploader-stack-item-leave-active' : ''" 
					:leave-to-class="enableAnimation ? 'ui-uploader-stack-item-leave-to' : ''" 
					mode="out-in"
				>
					<keep-alive>
						<component
							:is="currentComponent"
							v-bind="currentComponentProps"
							@showPopup="showPopup"
							@abortUpload="abortUpload"
						/>
					</keep-alive>
				</transition>
			</div>
			<div v-if="uploader.isMultiple()" ref="add-button" class="ui-uploader-stack-add-btn"></div>
		</div>
		<ErrorPopup
			v-if="error !== null && isMounted"
			:error="error"
			:popup-options="errorPopupOptions"
			@onDestroy="handlePopupDestroy"
		/>
	`
};