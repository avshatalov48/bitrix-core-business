import { EventEmitter } from 'main.core.events';
import { CopilotManager } from 'im.v2.lib.copilot';
import { Core } from 'im.v2.application.core';
import { Logger } from 'im.v2.lib.logger';
import { UserManager } from 'im.v2.lib.user';
import { UuidManager } from 'im.v2.lib.uuid';
import { InputActionListener } from 'im.v2.lib.input-action';
import { EventType, DialogScrollThreshold, UserRole, ChatType } from 'im.v2.const';
import { MessageService } from 'im.v2.provider.service.message';

import { MessageDeleteManager } from './classes/message-delete-manager';

import type { Store } from 'ui.vue3.vuex';
import type { ImModelChat, ImModelMessage } from 'im.v2.model';

import type {
	MessageAddParams,
	MessageUpdateParams,
	MessageDeleteParams,
	MessageDeleteCompleteParams,
	MultipleMessageDeleteParams,
	ReadMessageParams,
	ReadMessageOpponentParams,
	PinAddParams,
	PinDeleteParams,
	AddReactionParams,
	DeleteReactionParams,
	MessageDeleteCompletePreparedParams,
	PrepareDeleteMessageParams,
} from '../../types/message';
import type { PullExtraParams, RawFile, RawUser, RawMessage, RawChat } from '../../types/common';

type UserId = number;

export class MessagePullHandler
{
	#store: Store;
	#messageViews: {[messageId: string]: Set<UserId>} = {};
	#messageDeleteManager: MessageDeleteManager;

	constructor()
	{
		this.#store = Core.getStore();
		this.#messageDeleteManager = new MessageDeleteManager();
	}

