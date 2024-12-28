import { Type } from 'main.core';
import { BuilderModel } from 'ui.vue3.vuex';

import { Core } from 'im.v2.application.core';

import type { GetterTree, ActionTree, MutationTree } from 'ui.vue3.vuex';

import type { RecentItem as ImModelRecentItem } from '../type/recent-item';
import type { Chat as ImModelChat } from '../type/chat';

type CountersState = {
	unloadedChatCounters: {[chatId: string]: number},
	unloadedLinesCounters: {[chatId: string]: number},
	unloadedCopilotCounters: {[chatId: string]: number},
	unloadedCollabCounters: {[chatId: string]: number},
	commentCounters: CommentsCounters,
};

type CommentsCounters = {
	[channelChatId: string]: {
		[commentChatId: string]: number,
	},
}

type CommentsCounterPayload = {
	channelId: number,
	commentChatId: number
};

export class CountersModel extends BuilderModel
{
	getName(): string
	{
		return 'counters';
	}

	getState(): CountersState
	{
		return {
			unloadedChatCounters: {},
			unloadedLinesCounters: {},
			unloadedCopilotCounters: {},
			unloadedCollabCounters: {},
			commentCounters: {},
		};
	}

	// eslint-disable-next-line max-lines-per-function
	getGetters(): GetterTree
	{
		return {
			/** @function counters/getUnloadedChatCounters */
			getUnloadedChatCounters: (state: CountersState): number => {
				return state.unloadedChatCounters;
			},
			/** @function counters/getTotalChatCounter */
			getTotalChatCounter: (state: CountersState): number => {
				let loadedChatsCounter = 0;
				const recentCollection = Core.getStore().getters['recent/getRecentCollection'];
				recentCollection.forEach((recentItem: ImModelRecentItem) => {
					const recentItemCounter = this.#getRecentItemCounter(recentItem);
					loadedChatsCounter += recentItemCounter;
				});

				let unloadedChatsCounter = 0;
				Object.values(state.unloadedChatCounters).forEach((counter) => {
					unloadedChatsCounter += counter;
				});

				const channelCommentsCounter = Core.getStore().getters['counters/getTotalCommentsCounter'];

				return loadedChatsCounter + unloadedChatsCounter + channelCommentsCounter;
			},
			/** @function counters/getTotalCopilotCounter */
			getTotalCopilotCounter: (state: CountersState): number => {
				let loadedChatsCounter = 0;
				const recentCollection = Core.getStore().getters['recent/getCopilotCollection'];
				recentCollection.forEach((recentItem: ImModelRecentItem) => {
					const chat = this.#getChat(recentItem.dialogId);
					if (this.#isChatMuted(chat))
					{
						return;
					}
					loadedChatsCounter += chat.counter;
				});

				let unloadedChatsCounter = 0;
				Object.values(state.unloadedCopilotCounters).forEach((counter) => {
					unloadedChatsCounter += counter;
				});

				return loadedChatsCounter + unloadedChatsCounter;
			},
			/** @function counters/getTotalCollabCounter */
			getTotalCollabCounter: (state: CountersState): number => {
				let loadedChatsCounter = 0;
				const recentCollection = Core.getStore().getters['recent/getCollabCollection'];
				recentCollection.forEach((recentItem: ImModelRecentItem) => {
					const recentItemCounter = this.#getRecentItemCounter(recentItem);
					loadedChatsCounter += recentItemCounter;
				});

				let unloadedChatsCounter = 0;
				Object.values(state.unloadedCollabCounters).forEach((counter) => {
					unloadedChatsCounter += counter;
				});

				return loadedChatsCounter + unloadedChatsCounter;
			},
			/** @function counters/getTotalLinesCounter */
			getTotalLinesCounter: (state: CountersState): number => {
				let unloadedLinesCounter = 0;
				Object.values(state.unloadedLinesCounters).forEach((counter) => {
					unloadedLinesCounter += counter;
				});

				return unloadedLinesCounter;
			},
			/** @function counters/getSpecificLinesCounter */
			getSpecificLinesCounter: (state: CountersState) => (chatId: number): number => {
				if (!state.unloadedLinesCounters[chatId])
				{
					return 0;
				}

				return state.unloadedLinesCounters[chatId];
			},
			/** @function counters/getTotalCommentsCounter */
			getTotalCommentsCounter: (state: CountersState): number => {
				let totalCounter = 0;
				Object.entries(state.commentCounters).forEach(([channelChatId, channelCounters]) => {
					const channel = this.#getChatByChatId(channelChatId);
					if (this.#isChatMuted(channel))
					{
						return;
					}
					Object.values(channelCounters).forEach((commentCounter) => {
						totalCounter += commentCounter;
					});
				});

				return totalCounter;
			},
			/** @function counters/getChannelComments */
			getChannelComments: (state: CountersState) => (chatId: number): number[] => {
				if (!state.commentCounters[chatId])
				{
					return [];
				}

				return state.commentCounters[chatId];
			},
			/** @function counters/getChannelCommentsCounter */
			getChannelCommentsCounter: (state: CountersState) => (chatId: number): number => {
				if (!state.commentCounters[chatId])
				{
					return 0;
				}

				let result = 0;
				Object.values(state.commentCounters[chatId]).forEach((counter) => {
					result += counter;
				});

				return result;
			},
			/** @function counters/getChatCounterByChatId */
			getChatCounterByChatId: (state: CountersState) => (chatId: number): number => {
				const recentCollection: ImModelRecentItem[] = Core.getStore().getters['recent/getRecentCollection'];
				const recentItem = recentCollection.find((element) => {
					const chat: ImModelChat = this.store.getters['chats/get'](element.dialogId, true);

					return chat.chatId === chatId;
				});

				if (!recentItem)
				{
					return state.unloadedChatCounters[chatId] ?? 0;
				}

				return this.#getRecentItemCounter(recentItem);
			},
			/** @function counters/getSpecificCommentsCounter */
			getSpecificCommentsCounter: (state: CountersState) => (payload: CommentsCounterPayload): number => {
				const { channelId, commentChatId } = payload;
				if (!state.commentCounters[channelId])
				{
					return 0;
				}

				return state.commentCounters[channelId][commentChatId] ?? 0;
			},
		};
	}

	/* eslint-disable no-param-reassign */
	/* eslint-disable-next-line max-lines-per-function */
	getActions(): ActionTree
	{
		return {
			/** @function counters/setUnloadedChatCounters */
			setUnloadedChatCounters: (store, payload: {[chatId: string]: number}) => {
				if (!Type.isPlainObject(payload))
				{
					return;
				}

				store.commit('setUnloadedChatCounters', payload);
			},
			/** @function counters/setUnloadedLinesCounters */
			setUnloadedLinesCounters: (store, payload: {[chatId: string]: number}) => {
				if (!Type.isPlainObject(payload))
				{
					return;
				}

				store.commit('setUnloadedLinesCounters', payload);
			},
			/** @function counters/setUnloadedCopilotCounters */
			setUnloadedCopilotCounters: (store, payload: {[chatId: string]: number}) => {
				if (!Type.isPlainObject(payload))
				{
					return;
				}

				store.commit('setUnloadedCopilotCounters', payload);
			},
			/** @function counters/setUnloadedCollabCounters */
			setUnloadedCollabCounters: (store, payload: {[chatId: string]: number}) => {
				if (!Type.isPlainObject(payload))
				{
					return;
				}

				store.commit('setUnloadedCollabCounters', payload);
			},
			/** @function counters/setCommentCounters */
			setCommentCounters: (store, payload: CommentsCounters) => {
				if (!Type.isPlainObject(payload))
				{
					return;
				}

				store.commit('setCommentCounters', payload);
			},
			/** @function counters/readAllChannelComments */
			readAllChannelComments: (store, channelChatId: number) => {
				if (!Type.isNumber(channelChatId))
				{
					return;
				}

				store.commit('readAllChannelComments', channelChatId);
			},
			/** @function counters/deleteForChannel */
			deleteForChannel: (store, payload: {channelChatId: number, commentChatId?: number}) => {
				if (!Type.isPlainObject(payload))
				{
					return;
				}

				store.commit('deleteForChannel', payload);
			},
		};
	}

	getMutations(): MutationTree
	{
		return {
			setUnloadedChatCounters: (state: CountersState, payload: {[chatId: string]: number}) => {
				Object.entries(payload).forEach(([chatId, counter]) => {
					if (counter === 0)
					{
						delete state.unloadedChatCounters[chatId];

						return;
					}
					state.unloadedChatCounters[chatId] = counter;
				});
			},
			setUnloadedLinesCounters: (state: CountersState, payload: {[chatId: string]: number}) => {
				Object.entries(payload).forEach(([chatId, counter]) => {
					if (counter === 0)
					{
						delete state.unloadedLinesCounters[chatId];

						return;
					}
					state.unloadedLinesCounters[chatId] = counter;
				});
			},
			setUnloadedCopilotCounters: (state: CountersState, payload: {[chatId: string]: number}) => {
				Object.entries(payload).forEach(([chatId, counter]) => {
					if (counter === 0)
					{
						delete state.unloadedCopilotCounters[chatId];

						return;
					}
					state.unloadedCopilotCounters[chatId] = counter;
				});
			},
			setUnloadedCollabCounters: (state: CountersState, payload: {[chatId: string]: number}) => {
				Object.entries(payload).forEach(([chatId, counter]) => {
					if (counter === 0)
					{
						delete state.unloadedCollabCounters[chatId];

						return;
					}
					state.unloadedCollabCounters[chatId] = counter;
				});
			},
			setCommentCounters: (state: CountersState, payload: CommentsCounters) => {
				Object.entries(payload).forEach(([channelChatId, countersMap]) => {
					if (!state.commentCounters[channelChatId])
					{
						state.commentCounters[channelChatId] = {};
					}

					const channelMap = state.commentCounters[channelChatId];
					Object.entries(countersMap).forEach(([commentChatId, counter]) => {
						if (counter === 0)
						{
							delete channelMap[commentChatId];

							return;
						}

						channelMap[commentChatId] = counter;
					});
				});
			},
			readAllChannelComments: (state: CountersState, channelChatId: number) => {
				delete state.commentCounters[channelChatId];
			},
			deleteForChannel: (state: CountersState, payload: {channelChatId: number, commentChatId?: number}) => {
				const { channelChatId, commentChatId } = payload;
				if (!state.commentCounters[channelChatId])
				{
					return;
				}

				if (!commentChatId)
				{
					delete state.commentCounters[channelChatId];

					return;
				}

				delete state.commentCounters[channelChatId][commentChatId];
			},
		};
	}

	#getChat(dialogId): ImModelChat
	{
		return Core.getStore().getters['chats/get'](dialogId, true);
	}

	#getChatByChatId(chatId): ImModelChat
	{
		return Core.getStore().getters['chats/getByChatId'](chatId, true);
	}

	#isChatMuted(chat: ImModelChat): boolean
	{
		return chat.muteList.includes(Core.getUserId());
	}

	#getRecentItemCounter(recentItem: ImModelRecentItem): number
	{
		const chat = this.#getChat(recentItem.dialogId);
		if (this.#isChatMuted(chat))
		{
			return 0;
		}
		const isMarked = recentItem.unread;
		if (chat.counter === 0 && isMarked)
		{
			return 1;
		}

		return chat.counter;
	}
}
