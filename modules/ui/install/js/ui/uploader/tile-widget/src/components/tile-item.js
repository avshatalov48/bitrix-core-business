import { PopupOptions, MenuManager } from 'main.popup';
import { Text, Loc, Type } from 'main.core';
import { FileOrigin, FileStatus, FileStatusType } from 'ui.uploader.core';

import { UploadLoader } from './upload-loader';
import { ErrorPopup } from './error-popup';
import { FileIconComponent } from './file-icon';

import type { BitrixVueComponentProps } from 'ui.vue3';
import { TileWidgetSlot } from 'ui.uploader.tile-widget';

export const TileItem: BitrixVueComponentProps = {
	components: {
		UploadLoader,
		ErrorPopup,
		FileIconComponent,
	},
	inject: ['uploader', 'widgetOptions', 'emitter'],
	props: {
		item: {
			type: Object,
			default: {}
		},
	},
	data()
	{
		return {
			tileId: 'tile-uploader-' + Text.getRandom().toLowerCase(),
			showError: false,
		};
	},
	computed: {
		FileStatus: (): FileStatusType => FileStatus,
		status(): string
		{
			if (this.item.status === FileStatus.UPLOADING)
			{
				return this.item.progress + '%';
			}
			else if (this.item.status === FileStatus.LOAD_FAILED || this.item.status === FileStatus.UPLOAD_FAILED)
			{
				return Loc.getMessage('TILE_UPLOADER_ERROR_STATUS');
			}
			else
			{
				return Loc.getMessage('TILE_UPLOADER_WAITING_STATUS');
			}
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
			if (nameWithoutExtension.length > 27)
			{
				return nameWithoutExtension.substr(0, 17) + '...' + nameWithoutExtension.substr(-5);
			}

			return nameWithoutExtension;
		},

		showItemMenuButton(): boolean
		{
			if (Type.isBoolean(this.widgetOptions.showItemMenuButton))
			{
				return this.widgetOptions.showItemMenuButton;
			}
			else
			{
				return this.menuItems.length > 0;
			}
		},

		menuItems(): Array
		{
			const items = [];

			if (Type.isStringFilled(this.item.downloadUrl))
			{
				items.push({
					id: 'download',
					text: Loc.getMessage('TILE_UPLOADER_MENU_DOWNLOAD'),
					href: this.item.downloadUrl,
					onclick: (): void => {
						if (this.menu)
						{
							this.menu.close();
						}
					}
				});

				items.push({
					id: 'remove',
					text: Loc.getMessage('TILE_UPLOADER_MENU_REMOVE'),
					onclick: (): void => {
						this.remove();
					},
				});
			}

			return items;
		},
		extraAction(): ?BitrixVueComponentProps
		{
			return (
				this.widgetOptions.slots && this.widgetOptions.slots[TileWidgetSlot.ITEM_EXTRA_ACTION]
				? this.widgetOptions.slots[TileWidgetSlot.ITEM_EXTRA_ACTION]
				: null
			);
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
			this.uploader.removeFile(this.item.id);
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
			if (this.menu)
			{
				if (this.menu.getPopupWindow().isShown())
				{
					this.menu.close();

					return;
				}
				else
				{
					this.menu.destroy();
				}
			}

			this.menu = MenuManager.create({
				id: this.tileId,
				bindElement: this.$refs.menu,
				angle: true,
				offsetLeft: 13,
				minWidth: 100,
				cacheable: false,
				items: this.menuItems,
				events: {
					onDestroy: () => this.menu = null,
				},
			});

			this.emitter.emit('TileItem:onMenuCreate', { menu: this.menu, item: this.item })

			this.menu.show();
		},
	},
	// language=Vue
	template: `
	<div
		class="ui-tile-uploader-item"
		:class="['ui-tile-uploader-item--' + item.status, { '--image': item.isImage, '--selected': item.tileWidgetData?.selected } ]"
		ref="container"
	>
		<ErrorPopup v-if="item.error && showError" :error="item.error" :popup-options="errorPopupOptions"/>
		<div 
			class="ui-tile-uploader-item-content"
			@mouseenter="handleMouseEnter(item)" 
			@mouseleave="handleMouseLeave(item)"
		>
			<div v-if="item.status !== FileStatus.COMPLETE" class="ui-tile-uploader-item-state">
				<div class="ui-tile-uploader-item-loader" v-if="item.status === FileStatus.UPLOADING">
					<UploadLoader :progress="item.progress" :width="20" colorTrack="#73d8f8" colorBar="#fff" />
				</div>
				<div v-else class="ui-tile-uploader-item-state-icon"></div>
				<div class="ui-tile-uploader-item-status">
					<div class="ui-tile-uploader-item-status-name">{{status}}</div>
					<div v-if="fileSize" class="ui-tile-uploader-item-state-desc">{{fileSize}}</div>
				</div>
				<div class="ui-tile-uploader-item-state-remove" @click="remove" key="aaa"></div>
			</div>
			<template v-else>
				<div class="ui-tile-uploader-item-remove" @click="remove" key="remove"></div>
				<div class="ui-tile-uploader-item-actions" key="actions">
					<div class="ui-tile-uploader-item-actions-pad">
						<div v-if="extraAction" class="ui-tile-uploader-item-extra-actions">
							<component :is="extraAction" :item="this.item"></component>
						</div>
						<div v-if="showItemMenuButton" class="ui-tile-uploader-item-menu" @click="toggleMenu" ref="menu"></div>
					</div>
				</div>
			</template>
			<div class="ui-tile-uploader-item-preview">
				<div
					v-if="item.previewUrl"
					class="ui-tile-uploader-item-image"
					:class="{ 'ui-tile-uploader-item-image-default': item.previewUrl === null }"
					:style="{ backgroundImage: item.previewUrl !== null ? 'url(' + item.previewUrl + ')' : '' }">
				</div>
				<div 
					v-else-if="item.name" 
					class="ui-tile-uploader-item-file-icon"
				>
					<FileIconComponent :name="item.extension ? item.extension : '...'" />
				</div>
				<div 
					v-else 
					class="ui-tile-uploader-item-file-default"
				>
					<FileIconComponent :name="item.extension ? item.extension : '...'" :size="36" />
				</div>
			</div>
			<div v-if="item.name" class="ui-tile-uploader-item-name-box" :title="item.name">
				<div class="ui-tile-uploader-item-name">
					<span class="ui-tile-uploader-item-name-title">{{clampedFileName}}</span><!--
					--><span v-if="item.extension" class="ui-tile-uploader-item-name-extension">.{{item.extension}}</span>
				</div>
			</div>
		</div>
	</div>
	`
};
