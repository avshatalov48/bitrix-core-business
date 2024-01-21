import { DesktopManager } from 'im.v2.lib.desktop';

import '../../../css/desktop-account-list/desktop-account-item.css';

import type { DesktopAccount } from 'im.v2.lib.desktop-api';
import type { JsonObject } from 'main.core';

// @vue/component
export const DesktopAccountItem = {
	name: 'DesktopAccountItem',
	props:
	{
		account: {
			type: Object,
			required: true,
		},
	},
	emits: ['contextMenuClick'],
	data(): JsonObject
	{
		return {
			errorLoadAvatar: false,
		};
	},
	computed:
	{
		accountItem(): DesktopAccount
		{
			return this.account;
		},
		avatarUrl(): string
		{
			if (this.errorLoadAvatar || !this.hasAvatar)
			{
				return '';
			}

			if (this.accountItem.avatar.startsWith('http'))
			{
				return this.accountItem.avatar;
			}

			return `${this.accountItem.protocol}://${this.accountItem.host}${this.accountItem.avatar}`;
		},
		isConnected(): boolean
		{
			return this.accountItem.connected;
		},
		hasAvatar(): boolean
		{
			return this.accountItem.avatar && this.accountItem.avatar !== '/bitrix/js/im/images/blank.gif';
		},
	},
	methods:
	{
		onContextMenuClick(event)
		{
			this.$emit('contextMenuClick', {
				account: this.account,
				target: event.target,
			});
		},
		onDomainClick()
		{
			if (!this.isConnected)
			{
				return;
			}

			DesktopManager.getInstance().openAccountTab(this.accountItem.portal);
		},
		onError()
		{
			this.errorLoadAvatar = true;
		},
	},
	template: `
		<div class="bx-im-desktop-connection-list-item__container bx-im-desktop-connection-list-item__scope">
			<div class="bx-im-desktop-connection-list-item__content" :class="{'--disconnected': !isConnected}">
				<img 
					v-if="avatarUrl" 
					:src="avatarUrl"
					:alt="accountItem.portal"
					@error="onError"
					class="bx-im-desktop-connection-list-item__avatar" 
				/>
				<span v-else class="bx-im-desktop-connection-list-item__avatar-default"></span>
				<div class="bx-im-desktop-connection-list-item__title-container">
					<span class="bx-im-desktop-connection-list-item__title" @click="onDomainClick">
						{{ accountItem.portal }}
					</span>
					<span class="bx-im-desktop-connection-list-item__login">{{ accountItem.login }}</span>
				</div>
			</div>
			<button
				class="bx-im-messenger__context-menu-icon bx-im-desktop-connection-list-item__context-menu"
				@click="onContextMenuClick"
			></button>
		</div>
	`,
};
