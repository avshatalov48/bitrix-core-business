import {Store} from 'ui.vue3.vuex';
import {RestClient} from 'rest.client';

import {Core} from 'im.v2.application.core';
import {callBatch, runAction} from 'im.v2.lib.rest';
import {UserManager} from 'im.v2.lib.user';
import {Logger} from 'im.v2.lib.logger';
import {RestMethod} from 'im.v2.const';

import type {ImModelDialog} from 'im.v2.model';
import type {ImRestMessageResult, ImRestMessage} from '../../types/message';

export class LoadService
{
	static MESSAGE_REQUEST_LIMIT = 25;

	#store: Store;
	#restClient: RestClient;
	#chatId: number;
	#userManager: UserManager;

	#preparedHistoryMessages: ImRestMessage[] = [];
	#preparedUnreadMessages: ImRestMessage[] = [];
	#isLoading: boolean = false;

	constructor(chatId: number)
	{
		this.#store = Core.getStore();
		this.#restClient = Core.getRestClient();
		this.#userManager = new UserManager();

		this.#chatId = chatId;
	}

	loadUnread(): Promise
	{
		if (this.#isLoading || !this.#getDialog().hasNextPage)
		{
			return Promise.resolve(false);
		}
		Logger.warn('MessageService: loadUnread');
		const lastUnreadMessageId = this.#store.getters['messages/getLastId'](this.#chatId);
		if (!lastUnreadMessageId)
		{
			Logger.warn('MessageService: no lastUnreadMessageId, cant load unread');
			return Promise.resolve(false);
		}

		this.#isLoading = true;

		const query = {
			chatId: this.#chatId,
			filter: {
				lastId: lastUnreadMessageId
			},
			order: {
				id: 'ASC'
			}
		};
		return runAction(RestMethod.imV2ChatMessageTail, {data: query}).then(result => {
			Logger.warn('MessageService: loadUnread result', result);
			this.#preparedUnreadMessages = result.messages;

			return this.#updateModels(result);
		}).then(() => {
			this.#isLoading = false;
			return true;
		}).catch(error => {
			console.error('MessageService: loadUnread error:', error);
			this.#isLoading = false;
		});
	}

	loadHistory(): Promise
	{
		if (this.#isLoading || !this.#getDialog().hasPrevPage)
		{
			return Promise.resolve(false);
		}
		Logger.warn('MessageService: loadHistory');
		const lastHistoryMessageId = this.#store.getters['messages/getFirstId'](this.#chatId);
		if (!lastHistoryMessageId)
		{
			Logger.warn('MessageService: no lastHistoryMessageId, cant load unread');
			return Promise.resolve();
		}

		this.#isLoading = true;

		const query = {
			chatId: this.#chatId,
			filter: {
				lastId: lastHistoryMessageId
			},
			order: {
				id: 'DESC'
			}
		};
		return runAction(RestMethod.imV2ChatMessageTail, {data: query}).then(result => {
			Logger.warn('MessageService: loadHistory result', result);
			this.#preparedHistoryMessages = result.messages;
			const hasPrevPage = result.hasNextPage;
			const rawData = {...result, hasPrevPage, hasNextPage: null};

			return this.#updateModels(rawData);
		}).then(() => {
			this.#isLoading = false;
			return true;
		}).catch(error => {
			console.error('MessageService: loadHistory error:', error);
			this.#isLoading = false;
		});
	}

	hasPreparedHistoryMessages(): boolean
	{
		return this.#preparedHistoryMessages.length > 0;
	}

	drawPreparedHistoryMessages(): Promise
	{
		if (!this.hasPreparedHistoryMessages())
		{
			return Promise.resolve();
		}

		return this.#store.dispatch('messages/setChatCollection', {
			messages: this.#preparedHistoryMessages
		}).then(() => {
			this.#preparedHistoryMessages = [];

			return true;
		});
	}

	hasPreparedUnreadMessages(): boolean
	{
		return this.#preparedUnreadMessages.length > 0;
	}

