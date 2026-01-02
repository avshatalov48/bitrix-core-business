import { BaseEvent, EventEmitter } from 'main.core.events';

import { ChatType, EventType, MessageComponent, ActionByRole } from 'im.v2.const';
import { Utils } from 'im.v2.lib.utils';
import { PermissionManager } from 'im.v2.lib.permission';
import { Quote } from 'im.v2.lib.quote';
import { FadeAnimation } from 'im.v2.component.animation';
import { FeatureManager } from 'im.v2.lib.feature';
import { MessageComponentManager } from 'im.v2.lib.message-component';
import { MessageMenuManager } from 'im.v2.lib.menu';

import { DialogStatus } from './components/dialog-status/dialog-status';
import { DialogLoader } from './components/dialog-loader';
import { AvatarMenu } from './classes/avatar-menu';
import { ObserverManager } from './classes/observer-manager';

import { DateGroup } from './components/block/date-group';
import { AuthorGroup } from './components/block/author-group';
import { NewMessagesBlock } from './components/block/new-messages';
import { MarkedMessagesBlock } from './components/block/marked-messages';
import { EmptyState } from './components/empty-state';
import { HistoryLimitBanner } from './components/history-limit-banner';
import { CollectionManager, type DateGroupItem } from './classes/collection-manager/collection-manager';
import { MessageComponents } from './utils/message-components';

import './css/message-list.css';

import type { JsonObject } from 'main.core';
import type { ImModelChat, ImModelMessage, ImModelUser } from 'im.v2.model';

export { AvatarMenu } from './classes/avatar-menu';
export { AuthorGroup } from './components/block/author-group';
export { MessageComponents } from './utils/message-components';
export { CollectionManager } from './classes/collection-manager/collection-manager';

type ContextMenuEvent = BaseEvent<{
	message: ImModelMessage,
	dialogId: string,
	event: PointerEvent,
	bindElement?: HTMLElement
}>

