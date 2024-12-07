import { ChannelList } from 'im.v2.component.list.items.channel';
import { CreateChatPromo } from 'im.v2.component.elements';
import { Layout, ChatType, PromoId } from 'im.v2.const';
import { Analytics } from 'im.v2.lib.analytics';
import { Logger } from 'im.v2.lib.logger';
import { PromoManager } from 'im.v2.lib.promo';
import { CreateChatManager } from 'im.v2.lib.create-chat';

import './css/channel-container.css';

import type { JsonObject } from 'main.core';

// @vue/component
export const ChannelListContainer = {
	name: 'ChannelListContainer',
	components: { ChannelList, CreateChatPromo },
	emits: ['selectEntity'],
	data(): JsonObject
	{
		return {
			showPromo: false,
		};
	},
	computed:
	{
		ChatType: () => ChatType,
	},
	created()
	{
		Logger.warn('List: Channel container created');
	},
	methods:
	{
		onChatClick(dialogId): void
		{
			this.$emit('selectEntity', { layoutName: Layout.channel.name, entityId: dialogId });
		},
		onCreateClick(): void
		{
			Analytics.getInstance().onStartCreateNewChat(ChatType.channel);
			const promoBannerIsNeeded = PromoManager.getInstance().needToShow(PromoId.createChannel);
			if (promoBannerIsNeeded)
			{
				this.showPromo = true;

				return;
			}

			this.startChannelCreation();
		},
		onPromoContinueClick()
		{
			PromoManager.getInstance().markAsWatched(PromoId.createChannel);
			this.showPromo = false;
			this.startChannelCreation();
		},
		startChannelCreation()
		{
			CreateChatManager.getInstance().startChatCreation(ChatType.channel);
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-list-container-channel__container">
			<div class="bx-im-list-container-channel__header_container">
				<div class="bx-im-list-container-channel__header_title">{{ loc('IM_LIST_CONTAINER_CHANNEL_HEADER_TITLE') }}</div>
				<div @click="onCreateClick" class="bx-im-list-container-channel__header_create-channel"></div>
			</div>
			<div class="bx-im-list-container-channel__elements_container">
				<div class="bx-im-list-container-channel__elements">
					<ChannelList @chatClick="onChatClick" />
				</div>
			</div>
		</div>
		<CreateChatPromo
			v-if="showPromo"
			:chatType="ChatType.channel"
			@continue="onPromoContinueClick"
			@close="showPromo = false"
		/>
	`,
};
