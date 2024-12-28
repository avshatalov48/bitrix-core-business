import { Loc, type JsonObject } from 'main.core';
import { BaseEvent } from 'main.core.events';

import { CreateChatManager } from 'im.v2.lib.create-chat';
import { EmptyAvatar, EmptyAvatarType, AvatarSize } from 'im.v2.component.elements';
import { Layout, ChatType } from 'im.v2.const';

import '../css/create-chat.css';

const DefaultTitleByChatType = {
	[ChatType.chat]: Loc.getMessage('IM_LIST_RECENT_CREATE_CHAT_DEFAULT_TITLE'),
	[ChatType.videoconf]: Loc.getMessage('IM_LIST_RECENT_CREATE_CONFERENCE_DEFAULT_TITLE'),
	[ChatType.channel]: Loc.getMessage('IM_LIST_RECENT_CREATE_CHANNEL_DEFAULT_TITLE'),
	[ChatType.collab]: Loc.getMessage('IM_LIST_RECENT_CREATE_COLLAB_DEFAULT_TITLE'),
};

const SubtitleByChatType = {
	[ChatType.chat]: Loc.getMessage('IM_LIST_RECENT_CREATE_CHAT_SUBTITLE'),
	[ChatType.videoconf]: Loc.getMessage('IM_LIST_RECENT_CREATE_CONFERENCE_SUBTITLE'),
	[ChatType.channel]: Loc.getMessage('IM_LIST_RECENT_CREATE_CHANNEL_SUBTITLE'),
	[ChatType.collab]: Loc.getMessage('IM_LIST_RECENT_CREATE_COLLAB_SUBTITLE'),
};

// @vue/component
export const CreateChat = {
	name: 'CreateChat',
	components: { EmptyAvatar },
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
		AvatarSize: () => AvatarSize,
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
		preparedSubtitle(): string
		{
			return SubtitleByChatType[this.chatType];
		},
		preparedAvatar(): string | null
		{
			if (!this.chatAvatarFile)
			{
				return null;
			}

			return URL.createObjectURL(this.chatAvatarFile);
		},
		avatarType(): $Values<typeof EmptyAvatarType>
		{
			if (this.chatType === ChatType.collab)
			{
				return EmptyAvatarType.collab;
			}

			if (this.chatType === ChatType.chat)
			{
				return EmptyAvatarType.default;
			}

			return EmptyAvatarType.squared;
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

		CreateChatManager.getInstance().subscribe(CreateChatManager.events.titleChange, this.onTitleChange);
		CreateChatManager.getInstance().subscribe(CreateChatManager.events.avatarChange, this.onAvatarChange);
		CreateChatManager.getInstance().subscribe(CreateChatManager.events.chatTypeChange, this.onChatTypeChange);
	},
	beforeUnmount()
	{
		CreateChatManager.getInstance().unsubscribe(CreateChatManager.events.titleChange, this.onTitleChange);
		CreateChatManager.getInstance().unsubscribe(CreateChatManager.events.avatarChange, this.onAvatarChange);
		CreateChatManager.getInstance().unsubscribe(CreateChatManager.events.chatTypeChange, this.onChatTypeChange);
	},
	methods:
	{
		onTitleChange(event: BaseEvent<string>)
		{
			this.chatTitle = event.getData();
		},
		onAvatarChange(event: BaseEvent<string>)
		{
			this.chatAvatarFile = event.getData();
		},
		onChatTypeChange(event: BaseEvent<string>)
		{
			this.chatType = event.getData();
		},
		onClick()
		{
			CreateChatManager.getInstance().startChatCreation(this.chatType, {
				clearCurrentCreation: false,
			});
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
					<div class="bx-im-list-recent-create-chat__avatar-container">
						<EmptyAvatar 
							:url="preparedAvatar" 
							:size="AvatarSize.XL"
							:title="chatTitle"
							:type="avatarType"
						/>
					</div>
					<div class="bx-im-list-recent-item__content_container">
						<div class="bx-im-list-recent-item__content_header">
							<div class="bx-im-list-recent-create-chat__header --ellipsis">
								{{ preparedTitle }}
							</div>
						</div>
						<div class="bx-im-list-recent-item__content_bottom">
							<div class="bx-im-list-recent-item__message_container">
								{{ preparedSubtitle }}
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	`,
};
