import 'ui.notification';
import { EditableChatTitle } from 'im.v2.component.elements';
import { ChatService } from 'im.v2.provider.service';

import '../css/chat-header.css';

import type { JsonObject } from 'main.core';
import type { ImModelChat } from 'im.v2.model';

// @vue/component
export const ChatHeader = {
	name: 'ChatHeader',
	components: { EditableChatTitle },
	props:
	{
		dialogId: {
			type: String,
			default: '',
		},
	},
	data(): JsonObject
	{
		return {};
	},
	computed:
	{
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		chatId(): number
		{
			return this.dialog.chatId;
		},
	},
	methods:
	{
		onNewTitleSubmit(newTitle: string)
		{
			this.getChatService().renameChat(this.dialogId, newTitle).catch(() => {
				BX.UI.Notification.Center.notify({
					content: this.loc('IM_CONTENT_COPILOT_HEADER_RENAME_ERROR'),
				});
			});
		},
		getChatService(): ChatService
		{
			if (!this.chatService)
			{
				this.chatService = new ChatService();
			}

			return this.chatService;
		},
		loc(phraseCode: string, replacements: {[string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<div class="bx-im-copilot-header__container">
			<div class="bx-im-copilot-header__left">
				<div class="bx-im-copilot-header__avatar">
					<div class="bx-im-copilot-header__avatar_default"></div>
				</div>
				<div class="bx-im-copilot-header__info">
					<EditableChatTitle :dialogId="dialogId" @newTitleSubmit="onNewTitleSubmit" />
					<div 
						:title="loc('IM_CONTENT_CHAT_HEADER_OPEN_MEMBERS')" 
						class="bx-im-copilot-header__subtitle"
					>
						{{ loc('IM_CONTENT_COPILOT_HEADER_SUBTITLE') }}
					</div>
				</div>
			</div>
		</div>
	`,
};
