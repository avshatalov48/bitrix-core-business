import { Type, type JsonObject } from 'main.core';
import { BuilderModel } from 'ui.vue3.vuex';

import { Core } from 'im.v2.application.core';
import { Color, ChatType, UserRole } from 'im.v2.const';
import { Utils } from 'im.v2.lib.utils';
import { formatFieldsWithConfig } from 'im.v2.model';

import { chatFieldsConfig } from './format/field-config';
import { CollabsModel } from './nested-modules/collabs';

import type { GetterTree, ActionTree, MutationTree } from 'ui.vue3.vuex';
import type { Chat as ImModelChat } from '../type/chat';

type ChatState = {
	collection: {[dialogId: string]: ImModelChat},
};

/* eslint-disable no-param-reassign */
export class ChatsModel extends BuilderModel
{
	getName(): string
	{
		return 'chats';
	}

	getNestedModules(): { [moduleName: string]: BuilderModel }
	{
		return { collabs: CollabsModel };
	}

	getState(): ChatState
	{
		return {
			collection: {},
		};
	}

	getElementState(): ImModelChat
	{
		return {
			dialogId: '0',
			chatId: 0,
			type: ChatType.chat,
			name: '',
			description: '',
			avatar: '',
			color: Color.base,
			extranet: false,
			counter: 0,
			userCounter: 0,
			lastReadId: 0,
			markedId: 0,
			lastMessageId: 0,
			lastMessageViews: {
				countOfViewers: 0,
				firstViewer: null,
				messageId: 0,
			},
			savedPositionMessageId: 0,
			managerList: [],
			writingList: [],
			muteList: [],
			quoteId: 0,
			ownerId: 0,
			entityLink: {},
			dateCreate: null,
			public: {
				code: '',
				link: '',
			},
			inited: false,
			loading: false,
			hasPrevPage: false,
			hasNextPage: false,
			diskFolderId: 0,
			role: UserRole.guest,
			permissions: {
				manageUi: UserRole.none,
				manageSettings: UserRole.none,
				manageUsersAdd: UserRole.none,
				manageUsersDelete: UserRole.none,
				manageMessages: UserRole.none,
			},
			tariffRestrictions: {
				isHistoryLimitExceeded: false,
			},
			parentChatId: 0,
		};
	}

	// eslint-disable-next-line max-lines-per-function
	getGetters(): GetterTree
	{
		return {
			/** @function chats/get */
			get: (state: ChatState) => (dialogId: string, getBlank: boolean = false) => {
				if (!state.collection[dialogId] && getBlank)
				{
					return this.getElementState();
				}

				if (!state.collection[dialogId] && !getBlank)
				{
					return null;
				}

				return state.collection[dialogId];
			},
			/** @function chats/getByChatId */
			getByChatId: (state: ChatState) => (chatId: number | string, getBlank: boolean = false) => {
				const preparedChatId = Number.parseInt(chatId, 10);

				const chat = Object.values(state.collection).find((item) => {
					return item.chatId === preparedChatId;
				});

				if (!chat && getBlank)
				{
					return this.getElementState();
				}

				return chat;
			},
			/** @function chats/getQuoteId */
			getQuoteId: (state: ChatState) => (dialogId: string) => {
				if (!state.collection[dialogId])
				{
					return 0;
				}

				return state.collection[dialogId].quoteId;
			},
			/** @function chats/isUser */
			isUser: (state: ChatState) => (dialogId: string) => {
				if (!state.collection[dialogId])
				{
					return false;
				}

				return state.collection[dialogId].type === ChatType.user;
			},
			/** @function chats/getLastReadId */
			getLastReadId: (state: ChatState) => (dialogId: string): number => {
				if (!state.collection[dialogId])
				{
					return 0;
				}

				const { lastReadId } = state.collection[dialogId];
				const lastReadIdMessage = Core.getStore().getters['messages/getById'](lastReadId);
				if (!lastReadIdMessage)
				{
					return 0;
				}

				return lastReadId;
			},
			/** @function chats/getInitialMessageId */
			getInitialMessageId: (state: ChatState) => (dialogId: string): number => {
				if (!state.collection[dialogId])
				{
					return 0;
				}

				const { lastReadId, markedId } = state.collection[dialogId];
				if (markedId === 0)
				{
					return lastReadId;
				}

				return Math.min(lastReadId, markedId);
			},
			/** @function chats/isSupport */
			isSupport: (state: ChatState) => (dialogId: string): boolean => {
				if (!state.collection[dialogId])
				{
					return false;
				}

				return state.collection[dialogId].type === ChatType.support24Question;
			},
		};
	}

