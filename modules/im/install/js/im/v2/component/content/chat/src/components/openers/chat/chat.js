import 'ui.notification';

import { Messenger } from 'im.public';
import { ChatType, Layout, UserRole } from 'im.v2.const';
import { Analytics } from 'im.v2.lib.analytics';
import { LayoutManager } from 'im.v2.lib.layout';
import { Logger } from 'im.v2.lib.logger';
import { Utils } from 'im.v2.lib.utils';
import { ChannelManager } from 'im.v2.lib.channel';
import { ChatService } from 'im.v2.provider.service';
import { AccessErrorCode } from 'im.v2.lib.access';
import { BaseChatContent } from 'im.v2.component.content.elements';

import { ChannelContent } from '../../content/channel/channel';
import { CollabContent } from '../../content/collab/collab';
import { MultidialogContent } from '../../content/multidialog/multidialog';
import { BaseEmptyState as EmptyState } from './components/empty-state/base';
import { ChannelEmptyState } from './components/empty-state/channel';
import { UserService } from './classes/user-service';
import { CollabEmptyState } from './components/empty-state/collab/collab';

import './css/default-chat-content.css';

import type { JsonObject } from 'main.core';
import type { BitrixVueComponentProps } from 'ui.vue3';
import type { ImModelChat, ImModelLayout } from 'im.v2.model';

const EmptyStateComponentByLayout = {
	[Layout.channel.name]: ChannelEmptyState,
	[Layout.collab.name]: CollabEmptyState,
	default: EmptyState,
};

// @vue/component
export const ChatOpener = {
	name: 'ChatOpener',
	components: { BaseChatContent, ChannelContent, CollabContent, MultidialogContent, EmptyState, ChannelEmptyState },
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
	},
	emits: ['close'],
	data(): JsonObject
	{
		return {};
	},
	computed:
	{
		layout(): ImModelLayout
		{
			return this.$store.getters['application/getLayout'];
		},
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		isUser(): boolean
		{
			return this.dialog.type === ChatType.user;
		},
		isChannel(): boolean
		{
			return ChannelManager.isChannel(this.dialogId);
		},
		isCollab(): boolean
		{
			return this.dialog.type === ChatType.collab;
		},
		isMultidialog(): boolean
		{
			return this.$store.getters['sidebar/multidialog/isSupport'](this.dialogId);
		},
		isGuest(): boolean
		{
			return this.dialog.role === UserRole.guest;
		},
		emptyStateComponent(): BitrixVueComponentProps
		{
			return EmptyStateComponentByLayout[this.layout.name] ?? EmptyStateComponentByLayout.default;
		},
	},
	watch:
	{
		dialogId(newValue, oldValue)
		{
			Logger.warn(`ChatContent: switching from ${oldValue || 'empty'} to ${newValue}`);
			this.onChatChange();
		},
	},
	created()
	{
		if (!this.dialogId)
		{
			return;
		}

		this.onChatChange();
	},
	methods:
	{
		async onChatChange()
		{
			if (this.dialogId === '')
			{
				return;
			}

			if (Utils.dialog.isExternalId(this.dialogId))
			{
				const realDialogId = await this.getChatService().prepareDialogId(this.dialogId);

				void LayoutManager.getInstance().setLayout({
					name: Layout.chat.name,
					entityId: realDialogId,
					contextId: this.layout.contextId,
				});

				return;
			}

			if (this.dialog.inited)
			{
				Logger.warn(`ChatContent: chat ${this.dialogId} is already loaded`);
				if (this.isUser)
				{
					const userId = parseInt(this.dialog.dialogId, 10);
					void this.getUserService().updateLastActivityDate(userId);
				}
				else if (this.isChannel && !this.isGuest)
				{
					Logger.warn(`ChatContent: channel ${this.dialogId} is loaded, loading comments metadata`);
					void this.getChatService().loadCommentInfo(this.dialogId);
				}
				Analytics.getInstance().onOpenChat(this.dialog);

				return;
			}

			if (this.dialog.loading)
			{
				Logger.warn(`ChatContent: chat ${this.dialogId} is loading`);

				return;
			}

			if (this.layout.contextId)
			{
				await this.loadChatWithContext();
				Analytics.getInstance().onOpenChat(this.dialog);

				return;
			}

			await this.loadChat();
			Analytics.getInstance().onOpenChat(this.dialog);
		},
		async loadChatWithContext(): Promise
		{
			Logger.warn(`ChatContent: loading chat ${this.dialogId} with context - ${this.layout.contextId}`);

			await this.getChatService().loadChatWithContext(this.dialogId, this.layout.contextId)
				.catch((errors) => {
					this.sendAnalytics(errors);
					this.handleChatLoadError(errors);
					Logger.error(errors);
					Messenger.openChat();
				});

			Logger.warn(`ChatContent: chat ${this.dialogId} is loaded with context of ${this.layout.contextId}`);
		},
		async loadChat(): Promise
		{
			Logger.warn(`ChatContent: loading chat ${this.dialogId}`);

			await this.getChatService().loadChatWithMessages(this.dialogId)
				.catch((errors) => {
					this.handleChatLoadError(errors);
					Logger.error(errors);
					Messenger.openChat();
				});

			Logger.warn(`ChatContent: chat ${this.dialogId} is loaded`);
		},
		handleChatLoadError(errors: Error[]): void
		{
			const [firstError] = errors;
			if (firstError.code === AccessErrorCode.accessDenied)
			{
				this.showNotification(this.loc('IM_CONTENT_CHAT_ACCESS_ERROR_MSGVER_1'));
			}
			else if (firstError.code === AccessErrorCode.messageNotFound)
			{
				this.showNotification(this.loc('IM_CONTENT_CHAT_CONTEXT_MESSAGE_NOT_FOUND'));
			}
		},
		sendAnalytics(errors: Error[])
		{
			const [firstError] = errors;
			if (firstError.code !== AccessErrorCode.messageNotFound)
			{
				return;
			}

			Analytics.getInstance().messageDelete.onNotFoundNotification({ dialogId: this.dialogId });
		},
		showNotification(text: string)
		{
			BX.UI.Notification.Center.notify({ content: text });
		},
		getChatService(): ChatService
		{
			if (!this.chatService)
			{
				this.chatService = new ChatService();
			}

			return this.chatService;
		},
		getUserService(): UserService
		{
			if (!this.userService)
			{
				this.userService = new UserService();
			}

			return this.userService;
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-content-default-chat__container">
			<component :is="emptyStateComponent" v-if="!dialogId" />
			<ChannelContent v-else-if="isChannel" :dialogId="dialogId" />
			<CollabContent v-else-if="isCollab" :dialogId="dialogId" />
			<MultidialogContent v-else-if="isMultidialog" :dialogId="dialogId" />
			<BaseChatContent v-else :dialogId="dialogId" />
		</div>
	`,
};