	handleMessageAdd(params: MessageAddParams)
	{
		Logger.warn('MessagePullHandler: handleMessageAdd', params);
		this.#setMessageChat(params);
		this.#setUsers(params);
		this.#setFiles(params);
		this.#setAdditionalEntities(params);
		this.#setCommentInfo(params);
		this.#setCopilotData(params);
		this.#setMessagesAutoDeleteConfig(params);

		const messageWithTemplateId = this.#store.getters['messages/isInChatCollection']({
			messageId: params.message.templateId,
		});

		const messageWithRealId = this.#store.getters['messages/isInChatCollection']({
			messageId: params.message.id,
		});

		// update message with parsed link info
		if (messageWithRealId)
		{
			Logger.warn('New message pull handler: we already have this message', params.message);
			void this.#store.dispatch('messages/update', {
				id: params.message.id,
				fields: { ...params.message, error: false },
			});
			this.#sendScrollEvent(params.chatId);
		}
		else if (!messageWithRealId && messageWithTemplateId)
		{
			Logger.warn('New message pull handler: we already have the TEMPORARY message', params.message);
			void this.#store.dispatch('messages/updateWithId', {
				id: params.message.templateId,
				fields: { ...params.message, error: false },
			});
		}
		// it's an opponent message or our own message from somewhere else
		else if (!messageWithRealId && !messageWithTemplateId)
		{
			const hasLoadingMessage: boolean = this.#store.getters['messages/hasLoadingMessageByMessageId'](
				params.message.templateId,
			);
			if (hasLoadingMessage)
			{
				void this.#store.dispatch('messages/delete', {
					id: params.message.templateId,
				});
			}

			Logger.warn('New message pull handler: we dont have this message', params.message);
			this.#handleAddingMessageToModel(params);
		}

		InputActionListener.getInstance().stopAction({
			userId: params.message.senderId,
			dialogId: params.dialogId,
		});

		this.#updateDialog(params);
	}

	handleMessageUpdate(params: MessageUpdateParams)
	{
		Logger.warn('MessagePullHandler: handleMessageUpdate', params);
		InputActionListener.getInstance().stopAction({
			userId: params.senderId,
			dialogId: params.dialogId,
		});
		this.#store.dispatch('messages/update', {
			id: params.id,
			fields: {
				text: params.text,
				params: params.params,
			},
		});
		this.#sendScrollEvent(params.chatId);
	}

	handleMessageDeleteV2(params: MultipleMessageDeleteParams)
	{
		Logger.warn('MessageDeletePullHandler: handleMultipleMessageDelete', params);

		const messages = params.messages;

		messages.forEach((message) => {
			if (message.completelyDeleted)
			{
				const preparedParams = this.#prepareDeleteMessageParams(params, true, message);
				this.#messageDeleteManager.deleteMessageComplete(preparedParams);

				return;
			}

			const preparedParams = this.#prepareDeleteMessageParams(params, false, message);
			this.#messageDeleteManager.deleteMessage(preparedParams);
		});
	}

	handleMessageDelete(params: MessageDeleteParams)
	{
		Logger.warn('MessageDeletePullHandler: handleMessageDelete', params);
		const preparedParams = this.#prepareDeleteMessageParams(params);
		this.#messageDeleteManager.deleteMessage(preparedParams);
	}

	handleMessageDeleteComplete(params: MessageDeleteCompleteParams)
	{
		Logger.warn('MessageDeletePullHandler: handleMessageDeleteComplete', params);

		const preparedParams = this.#prepareDeleteMessageParams(params, true);
		this.#messageDeleteManager.deleteMessageComplete(preparedParams);
	}

	handleAddReaction(params: AddReactionParams)
	{
		Logger.warn('MessagePullHandler: handleAddReaction', params);
		const {
			actualReactions: { reaction: actualReactionsState, usersShort },
			userId,
			reaction,
		} = params;
		if (Core.getUserId() === userId)
		{
			actualReactionsState.ownReactions = [reaction];
		}

		const userManager = new UserManager();
		userManager.addUsersToModel(usersShort);

		this.#store.dispatch('messages/reactions/set', [actualReactionsState]);
	}

	handleDeleteReaction(params: DeleteReactionParams)
	{
		Logger.warn('MessagePullHandler: handleDeleteReaction', params);
		const { actualReactions: { reaction: actualReactionsState } } = params;
		this.#store.dispatch('messages/reactions/set', [actualReactionsState]);
	}

	handleMessageParamsUpdate(params)
	{
		Logger.warn('MessagePullHandler: handleMessageParamsUpdate', params);

		this.#store.dispatch('messages/update', {
			id: params.id,
			chatId: params.chatId,
			fields: { params: params.params },
		});
	}

	handleReadMessage(params: ReadMessageParams, extra: PullExtraParams)
	{
		Logger.warn('MessagePullHandler: handleReadMessage', params);
		const uuidManager = UuidManager.getInstance();
		if (uuidManager.hasActionUuid(extra.action_uuid))
		{
			Logger.warn('MessagePullHandler: handleReadMessage: we have this uuid, skip');
			uuidManager.removeActionUuid(extra.action_uuid);

			return;
		}

		this.#store.dispatch('messages/readMessages', {
			chatId: params.chatId,
			messageIds: params.viewedMessages,
		}).then(() => {
			this.#store.dispatch('chats/update', {
				dialogId: params.dialogId,
				fields: {
					counter: params.counter,
					lastId: params.lastId,
				},
			});
		}).catch((error) => {
			// eslint-disable-next-line no-console
			console.error('MessagePullHandler: error handling readMessage', error);
		});
	}

	handleReadMessageOpponent(params: ReadMessageOpponentParams)
	{
		if (params.userId === Core.getUserId())
		{
			return;
		}
		Logger.warn('MessagePullHandler: handleReadMessageOpponent', params);
		this.#updateMessageViewedByOthers(params);
		this.#updateChatLastMessageViews(params);
	}

	handlePinAdd(params: PinAddParams)
	{
		Logger.warn('MessagePullHandler: handlePinAdd', params);
		this.#setFiles(params);
		this.#setUsers(params);
		this.#store.dispatch('messages/store', params.additionalMessages);
		this.#store.dispatch('messages/pin/add', {
			chatId: params.pin.chatId,
			messageId: params.pin.messageId,
		});
		if (Core.getUserId() !== params.pin.authorId)
		{
			// this.#sendScrollEvent(params.link.chatId);
		}
	}

	handlePinDelete(params: PinDeleteParams)
	{
		Logger.warn('MessagePullHandler: handlePinDelete', params);
		this.#store.dispatch('messages/pin/delete', {
			chatId: params.chatId,
			messageId: params.messageId,
		});
	}

	// helpers
	#setMessageChat(params: MessageAddParams)
	{
		const chat = params.chat?.[params.chatId];
		if (!chat)
		{
			return;
		}

		const chatToAdd = { ...params.chat[params.chatId], dialogId: params.dialogId };
		const dialogExists = Boolean(this.#getDialog(params.dialogId));
		const messageWithoutNotification = !params.notify || params.message?.params?.NOTIFY === 'N';
		if (!dialogExists && !messageWithoutNotification && !chatToAdd.role)
		{
			chatToAdd.role = UserRole.member;
		}
		this.#store.dispatch('chats/set', chatToAdd);
	}

	#setUsers(params: {users: {[userId: string]: RawUser} | []})
	{
		if (!params.users)
		{
			return;
		}

		const userManager = new UserManager();
		userManager.setUsersToModel(Object.values(params.users));
	}

	#setFiles(params: {files: {[fileId: string]: RawFile} | [], message?: RawMessage})
	{
		if (!params.files)
		{
			return;
		}

		const files = Object.values(params.files);
		files.forEach((file: RawFile) => {
			void this.#store.dispatch('files/set', file);
		});
	}

	#setAdditionalEntities(params: MessageAddParams): void
	{
		if (!params.message.additionalEntities)
		{
			return;
		}

		const {
			additionalMessages,
			messages,
			files,
			users,
		} = params.message.additionalEntities;
		const newMessages = [...messages, ...additionalMessages];
		this.#store.dispatch('messages/store', newMessages);
		this.#store.dispatch('files/set', files);
		this.#store.dispatch('users/set', users);
	}

	#setCommentInfo(params: MessageAddParams): void
	{
		const chat: RawChat = params.chat?.[params.chatId];
		if (!chat || chat.type !== ChatType.comment)
		{
			return;
		}

		this.#store.dispatch('messages/comments/set', {
			messageId: chat.parent_message_id,
			chatId: params.chatId,
			messageCount: chat.message_count,
		});
		this.#store.dispatch('messages/comments/setLastUser', {
			messageId: chat.parent_message_id,
			newUserId: params.message.senderId,
		});
	}

	#handleAddingMessageToModel(params: MessageAddParams)
	{
		const dialog = this.#getDialog(params.dialogId, true);
		if (dialog.hasNextPage)
		{
			this.#store.dispatch('messages/store', params.message);

			return;
		}

		const chatIsOpened = this.#store.getters['application/isChatOpen'](params.dialogId);
		const unreadMessages: ImModelMessage[] = this.#store.getters['messages/getChatUnreadMessages'](params.chatId);
		const RELOAD_LIMIT = MessageService.getMessageRequestLimit() * 5;
		if (dialog.inited && !chatIsOpened && unreadMessages.length > RELOAD_LIMIT)
		{
			void this.#store.dispatch('messages/store', params.message);
			const messageService = new MessageService({ chatId: params.chatId });
			messageService.reloadMessageList();

			return;
		}

		this.#addMessageToModel(params.message);
		this.#sendScrollEvent(params.chatId);
	}

	#addMessageToModel(message)
	{
		const newMessage = { ...message };
		if (message.senderId === Core.getUserId())
		{
			newMessage.unread = false;
		}
		else
		{
			newMessage.unread = true;
			newMessage.viewed = false;
		}
		this.#store.dispatch('messages/setChatCollection', { messages: [newMessage] });
	}

	#updateDialog(params)
	{
		const dialog = this.#getDialog(params.dialogId, true);

		const dialogFieldsToUpdate = {};
		if (params.message.id > dialog.lastMessageId)
		{
			dialogFieldsToUpdate.lastMessageId = params.message.id;
		}

		if (params.message.senderId === Core.getUserId() && params.message.id > dialog.lastReadId)
		{
			dialogFieldsToUpdate.lastId = params.message.id;
		}

		dialogFieldsToUpdate.counter = params.counter;

		this.#store.dispatch('chats/update', {
			dialogId: params.dialogId,
			fields: dialogFieldsToUpdate,
		});
		this.#store.dispatch('chats/clearLastMessageViews', {
			dialogId: params.dialogId,
		});
	}

	#updateMessageViewedByOthers(params: ReadMessageOpponentParams)
	{
		this.#store.dispatch('messages/setViewedByOthers', { ids: params.viewedMessages });
	}

	#updateChatLastMessageViews(params: ReadMessageOpponentParams)
	{
		const dialog = this.#getDialog(params.dialogId);
		if (!dialog)
		{
			return;
		}

		const isLastMessage = params.viewedMessages.includes(dialog.lastMessageId);
		if (!isLastMessage)
		{
			return;
		}

		if (this.#checkMessageViewsRegistry(params.userId, dialog.lastMessageId))
		{
			return;
		}

		const hasFirstViewer = Boolean(dialog.lastMessageViews.firstViewer);
		if (hasFirstViewer)
		{
			this.#store.dispatch('chats/incrementLastMessageViews', {
				dialogId: params.dialogId,
			});
			this.#updateMessageViewsRegistry(params.userId, dialog.lastMessageId);

			return;
		}

		this.#store.dispatch('chats/setLastMessageViews', {
			dialogId: params.dialogId,
			fields: {
				userId: params.userId,
				userName: params.userName,
				date: params.date,
				messageId: dialog.lastMessageId,
			},
		});

		this.#updateMessageViewsRegistry(params.userId, dialog.lastMessageId);
	}

	#checkMessageViewsRegistry(userId: number, messageId: number): boolean
	{
		return Boolean(this.#messageViews[messageId]?.has(userId));
	}

	#updateMessageViewsRegistry(userId: number, messageId: number): void
	{
		if (!this.#messageViews[messageId])
		{
			this.#messageViews[messageId] = new Set();
		}

		this.#messageViews[messageId].add(userId);
	}

	#sendScrollEvent(chatId: number)
	{
		EventEmitter.emit(EventType.dialog.scrollToBottom, { chatId, threshold: DialogScrollThreshold.nearTheBottom });
	}

	#getDialog(dialogId: string, temporary: boolean = false): ?ImModelChat
	{
		return this.#store.getters['chats/get'](dialogId, temporary);
	}

	#setCopilotData(params)
	{
		if (!params.copilot)
		{
			return;
		}

		const copilotManager = new CopilotManager();
		void copilotManager.handleMessageAdd(params.copilot);
	}

	#setMessagesAutoDeleteConfig(params: MessageAddParams)
	{
		const { messagesAutoDeleteConfigs } = params;
		void this.#store.dispatch('chats/autoDelete/set', messagesAutoDeleteConfigs);
	}

	#prepareDeleteMessageParams(
		params: PrepareDeleteMessageParams,
		isComplete = false,
		message = null,
	): MessageDeleteCompletePreparedParams
	{
		const baseParams = {
			id: message ? message.id : params.id,
			senderId: message ? message.senderId : params.senderId,
			dialogId: params.dialogId,
		};

		if (isComplete)
		{
			return {
				...baseParams,
				newLastMessage: params.newLastMessage,
				lastMessageViews: params.lastMessageViews,
				counter: params.counter,
			};
		}

		return baseParams;
	}
}
