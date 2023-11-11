import { Loc, type JsonObject } from 'main.core';
import { BaseEvent } from 'main.core.events';

import { CreateChatManager } from 'im.v2.lib.create-chat';
import { Layout, DialogType } from 'im.v2.const';

import '../css/create-chat.css';

const DefaultTitleByChatType = {
	[DialogType.chat]: Loc.getMessage('IM_LIST_RECENT_CREATE_CHAT_DEFAULT_TITLE'),
	[DialogType.videoconf]: Loc.getMessage('IM_LIST_RECENT_CREATE_CONFERENCE_DEFAULT_TITLE'),
};

// @vue/component
export const CreateChat = {
	data(): JsonObject
	{
		return {
			chatTitle: '',
			chatAvatarFile: '',
			chatType: '',
		};
	},
	computed:
	{
		chatCreationIsOpened(): boolean
		{
			const { name: currentLayoutName } = this.$store.getters['application/getLayout'];

			return currentLayoutName === Layout.createChat.name;
		},
		preparedTitle(): string
		{
			if (this.chatTitle === '')
			{
				return DefaultTitleByChatType[this.chatType];
			}

			return this.chatTitle;
		},
		preparedAvatar(): string | null
		{
			if (!this.chatAvatarFile)
			{
				return null;
			}

			return URL.createObjectURL(this.chatAvatarFile);
		},
	},
	created()
	{
		const existingTitle = CreateChatManager.getInstance().getChatTitle();
		if (existingTitle)
		{
			this.chatTitle = existingTitle;
		}

		const existingAvatar = CreateChatManager.getInstance().getChatAvatar();
		if (existingAvatar)
		{
			this.chatAvatarFile = existingAvatar;
		}

		this.chatType = CreateChatManager.getInstance().getChatType();

		CreateChatManager.getInstance().subscribe(
			CreateChatManager.events.titleChange,
			(event: BaseEvent<string>) => {
				this.chatTitle = event.getData();
			},
		);

		CreateChatManager.getInstance().subscribe(
			CreateChatManager.events.avatarChange,
			(event: BaseEvent<string>) => {
				this.chatAvatarFile = event.getData();
			},
		);

		CreateChatManager.getInstance().subscribe(
			CreateChatManager.events.chatTypeChange,
			(event: BaseEvent<string>) => {
				this.chatType = event.getData();
			},
		);
	},
	methods:
	{
		onClick()
		{
			this.$store.dispatch('application/setLayout', { layoutName: Layout.createChat.name, entityId: this.chatType });
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-list-recent-create-chat__container">
			<div class="bx-im-list-recent-item__wrap" :class="{'--selected': chatCreationIsOpened}" @click="onClick">
				<div class="bx-im-list-recent-item__container">
					<div class="bx-im-list-recent-item__avatar_container">
						<div v-if="!preparedAvatar" class="bx-im-list-recent-create-chat__avatar --default"></div>
						<img v-else class="bx-im-list-recent-create-chat__avatar --image" :src="preparedAvatar" :alt="chatTitle" />
					</div>
					<div class="bx-im-list-recent-item__content_container">
						<div class="bx-im-list-recent-item__content_header">
							<div class="bx-im-list-recent-create-chat__header">
								{{ preparedTitle }}
							</div>
						</div>
						<div class="bx-im-list-recent-item__content_bottom">
							<div class="bx-im-list-recent-item__message_container">
								{{ loc('IM_LIST_RECENT_CREATE_CHAT_SUBTITLE') }}
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	`,
};
