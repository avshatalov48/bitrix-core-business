import { PopupManager } from 'main.popup';

import { PopupType } from 'im.v2.const';
import { DesktopApi, type DesktopAccount } from 'im.v2.lib.desktop-api';

import { DesktopItemContextMenu } from '../../../classes/desktop-item-context-menu';
import { DesktopAccountItem } from './desktop-account-item';

import '../../../css/desktop-account-list/desktop-account-list.css';

import type { JsonObject } from 'main.core';

// @vue/component
export const DesktopAccountList = {
	name: 'DesktopAccountList',
	components: { DesktopAccountItem },
	emits: ['openContextMenu'],
	data(): JsonObject
	{
		return {
			accounts: [],
		};
	},
	computed:
	{
		isEmptyState(): boolean
		{
			return this.accounts.length === 0;
		},
	},
	created()
	{
		this.contextMenu = new DesktopItemContextMenu();
		this.accounts = DesktopApi.getAccountList();
	},
	beforeUnmount()
	{
		this.contextMenu.destroy();
	},
	methods:
	{
		openLoginTab()
		{
			this.contextMenu.destroy();
			PopupManager.getPopupById(PopupType.userProfile)?.close();
			DesktopApi.openAddAccountTab();
		},
		onContextMenuClick(event: { account: DesktopAccount, target: HTMLElement })
		{
			const { account, target } = event;
			this.contextMenu.openMenu(account, target);
			this.$emit('openContextMenu');
		},
	},
	template: `
		<div class="bx-im-desktop-connection-list__container bx-im-desktop-connection-list__scope">
			<div class="bx-im-desktop-connection-list__header">
				<span class="bx-im-desktop-connection-list__title">
					{{ $Bitrix.Loc.getMessage('IM_USER_SETTINGS_CONNECTED_BITRIX24') }}
				</span>
				<span class="bx-im-desktop-connection-list__add" @click="openLoginTab">
					{{ $Bitrix.Loc.getMessage('IM_USER_SETTINGS_CONNECT_BITRIX24') }}
				</span>
			</div>
			<div class="bx-im-desktop-connection-list__items">
				<DesktopAccountItem 
					v-for="account in accounts" 
					:account="account" 
					@contextMenuClick="onContextMenuClick"
				/>
			</div>
		</div>
	`,
};
