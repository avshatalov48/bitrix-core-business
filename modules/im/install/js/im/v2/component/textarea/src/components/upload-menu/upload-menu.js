import {MessengerMenu, MenuItem, MenuItemIcon} from 'im.v2.component.elements';

import {DocumentPanel} from './document-panel';
import {DiskPopup} from './disk-popup';

import '../../css/upload-menu.css';

import type {PopupOptions} from 'main.popup';

// @vue/component
export const UploadMenu = {
	components: {DocumentPanel, MessengerMenu, MenuItem, DiskPopup},
	emits: ['fileSelect', 'diskFileSelect'],
	data()
	{
		return {
			showMenu: false,
			showDiskPopup: false
		};
	},
	computed:
	{
		MenuItemIcon: () => MenuItemIcon,
		menuConfig(): PopupOptions
		{
			return {
				width: 278,
				bindElement: this.$refs['upload'] || {},
				bindOptions: {
					position: 'top'
				},
				offsetTop: 30,
				offsetLeft: -10,
				padding: 0,
			};
		}
	},
	methods:
	{
		onSelectFromComputerClick()
		{
			this.$refs['fileInput'].click();
			this.showMenu = false;
		},
		onSelectFromDiskClick()
		{
			this.showDiskPopup = true;
			this.showMenu = false;
		},
		onFileSelect(event)
		{
			this.$emit('fileSelect', event);
			this.showMenu = false;
		},
		onDiskFileSelect(event)
		{
			this.$emit('diskFileSelect', event);
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		}
	},
	template: `
		<div
			class="bx-im-textarea__icon --upload"
			:class="{'--active': showMenu}"
			:title="loc('IM_TEXTAREA_ICON_UPLOAD')"
			@click="showMenu = true"
			ref="upload"
		>
		</div>
		<MessengerMenu v-if="showMenu" :config="menuConfig" @close="showMenu = false" className="bx-im-file-menu__scope">
			<template #header>
				<DocumentPanel />
			</template>
			<MenuItem
				:icon="MenuItemIcon.upload"
				:title="loc('IM_TEXTAREA_SELECT_FROM_COMPUTER')"
				@click="onSelectFromComputerClick"
			/>
			<MenuItem
				:icon="MenuItemIcon.disk"
				:title="loc('IM_TEXTAREA_SELECT_FROM_BITRIX24_DISK')"
				@click="onSelectFromDiskClick"
			/>
			<input type="file" @change="onFileSelect" multiple class="bx-im-file-menu__file-input" ref="fileInput">
		</MessengerMenu>
		<DiskPopup v-if="showDiskPopup" @diskFileSelect="onDiskFileSelect" @close="showDiskPopup = false"/>
	`
};