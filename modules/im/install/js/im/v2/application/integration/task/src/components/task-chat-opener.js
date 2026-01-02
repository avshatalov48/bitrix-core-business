import 'im.v2.css.classes';
import 'im.v2.css.icons';
import 'im.v2.css.tokens';

import { Logger } from 'im.v2.lib.logger';
import { ChatService } from 'im.v2.provider.service.chat';

import { TaskChatPlaceholder } from './placeholder/placeholder';
import { TaskChatContent } from './content/task-content';

import type { ImModelChat } from 'im.v2.model';

import './css/task-chat-opener.css';

// @vue/component
export const TaskChatOpener = {
	name: 'TaskChatOpener',
	components: { TaskChatContent, TaskChatPlaceholder },
	props: {
		chatId: {
			type: Number,
			required: true,
		},
		chatType: {
			type: String,
			required: true,
		},
	},
	computed: {
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/getByChatId'](this.chatId, true);
		},
		dialogId(): string
		{
			return this.dialog.dialogId;
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
				Logger.warn(`TaskChatOpener: chat ${this.chatId} is already loaded`);

				return;
			}

			await this.loadChat();
		},
		async loadChat(): Promise
		{
			Logger.warn(`TaskChatOpener: loading chat ${this.chatId}`);
			await this.getChatService().loadChatByChatId(this.chatId);
			Logger.warn(`TaskChatOpener: chat ${this.chatId} is loaded`);
		},
		getChatService(): ChatService
		{
			if (!this.chatService)
			{
				this.chatService = new ChatService();
			}

			return this.chatService;
		},
	},
	template: `
		<div class="bx-im-messenger__scope bx-im-task-chat-opener__container">
			<TaskChatContent :dialogId="dialogId" />
		</div>
	`,
};
