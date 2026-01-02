import 'im.v2.css.classes';
import { Logger } from 'im.v2.lib.logger';
import { ChatService } from 'im.v2.provider.service.chat';

import { AiAssistantWidgetChatContent } from './content/chat-content';

import type { ImModelChat } from 'im.v2.model';

import './css/ai-assistant-chat-opener.css';

// @vue/component
export const AiAssistantWidgetChatOpener = {
	name: 'AiAssistantWidgetChatOpener',
	components: { AiAssistantWidgetChatContent },
	props: {
		dialogId: {
			type: String,
			required: true,
		},
	},
	computed: {
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		chatId(): number
		{
			return this.dialog.chatId;
		},
	},
	created(): Promise
	{
		return this.onChatOpen();
	},
	methods: {
		async onChatOpen()
		{
			if (this.dialog.inited)
			{
				Logger.warn(`AiAssistantChatOpener: chat ${this.chatId} is already loaded`);

				return;
			}

			await this.loadChat();
		},
		async loadChat()
		{
			Logger.warn(`AiAssistantChatOpener: loading chat ${this.chatId}`);
			await this.getChatService().loadChatWithMessages(this.dialogId);
			Logger.warn(`AiAssistantChatOpener: chat ${this.chatId} is loaded`);
		},
		getChatService(): ChatService
		{
			if (!this.chatService)
			{
				this.chatSerivce = new ChatService();
			}

			return this.chatSerivce;
		},
	},
	template: `
		<div class="bx-im-messenger__scope bx-im-ai-assistant-chat-opener__container">
			<AiAssistantWidgetChatContent :dialogId="dialogId" :withSidebar="false"/>
		</div>
	`,
};
