import {ChatService} from 'im.v2.provider.service';
import {MessengerMenu, MenuItem} from 'im.v2.component.elements';

import type {PopupOptions} from 'main.popup';

// @vue/component
export const HeaderMenu = {
	components: {MessengerMenu, MenuItem},
	emits: ['showUnread'],
	data() {
		return {
			showPopup: false,
		};
	},
	computed:
	{
		menuConfig(): PopupOptions
		{
			return {
				id: 'im-recent-header-menu',
				width: 284,
				bindElement: this.$refs['icon'] || {},
				offsetTop: 4,
				padding: 0,
			};
		},
		unreadCounter(): number
		{
			return this.$store.getters['recent/getTotalCounter'];
		}
	},
	methods:
	{
		onIconClick()
		{
			this.showPopup = true;
		},
		onReadAllClick()
		{
			new ChatService().readAll();
			this.showPopup = false;
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		}
	},
	template: `
		<div @click="onIconClick" class="bx-im-list-container-recent__header-menu_icon" :class="{'--active': showPopup}" ref="icon"></div>
		<MessengerMenu v-if="showPopup" :config="menuConfig" @close="showPopup = false">
			<MenuItem
				:title="loc('IM_RECENT_HEADER_MENU_READ_ALL')"
				@click="onReadAllClick"
			/>
			<MenuItem
				:title="loc('IM_RECENT_HEADER_MENU_SHOW_UNREAD_ONLY')"
				:counter="unreadCounter"
				:disabled="true"
			/>
			<MenuItem
				:title="loc('IM_RECENT_HEADER_MENU_CHAT_GROUPS_TITLE')"
				:subtitle="loc('IM_RECENT_HEADER_MENU_CHAT_GROUPS_SUBTITLE')"
				:disabled="true"
			/>
		</MessengerMenu>
	`
};