// @vue/component
export const MessageList = {
	name: 'MessageList',
	directives:
	{
		'message-observer': {
			mounted(element, binding)
			{
				binding.instance.observer.observeMessage(element);
			},
			beforeUnmount(element, binding)
			{
				binding.instance.observer.unobserveMessage(element);
			},
		},
	},
	components:
	{
		DateGroup,
		AuthorGroup,
		NewMessagesBlock,
		MarkedMessagesBlock,
		DialogStatus,
		DialogLoader,
		EmptyState,
		FadeAnimation,
		HistoryLimitBanner,
		...MessageComponents,
	},
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
		containerHeight: {
			type: [Number, null],
			default: null,
		},
	},
	data(): JsonObject
	{
		return {
			windowFocused: false,
		};
	},
	computed:
	{
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		user(): ImModelUser
		{
			return this.$store.getters['users/get'](this.dialogId, true);
		},
		messageCollection(): ImModelMessage[]
		{
			return this.$store.getters['messages/getByChatId'](this.dialog.chatId);
		},
		isUser(): boolean
		{
			return this.dialog.type === ChatType.user;
		},
		dialogInited(): boolean
		{
			return this.dialog.inited;
		},
		formattedCollection(): DateGroupItem[]
		{
			if (!this.dialogInited && this.messageCollection.length === 0)
			{
				return [];
			}

			return this.getCollectionManager().formatMessageCollection(this.messageCollection);
		},
		noMessages(): boolean
		{
			return this.formattedCollection.length === 0;
		},
		isHistoryLimitExceeded(): boolean
		{
			return !FeatureManager.chatHistory.isAvailable() && this.dialog.tariffRestrictions.isHistoryLimitExceeded;
		},
		showDialogStatus(): boolean
		{
			return this.messageCollection.some((message) => {
				return message.id === this.dialog.lastMessageId;
			});
		},
		showEmptyState(): boolean
		{
			return this.dialogInited && this.noMessages && this.isUser && !this.isHistoryLimitExceeded;
		},
	},
	created()
	{
		this.initCollectionManager();
		this.initObserverManager();
	},
	mounted()
	{
		this.subscribeToEvents();
	},
	beforeUnmount()
	{
		this.unsubscribeFromEvents();
	},
	methods:
	{
		subscribeToEvents(): void
		{
			EventEmitter.subscribe(EventType.dialog.onClickMessageContextMenu, this.onMessageContextMenuClick);
		},
		unsubscribeFromEvents(): void
		{
			EventEmitter.unsubscribe(EventType.dialog.onClickMessageContextMenu, this.onMessageContextMenuClick);
		},
		insertTextQuote(message: ImModelMessage): void
		{
			EventEmitter.emit(EventType.textarea.insertText, {
				text: Quote.prepareQuoteText(message),
				withNewLine: true,
				replace: false,
				dialogId: this.dialogId,
			});
		},
		insertMention(user: ImModelUser): void
		{
			EventEmitter.emit(EventType.textarea.insertMention, {
				mentionText: user.name,
				mentionReplacement: Utils.text.getMentionBbCode(user.id, user.name),
				dialogId: this.dialogId,
			});
		},
		openReplyPanel(messageId: number): void
		{
			EventEmitter.emit(EventType.textarea.replyMessage, {
				messageId,
				dialogId: this.dialogId,
			});
		},
		onAvatarClick(params: { dialogId: string, $event: PointerEvent })
		{
			const { dialogId, $event: event } = params;
			const user: ImModelUser = this.$store.getters['users/get'](dialogId);
			if (Utils.key.isAltOrOption(event))
			{
				this.insertMention(user);

				return;
			}

			const avatarMenu = new AvatarMenu();
			avatarMenu.openMenu({ user, dialog: this.dialog }, event.currentTarget);
		},
		onMessageContextMenuClick(eventData: ContextMenuEvent)
		{
			const { message, event, dialogId, bindElement } = eventData.getData();
			if (dialogId !== this.dialogId)
			{
				return;
			}

			const permissionManager = PermissionManager.getInstance();
			if (!permissionManager.canPerformActionByRole(ActionByRole.openMessageMenu, this.dialogId))
			{
				return;
			}

			if (Utils.key.isCombination(event, ['Alt+Ctrl']))
			{
				this.insertTextQuote(message);

				return;
			}

			if (Utils.key.isCmdOrCtrl(event))
			{
				this.openReplyPanel(message.id);

				return;
			}

			const context = { dialogId: this.dialogId, ...message };

			const messageMenuManager = MessageMenuManager.getInstance();

			let target = {
				left: event.clientX,
				top: event.clientY,
			};

			if (bindElement)
			{
				target = bindElement;
			}

			messageMenuManager.openMenu(context, target);
		},
		initObserverManager()
		{
			this.observer = new ObserverManager(this.dialogId);
		},
		getMessageComponentName(message: ImModelMessage): $Values<typeof MessageComponent>
		{
			return (new MessageComponentManager(message)).getName();
		},
		initCollectionManager()
		{
			this.collectionManager = new CollectionManager(this.dialogId);
		},
		getCollectionManager(): CollectionManager
		{
			return this.collectionManager;
		},
	},
	template: `
		<slot v-if="!dialogInited" name="loader">
			<DialogLoader />
		</slot>
		<FadeAnimation :duration="200">
			<div v-if="dialogInited" class="bx-im-message-list__container">
				<EmptyState v-if="showEmptyState" :dialogId="dialogId" />
				<slot name="before-messages" :getMessageComponentName="getMessageComponentName"></slot>
				<HistoryLimitBanner v-if="isHistoryLimitExceeded" :dialogId="dialogId" :noMessages="noMessages" />
				<DateGroup v-for="dateGroup in formattedCollection" :key="dateGroup.dateTitle" :item="dateGroup">
					<!-- Slot for every date group item -->
					<template #dateGroupItem="{ dateGroupItem, isMarkedBlock, isNewMessagesBlock, isAuthorBlock }">
						<MarkedMessagesBlock v-if="isMarkedBlock" data-id="newMessages" />
						<NewMessagesBlock v-else-if="isNewMessagesBlock" data-id="newMessages" />
						<AuthorGroup 
							v-else-if="isAuthorBlock" 
							:item="dateGroupItem"
							:contextDialogId="dialogId"
							@avatarClick="onAvatarClick"
						>
							<!-- Slot for every message -->
							<template #message="{ message, index }">
								<component
									v-message-observer
									:is="getMessageComponentName(message)"
									:withTitle="index === 0"
									:item="message"
									:dialogId="dialogId"
									:key="message.id"
									:data-viewed="message.viewed"
									:containerHeight="containerHeight"
								>
								</component>
							</template>
						</AuthorGroup>
					</template>
				</DateGroup>
				<DialogStatus v-if="showDialogStatus" :dialogId="dialogId" />
			</div>
		</FadeAnimation>
	`,
};
