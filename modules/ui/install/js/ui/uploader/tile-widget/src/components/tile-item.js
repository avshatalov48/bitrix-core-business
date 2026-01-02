import { Dom, Loc, Text, Type } from 'main.core';
import { MenuManager, type PopupOptions } from 'main.popup';

import { FileOrigin, FileStatus } from 'ui.uploader.core';
import { TileWidgetSlot } from 'ui.uploader.tile-widget';
import { BIcon } from 'ui.icon-set.api.vue';
import { Actions, Outline } from 'ui.icon-set.api.core';
import 'ui.icon-set.actions';
import 'ui.icon-set.outline';
import type { BitrixVueComponentProps } from 'ui.vue3';

import { ErrorPopup } from './error-popup';
import { FileIconComponent } from './file-icon';
import { InsertIntoTextButton } from './insert-into-text-button';
import { UploadLoader } from './upload-loader';

// @vue/component
export const TileItem: BitrixVueComponentProps = {
	components: {
		BIcon,
		UploadLoader,
		ErrorPopup,
		FileIconComponent,
	},
	inject: ['uploader', 'adapter', 'widgetOptions', 'emitter'],
	props: {
		item: {
			type: Object,
			default: () => {},
		},
		readonly: {
			type: Boolean,
			default: false,
		},
		viewerGroupBy: {
			type: [String, null],
			default: null,
		},
		removeFromServer: {
			type: Boolean,
			default: true,
		},
		forceDisableSelection: {
			type: Boolean,
			default: false,
		},
	},
	setup(): Object
	{
		return {
			Actions,
			Outline,
			FileStatus,
			menuId: `ui-tile-uploader-item-menu-${Text.getRandom().toLowerCase()}`,
		};
	},
	data(): Object
	{
		return {
			showError: false,
			isMenuShown: false,
		};
	},
	computed: {
		status(): string
		{
			if (this.item.status === FileStatus.UPLOADING)
			{
				return `${this.item.progress}%`;
			}

			if (this.item.status === FileStatus.LOAD_FAILED || this.item.status === FileStatus.UPLOAD_FAILED)
			{
				return Loc.getMessage('TILE_UPLOADER_ERROR_STATUS');
			}

			return Loc.getMessage('TILE_UPLOADER_WAITING_STATUS');
		},
		fileSize(): string
		{
			if (
				[FileStatus.LOADING, FileStatus.LOAD_FAILED].includes(this.item.status)
				&& this.item.origin === FileOrigin.SERVER
			)
			{
				return '';
			}

			return this.item.sizeFormatted;
		},
		errorPopupOptions(): PopupOptions
		{
			const targetNode = this.$refs.container;
			const targetNodeWidth = targetNode.offsetWidth;

			return {
				bindElement: targetNode,
				darkMode: true,
				offsetTop: 6,
				minWidth: targetNodeWidth,
				maxWidth: 500,
			};
		},
		clampedFileName(): string
		{
			const nameParts = this.item.name.split('.');
			if (nameParts.length > 1)
			{
				nameParts.pop();
			}

			const nameWithoutExtension = nameParts.join('.');
			const maxLength = this.widgetOptions.compact ? 22 : 27;
			if (nameWithoutExtension.length > maxLength)
			{
				return `${nameWithoutExtension.slice(0, maxLength - 10)}...${nameWithoutExtension.slice(-5)}`;
			}

			return nameWithoutExtension;
		},
		showItemMenuButton(): boolean
		{
			if (Type.isBoolean(this.widgetOptions.showItemMenuButton))
			{
				return this.widgetOptions.showItemMenuButton;
			}

			return this.menuItems.length > 0;
		},
		menuItems(): Array
		{
			const items = [];
			items.push(
				{
					id: 'filesize',
					text: Loc.getMessage('TILE_UPLOADER_FILE_SIZE', { '#filesize#': this.item.sizeFormatted }),
					disabled: true,
				},
				{ delimiter: true },
			);

			if (this.widgetOptions.insertIntoText === true)
			{
				items.push({
					id: 'insert-into-text',
					text: Loc.getMessage('TILE_UPLOADER_INSERT_INTO_THE_TEXT'),
					onclick: (): void => {
						if (this.menu)
						{
							this.menu.close();
						}

						this.emitter.emit('onInsertIntoText', { item: this.item });
					},
				});
			}

			if (Type.isStringFilled(this.item.downloadUrl))
			{
				items.push(
					{
						id: 'download',
						text: Loc.getMessage('TILE_UPLOADER_MENU_DOWNLOAD'),
						href: this.item.downloadUrl,
						onclick: (): void => this.menu?.close(),
					},
				);
			}

			if (!this.readonly)
			{
				const removeItem = {
					id: 'remove',
					text: Loc.getMessage('TILE_UPLOADER_MENU_REMOVE'),
					onclick: this.remove,
				};

				items.push(removeItem);
			}

			return items;
		},
		extraAction(): ?BitrixVueComponentProps
		{
			return (
				this.widgetOptions.slots && this.widgetOptions.slots[TileWidgetSlot.ITEM_EXTRA_ACTION]
					? this.widgetOptions.slots[TileWidgetSlot.ITEM_EXTRA_ACTION]
					: (this.widgetOptions.insertIntoText === true ? InsertIntoTextButton : null)
			);
		},
		isSelected(): boolean
		{
			if (this.forceDisableSelection)
			{
				return false;
			}

			return this.item.customData.tileSelected === true;
		},
		fileIconSize(): number
		{
			return this.widgetOptions.compact ? 24 : 36;
		},
		viewerAttrs(): Object
		{
			const { viewerAttrs, previewUrl } = this.item;

			if (!viewerAttrs)
			{
				return {};
			}

			const params = {};

			for (const [key, value] of Object.entries(viewerAttrs))
			{
				params[`data-${Text.toKebabCase(key)}`] = value;
			}

			params['data-viewer'] = true;

			if (previewUrl)
			{
				params['data-viewer-preview'] = previewUrl;
			}

			if (this.viewerGroupBy && Type.isUndefined(viewerAttrs.viewerSeparateItem))
			{
				params['data-viewer-group-by'] = this.viewerGroupBy;
			}

			return params;
		},
	},
	created(): void
	{
		this.menu = null;
	},
	beforeUnmount(): void
	{
		if (this.menu)
		{
			this.menu.destroy();
			this.menu = null;
		}
	},
	methods: {
		remove(): void
		{
			if (this.readonly)
			{
				return;
			}

			this.uploader.removeFile(this.item.id, { removeFromServer: this.removeFromServer });
		},

		handleMouseEnter(item): void
		{
			if (item.error)
			{
				this.showError = true;
			}
		},
		handleMouseLeave(): void
		{
			this.showError = false;
		},
		toggleMenu(): void
		{
			setTimeout(() => {
				if (this.menu)
				{
					if (this.menu.getPopupWindow().isShown())
					{
						this.menu.close();

						return;
					}

					this.menu.destroy();
				}

				this.menu = MenuManager.create({
					id: this.menuId,
					bindElement: this.$refs.menu.$el,
					targetContainer: document.body,
					angle: true,
					offsetLeft: 13,
					minWidth: 100,
					cacheable: false,
					items: this.menuItems,
					bindOptions: {
						forceBindPosition: true,
					},
					events: {
						onShow: (): void => {
							this.isMenuShown = true;
							this.adapter.getItem(this.item.id).isMenuShown = true;
						},
						onClose: (): void => {
							this.isMenuShown = false;
							this.adapter.getItem(this.item.id).isMenuShown = false;
						},
						onDestroy: (): void => {
							this.menu = null;
						},
					},
				});

				const downloadItem = this.menu.getMenuItem('download');
				if (downloadItem)
				{
					Dom.attr(downloadItem.getContainer(), 'download', true);
				}

				this.emitter.emit('TileItem:onMenuCreate', {
					menu: this.menu,
					item: this.item,
				});

				this.menu.show();
			});
		},
	},
	template: `
		<div
			class="ui-tile-uploader-item"
			:class="[
				'ui-tile-uploader-item--' + item.status,
				{
					'--image': item.isImage,
					'--selected': (isMenuShown && widgetOptions.compact) || isSelected,
				},
			]"
			ref="container"
		>
			<ErrorPopup v-if="item.error && showError" :error="item.error" :popup-options="errorPopupOptions"/>
			<div
				class="ui-tile-uploader-item-content"
				@mouseenter="handleMouseEnter(item)"
				@mouseleave="handleMouseLeave"
			>
				<div v-if="item.status !== FileStatus.COMPLETE" class="ui-tile-uploader-item-state">
					<div class="ui-tile-uploader-item-loader" v-if="item.status === FileStatus.UPLOADING">
						<UploadLoader :progress="item.progress" :width="20" colorTrack="#73d8f8" colorBar="#fff"/>
					</div>
					<div v-else class="ui-tile-uploader-item-state-icon"></div>
					<div class="ui-tile-uploader-item-status">
						<div class="ui-tile-uploader-item-status-name">{{status}}</div>
						<div v-if="fileSize" class="ui-tile-uploader-item-state-desc">{{fileSize}}</div>
					</div>
					<div v-if="!readonly" class="ui-tile-uploader-item-state-remove" @click="remove" key="aaa"></div>
				</div>
				<template v-else>
					<div v-if="!readonly" class="ui-tile-uploader-item-remove" key="remove" @click="remove">
						<BIcon :name="Outline.CROSS_L"/>
					</div>
					<div class="ui-tile-uploader-item-actions" key="actions">
						<div class="ui-tile-uploader-item-actions-pad">
							<div v-if="extraAction" class="ui-tile-uploader-item-extra-actions">
								<component :is="extraAction" :item="item"></component>
							</div>
							<BIcon
								v-if="showItemMenuButton"
								class="ui-tile-uploader-item-menu"
								:name="Actions.MORE"
								ref="menu"
								@click="toggleMenu"
							/>
						</div>
					</div>
				</template>
				<div class="ui-tile-uploader-item-preview-content" v-bind="viewerAttrs">
					<div class="ui-tile-uploader-item-preview">
						<div
							v-if="item.previewUrl"
							class="ui-tile-uploader-item-image"
							:class="{ 'ui-tile-uploader-item-image-default': item.previewUrl === null }"
							:style="{ backgroundImage: item.previewUrl !== null ? 'url(' + item.previewUrl + ')' : '' }">
						</div>
						<FileIconComponent v-else :name="item.extension || '...'" :size="fileIconSize"/>
					</div>
					<div
						v-if="item.name"
						class="ui-tile-uploader-item-name-box"
						:title="item.name"
					>
						<div class="ui-tile-uploader-item-name">
							<span class="ui-tile-uploader-item-name-title">{{clampedFileName}}</span>
							<span v-if="item.extension" class="ui-tile-uploader-item-name-extension">.{{item.extension}}</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	`,
};
