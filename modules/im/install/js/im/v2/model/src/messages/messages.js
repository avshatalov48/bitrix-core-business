import type { JsonObject } from 'main.core';
import { Type } from 'main.core';
import { BuilderModel } from 'ui.vue3.vuex';

import { Core } from 'im.v2.application.core';
import { Utils } from 'im.v2.lib.utils';
import { Logger } from 'im.v2.lib.logger';
import { MessageComponent, MessageType, UserIdNetworkPrefix } from 'im.v2.const';
import { UserManager } from 'im.v2.lib.user';
import { formatFieldsWithConfig } from 'im.v2.model';

import { convertToNumber } from '../utils/format';
import { messageFieldsConfig } from './format/field-config';
import { PinModel } from './nested-modules/pin';
import { ReactionsModel } from './nested-modules/reactions';
import { CommentsModel } from './nested-modules/comments/comments';

import type { GetterTree, ActionTree, MutationTree } from 'ui.vue3.vuex';
import type { ImModelMessage, ImModelFile } from 'im.v2.model';
import type { AttachConfig } from 'im.v2.const';
import type { RawMessage } from '../type/message';

type MessagesState = {
	collection: {
		[messageId: string]: ImModelMessage
	},
	chatCollection: {
		[chatId: string]: Set<string | number>
	}
};

export class MessagesModel extends BuilderModel
{
	getName(): string
	{
		return 'messages';
	}

	getNestedModules(): { [moduleName: string]: BuilderModel }
	{
		return {
			pin: PinModel,
			reactions: ReactionsModel,
			comments: CommentsModel,
		};
	}

	getState(): MessagesState
	{
		return {
			collection: {},
			chatCollection: {},
		};
	}

	getElementState(): ImModelMessage
	{
		return {
			id: 0,
			chatId: 0,
			authorId: 0,
			replyId: 0,
			date: new Date(),
			text: '',
			files: [],
			attach: [],
			keyboard: [],
			unread: false,
			viewed: true,
			viewedByOthers: false,
			sending: false,
			error: false,
			componentId: MessageComponent.default,
			componentParams: {},
			forward: {
				id: '',
				userId: 0,
			},
			isEdited: false,
			isDeleted: false,
		};
	}

