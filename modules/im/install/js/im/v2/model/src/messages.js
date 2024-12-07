import { Type } from 'main.core';
import { BuilderModel } from 'ui.vue3.vuex';

import { Core } from 'im.v2.application.core';
import { Utils } from 'im.v2.lib.utils';
import { Logger } from 'im.v2.lib.logger';
import { MessageComponent, MessageType } from 'im.v2.const';

import { PinModel } from './messages/pin';
import { ReactionsModel } from './messages/reactions';

import type { GetterTree, ActionTree, MutationTree } from 'ui.vue3.vuex';
import type { ImModelMessage, ImModelFile } from 'im.v2.model';
import type { RawMessage, RawMessageParams, PreparedMessageParams } from './type/message';
import type { AttachConfig } from 'im.v2.const';

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
			replaces: [],
			files: [],
			attach: [],
			unread: false,
			viewed: true,
			viewedByOthers: false,
			sending: false,
			error: false,
			retry: false,
			componentId: MessageComponent.base,
			componentParams: {},
			isEdited: false,
			isDeleted: false,
			removeLinks: false,
		};
	}

	// eslint-disable-next-line max-lines-per-function
	getGetters(): GetterTree
	{
		return {
			/** @function messages/get */
			get: (state: MessagesState) => (chatId: number) => {
				if (!state.chatCollection[chatId])
				{
					return [];
				}

				return [...state.chatCollection[chatId]].map((messageId: number | string) => {
					return state.collection[messageId];
				}).sort(this.#sortCollection);
			},
			/** @function messages/getById */
			getById: (state: MessagesState) => (id: number): ?ImModelMessage => {
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
			hasMessage: (state: MessagesState) => ({ chatId, messageId}) => {
				if (!state.chatCollection[chatId])
				{
					return false;
				}

				return state.chatCollection[chatId].has(messageId);
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
					return { ...this.getElementState(), ...this.validate(message) };
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
					return { ...this.getElementState(), ...this.validate(message) };
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
					...this.validate(payload),
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
					fields: this.validate(fields),
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
					fields: { ...currentMessage, ...this.validate(fields) }
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
					return attachId !== attachItem.ID;
				});

				store.commit('update', {
					id: messageId,
					fields: { ...message, ...this.validate({ attach }) },
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
				state.chatCollection[chatId].delete(id);
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

	validate(fields: Object): Object
	{
		let result = {};

		if (Type.isNumber(fields.id))
		{
			result.id = fields.id;
		}
		else if (Utils.text.isUuidV4(fields.temporaryId))
		{
			result.id = fields.temporaryId;
		}

		if (!Type.isUndefined(fields.chat_id))
		{
			fields.chatId = fields.chat_id;
		}
		if (Type.isNumber(fields.chatId) || Type.isStringFilled(fields.chatId))
		{
			result.chatId = Number.parseInt(fields.chatId, 10);
		}

		if (Type.isStringFilled(fields.date))
		{
			result.date = Utils.date.cast(fields.date);
		}

		if (Type.isNumber(fields.text) || Type.isString(fields.text))
		{
			result.text = fields.text.toString();
		}

		if (Type.isStringFilled(fields.system))
		{
			fields.isSystem = fields.system === 'Y';
		}

		if (!Type.isUndefined(fields.senderId))
		{
			fields.authorId = fields.senderId;
		}
		else if (!Type.isUndefined(fields.author_id))
		{
			fields.authorId = fields.author_id;
		}
		if (Type.isNumber(fields.authorId) || Type.isStringFilled(fields.authorId))
		{
			result.authorId = Number.parseInt(fields.authorId, 10);
		}

		if (fields.isSystem === true)
		{
			result.authorId = 0;
		}

		if (Type.isArray(fields.replaces))
		{
			result.replaces = fields.replaces;
		}

		if (Type.isBoolean(fields.sending))
		{
			result.sending = fields.sending;
		}

		if (Type.isBoolean(fields.unread))
		{
			result.unread = fields.unread;
		}

		if (Type.isBoolean(fields.viewed))
		{
			result.viewed = fields.viewed;
		}

		if (Type.isBoolean(fields.viewedByOthers))
		{
			result.viewedByOthers = fields.viewedByOthers;
		}

		if (Type.isBoolean(fields.error))
		{
			result.error = fields.error;
		}

		if (Type.isBoolean(fields.retry))
		{
			result.retry = fields.retry;
		}

		if (Type.isString(fields.componentId))
		{
			result.componentId = fields.componentId;
		}

		if (Type.isArray(fields.files))
		{
			result.files = fields.files;
		}

		if (Type.isArray(fields.attach))
		{
			result.attach = fields.attach;
		}

		if (Type.isBoolean(fields.isEdited))
		{
			result.isEdited = fields.isEdited;
		}

		if (Type.isBoolean(fields.isDeleted))
		{
			result.isDeleted = fields.isDeleted;
		}

		if (Type.isBoolean(fields.removeLinks))
		{
			result.removeLinks = fields.removeLinks;
		}

		if (Type.isNumber(fields.replyId))
		{
			result.replyId = fields.replyId;
		}

		if (Type.isPlainObject(fields.params))
		{
			const preparedParams = this.prepareParams(fields.params);
			result = {...result, ...preparedParams};
		}

		return result;
	}

	prepareParams(rawParams: RawMessageParams): PreparedMessageParams
	{
		const result = {};

		Object.entries(rawParams).forEach(([key, value]) => {
			if (key === 'COMPONENT_ID' && Type.isStringFilled(value))
			{
				if (Object.values(MessageComponent).includes(value))
				{
					result.componentId = value;
				}
				else
				{
					result.componentId = MessageComponent.unsupported;
				}
			}
			else if (key === 'COMPONENT_PARAMS' && Type.isPlainObject(value))
			{
				result.componentParams = value;
			}
			else if (key === 'FILE_ID' && Type.isArray(value))
			{
				result.files = value;
			}
			else if (key === 'IS_EDITED' && Type.isStringFilled(value))
			{
				result.isEdited = value === 'Y';
			}
			else if (key === 'REPLY_ID' && (Type.isStringFilled(value) || Type.isNumber(value)))
			{
				result.replyId = Number(value);
			}
			else if (key === 'IS_DELETED' && Type.isStringFilled(value))
			{
				result.isDeleted = value === 'Y';
			}
			else if (key === 'ATTACH' && (Type.isArray(value) || Type.isBoolean(value) || Type.isString(value)))
			{
				result.attach = value;
			}
			else if (key === 'LINK_ACTIVE' && Type.isArrayFilled(value))
			{
				result.removeLinks = value.includes(Core.getUserId());
			}
		});

		return result;
	}

	#getMaxMessageId(messageIds: number[]): number
	{
		let maxMessageId = 0;
		messageIds.forEach(messageId => {
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