	// eslint-disable-next-line max-lines-per-function
	getActions(): ActionTree
	{
		return {
			/** @function chats/set */
			set: (store, rawPayload: Array | Object) => {
				let payload = rawPayload;
				if (!Array.isArray(payload) && Type.isPlainObject(payload))
				{
					payload = [payload];
				}

				payload.map((element) => {
					return this.formatFields(element);
				}).forEach((element) => {
					const existingItem = store.state.collection[element.dialogId];
					if (existingItem)
					{
						store.commit('update', {
							dialogId: element.dialogId,
							fields: element,
						});
					}
					else
					{
						store.commit('add', {
							dialogId: element.dialogId,
							fields: { ...this.getElementState(), ...element },
						});
					}
				});
			},
			/** @function chats/add */
			add: (store, rawPayload: Array | Object) => {
				let payload = rawPayload;
				if (!Array.isArray(payload) && Type.isPlainObject(payload))
				{
					payload = [payload];
				}

				payload.map((element) => {
					return this.formatFields(element);
				}).forEach((element) => {
					const existingItem = store.state.collection[element.dialogId];
					if (!existingItem)
					{
						store.commit('add', {
							dialogId: element.dialogId,
							fields: { ...this.getElementState(), ...element },
						});
					}
				});
			},
			/** @function chats/update */
			update: (store, payload: {dialogId: string, fields: Object}) => {
				const existingItem = store.state.collection[payload.dialogId];
				if (!existingItem)
				{
					return;
				}

				store.commit('update', {
					dialogId: payload.dialogId,
					fields: this.formatFields(payload.fields),
				});
			},
			/** @function chats/delete */
			delete: (store, payload: {dialogId: string}) => {
				const existingItem = store.state.collection[payload.dialogId];
				if (!existingItem)
				{
					return;
				}

				store.commit('delete', { dialogId: payload.dialogId });
			},
			/** @function chats/clearCounters */
			clearCounters: (store) => {
				store.commit('clearCounters');
			},
			/** @function chats/mute */
			mute: (store, payload: {dialogId: string}) => {
				const existingItem = store.state.collection[payload.dialogId];
				if (!existingItem)
				{
					return;
				}

				const currentUserId = Core.getUserId();
				if (existingItem.muteList.includes(currentUserId))
				{
					return;
				}
				const muteList = [...existingItem.muteList, currentUserId];

				store.commit('update', {
					actionName: 'mute',
					dialogId: payload.dialogId,
					fields: this.formatFields({ muteList }),
				});
			},
			/** @function chats/unmute */
			unmute: (store, payload: {dialogId: string}) => {
				const existingItem = store.state.collection[payload.dialogId];
				if (!existingItem)
				{
					return;
				}

				const currentUserId = Core.getUserId();
				const muteList = existingItem.muteList.filter((item) => item !== currentUserId);

				store.commit('update', {
					actionName: 'unmute',
					dialogId: payload.dialogId,
					fields: this.formatFields({ muteList }),
				});
			},
			/** @function chats/setLastMessageViews */
			setLastMessageViews: (store, payload: {
				dialogId: string,
				fields: {userId: number, userName: string, date: string, messageId: number}
			}) => {
				const { dialogId, fields: { userId, userName, date, messageId } } = payload;
				const existingItem: ImModelChat = store.state.collection[dialogId];
				if (!existingItem)
				{
					return;
				}

				const newLastMessageViews = {
					countOfViewers: 1,
					messageId,
					firstViewer: {
						userId,
						userName,
						date: Utils.date.cast(date),
					},
				};
				store.commit('update', {
					actionName: 'setLastMessageViews',
					dialogId,
					fields: {
						lastMessageViews: newLastMessageViews,
					},
				});
			},
			/** @function chats/clearLastMessageViews */
			clearLastMessageViews: (store, payload: {dialogId: string}) => {
				const existingItem = store.state.collection[payload.dialogId];
				if (!existingItem)
				{
					return;
				}

				const { lastMessageViews: defaultLastMessageViews } = this.getElementState();
				store.commit('update', {
					actionName: 'clearLastMessageViews',
					dialogId: payload.dialogId,
					fields: {
						lastMessageViews: defaultLastMessageViews,
					},
				});
			},
			/** @function chats/incrementLastMessageViews */
			incrementLastMessageViews: (store, payload: {dialogId: string}) => {
				const existingItem: ImModelChat = store.state.collection[payload.dialogId];
				if (!existingItem)
				{
					return;
				}

				const newCounter = existingItem.lastMessageViews.countOfViewers + 1;
				store.commit('update', {
					actionName: 'incrementLastMessageViews',
					dialogId: payload.dialogId,
					fields: {
						lastMessageViews: { ...existingItem.lastMessageViews, countOfViewers: newCounter },
					},
				});
			},
		};
	}

	getMutations(): MutationTree
	{
		return {
			add: (state: ChatState, payload) => {
				state.collection[payload.dialogId] = payload.fields;
			},
			update: (state: ChatState, payload) => {
				state.collection[payload.dialogId] = { ...state.collection[payload.dialogId], ...payload.fields };
			},
			delete: (state: ChatState, payload) => {
				delete state.collection[payload.dialogId];
			},
			clearCounters: (state: ChatState) => {
				Object.keys(state.collection).forEach((key) => {
					state.collection[key].counter = 0;
					state.collection[key].markedId = 0;
				});
			},
		};
	}

	formatFields(fields: JsonObject): JsonObject
	{
		return formatFieldsWithConfig(fields, chatFieldsConfig);
	}
}
