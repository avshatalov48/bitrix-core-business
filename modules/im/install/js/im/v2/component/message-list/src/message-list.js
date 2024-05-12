import { BaseEvent, EventEmitter } from 'main.core.events';

import { Core } from 'im.v2.application.core';
import { ChatType, EventType, MessageComponent } from 'im.v2.const';
import { Utils } from 'im.v2.lib.utils';
import { DialogStatus } from 'im.v2.component.elements';

import { DialogLoader } from './components/dialog-loader';
import { MessageComponentManager } from './classes/message-component-manager';
import { AvatarMenu } from './classes/avatar-menu';
import { MessageMenu } from './classes/message-menu';

import { DateGroup } from './components/block/date-group';
import { AuthorGroup } from './components/block/author-group';
import { NewMessagesBlock } from './components/block/new-messages';
import { MarkedMessagesBlock } from './components/block/marked-messages';
import { EmptyState } from './components/empty-state';
import { CollectionManager, type FormattedCollectionItem } from './classes/collection-manager';
import { messageComponents } from './utils/message-components';

import './css/message-list.css';

import type { JsonObject } from 'main.core';
import type { BitrixVueComponentProps } from 'ui.vue3';
import type { ImModelChat, ImModelMessage, ImModelUser } from 'im.v2.model';

// @vue/component
export const MessageList = {
	name: 'MessageList',
	directives: {
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
	components: {
		DateGroup,
		AuthorGroup,
		NewMessagesBlock,
		MarkedMessagesBlock,
		DialogStatus,
		DialogLoader,
		EmptyState,
		...messageComponents,
	},
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
		messages: {
			type: Array,
			required: true,
		},
		observer: {
			type: Object,
			required: true,
		},
	},
	emits: ['showQuoteButton'],
	data(): JsonObject
	{
		return {
			messageMenuIsActiveForId: 0,
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
		isUser(): boolean
		{
			return this.dialog.type === ChatType.user;
		},
		dialogInited(): boolean
		{
			return this.dialog.inited;
		},
		messageCollection(): ImModelMessage[]
		{
			return this.messages;
		},
		formattedCollection(): FormattedCollectionItem[]
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
		showDialogStatus(): boolean
		{
			return this.messageCollection.some((message) => {
				return message.id === this.dialog.lastMessageId;
			});
		},
		statusComponent(): BitrixVueComponentProps
		{
			return DialogStatus;
		},
	},
	created()
	{
		this.initContextMenu();
		this.initCollectionManager();
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
		needToShowAvatarMenuFor(user: ImModelUser): boolean
		{
			if (!user)
			{
				return false;
			}

			const isCurrentUser = user.id === Core.getUserId();
			const isBotChat = this.isUser && this.user.bot === true;

			return !isCurrentUser && !isBotChat;
		},
		subscribeToEvents(): void
		{
			EventEmitter.subscribe(EventType.dialog.onClickMessageContextMenu, this.onMessageContextMenuClick);
		},
		unsubscribeFromEvents(): void
		{
			EventEmitter.unsubscribe(EventType.dialog.onClickMessageContextMenu, this.onMessageContextMenuClick);
		},
		initCollectionManager()
		{
			this.getCollectionManager();
		},
		initContextMenu()
		{
			this.messageMenu = new MessageMenu();
			this.messageMenu.subscribe(MessageMenu.events.onCloseMenu, () => {
				this.messageMenuIsActiveForId = 0;
			});

			this.avatarMenu = new AvatarMenu();
		},
		onAvatarClick(params: { dialogId: string, $event: PointerEvent })
		{
			const { dialogId, $event: event } = params;
			const user: ImModelUser = this.$store.getters['users/get'](dialogId);
			if (!this.needToShowAvatarMenuFor(user))
			{
				return;
			}

			if (Utils.key.isAltOrOption(event))
			{
				EventEmitter.emit(EventType.textarea.insertMention, {
					mentionText: user.name,
					mentionReplacement: Utils.text.getMentionBbCode(user.id, user.name),
				});

				return;
			}

			this.avatarMenu.openMenu({ user, dialog: this.dialog }, event.currentTarget);
		},
		onMessageContextMenuClick(eventData: BaseEvent<{ message: ImModelMessage, event: PointerEvent }>)
		{
			const { message, event } = eventData.getData();

			const context = { dialogId: this.dialogId, ...message };
			this.messageMenu.openMenu(context, event.currentTarget);
			this.messageMenuIsActiveForId = message.id;
		},
		async onMessageMouseUp(message: ImModelMessage, event: MouseEvent)
		{
			await Utils.browser.waitForSelectionToUpdate();
			const selection = window.getSelection().toString().trim();
			if (selection.length === 0 || this.isGuest)
			{
				return;
			}

			this.$emit('showQuoteButton', message, event);
		},
		getMessageComponentName(message: ImModelMessage): $Values<typeof MessageComponent>
		{
			return (new MessageComponentManager(message)).getName();
		},
		getCollectionManager(): CollectionManager
		{
			if (!this.collectionManager)
			{
				this.collectionManager = new CollectionManager(this.dialogId);
			}

			return this.collectionManager;
		},
	},
	template: `
		<div class="bx-im-message-list__container">
			<DialogLoader v-if="!dialogInited" :fullHeight="noMessages" />
			<EmptyState v-else-if="noMessages && isUser" />
			<DateGroup v-for="dateGroup in formattedCollection" :key="dateGroup.date.id" :item="dateGroup">
				<!-- Slot for every date group item -->
				<template #dateGroupItem="{ dateGroupItem, isMarkedBlock, isNewMessagesBlock, isAuthorBlock }">
					<MarkedMessagesBlock v-if="isMarkedBlock" data-id="newMessages" />
					<NewMessagesBlock v-else-if="isNewMessagesBlock" data-id="newMessages" />
					<AuthorGroup v-else-if="isAuthorBlock" :item="dateGroupItem" @avatarClick="onAvatarClick">
						<!-- Slot for every message -->
						<template #message="{ message, index }">
							<component
								v-message-observer
								:is="getMessageComponentName(message)"
								:withTitle="index === 0"
								:item="message"
								:dialogId="dialogId"
								:key="message.id"
								:menuIsActiveForId="messageMenuIsActiveForId"
								:data-viewed="message.viewed"
								@mouseup="onMessageMouseUp(message, $event)"
							>
							</component>
						</template>
					</AuthorGroup>
				</template>
			</DateGroup>
			<component :is="statusComponent" v-if="showDialogStatus" :dialogId="dialogId" />
		</div>
	`,
};