	drawPreparedUnreadMessages(): Promise
	{
		if (!this.hasPreparedUnreadMessages())
		{
			return Promise.resolve();
		}

		return this.#store.dispatch('messages/setChatCollection', {
			messages: this.#preparedUnreadMessages
		}).then(() => {
			this.#preparedUnreadMessages = [];
			return true;
		});
	}

	loadContext(messageId: number): Promise
	{
		const query = {
			[RestMethod.imV2ChatMessageGetContext]: {
				id: messageId,
				range: LoadService.MESSAGE_REQUEST_LIMIT
			},
			[RestMethod.imV2ChatMessageRead]: {
				chatId: this.#chatId,
				ids: [messageId]
			}
		};
		Logger.warn('MessageService: loadContext for: ', messageId);
		this.#isLoading = true;
		return callBatch(query).then(data => {
			Logger.warn('MessageService: loadContext result', data);
			return this.#handleLoadedMessages(data[RestMethod.imV2ChatMessageGetContext]);
		}).finally(() => {
			this.#isLoading = false;
		});
	}

	reloadMessageList(): Promise
	{
		Logger.warn('MessageService: loadChatOnExit for: ', this.#chatId);
		let targetMessageId = 0;
		if (this.#getDialog().markedId)
		{
			targetMessageId = this.#getDialog().markedId;
		}
		else if (this.#getDialog().savedPositionMessageId)
		{
			targetMessageId = this.#getDialog().savedPositionMessageId;
		}

		const wasInitedBefore = this.#getDialog().inited;
		this.#setDialogInited(false);
		if (targetMessageId)
		{
			return this.loadContext(targetMessageId).then(() => {
				this.#setDialogInited(true, wasInitedBefore);
			});
		}

		return this.loadInitialMessages().then(() => {
			this.#setDialogInited(true, wasInitedBefore);
		});
	}

	loadInitialMessages(): Promise
	{
		Logger.warn('MessageService: loadInitialMessages for: ', this.#chatId);
		this.#isLoading = true;
		return this.#restClient.callMethod(RestMethod.imV2ChatMessageList, {
			chatId: this.#chatId,
			limit: LoadService.MESSAGE_REQUEST_LIMIT
		}).then((result) => {
			Logger.warn('MessageService: loadInitialMessages result', result.data());
			return this.#handleLoadedMessages(result.data());
		}).then(() => {
			this.#isLoading = false;
			return true;
		}).catch(error => {
			console.error('MessageService: loadInitialMessages error:', error);
			this.#isLoading = false;
		});
	}

	isLoading(): boolean
	{
		return this.#isLoading;
	}

	#handleLoadedMessages(restResult): Promise
	{
		const {messages} = restResult;
		const messagesPromise = this.#store.dispatch('messages/setChatCollection', {
			messages,
			clearCollection: true
		});
		const updateModelsPromise = this.#updateModels(restResult);

		return Promise.all([messagesPromise, updateModelsPromise]);
	}

	#updateModels(rawData: ImRestMessageResult): Promise
	{
		const {files, users, usersShort, reactions, hasPrevPage, hasNextPage} = rawData;

		const dialogPromise = this.#store.dispatch('dialogues/update', {
			dialogId: this.#getDialog().dialogId,
			fields: {
				hasPrevPage,
				hasNextPage
			}
		});
		const usersPromise = Promise.all([
			this.#userManager.setUsersToModel(users),
			this.#userManager.addUsersToModel(usersShort)
		]);
		const filesPromise = this.#store.dispatch('files/set', files);
		const reactionsPromise = this.#store.dispatch('messages/reactions/set', reactions);

		return Promise.all([
			dialogPromise, filesPromise, usersPromise, reactionsPromise
		]);
	}

	#setDialogInited(flag: boolean, wasInitedBefore: boolean = true)
	{
		const fields = {
			inited: flag,
			loading: !flag
		};
		if (flag === true && !wasInitedBefore)
		{
			delete fields.inited;
		}

		this.#store.dispatch('dialogues/update', {
			dialogId: this.#getDialog().dialogId,
			fields
		});
	}

	#getDialog(): ImModelDialog
	{
		return this.#store.getters['dialogues/getByChatId'](this.#chatId);
	}
}