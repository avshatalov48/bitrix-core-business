import {EventEmitter} from 'main.core.events';
import {Store} from 'ui.vue3.vuex';

import {Core} from 'im.v2.application.core';
import {Logger} from 'im.v2.lib.logger';
import {UserManager} from 'im.v2.lib.user';
import {UuidManager} from 'im.v2.lib.uuid';
import {EventType, DialogScrollThreshold} from 'im.v2.const';
import {MessageService} from 'im.v2.provider.service';

import type {ImModelDialog, ImModelMessage} from 'im.v2.model';

import type {
	MessageAddParams,
	MessageUpdateParams,
	MessageDeleteParams,
	ReadMessageParams,
	ReadMessageOpponentParams,
	PinAddParams,
	PinDeleteParams,
	AddReactionParams,
	DeleteReactionParams
} from '../types/message';
import type {PullExtraParams, RawFile, RawUser, RawMessage} from '../types/common';

export class MessagePullHandler
{
	#store: Store;

	constructor()
	{
		this.#store = Core.getStore();
	}

	handleMessageAdd(params: MessageAddParams)
	{
		Logger.warn('MessagePullHandler: handleMessageAdd', params);
		this.#setMessageChat(params);
		this.#setUsers(params);
		this.#setFiles(params);

		const messageWithTemplateId = this.#store.getters['messages/isInChatCollection']({
			messageId: params.message.templateId
		});

		const messageWithRealId = this.#store.getters['messages/isInChatCollection']({
			messageId: params.message.id
		});

		// update message with parsed link info
		if (messageWithRealId)
		{
			Logger.warn('New message pull handler: we already have this message', params.message);
			this.#store.dispatch('messages/update', {
				id: params.message.id,
				fields: params.message
			});
			this.#sendScrollEvent(params.chatId);
		}
		else if (!messageWithRealId && messageWithTemplateId)
		{
			Logger.warn('New message pull handler: we already have the TEMPORARY message', params.message);
			this.#store.dispatch('messages/updateWithId', {
				id: params.message.templateId,
				fields: params.message
			});
		}
		// it's an opponent message or our own message from somewhere else
		else if (!messageWithRealId && !messageWithTemplateId)
		{
			Logger.warn('New message pull handler: we dont have this message', params.message);
			this.#handleAddingMessageToModel(params);
		}

		//stop writing event
		this.#store.dispatch('dialogues/stopWriting', {
			dialogId: params.dialogId,
			userId: params.message.senderId
		});

		this.#updateDialog(params);
	}

	handleMessageUpdate(params: MessageUpdateParams)
	{
		Logger.warn('MessagePullHandler: handleMessageUpdate', params);
		this.#store.dispatch('dialogues/stopWriting', {
			dialogId: params.dialogId,
			userId: params.senderId
		});
		this.#store.dispatch('messages/update', {
			id: params.id,
			fields: {
				text: params.text,
				params: params.params
			}
		});
		this.#sendScrollEvent(params.chatId);
	}

	handleMessageDelete(params: MessageDeleteParams)
	{
		Logger.warn('MessagePullHandler: handleMessageDelete', params);
		this.#store.dispatch('dialogues/stopWriting', {
			dialogId: params.dialogId,
			userId: params.senderId
		});
		this.#store.dispatch('messages/update', {
			id: params.id,
			fields: {
				params: params.params,
			}
		});
	}

	handleMessageDeleteComplete(params)
	{
		Logger.warn('MessagePullHandler: handleMessageDeleteComplete', params);
		// this.store.dispatch('messages/delete', {
		// 	id: params.id,
		// 	chatId: params.chatId,
		// });

		// this.store.dispatch('dialogues/stopWriting', {
		// 	dialogId: params.dialogId,
		// 	userId: params.senderId
		// });
	}

	handleAddReaction(params: AddReactionParams)
	{
		Logger.warn('MessagePullHandler: handleAddReaction', params);
		const {actualReactions: {reaction: actualReactionsState, usersShort}, userId, reaction} = params;
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
		const {actualReactions: {reaction: actualReactionsState}} = params;
		this.#store.dispatch('messages/reactions/set', [actualReactionsState]);
	}

	handleMessageParamsUpdate(params)
	{
		Logger.warn('MessagePullHandler: handleMessageParamsUpdate', params);
		// this.store.dispatch('messages/update', {
		// 	id: params.id,
		// 	chatId: params.chatId,
		// 	fields: {params: params.params}
		// }).then(() => {
		// 	EventEmitter.emit(EventType.dialog.scrollToBottom, {chatId: params.chatId, cancelIfScrollChange: true});
		// });
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
			messageIds: params.viewedMessages
		}).then(() => {
			this.#store.dispatch('dialogues/update', {
				dialogId: params.dialogId,
				fields: {
					counter: params.counter,
					lastId: params.lastId
				}
			});
		});
	}

	handleReadMessageOpponent(params: ReadMessageOpponentParams)
	{
		Logger.warn('MessagePullHandler: handleReadMessageOpponent', params);
		this.#updateMessageViewedByOthers(params);
		this.#updateChatLastMessageViews(params);
	}

	handlePinAdd(params: PinAddParams)
	{
		Logger.warn('MessagePullHandler: handlePinAdd', params);
		this.#setFiles(params);
		this.#setUsers(params);
		this.#store.dispatch('messages/store', params.link.message);
		this.#store.dispatch('messages/pin/add', {
			chatId: params.link.chatId,
			messageId: params.link.messageId
		});
		if (Core.getUserId() !== params.link.authorId)
		{
			// this.#sendScrollEvent(params.link.chatId);
		}
	}

	handlePinDelete(params: PinDeleteParams)
	{
		Logger.warn('MessagePullHandler: handlePinDelete', params);
		this.#store.dispatch('messages/pin/delete', {
			chatId: params.chatId,
			messageId: params.messageId
		});
	}

	// helpers
	#setMessageChat(params: MessageAddParams)
	{
		if (!params?.chat[params.chatId])
		{
			return;
		}

		const chatToAdd = {...params.chat[params.chatId], dialogId: params.dialogId};
		this.#store.dispatch('dialogues/set', chatToAdd);
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
			const templateFileIdExists = this.#store.getters['files/isInCollection']({
				fileId: params.message?.templateFileId
			});
			if (templateFileIdExists)
			{
				this.#store.dispatch('files/updateWithId', {
					id: params.message?.templateFileId,
					fields: file
				});
			}
			else
			{
				this.#store.dispatch('files/set', file);
			}
		});
	}

	#handleAddingMessageToModel(params: MessageAddParams)
	{
		const dialog = this.#getDialog(params.dialogId, true);
		if (dialog.inited && dialog.hasNextPage)
		{
			return;
		}

		const chatIsOpened = this.#store.getters['application/isChatOpen'](params.dialogId);
		const unreadMessages: ImModelMessage[] = this.#store.getters['messages/getChatUnreadMessages'](params.chatId);
		if (!chatIsOpened && unreadMessages.length > MessageService.getMessageRequestLimit())
		{
			const messageService = new MessageService({chatId: params.chatId});
			messageService.reloadMessageList();
			return;
		}

		this.#addMessageToModel(params.message);
		this.#sendScrollEvent(params.chatId);
	}

	#addMessageToModel(message)
	{
		const newMessage = {...message};
		if (message.senderId !== Core.getUserId())
		{
			newMessage.unread = true;
			newMessage.viewed = false;
		}
		else
		{
			newMessage.unread = false;
		}
		this.#store.dispatch('messages/setChatCollection', {messages: [newMessage]});
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
		else
		{
			dialogFieldsToUpdate.counter = params.counter;
		}
		this.#store.dispatch('dialogues/update', {
			dialogId: params.dialogId,
			fields: dialogFieldsToUpdate
		});
		this.#store.dispatch('dialogues/clearLastMessageViews', {
			dialogId: params.dialogId
		});
	}

	#updateMessageViewedByOthers(params: ReadMessageOpponentParams)
	{
		this.#store.dispatch('messages/setViewedByOthers', {ids: params.viewedMessages});
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

		const hasFirstViewer = !!dialog.lastMessageViews.firstViewer;
		if (hasFirstViewer)
		{
			this.#store.dispatch('dialogues/incrementLastMessageViews', {
				dialogId: params.dialogId
			});

			return;
		}

		this.#store.dispatch('dialogues/setLastMessageViews', {
			dialogId: params.dialogId,
			fields: {
				userId: params.userId,
				userName: params.userName,
				date: params.date,
				messageId: dialog.lastMessageId
			}
		});
	}

	#sendScrollEvent(chatId: number)
	{
		EventEmitter.emit(EventType.dialog.scrollToBottom, {chatId, threshold: DialogScrollThreshold.nearTheBottom});
	}

	#getDialog(dialogId: string, temporary: boolean = false): ?ImModelDialog
	{
		return this.#store.getters['dialogues/get'](dialogId, temporary);
	}
}