	// eslint-disable-next-line max-lines-per-function
	getGetters(): GetterTree
	{
		return {
			/** @function messages/getByChatId */
			getByChatId: (state: MessagesState) => (chatId: number) => {
				if (!state.chatCollection[chatId])
				{
					return [];
				}

				return [...state.chatCollection[chatId]].map((messageId: number | string) => {
					return state.collection[messageId];
				}).sort(this.#sortCollection);
			},
			/** @function messages/getById */
			getById: (state: MessagesState) => (id: number | string): ?ImModelMessage => {
				return state.collection[id];
			},
			/** @function messages/getByIdList */
			getByIdList: (state: MessagesState) => (idList: number[]): ImModelMessage[] => {
				const result = [];
				idList.forEach((id) => {
					if (state.collection[id])
					{
						result.push(state.collection[id]);
					}
				});

				return result;
			},
			/** @function messages/hasMessage */
			hasMessage: (state: MessagesState) => ({ chatId, messageId }) => {
				if (!state.chatCollection[chatId])
				{
					return false;
				}

				return state.chatCollection[chatId].has(messageId);
			},
			/** @function messages/isForward */
			isForward: (state: MessagesState) => (id: number | string) => {
				const message = state.collection[id];
				if (!message)
				{
					return false;
				}

				return Type.isStringFilled(message.forward.id);
			},
			/** @function messages/isInChatCollection */
			isInChatCollection: (state: MessagesState) => (payload: {messageId: number}): boolean => {
				const { messageId } = payload;
				const message = state.collection[messageId];
				if (!message)
				{
					return false;
				}
				const { chatId } = message;

				return state.chatCollection[chatId]?.has(messageId);
			},
			/** @function messages/getFirstId */
			getFirstId: (state: MessagesState) => (chatId: number): number => {
				if (!state.chatCollection[chatId])
				{
					return 0;
				}

				return this.#findLowestMessageId(state, chatId);
			},
			/** @function messages/getLastId */
			getLastId: (state: MessagesState) => (chatId: number): number => {
				if (!state.chatCollection[chatId])
				{
					return 0;
				}

				return this.#findMaxMessageId(state, chatId);
			},
			/** @function messages/getLastOwnMessageId */
			getLastOwnMessageId: (state: MessagesState) => (chatId: number): number => {
				if (!state.chatCollection[chatId])
				{
					return 0;
				}

				return this.#findLastOwnMessageId(state, chatId);
			},
			/** @function messages/getFirstUnread */
			getFirstUnread: (state: MessagesState) => (chatId: number): number => {
				if (!state.chatCollection[chatId])
				{
					return 0;
				}

				return this.#findFirstUnread(state, chatId);
			},
			/** @function messages/getChatUnreadMessages */
			getChatUnreadMessages: (state: MessagesState) => (chatId: number): ImModelMessage[] => {
				if (!state.chatCollection[chatId])
				{
					return [];
				}

				const messages = [...state.chatCollection[chatId]].map((messageId: number | string) => {
					return state.collection[messageId];
				});

				return messages.filter((message: ImModelMessage) => {
					return message.unread === true;
				});
			},
			/** @function messages/getMessageFiles */
			getMessageFiles: (state: MessagesState) => (payload: number): ImModelFile[] => {
				const messageId = payload;
				if (!state.collection[messageId])
				{
					return [];
				}

				return state.collection[messageId].files.map((fileId) => {
					return this.store.getters['files/get'](fileId, true);
				});
			},
			/** @function messages/getMessageType */
			getMessageType: (state: MessagesState) => (messageId: number): ?$Values<typeof MessageType> => {
				const message = state.collection[messageId];
				if (!message)
				{
					return null;
				}

				const currentUserId = Core.getUserId();
				if (message.authorId === 0)
				{
					return MessageType.system;
				}

				if (message.authorId === currentUserId)
				{
					return MessageType.self;
				}

				return MessageType.opponent;
			},
			/** @function messages/getPreviousMessage */
			getPreviousMessage: (state: MessagesState) => (payload: {messageId: number, chatId: number}): ?ImModelMessage => {
				const { messageId, chatId } = payload;
				const message = state.collection[messageId];
				if (!message)
				{
					return null;
				}

				const chatCollection = [...state.chatCollection[chatId]];
				const initialMessageIndex = chatCollection.indexOf(messageId);
				const desiredMessageId = chatCollection[initialMessageIndex - 1];
				if (!desiredMessageId)
				{
					return null;
				}

				return state.collection[desiredMessageId];
			},
		};
	}

	// eslint-disable-next-line max-lines-per-function
	getActions(): ActionTree
	{
		return {
			/** @function messages/setChatCollection */
			setChatCollection: (store, payload: {messages: RawMessage | RawMessage[], clearCollection: boolean}) => {
				let { messages, clearCollection } = payload;
				clearCollection = clearCollection ?? false;
				if (!Array.isArray(messages) && Type.isPlainObject(messages))
				{
					messages = [messages];
				}

				messages = messages.map((message: RawMessage) => {
					return { ...this.getElementState(), ...this.#formatFields(message) };
				});
				const chatId = messages[0]?.chatId;
				if (chatId && clearCollection)
				{
					store.commit('clearCollection', { chatId });
				}

				store.commit('store', { messages });
				store.commit('setChatCollection', { messages });
			},
			/** @function messages/store */
			store: (store, payload: RawMessage | RawMessage[]) => {
				let preparedMessages = payload;
				if (Type.isPlainObject(payload))
				{
					preparedMessages = [payload];
				}

				preparedMessages = preparedMessages.map((message: RawMessage) => {
					const currentMessage: ImModelMessage = store.state.collection[message.id];
					if (currentMessage)
					{
						return { ...currentMessage, ...this.#formatFields(message) };
					}

					return { ...this.getElementState(), ...this.#formatFields(message) };
				});

				if (preparedMessages.length === 0)
				{
					return;
				}

				store.commit('store', {
					messages: preparedMessages,
				});
			},
			/** @function messages/add */
			add: (store, payload: RawMessage) => {
				const message = {
					...this.getElementState(),
					...this.#formatFields(payload),
				};
				store.commit('store', {
					messages: [message],
				});
				store.commit('setChatCollection', {
					messages: [message],
				});

				return message.id;
			},
			/** @function messages/updateWithId */
			updateWithId: (store, payload: {id: string | number, fields: Object}) => {
				const { id, fields } = payload;
				if (!store.state.collection[id])
				{
					return;
				}

				store.commit('updateWithId', {
					id,
					fields: this.#formatFields(fields),
				});
			},
			/** @function messages/update */
			update: (store, payload: {id: string | number, fields: Object}) => {
				const { id, fields } = payload;
				const currentMessage = store.state.collection[id];
				if (!currentMessage)
				{
					return;
				}

				store.commit('update', {
					id,
					fields: { ...currentMessage, ...this.#formatFields(fields) },
				});
			},
			/** @function messages/readMessages */
			readMessages: (store, payload: {chatId: number, messageIds: number[]}): number => {
				const { chatId, messageIds } = payload;
				if (!store.state.chatCollection[chatId])
				{
					return 0;
				}

				const chatMessages = [...store.state.chatCollection[chatId]].map((messageId: number) => {
					return store.state.collection[messageId];
				});

				let messagesToReadCount = 0;
				const maxMessageId = this.#getMaxMessageId(messageIds);
				const messageIdsToView = messageIds;
				const messageIdsToRead = [];
				chatMessages.forEach((chatMessage: ImModelMessage) => {
					if (!chatMessage.unread)
					{
						return;
					}

					if (chatMessage.id <= maxMessageId)
					{
						messagesToReadCount++;
						messageIdsToRead.push(chatMessage.id);
					}
				});

				store.commit('readMessages', {
					messageIdsToRead,
					messageIdsToView,
				});

				return messagesToReadCount;
			},
			/** @function messages/setViewedByOthers */
			setViewedByOthers: (store, payload: {ids: number[]}): number => {
				const { ids } = payload;
				store.commit('setViewedByOthers', {
					ids,
				});
			},
			/** @function messages/delete */
			delete: (store, payload: {id: string | number}) => {
				const { id } = payload;
				if (!store.state.collection[id])
				{
					return;
				}

				store.commit('delete', { id });
			},
			/** @function messages/clearChatCollection */
			clearChatCollection: (store, payload: {chatId: number}) => {
				const { chatId } = payload;
				store.commit('clearCollection', { chatId });
			},
			/** @function messages/deleteAttach */
			deleteAttach: (store, payload: {messageId: number, attachId: string }) => {
				const { messageId, attachId } = payload;
				const message: ImModelMessage = store.state.collection[messageId];
				if (!message || !Type.isArray(message.attach))
				{
					return;
				}

				const attach = message.attach.filter((attachItem: AttachConfig) => {
					return attachId !== attachItem.id;
				});

				store.commit('update', {
					id: messageId,
					fields: { ...message, ...this.#formatFields({ attach }) },
				});
			},
		};
	}

	/* eslint-disable no-param-reassign */
	getMutations(): MutationTree
	{
		return {
			setChatCollection: (state: MessagesState, payload: {messages: ImModelMessage[]}) => {
				Logger.warn('Messages model: setChatCollection mutation', payload);
				payload.messages.forEach((message) => {
					if (!state.chatCollection[message.chatId])
					{
						state.chatCollection[message.chatId] = new Set();
					}
					state.chatCollection[message.chatId].add(message.id);
				});
			},
			store: (state: MessagesState, payload: {messages: ImModelMessage[]}) => {
				Logger.warn('Messages model: store mutation', payload);
				payload.messages.forEach((message) => {
					state.collection[message.id] = message;
				});
			},
			updateWithId: (state: MessagesState, payload: {id: number | string, fields: Object}) => {
				Logger.warn('Messages model: updateWithId mutation', payload);
				const { id, fields } = payload;
				const currentMessage = { ...state.collection[id] };

				delete state.collection[id];
				state.collection[fields.id] = { ...currentMessage, ...fields, sending: false };

				if (state.chatCollection[currentMessage.chatId].has(id))
				{
					state.chatCollection[currentMessage.chatId].delete(id);
					state.chatCollection[currentMessage.chatId].add(fields.id);
				}
			},
			update: (state: MessagesState, payload: {id: number | string, fields: Object}) => {
				Logger.warn('Messages model: update mutation', payload);
				const { id, fields } = payload;
				state.collection[id] = { ...state.collection[id], ...fields };
			},
			delete: (state: MessagesState, payload: {id: number | string}) => {
				Logger.warn('Messages model: delete mutation', payload);
				const { id } = payload;
				const { chatId } = state.collection[id];
				state.chatCollection[chatId]?.delete(id);
				delete state.collection[id];
			},
			clearCollection: (state: MessagesState, payload: {chatId: number}) => {
				Logger.warn('Messages model: clear collection mutation', payload.chatId);
				state.chatCollection[payload.chatId] = new Set();
			},
			readMessages: (state: MessagesState, payload: {messageIdsToRead: number[], messageIdsToView: number[]}) => {
				const { messageIdsToRead, messageIdsToView } = payload;
				messageIdsToRead.forEach((messageId) => {
					const message = state.collection[messageId];
					if (!message)
					{
						return;
					}

					message.unread = false;
				});
				messageIdsToView.forEach((messageId) => {
					const message = state.collection[messageId];
					if (!message)
					{
						return;
					}

					message.viewed = true;
				});
			},
			setViewedByOthers: (state: MessagesState, payload: {ids: number[]}) => {
				const { ids } = payload;
				ids.forEach((id) => {
					const message = state.collection[id];
					if (!message)
					{
						return;
					}
					const isOwnMessage = message.authorId === Core.getUserId();
					if (!isOwnMessage || message.viewedByOthers)
					{
						return;
					}

					message.viewedByOthers = true;
				});
			},
		};
	}

	#formatFields(rawFields: JsonObject): JsonObject
	{
		const messageParams = Type.isPlainObject(rawFields.params) ? rawFields.params : {};
		const fields = { ...rawFields, ...messageParams };

		const formattedFields: ImModelMessage = formatFieldsWithConfig(fields, messageFieldsConfig);
		if (this.#needToSwapAuthorId(formattedFields, messageParams))
		{
			formattedFields.authorId = this.#prepareSwapAuthorId(formattedFields, messageParams);
		}

		return formattedFields;
	}

	#needToSwapAuthorId(formattedFields: ImModelMessage, messageParams: JsonObject): boolean
	{
		const { NAME: name, USER_ID: userId } = messageParams;

		return Boolean(name && userId && formattedFields.authorId);
	}

	#prepareSwapAuthorId(formattedFields: ImModelMessage, messageParams: JsonObject): string
	{
		const { NAME: authorName, USER_ID: userId, AVATAR: avatar } = messageParams;
		const originalAuthorId = formattedFields.authorId;
		const fakeAuthorId = convertToNumber(userId);
		const userManager = new UserManager();
		const networkId = `${UserIdNetworkPrefix}-${originalAuthorId}-${fakeAuthorId}`;
		void userManager.setUsersToModel({
			networkId,
			name: authorName,
			avatar: avatar ?? '',
		});

		return networkId;
	}

	#getMaxMessageId(messageIds: number[]): number
	{
		let maxMessageId = 0;
		messageIds.forEach((messageId) => {
			if (maxMessageId < messageId)
			{
				maxMessageId = messageId;
			}
		});

		return maxMessageId;
	}

	#findLowestMessageId(state: MessagesState, chatId: number): number
	{
		let firstId = null;
		const messages = [...state.chatCollection[chatId]];
		for (const messageId of messages)
		{
			const element = state.collection[messageId];
			if (!firstId)
			{
				firstId = element.id;
			}

			if (Utils.text.isTempMessage(element.id))
			{
				continue;
			}

			if (element.id < firstId)
			{
				firstId = element.id;
			}
		}

		return firstId;
	}

	#findMaxMessageId(state: MessagesState, chatId: number): number
	{
		let lastId = 0;
		const messages = [...state.chatCollection[chatId]];
		for (const messageId of messages)
		{
			const element = state.collection[messageId];
			if (Utils.text.isTempMessage(element.id))
			{
				continue;
			}

			if (element.id > lastId)
			{
				lastId = element.id;
			}
		}

		return lastId;
	}

	#findLastOwnMessageId(state: MessagesState, chatId: number): number
	{
		let lastOwnMessageId = 0;
		const messages = [...state.chatCollection[chatId]].sort((a, z) => z - a);
		for (const messageId of messages)
		{
			const element = state.collection[messageId];
			if (Utils.text.isTempMessage(element.id))
			{
				continue;
			}

			if (element.authorId === Core.getUserId())
			{
				lastOwnMessageId = element.id;
				break;
			}
		}

		return lastOwnMessageId;
	}

	#findFirstUnread(state: MessagesState, chatId: number): number
	{
		let resultId = 0;
		for (const messageId of state.chatCollection[chatId])
		{
			const message: ImModelMessage = state.collection[messageId];
			if (message.unread)
			{
				resultId = messageId;
				break;
			}
		}

		return resultId;
	}

	#sortCollection(a: ImModelMessage, b: ImModelMessage): number
	{
		if (Utils.text.isUuidV4(a.id) && !Utils.text.isUuidV4(b.id))
		{
			return 1;
		}

		if (!Utils.text.isUuidV4(a.id) && Utils.text.isUuidV4(b.id))
		{
			return -1;
		}

		if (Utils.text.isUuidV4(a.id) && Utils.text.isUuidV4(b.id))
		{
			return a.date.getTime() - b.date.getTime();
		}

		return a.id - b.id;
	}
}
