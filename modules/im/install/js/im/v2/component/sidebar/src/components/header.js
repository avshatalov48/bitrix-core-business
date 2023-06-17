import {EventEmitter} from 'main.core.events';
import {EventType} from 'im.v2.const';
import {AddToChat} from 'im.v2.component.entity-selector';
import {MainMenu} from '../classes/context-menu/main/main-menu';
import '../css/header.css';

import type {ImModelRecentItem} from 'im.v2.model';

// @vue/component
export const SidebarHeader = {
	name: 'SidebarHeader',
	components: {AddToChat},
	props: {
		isLoading: {
			type: Boolean,
			default: false
		},
		dialogId: {
			type: String,
			required: true
		},
		chatId: {
			type: Number,
			required: true
		}
	},
	data() {
		return {
			showAddToChatPopup: false
		};
	},
	computed:
	{
		recentItem(): ImModelRecentItem
		{
			return this.$store.getters['recent/get'](this.dialogId, true);
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
		onContextMenuClick(event)
		{
			const item = {
				dialogId: this.dialogId,
				...this.recentItem
			};

			this.contextMenu.openMenu(item, event.target);
		},
		onSidebarCloseClick()
		{
			EventEmitter.emit(EventType.sidebar.close);
		}
	},
	template: `
		<div class="bx-im-sidebar-header__container bx-im-sidebar-header__scope">
			<div class="bx-im-sidebar-header__title-container">
				<button 
					class="bx-im-sidebar-header__cross-icon bx-im-messenger__cross-icon" 
					@click="onSidebarCloseClick"
				></button>
				<div class="bx-im-sidebar-header__title">{{ $Bitrix.Loc.getMessage('IM_SIDEBAR_HEADER_TITLE') }}</div>
			</div>
			<button
				class="bx-im-sidebar-header__context-menu-icon bx-im-messenger__context-menu-icon"
				@click="onContextMenuClick"
				ref="context-menu"
			></button>
			<AddToChat
				:bindElement="$refs['context-menu'] || {}"
				:chatId="chatId"
				:dialogId="dialogId"
				:showPopup="showAddToChatPopup"
				:popupConfig="{offsetTop: 0, offsetLeft: -420}"
				@close="showAddToChatPopup = false"
			/>
		</div>
	`
};