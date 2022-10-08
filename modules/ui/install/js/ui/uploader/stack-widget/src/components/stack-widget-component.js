import { Text, Loc } from 'main.core';
import { Popup } from 'main.popup';
import { CloseButton } from 'ui.buttons';

import { BitrixVue } from 'ui.vue';
import { MountingPortal } from 'ui.vue.portal';

import { FileStatus, FileOrigin, UploaderStatus } from 'ui.uploader.core';
import { TileList, ErrorPopup, DragOverMixin } from 'ui.uploader.tile-widget';

import { StackUpload } from './stack-upload';
import { StackLoad } from './stack-load';
import { StackPreview } from './stack-preview';
import { StackDropArea } from './stack-drop-area';

import type { BaseEvent } from 'main.core.events';

const isItemLoading = item => item.status === FileStatus.LOADING;

export const StackWidgetComponent = BitrixVue.localComponent('ui.uploader.stack-widget', {
	components: {
		TileList,
		ErrorPopup,
		StackUpload,
		StackLoad,
		StackPreview,
		StackDropArea,
		MountingPortal,
	},
	mixins: [
		DragOverMixin,
	],
	props: {
		items: {
			type: Array,
			default: [],
		},
	},
	data()
	{
		return {
			popup: null,
			popupContentId: '',
			queueItems: [],
			enableAnimation: true,
			dragMode: false,
			uploaderError: null,
		}
	},
	computed: {
		containerClasses()
		{
			return [
				{
					'--multiple': this.$root.multiple,
					'--only-images': this.$root.acceptOnlyImages,
					'--many-items': this.items.length > 1,
				},
				`--${this.$root.widget.size}`
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
		errorsCount()
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
		errorPopupOptions()
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
		}
	},
	watch: {
		currentComponent(newValue, oldValue)
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
		items()
		{
			if (this.items.length === 0 && this.popup)
			{
				this.popup.close();
			}
		}
	},
	created()
	{
		this.$Bitrix.eventEmitter.subscribe('Uploader:onUploadStart', () => {
			this.items.forEach(item => {
				if (item.origin === FileOrigin.CLIENT && item.queued !== true)
				{
					item.queued = true;
					this.queueItems.push(item);
				}
			})
		});

		this.$Bitrix.eventEmitter.subscribe('Uploader:onUploadComplete', () => {
			this.queueItems = [];
		});

		this.$Bitrix.eventEmitter.subscribe('Item:onAdd', (event: BaseEvent) => {
			this.uploaderError = null;
			if (this.$root.getUploader().getStatus() === UploaderStatus.STARTED)
			{
				const item = event.getData().item;
				item.queued = true;
				this.queueItems.push(event.getData().item);
			}
		});

		this.$Bitrix.eventEmitter.subscribe('Item:onRemove', (event: BaseEvent) => {
			this.uploaderError = null;
			const item = event.getData().item;
			const position = this.queueItems.indexOf(item);
			if (position >= 0)
			{
				this.queueItems.splice(position, 1);
			}
		});

		this.$Bitrix.eventEmitter.subscribe('Uploader:onError', (event: BaseEvent) => {
			this.uploaderError = event.getData().error;
		});
	},
	mounted()
	{
		this.$root.getUploader().assignBrowse(this.$refs['add-button']);
	},
	methods: {
		showPopup()
		{
			if (!this.popup)
			{
				const id = 'stack-uploader-' + Text.getRandom().toLowerCase();
				const popup = new Popup({
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
						onDestroy: () => this.popup = null,
					},
					buttons: [
						new CloseButton({ onclick: () => this.popup.close() }),
					]
				});

				this.popupContentId = `#${id}`;
				this.popup = popup;
			}

			this.popup.show();
		},
		abortUpload()
		{
			const items = Array.from(this.queueItems);
			this.queueItems = [];

			items.forEach(item => {
				this.$root.getWidget().remove(item.id);
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
			<mounting-portal v-if="popup" :mount-to="popupContentId" append>
				<TileList :items="items" />
			</mounting-portal>
			<div class="ui-uploader-stack-item" ref="item">
				<transition name="ui-uploader-stack-item" mode="out-in" :css="enableAnimation">
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
			<div v-if="$root.multiple" ref="add-button" class="ui-uploader-stack-add-btn"></div>
			<ErrorPopup
				v-if="error !== null"
				:error="error"
				:popup-options="errorPopupOptions"
				@onDestroy="handlePopupDestroy"
			/>
		</div>
	`
});