import { MenuManager } from 'main.popup';
import { Text, Loc, Type } from 'main.core';
import { FileOrigin, FileStatus } from 'ui.uploader.core';

import { UploadLoader } from './upload-loader';
import { ErrorPopup } from './error-popup';
import { FileIconComponent } from './file-icon';

export const TileItem = {
	components: {
		UploadLoader,
		ErrorPopup,
		FileIconComponent,
	},
	inject: ['uploader'],
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
		FileStatus: () => FileStatus,
		status()
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
		fileSize()
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
		errorPopupOptions()
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
		clampedFileName()
		{
			const nameParts = this.item.originalName.split('.');
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
		menuItems()
		{
			const items = [];

			if (Type.isStringFilled(this.item.downloadUrl))
			{
				items.push({
					text: Loc.getMessage('TILE_UPLOADER_MENU_DOWNLOAD'),
					href: this.item.downloadUrl,
				});

				items.push({
					text: Loc.getMessage('TILE_UPLOADER_MENU_REMOVE'),
					onclick: () => {
						this.remove();
					},
				});
			}

			return items;
		}
	},
	created()
	{
		this.menu = null;
	},
	beforeUnmount()
	{
		if (this.menu)
		{
			this.menu.destroy();
			this.menu = null;
		}
	},
	methods: {
		remove()
		{
			this.uploader.removeFile(this.item.id);
		},

		handleMouseEnter(item)
		{
			if (item.error)
			{
				this.showError = true;
			}
		},

		handleMouseLeave()
		{
			this.showError = false;
		},


		showMenu()
		{
			if (this.menu)
			{
				this.menu.destroy();
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

			this.menu.show();
		},
	},
	// language=Vue
	template: `
	<transition name="ui-tile-uploader-item">
		<div
			class="ui-tile-uploader-item"
			:class="['ui-tile-uploader-item--' + item.status, { '--image': item.isImage } ]"
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
						<div v-if="menuItems.length" class="ui-tile-uploader-item-menu" @click="showMenu" ref="menu"></div>
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
						v-else-if="item.name && item.status !== FileStatus.LOADING" 
						class="ui-tile-uploader-item-file-icon"
					>
						<FileIconComponent :name="item.extension" />
					</div>
					<div 
						v-else 
						class="ui-tile-uploader-item-file-default"
					>
						<FileIconComponent :name="item.extension ? item.extension : '...'" :size="36" />
					</div>
				</div>
				<div v-if="item.originalName" class="ui-tile-uploader-item-name-box" :title="item.originalName">
					<div class="ui-tile-uploader-item-name">
						<span class="ui-tile-uploader-item-name-title">{{clampedFileName}}</span><!--
						--><span v-if="item.extension" class="ui-tile-uploader-item-name-extension">.{{item.extension}}</span>
					</div>
				</div>
			</div>
		</div>
	</transition>
	`
};
