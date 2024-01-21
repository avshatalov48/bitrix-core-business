import 'ui.notification';
import { BaseEvent } from 'main.core.events';

import { Messenger } from 'im.public';
import { ChatService } from 'im.v2.provider.service';
import { Logger } from 'im.v2.lib.logger';
import { ThemeManager } from 'im.v2.lib.theme';
import { ResizeManager } from 'im.v2.lib.textarea';
import { Settings } from 'im.v2.const';

import { ChatHeader } from './components/chat-header';
import { EmptyState } from './components/empty-state';
import { CopilotTextarea } from './components/textarea/textarea';
import { CopilotDialog } from './components/dialog';

import './css/copilot-content.css';

import type { ImModelChat, ImModelLayout } from 'im.v2.model';
import type { BackgroundStyle } from 'im.v2.lib.theme';

// @vue/component
export const CopilotContent = {
	name: 'CopilotContent',
	components: { EmptyState, ChatHeader, CopilotDialog, CopilotTextarea },
	directives:
	{
		'textarea-observer': {
			mounted(element, binding)
			{
				binding.instance.textareaResizeManager.observeTextarea(element);
			},
			beforeUnmount(element, binding)
			{
				binding.instance.textareaResizeManager.unobserveTextarea(element);
			},
		},
	},
	props:
	{
		entityId: {
			type: String,
			default: '',
		},
		contextMessageId: {
			type: Number,
			default: 0,
		},
	},
	data(): Object
	{
		return {
			textareaHeight: 0,
		};
	},
	computed:
	{
		layout(): ImModelLayout
		{
			return this.$store.getters['application/getLayout'];
		},
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.entityId, true);
		},
		containerClasses(): string[]
		{
			const alignment = this.$store.getters['application/settings/get'](Settings.appearance.alignment);

			return [`--${alignment}-align`];
		},
		backgroundStyle(): BackgroundStyle
		{
			const COPILOT_BACKGROUND_ID = 4;

			return ThemeManager.getBackgroundStyleById(COPILOT_BACKGROUND_ID);
		},
		dialogContainerStyle(): Object
		{
			const CHAT_HEADER_HEIGHT = 64;

			return {
				height: `calc(100% - ${CHAT_HEADER_HEIGHT}px - ${this.textareaHeight}px)`,
			};
		},
	},
	watch:
	{
		entityId(newValue, oldValue)
		{
			Logger.warn(`CopilotContent: switching from ${oldValue || 'empty'} to ${newValue}`);
			this.onChatChange();
		},
	},
	created()
	{
		if (this.entityId)
		{
			this.onChatChange();
		}

		this.initTextareaResizeManager();
	},
	methods:
	{
		async onChatChange()
		{
			if (this.entityId === '')
			{
				return;
			}

			if (this.dialog.inited)
			{
				Logger.warn(`CopilotContent: chat ${this.entityId} is already loaded`);

				return;
			}

			if (this.dialog.loading)
			{
				Logger.warn(`CopilotContent: chat ${this.entityId} is loading`);

				return;
			}

			if (this.layout.contextId)
			{
				await this.loadChatWithContext();

				return;
			}

			await this.loadChat();
		},
		loadChatWithContext(): Promise
		{
			Logger.warn(`CopilotContent: loading chat ${this.entityId} with context - ${this.layout.contextId}`);

			return this.getChatService().loadChatWithContext(this.entityId, this.layout.contextId).then(() => {
				Logger.warn(`CopilotContent: chat ${this.entityId} is loaded with context of ${this.layout.contextId}`);
			}).catch((error) => {
				if (error.code === 'ACCESS_ERROR')
				{
					this.showNotification(this.loc('IM_CONTENT_CHAT_ACCESS_ERROR'));
				}
				Logger.error(error);
				Messenger.openCopilot();
			});
		},
		loadChat(): Promise
		{
			Logger.warn(`CopilotContent: loading chat ${this.entityId}`);

			return this.getChatService().loadChatWithMessages(this.entityId).then(() => {
				Logger.warn(`CopilotContent: chat ${this.entityId} is loaded`);
			}).catch(() => {
				Messenger.openCopilot();
			});
		},
		initTextareaResizeManager()
		{
			this.textareaResizeManager = new ResizeManager();
			this.textareaResizeManager.subscribe(
				ResizeManager.events.onHeightChange,
				(event: BaseEvent<{newHeight: number}>) => {
					const { newHeight } = event.getData();
					this.textareaHeight = newHeight;
				},
			);
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
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-content-chat__container bx-im-content-copilot__container" :class="containerClasses" :style="backgroundStyle">
			<div v-if="entityId" class="bx-im-content-copilot__content">
				<ChatHeader :dialogId="entityId" :key="entityId"/>
				<div :style="dialogContainerStyle" class="bx-im-content-copilot__dialog_container">
					<div class="bx-im-content-copilot__dialog_content">
						<CopilotDialog :dialogId="entityId" :key="entityId" :textareaHeight="textareaHeight" />
					</div>
				</div>
				<div v-textarea-observer class="bx-im-content-copilot__textarea_container">
					<CopilotTextarea :dialogId="entityId" :key="entityId" />
				</div>
			</div>
			<EmptyState v-else />
		</div>
	`,
};
