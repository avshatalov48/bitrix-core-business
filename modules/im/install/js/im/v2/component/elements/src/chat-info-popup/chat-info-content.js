import {Messenger} from 'im.public';
import {ChatService} from 'im.v2.provider.service';
import {DialogType} from 'im.v2.const';
import {Utils} from 'im.v2.lib.utils';

import {Avatar, ChatTitle, Button, ButtonColor, ButtonSize} from '../registry';

import './chat-info-content.css';

// @vue/component
export const ChatInfoContent = {
	components: {Avatar, ChatTitle, Button},
	props: {
		dialogId: {
			type: String,
			required: true
		}
	},
	data()
	{
		return {
			hasError: false,
			isLoading: false
		};
	},
	computed:
	{
		ButtonColor: () => ButtonColor,
		ButtonSize: () => ButtonSize,
		dialog(): ?Object
		{
			return this.$store.getters['dialogues/get'](this.dialogId);
		},
		user()
		{
			return this.$store.getters['users/get'](this.dialogId, true);
		},
		isUser()
		{
			return this.dialog?.type === DialogType.user;
		},
		isChat()
		{
			return !this.isUser;
		},
		chatType(): string
		{
			if (this.isUser)
			{
				return this.$store.getters['users/getPosition'](this.dialogId);
			}

			return this.$Bitrix.Loc.getMessage('IM_LIST_RECENT_CHAT_TYPE_GROUP_V2');
		},
		openChatButtonText()
		{
			if (this.isChat)
			{
				return this.$Bitrix.Loc.getMessage('IM_ELEMENTS_CHAT_INFO_POPUP_OPEN_CHAT');
			}

			return this.$Bitrix.Loc.getMessage('IM_ELEMENTS_CHAT_INFO_POPUP_WRITE_A_MESSAGE');
		},
		userProfileLink(): string
		{
			return Utils.user.getProfileLink(this.dialogId);
		},
	},
	created()
	{
		this.chatService = new ChatService();
		if (!this.dialog)
		{
			this.loadChat();
		}
	},
	methods:
	{
		loadChat()
		{
			this.isLoading = true;
			this.chatService.loadChat(this.dialogId).then(() => {
				this.isLoading = false;
			}).catch((error) => {
				this.isLoading = false;
				this.hasError = true;
				console.error(error);
			});
		},
		onOpenChat()
		{
			Messenger.openChat(this.dialogId);
		},
		onClickVideoCall()
		{
			Messenger.startVideoCall(this.dialogId);
		},
	},
	template: `
		<div class="bx-im-chat-info-content__container">
			<template v-if="!isLoading && !hasError">
				<div class="bx-im-chat-info-content__detail-info-container">
					<div class="bx-im-chat-info-content__avatar-container">
						<Avatar :dialogId="dialogId" size="XL"/>
					</div>
					<div class="bx-im-chat-info-content__title-container">
						<ChatTitle v-if="isChat" :dialogId="dialogId" />
						<a v-else :href="userProfileLink" target="_blank">
							<ChatTitle :dialogId="dialogId" />
						</a>
						<div class="bx-im-chat-info-content__chat-description_text">
							{{ chatType }}
						</div>
					</div>
				</div>
				<div class="bx-im-chat-info-content__buttons-container">
					<Button
						:size="ButtonSize.M"
						:color="ButtonColor.PrimaryBorder"
						:isRounded="true"
						:text="openChatButtonText"
						:isUppercase="false"
						@click="onOpenChat"
					/>
					<Button
						v-if="isUser"
						:size="ButtonSize.M"
						:color="ButtonColor.PrimaryBorder"
						:isRounded="true"
						:isUppercase="false"
						:text="$Bitrix.Loc.getMessage('IM_ELEMENTS_CHAT_INFO_POPUP_VIDEOCALL')"
						@click="onClickVideoCall"
					/>
				</div>
			</template>
			<template v-else-if="isLoading">
				<div class="bx-im-chat-info-content__loader-container">
					<div class="bx-im-chat-info-content__loader_icon"></div>
				</div>
			</template>
			<template v-else-if="hasError">
				<div class="bx-im-chat-info-content__error-container">
					{{ $Bitrix.Loc.getMessage('IM_ELEMENTS_CHAT_INFO_POPUP_NO_ACCESS') }}
				</div>
			</template>
		</div>
	`
};
