import { EventEmitter } from 'main.core.events';

import { EventType, ChatType, ActionByRole } from 'im.v2.const';
import { PermissionManager } from 'im.v2.lib.permission';
import { SidebarManager, SidebarConfig } from 'im.v2.lib.sidebar';
import { AddToChat, AddToCollab } from 'im.v2.component.entity-selector';

import { MainMenu } from '../../../../classes/context-menu/main/main-menu';

import '../css/header.css';

import type { JsonObject } from 'main.core';
import type { BitrixVueComponentProps } from 'ui.vue3';
import type { ImModelRecentItem, ImModelChat } from 'im.v2.model';

// @vue/component
export const MainHeader = {
	name: 'MainHeader',
	components: { AddToChat, AddToCollab },
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
	},
	data(): JsonObject
	{
		return {
			showAddToChatPopup: false,
		};
	},
	computed:
	{
		recentItem(): ?ImModelRecentItem
		{
			return this.$store.getters['recent/get'](this.dialogId);
		},
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		sidebarConfig(): SidebarConfig
		{
			return SidebarManager.getInstance().getConfig(this.dialogId);
		},
		headerTitle(): string
		{
			return this.sidebarConfig.getHeaderTitle();
		},
		showMenuIcon(): boolean
		{
			return this.canOpenMenu && this.isMenuEnabled;
		},
		canOpenMenu(): boolean
		{
			return PermissionManager.getInstance().canPerformActionByRole(ActionByRole.openSidebarMenu, this.dialogId);
		},
		isMenuEnabled(): boolean
		{
			return this.sidebarConfig.isHeaderMenuEnabled();
		},
		addMembersPopupComponent(): BitrixVueComponentProps
		{
			return this.dialog.type === ChatType.collab ? AddToCollab : AddToChat;
		},
	},
	created()
	{
		this.contextMenu = new MainMenu();
		this.contextMenu.subscribe(MainMenu.events.onAddToChatShow, this.onAddChatShow);
	},
	beforeUnmount()
	{
		this.contextMenu.destroy();
		this.contextMenu.unsubscribe(MainMenu.events.onAddToChatShow, this.onAddChatShow);
	},
	methods:
	{
		onAddChatShow()
		{
			this.showAddToChatPopup = true;
		},
		onContextMenuClick(event: PointerEvent)
		{
			const context = {
				dialogId: this.dialogId,
				recentItem: this.recentItem,
			};

			this.contextMenu.openMenu(context, event.target);
		},
		onSidebarCloseClick()
		{
			EventEmitter.emit(EventType.sidebar.close);
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-sidebar-header__container bx-im-sidebar-header__scope">
			<div class="bx-im-sidebar-header__title-container">
				<button 
					class="bx-im-sidebar-header__cross-icon bx-im-messenger__cross-icon" 
					@click="onSidebarCloseClick"
				></button>
				<div class="bx-im-sidebar-header__title">{{ headerTitle }}</div>
			</div>
			<button
				v-if="showMenuIcon"
				class="bx-im-sidebar-header__context-menu-icon bx-im-messenger__context-menu-icon"
				@click="onContextMenuClick"
				ref="context-menu"
			></button>
			<component
				v-if="showAddToChatPopup"
				:is="addMembersPopupComponent"
				:bindElement="$refs['context-menu'] || {}"
				:dialogId="dialogId"
				:popupConfig="{offsetTop: 0, offsetLeft: -420}"
				@close="showAddToChatPopup = false"
			/>
		</div>
	`,
};
