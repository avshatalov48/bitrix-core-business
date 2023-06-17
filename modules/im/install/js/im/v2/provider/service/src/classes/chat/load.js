import {Type} from 'main.core';
import {Store} from 'ui.vue3.vuex';

import {Core} from 'im.v2.application.core';
import {RestMethod} from 'im.v2.const';
import {callBatch} from 'im.v2.lib.rest';
import {MessageService} from 'im.v2.provider.service';
import {UserManager} from 'im.v2.lib.user';

import {ChatDataExtractor} from '../chat-data-extractor';

export class LoadService
{
	#store: Store;

	constructor()
	{
		this.#store = Core.getStore();
	}

	loadChat(dialogId: string): Promise
	{
		if (!Type.isStringFilled(dialogId))
		{
			return Promise.reject(new Error('ChatService: loadChat: dialogId is not provided'));
		}

		const query = this.#prepareLoadChatQuery(dialogId);

		return this.#loadChatRequest(dialogId, query);
	}

	loadChatWithMessages(dialogId: string): Promise
	{
		if (!Type.isStringFilled(dialogId))
		{
			return Promise.reject(new Error('ChatService: loadChatWithMessages: dialogId is not provided'));
		}

		const query = this.#prepareLoadChatWithMessagesQuery(dialogId);

		return this.#loadChatRequest(dialogId, query);
	}

	loadChatWithContext(dialogId: string, messageId: number): Promise
	{
		if (!Type.isStringFilled(dialogId))
		{
			return Promise.reject(new Error('ChatService: loadChatWithContext: dialogId is not provided'));
		}

		if (!messageId || !Type.isNumber(messageId))
		{
			return Promise.reject(new Error('ChatService: loadChatWithContext: messageId is not provided'));
		}

		const query = this.#prepareLoadChatWithContextQuery(dialogId, messageId);

		return this.#loadChatRequest(dialogId, query);
	}

	#loadChatRequest(dialogId: string, query: Object): Promise
	{
		this.#store.dispatch('dialogues/update', {
			dialogId: dialogId,
			fields: {
				loading: true
			}
		});

		return callBatch(query).then(data => {
			return this.#updateModels(data);
		}).then(() => {
			return this.#store.dispatch('dialogues/update', {
				dialogId: dialogId,
				fields: {
					inited: true,
					loading: false
				}
			});
		});
	}

	#updateModels(response: Object): Promise
	{
		const extractor = new ChatDataExtractor(response);
		extractor.extractData();

		if (extractor.isOpenlinesChat())
		{
			return Promise.reject('OL chats are not supported');
		}

		const dialoguesPromise = this.#store.dispatch('dialogues/set', extractor.getDialogues());
		const filesPromise = this.#store.dispatch('files/set', extractor.getFiles());

		const userManager = new UserManager();
		const usersPromise = [
			this.#store.dispatch('users/set', extractor.getUsers()),
			userManager.addUsersToModel(extractor.getAdditionalUsers())
		];

		const messagesPromise = [
			this.#store.dispatch('messages/setChatCollection', {
				messages: extractor.getMessages(),
				clearCollection: true
			}),
			this.#store.dispatch('messages/store', extractor.getMessagesToStore()),
			this.#store.dispatch('messages/pin/setPinned', {
				chatId: extractor.getChatId(),
				pinnedMessages: extractor.getPinnedMessages()
			}),
			this.#store.dispatch('messages/reactions/set', extractor.getReactions())
		];

		return Promise.all([
			dialoguesPromise,
			filesPromise,
			Promise.all(usersPromise),
			Promise.all(messagesPromise)
		]);
	}

	#prepareLoadChatQuery(dialogId: string): Object
	{
		const query = {
			[RestMethod.imChatGet]: {dialog_id: dialogId}
		};
		const isChat = dialogId.toString().startsWith('chat');
		if (isChat)
		{
			query[RestMethod.imUserGet] = {};
		}
		else
		{
			query[RestMethod.imUserListGet] = {id: [Core.getUserId(), dialogId]};
		}

		return query;
	}

	#prepareLoadChatWithMessagesQuery(dialogId: string): Object
	{
		const query = this.#prepareLoadChatQuery(dialogId);

		query[RestMethod.imV2ChatMessageList] = {
			dialogId,
			limit: MessageService.getMessageRequestLimit()
		};
		query[RestMethod.imV2ChatPinTail] = {
			chatId: `$result[${RestMethod.imChatGet}][id]`
		};

		return query;
	}

	#prepareLoadChatWithContextQuery(dialogId: string, messageId: number): Object
	{
		const query = this.#prepareLoadChatQuery(dialogId);

		query[RestMethod.imV2ChatMessageGetContext] = {
			id: messageId,
			range: MessageService.getMessageRequestLimit()
		};
		query[RestMethod.imV2ChatMessageRead] = {
			dialogId,
			ids: [messageId]
		};

		return query;
	}
}