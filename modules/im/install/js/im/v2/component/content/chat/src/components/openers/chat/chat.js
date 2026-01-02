import 'ui.notification';

import { Messenger } from 'im.public';
import { ChatType, Layout, UserRole, ErrorCode, PromoId } from 'im.v2.const';
import { Analytics } from 'im.v2.lib.analytics';
import { LayoutManager } from 'im.v2.lib.layout';
import { Logger } from 'im.v2.lib.logger';
import { Utils } from 'im.v2.lib.utils';
import { ChannelManager } from 'im.v2.lib.channel';
import { PromoManager } from 'im.v2.lib.promo';
import { ChatService } from 'im.v2.provider.service.chat';
import { BaseChatContent } from 'im.v2.component.content.elements';

import { ChannelContent } from '../../content/channel/channel';
import { CollabContent } from '../../content/collab/collab';
import { MultidialogContent } from '../../content/multidialog/multidialog';
import { NotesContent } from '../../content/notes/notes-content';
import { CopilotContent } from '../../content/copilot/copilot';
import { AiAssistantBotContent } from '../../content/ai-assistant-bot/ai-assistant-bot';
import { TaskCommentsContent } from '../../content/task-comments/task-comments';
import { BaseEmptyState as EmptyState } from './components/empty-state/base';
import { ChannelEmptyState } from './components/empty-state/channel';
import { EmbeddedChatPromoEmptyState } from './components/empty-state/chat/embedded-promo';
import { EmbeddedChatEmptyState } from './components/empty-state/chat/embedded';
import { CollabEmptyState } from './components/empty-state/collab/collab';
import { CopilotEmptyState } from './components/empty-state/copilot/copilot';
import { TaskEmptyState } from './components/empty-state/task/task';
import { UserService } from './classes/user-service';

import './css/default-chat-content.css';

import type { BitrixVueComponentProps } from 'ui.vue3';
import type { ImModelChat, ImModelLayout } from 'im.v2.model';

type ContentComponentConfigItem = {
	condition: boolean,
	component: BitrixVueComponentProps
};

// @vue/component
export const ChatOpener = {
	name: 'ChatOpener',
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
	},
	emits: ['close'],
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
		isNotes(): boolean
		{
			return this.$store.getters['chats/isNotes'](this.dialogId);
		},
		isCopilot(): boolean
		{
			return this.dialog.type === ChatType.copilot;
		},
		isAiAssistantBot(): boolean
		{
			return this.$store.getters['users/bots/isAiAssistant'](this.dialogId);
		},
		isTaskComments(): boolean
		{
			return this.dialog.type === ChatType.taskComments;
		},
		isGuest(): boolean
		{
			return this.dialog.role === UserRole.guest;
		},
		contentComponentConfig(): ContentComponentConfigItem[]
		{
			return [
				{
					condition: this.isChannel,
					component: ChannelContent,
				},
				{
					condition: this.isCollab,
					component: CollabContent,
				},
				{
					condition: this.isMultidialog,
					component: MultidialogContent,
				},
				{
					condition: this.isNotes,
					component: NotesContent,
				},
				{
					condition: this.isCopilot,
					component: CopilotContent,
				},
				{
					condition: this.isAiAssistantBot,
					component: AiAssistantBotContent,
				},
				{
					condition: this.isTaskComments,
					component: TaskCommentsContent,
				},
			];
		},
		contentComponent(): BitrixVueComponentProps
		{
			const matchingItem: ContentComponentConfigItem = this.contentComponentConfig.find((item) => {
				return item.condition === true;
			});

			return matchingItem ? matchingItem.component : BaseChatContent;
		},
		emptyStateComponent(): BitrixVueComponentProps
		{
			const EmptyStateComponentByLayout = {
				[Layout.channel]: ChannelEmptyState,
				[Layout.collab]: CollabEmptyState,
				[Layout.copilot]: CopilotEmptyState,
				[Layout.chat]: this.chatEmptyStateComponent,
				[Layout.taskComments]: TaskEmptyState,
				default: EmptyState,
			};

			return EmptyStateComponentByLayout[this.layout.name] ?? EmptyStateComponentByLayout.default;
		},
		chatEmptyStateComponent(): BitrixVueComponentProps
		{
			const isEmbeddedMode = LayoutManager.getInstance().isEmbeddedMode();
			const needToShowPromoEmptyState = PromoManager.getInstance().needToShow(PromoId.embeddedChatEmptyState);

			if (!isEmbeddedMode)
			{
				return EmptyState;
			}

			return needToShowPromoEmptyState ? EmbeddedChatPromoEmptyState : EmbeddedChatEmptyState;
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
		async onChatChange(): void
		{
			if (this.dialogId === '')
			{
				return;
			}

			if (Utils.dialog.isExternalId(this.dialogId))
			{
				const realDialogId = await this.getChatService().prepareDialogId(this.dialogId);

				void LayoutManager.getInstance().setLayout({
					name: Layout.chat,
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
					this.getUserService().updateLastActivityDate(userId);
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
				.catch((error) => {
					this.sendAnalytics(error);
					Messenger.openChat();
				});

			Logger.warn(`ChatContent: chat ${this.dialogId} is loaded with context of ${this.layout.contextId}`);
		},
		async loadChat(): Promise
		{
			Logger.warn(`ChatContent: loading chat ${this.dialogId}`);

			await this.getChatService().loadChatWithMessages(this.dialogId)
				.catch(() => {
					Messenger.openChat();
				});

			Logger.warn(`ChatContent: chat ${this.dialogId} is loaded`);
		},
		sendAnalytics(error: Error)
		{
			if (error.code !== ErrorCode.message.notFound)
			{
				return;
			}

			Analytics.getInstance().messageDelete.onNotFoundNotification({ dialogId: this.dialogId });
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
			<component :is="contentComponent" v-else :dialogId="dialogId" />
		</div>
	`,
};
