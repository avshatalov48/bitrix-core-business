import { Loc } from 'main.core';
import { Store } from 'ui.vue3.vuex';

import { Core } from 'im.v2.application.core';
import { callBatch, runAction } from 'im.v2.lib.rest';
import { UserManager } from 'im.v2.lib.user';
import { Logger } from 'im.v2.lib.logger';
import { RestMethod } from 'im.v2.const';
import { CopilotManager } from 'im.v2.lib.copilot';
import { Analytics } from 'im.v2.lib.analytics';
import { AccessErrorCode } from 'im.v2.lib.access';

import type { ImModelChat, ImModelMessage } from 'im.v2.model';
import type { PaginationRestResult } from '../types/message';
import type { RawMessage, RawCommentInfo, RawTariffRestrictions } from '../../types/rest';

export class LoadService
{
	static MESSAGE_REQUEST_LIMIT = 25;

	#store: Store;
	#chatId: number;
	#userManager: UserManager;

	#preparedHistoryMessages: RawMessage[] = [];
	#preparedUnreadMessages: RawMessage[] = [];
	#isLoading: boolean = false;

	constructor(chatId: number)
	{
		this.#store = Core.getStore();
		this.#userManager = new UserManager();

		this.#chatId = chatId;
	}

	async loadUnread(): Promise
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
			filter: { lastId: lastUnreadMessageId },
			order: { id: 'ASC' },
			limit: LoadService.MESSAGE_REQUEST_LIMIT,
		};

		const result: PaginationRestResult = await runAction(RestMethod.imV2ChatMessageTail, { data: query })
			.catch((error) => {
				// eslint-disable-next-line no-console
				console.error('MessageService: loadUnread error:', error);
				this.#isLoading = false;
			});

		Logger.warn('MessageService: loadUnread result', result);
		this.#preparedUnreadMessages = result.messages;

		const rawData: PaginationRestResult = {
			...result,
			tariffRestrictions: this.#prepareTariffRestrictions(result.tariffRestrictions),
		};
		await this.#updateModels(rawData);

		this.#isLoading = false;

		return Promise.resolve();
	}

	async loadHistory(): Promise
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
			filter: { lastId: lastHistoryMessageId },
			order: { id: 'DESC' },
			limit: LoadService.MESSAGE_REQUEST_LIMIT,
		};

		const result: PaginationRestResult = await runAction(RestMethod.imV2ChatMessageTail, { data: query })
			.catch((error) => {
				// eslint-disable-next-line no-console
				console.error('MessageService: loadHistory error:', error);
				this.#isLoading = false;
			});

		Logger.warn('MessageService: loadHistory result', result);
		this.#preparedHistoryMessages = result.messages;

		const hasPrevPage = result.hasNextPage;
		const rawData = { ...result, hasPrevPage, hasNextPage: null };
		await this.#updateModels(rawData);

		this.#isLoading = false;

		return Promise.resolve();
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
			messages: this.#preparedHistoryMessages,
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
			messages: this.#preparedUnreadMessages,
		}).then(() => {
			this.#preparedUnreadMessages = [];

			return true;
		});
	}

	async loadFirstPage(): Promise
	{
		Logger.warn('MessageService: loadFirstPage for: ', this.#chatId);
		this.#isLoading = true;

		const payload = {
			data: {
				chatId: this.#chatId,
				limit: LoadService.MESSAGE_REQUEST_LIMIT,
				order: { id: 'ASC' },
			},
		};
		const restResult = await runAction(RestMethod.imV2ChatMessageTail, payload)
			.catch((error) => {
				// eslint-disable-next-line no-console
				console.error('MessageService: loadFirstPage error:', error);
				this.#isLoading = false;
				throw new Error(error);
			});
		Logger.warn('MessageService: loadFirstPage result', restResult);

		await this.#handleLoadedMessages(restResult);
		await this.#store.dispatch('chats/update', {
			dialogId: this.#getDialog().dialogId,
			fields: {
				hasPrevPage: false,
				hasNextPage: restResult.hasNextPage,
			},
		});
		this.#isLoading = false;
	}

	loadContext(messageId: number): Promise
	{
		const query = {
			[RestMethod.imV2ChatMessageGetContext]: {
				id: messageId,
				range: LoadService.MESSAGE_REQUEST_LIMIT,
			},
			[RestMethod.imV2ChatMessageRead]: {
				chatId: this.#chatId,
				ids: [messageId],
			},
		};
		Logger.warn('MessageService: loadContext for: ', messageId);
		this.#isLoading = true;

		return callBatch(query)
			.then((data) => {
				Logger.warn('MessageService: loadContext result', data);

				return this.#handleLoadedMessages(data[RestMethod.imV2ChatMessageGetContext]);
			})
			.catch((error) => {
				this.#sendAnalytics(error);
				this.#handleLoadContextError(error);
				// eslint-disable-next-line no-console
				console.error('MessageService: loadContext error:', error);
				throw new Error(error);
			})
			.finally(() => {
				this.#isLoading = false;
			});
	}

	async loadContextByChatId(chatId: number): Promise<number>
	{
		const queryParams = {
			data: { commentChatId: chatId },
		};
		const result = await runAction(RestMethod.imV2ChatMessageGetContext, queryParams)
			.catch((error) => {
				// eslint-disable-next-line no-console
				console.error('MessageService: loadHistory error:', error);
			});

		const commentInfo: RawCommentInfo[] = result.commentInfo;
		const targetCommentInfo: ?RawCommentInfo = commentInfo.find((item) => {
			return item.chatId === chatId;
		});
		const targetMessageId = targetCommentInfo?.messageId;

		Logger.warn('MessageService: loadContextByChatId result', result);
		void this.#handleLoadedMessages(result);

		return targetMessageId;
	}

	reloadMessageList(): Promise
	{
		Logger.warn('MessageService: loadChatOnExit for: ', this.#chatId);
		let targetMessageId = 0;
		if (this.#getDialog().chatId <= 0)
		{
			return Promise.resolve();
		}

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
			return this.loadContext(targetMessageId)
				.finally(() => {
					this.#setDialogInited(true, wasInitedBefore);
				});
		}

		return this.loadInitialMessages()
			.finally(() => {
				this.#setDialogInited(true, wasInitedBefore);
			});
	}

	async loadInitialMessages(): Promise
	{
		Logger.warn('MessageService: loadInitialMessages for: ', this.#chatId);
		this.#isLoading = true;

		const payload = {
			data: {
				chatId: this.#chatId,
				limit: LoadService.MESSAGE_REQUEST_LIMIT,
			},
		};
		const restResult = await runAction(RestMethod.imV2ChatMessageList, payload)
			.catch((error) => {
				// eslint-disable-next-line no-console
				console.error('MessageService: loadInitialMessages error:', error);
				this.#isLoading = false;
				throw new Error(error);
			});
		Logger.warn('MessageService: loadInitialMessages result', restResult);
		restResult.messages = this.#prepareInitialMessages(restResult.messages);

		await this.#handleLoadedMessages(restResult);
		this.#isLoading = false;
	}

	#prepareInitialMessages(rawMessages: RawMessage[]): RawMessage[]
	{
		if (rawMessages.length === 0)
		{
			return rawMessages;
		}

		const lastMessageId = this.#getDialog().lastMessageId;
		const newMaxId = Math.max(...rawMessages.map((message) => message.id));
		if (newMaxId >= lastMessageId)
		{
			return rawMessages;
		}

		const messagesCollection: ImModelMessage[] = this.#store.getters['messages/getByChatId'](this.#chatId);
		const additionalMessages = messagesCollection.filter((message) => {
			return message.id > newMaxId;
		});
		Logger.warn('MessageService: loadInitialMessages: local id is higher than server one', additionalMessages);

		return [...rawMessages, ...additionalMessages];
	}

	isLoading(): boolean
	{
		return this.#isLoading;
	}

	#handleLoadedMessages(restResult): Promise
	{
		const { messages } = restResult;
		const messagesPromise = this.#store.dispatch('messages/setChatCollection', {
			messages,
			clearCollection: true,
		});
		const updateModelsPromise = this.#updateModels(restResult);

		return Promise.all([messagesPromise, updateModelsPromise]);
	}

	#updateModels(rawData: PaginationRestResult): Promise
	{
		const {
			files,
			users,
			usersShort,
			reactions,
			hasPrevPage,
			hasNextPage,
			additionalMessages,
			commentInfo,
			copilot,
			tariffRestrictions,
		} = rawData;

		const dialogPromise = this.#store.dispatch('chats/update', {
			dialogId: this.#getDialog().dialogId,
			fields: {
				hasPrevPage,
				hasNextPage,
				tariffRestrictions,
			},
		});
		const usersPromise = Promise.all([
			this.#userManager.setUsersToModel(users),
			this.#userManager.addUsersToModel(usersShort),
		]);
		const filesPromise = this.#store.dispatch('files/set', files);
		const reactionsPromise = this.#store.dispatch('messages/reactions/set', reactions);
		const additionalMessagesPromise = this.#store.dispatch('messages/store', additionalMessages);
		const commentInfoPromise = this.#store.dispatch('messages/comments/set', commentInfo);
		const copilotManager = new CopilotManager();
		const copilotPromise = copilotManager.handleChatLoadResponse(copilot);

		return Promise.all([
			dialogPromise,
			filesPromise,
			usersPromise,
			reactionsPromise,
			additionalMessagesPromise,
			commentInfoPromise,
			copilotPromise,
		]);
	}

	#setDialogInited(flag: boolean, wasInitedBefore: boolean = true)
	{
		const fields = {
			inited: flag,
			loading: !flag,
		};
		if (flag === true && !wasInitedBefore)
		{
			delete fields.inited;
		}

		this.#store.dispatch('chats/update', {
			dialogId: this.#getDialog().dialogId,
			fields,
		});
	}

	#prepareTariffRestrictions(restrictions: RawTariffRestrictions): RawTariffRestrictions
	{
		const dialogId = this.#getDialog().dialogId;
		const chat: ?ImModelChat = this.#store.getters['chats/get'](dialogId);
		if (!chat)
		{
			return restrictions;
		}

		const { tariffRestrictions: { isHistoryLimitExceeded } } = chat;
		if (isHistoryLimitExceeded === true)
		{
			return {
				...restrictions,
				isHistoryLimitExceeded: true,
			};
		}

		return restrictions;
	}

	#getDialog(): ImModelChat
	{
		return this.#store.getters['chats/getByChatId'](this.#chatId);
	}

	#handleLoadContextError(error): void
	{
		if (error.code !== AccessErrorCode.messageNotFound)
		{
			return;
		}

		this.#showNotification(Loc.getMessage('IM_CONTENT_CHAT_CONTEXT_MESSAGE_NOT_FOUND'));
	}

	#showNotification(text: string)
	{
		BX.UI.Notification.Center.notify({ content: text });
	}

	#sendAnalytics(error)
	{
		if (error.code !== AccessErrorCode.messageNotFound)
		{
			return;
		}

		const chat = this.#getDialog();
		const dialogId = chat.dialogId;
		Analytics.getInstance().messageDelete.onNotFoundNotification({ dialogId });
	}
}
