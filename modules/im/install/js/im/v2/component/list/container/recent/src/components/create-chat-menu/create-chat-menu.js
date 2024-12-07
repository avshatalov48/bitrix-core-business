import { Type } from 'main.core';
import { FeaturePromoter } from 'ui.info-helper';

import { Messenger } from 'im.public';
import { MessengerMenu, MenuItem, MenuItemIcon, CreateChatPromo } from 'im.v2.component.elements';
import { Layout, PromoId, ChatType, SliderCode } from 'im.v2.const';
import { Analytics } from 'im.v2.lib.analytics';
import { PromoManager } from 'im.v2.lib.promo';
import { CreateChatManager } from 'im.v2.lib.create-chat';
import { Feature, FeatureManager } from 'im.v2.lib.feature';

import { CreateChatHelp } from './create-chat-help';

import type { JsonObject } from 'main.core';
import type { MenuOptions } from 'main.popup';

type MenuItemType = {
	icon: $Values<typeof MenuItemIcon>,
	title: string,
	subtitle: string,
	clickHandler: () => void,
	showCondition?: () => boolean,
};

const PromoByChatType = {
	[ChatType.chat]: PromoId.createGroupChat,
	[ChatType.videoconf]: PromoId.createConference,
	[ChatType.channel]: PromoId.createChannel,
};

// @vue/component
export const CreateChatMenu = {
	components: { MessengerMenu, MenuItem, CreateChatHelp, CreateChatPromo },
	data(): JsonObject
	{
		return {
			showPopup: false,
			chatTypeToCreate: '',
			showPromo: false,
		};
	},
	computed:
	{
		ChatType: () => ChatType,
		menuConfig(): MenuOptions
		{
			return {
				id: 'im-create-chat-menu',
				width: 275,
				bindElement: this.$refs.icon || {},
				offsetTop: 4,
				padding: 0,
			};
		},
		menuItems(): MenuItemType[]
		{
			return [
				{
					icon: MenuItemIcon.chat,
					title: this.loc('IM_RECENT_CREATE_GROUP_CHAT_TITLE_V2'),
					subtitle: this.loc('IM_RECENT_CREATE_GROUP_CHAT_SUBTITLE_V2'),
					clickHandler: this.onChatCreateClick.bind(this, ChatType.chat),
				},
				{
					icon: MenuItemIcon.channel,
					title: this.loc('IM_RECENT_CREATE_CHANNEL_TITLE_V2'),
					subtitle: this.loc('IM_RECENT_CREATE_CHANNEL_SUBTITLE_V3'),
					clickHandler: this.onChatCreateClick.bind(this, ChatType.channel),
				},
				{
					icon: MenuItemIcon.conference,
					title: this.loc('IM_RECENT_CREATE_CONFERENCE_TITLE'),
					subtitle: this.loc('IM_RECENT_CREATE_CONFERENCE_SUBTITLE_V2'),
					clickHandler: this.onChatCreateClick.bind(this, ChatType.videoconf),
				},
				{
					icon: MenuItemIcon.conference,
					title: this.loc('IM_RECENT_CREATE_COLLAB_TITLE'),
					subtitle: this.loc('IM_RECENT_CREATE_COLLAB_SUBTITLE'),
					clickHandler: this.onChatCreateClick.bind(this, ChatType.collab),
					showCondition: () => FeatureManager.isFeatureAvailable(Feature.collabAvailable),
				},
			];
		},
	},
	methods:
	{
		onChatCreateClick(type: $Values<typeof ChatType>)
		{
			Analytics.getInstance().onStartCreateNewChat(type);
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
		onCopilotClick()
		{
			this.showPopup = false;

			if (!FeatureManager.isFeatureAvailable(Feature.copilotActive))
			{
				const promoter = new FeaturePromoter({ code: SliderCode.copilotDisabled });
				promoter.show();

				return;
			}

			void Messenger.openCopilot();
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
			CreateChatManager.getInstance().startChatCreation(this.chatTypeToCreate);
		},
		getPromoType(): string
		{
			return PromoByChatType[this.chatTypeToCreate] ?? '';
		},
		needToShowMenuItem(showCondition: () => boolean): boolean
		{
			if (!Type.isFunction(showCondition))
			{
				return true;
			}

			return showCondition();
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
			<template v-for="{ icon, title, subtitle, clickHandler, showCondition } in menuItems">
				<MenuItem
					v-if="needToShowMenuItem(showCondition)"
					:key="title"
					:icon="icon"
					:title="title"
					:subtitle="subtitle"
					@click="clickHandler"
				/>
			</template>
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
