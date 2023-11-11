import { MessengerMenu, MenuItem, MenuItemIcon } from 'im.v2.component.elements';
import { Layout, PromoId, DialogType } from 'im.v2.const';
import { PromoManager } from 'im.v2.lib.promo';
import { CreateChatManager } from 'im.v2.lib.create-chat';

import { CreateChatHelp } from './create-chat-help';
import { CreateChatPromo } from './promo/create-chat';
import { GroupChatPromo } from './promo/group-chat';

import type { JsonObject } from 'main.core';
import type { MenuOptions } from 'main.popup';

const PromoByChatType = {
	[DialogType.chat]: PromoId.createGroupChat,
	[DialogType.videoconf]: PromoId.createConference,
};

// @vue/component
export const CreateChatMenu = {
	components: { MessengerMenu, MenuItem, CreateChatHelp, CreateChatPromo, GroupChatPromo },
	data(): JsonObject {
		return {
			showPopup: false,
			chatTypeToCreate: '',
			showPromo: false,
		};
	},
	computed:
	{
		DialogType: () => DialogType,
		MenuItemIcon: () => MenuItemIcon,
		menuConfig(): MenuOptions
		{
			return {
				id: 'im-create-chat-menu',
				width: 255,
				bindElement: this.$refs.icon || {},
				offsetTop: 4,
				padding: 0,
			};
		},
	},
	methods:
	{
		onChatCreateClick(type: $Values<typeof DialogType>)
		{
			this.chatTypeToCreate = type;

			const promoBannerIsNeeded = PromoManager.getInstance().needToShow(this.getPromoType());
			if (promoBannerIsNeeded)
			{
				this.showPromo = true;
				this.showPopup = false;

				return;
			}

			this.startChatCreation();
			this.showPopup = false;
		},
		onPromoContinueClick()
		{
			PromoManager.getInstance().markAsWatched(this.getPromoType());
			this.startChatCreation();
			this.showPromo = false;
			this.showPopup = false;
			this.chatTypeToCreate = '';
		},
		startChatCreation()
		{
			const { name: currentLayoutName, entityId: currentLayoutChatType } = this.$store.getters['application/getLayout'];
			if (currentLayoutName === Layout.createChat.name && currentLayoutChatType === this.chatTypeToCreate)
			{
				return;
			}
			CreateChatManager.getInstance().setCreationStatus(false);
			this.$store.dispatch('application/setLayout', {
				layoutName: Layout.createChat.name,
				entityId: this.chatTypeToCreate,
			});
		},
		getPromoType(): string
		{
			return PromoByChatType[this.chatTypeToCreate] ?? '';
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div
			class="bx-im-list-container-recent__create-chat_icon"
			:class="{'--active': showPopup}"
			@click="showPopup = true"
			ref="icon"
		></div>
		<MessengerMenu v-if="showPopup" :config="menuConfig" @close="showPopup = false">
			<MenuItem
				:icon="MenuItemIcon.chat"
				:title="loc('IM_RECENT_CREATE_GROUP_CHAT_TITLE_V2')"
				:subtitle="loc('IM_RECENT_CREATE_GROUP_CHAT_SUBTITLE')"
				@click="onChatCreateClick(DialogType.chat)"
			/>
			<MenuItem
				:icon="MenuItemIcon.conference"
				:title="loc('IM_RECENT_CREATE_CONFERENCE_TITLE')"
				:subtitle="loc('IM_RECENT_CREATE_CONFERENCE_SUBTITLE')"
				@click="onChatCreateClick(DialogType.videoconf)"
			/>
			<MenuItem
				:icon="MenuItemIcon.channel"
				:title="loc('IM_RECENT_CREATE_CHANNEL_TITLE_V2')"
				:subtitle="loc('IM_RECENT_CREATE_CHANNEL_SUBTITLE_V2')"
				:disabled="true"
			/>
			<template #footer>
				<CreateChatHelp @articleOpen="showPopup = false" />
			</template>
		</MessengerMenu>
		<CreateChatPromo
			v-if="showPromo"
			:chatType="chatTypeToCreate"
			@continue="onPromoContinueClick"
			@close="showPromo = false"
		/>
	`,
};
