import {MessengerMenu, MenuItem, MenuItemIcon} from 'im.v2.component.elements';
import {Layout} from 'im.v2.const';

import {CreateChatHelp} from './create-chat-help';

// @vue/component
export const CreateChatMenu = {
	components: {MessengerMenu, MenuItem, CreateChatHelp},
	data() {
		return {
			showPopup: false,
		};
	},
	computed:
	{
		MenuItemIcon: () => MenuItemIcon,
		menuConfig()
		{
			return {
				id: 'im-create-chat-menu',
				width: 255,
				bindElement: this.$refs['icon'] || {},
				offsetTop: 4,
				padding: 0,
			};
		}
	},
	methods:
	{
		onGroupChatCreate()
		{
			this.$store.dispatch('application/setLayout', {layoutName: Layout.createChat.name});
			this.showPopup = false;
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		}
	},
	template: `
		<div @click="showPopup = true" class="bx-im-list-container-recent__create-chat_icon" :class="{'--active': showPopup}" ref="icon"></div>
		<MessengerMenu v-if="showPopup" :config="menuConfig" @close="showPopup = false">
			<MenuItem
				:icon="MenuItemIcon.chat"
				:title="loc('IM_RECENT_CREATE_GROUP_CHAT_TITLE_V2')"
				:subtitle="loc('IM_RECENT_CREATE_GROUP_CHAT_SUBTITLE')"
				@click="onGroupChatCreate"
			/>
			<MenuItem
				:icon="MenuItemIcon.channel"
				:title="loc('IM_RECENT_CREATE_CHANNEL_TITLE_V2')"
				:subtitle="loc('IM_RECENT_CREATE_CHANNEL_SUBTITLE_V2')"
				:disabled="true"
			/>
			<MenuItem
				:icon="MenuItemIcon.conference"
				:title="loc('IM_RECENT_CREATE_CONFERENCE_TITLE')"
				:subtitle="loc('IM_RECENT_CREATE_CONFERENCE_SUBTITLE')"
				:disabled="true"
			/>
			<template #footer>
				<CreateChatHelp @articleOpen="showPopup = false" />
			</template>
		</MessengerMenu>
	`
};