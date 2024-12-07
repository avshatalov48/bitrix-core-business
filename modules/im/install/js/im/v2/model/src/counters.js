import { Type } from 'main.core';
import { BuilderModel } from 'ui.vue3.vuex';

import { Core } from 'im.v2.application.core';

import type { GetterTree, ActionTree, MutationTree } from 'ui.vue3.vuex';

import type { RecentItem as ImModelRecentItem } from './type/recent-item';
import type { Dialog as ImModelDialog } from './type/dialog';

type CountersState = {
	unloadedChatCounters: {[chatId: string]: number},
	unloadedLinesCounters: {[chatId: string]: number},
	unloadedCopilotCounters: {[chatId: string]: number},
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
		};
	}

	// eslint-disable-next-line max-lines-per-function
	getGetters(): GetterTree
	{
		return {
			/** @function counters/getTotalChatCounter */
			getTotalChatCounter: (state: CountersState): number => {
				let loadedChatsCounter = 0;
				const recentCollection = Core.getStore().getters['recent/getRecentCollection'];
				recentCollection.forEach((recentItem: ImModelRecentItem) => {
					const dialog: ImModelDialog = this.store.getters['dialogues/get'](recentItem.dialogId, true);
					const isMuted = dialog.muteList.includes(Core.getUserId());
					if (isMuted)
					{
						return;
					}
					const isMarked = recentItem.unread;
					if (dialog.counter === 0 && isMarked)
					{
						loadedChatsCounter++;

						return;
					}
					loadedChatsCounter += dialog.counter;
				});

				let unloadedChatsCounter = 0;
				Object.values(state.unloadedChatCounters).forEach((counter) => {
					unloadedChatsCounter += counter;
				});

				return loadedChatsCounter + unloadedChatsCounter;
			},
			/** @function counters/getTotalCopilotCounter */
			getTotalCopilotCounter: (state: CountersState): number => {
				let loadedChatsCounter = 0;
				const recentCollection = Core.getStore().getters['recent/getCopilotCollection'];
				recentCollection.forEach((recentItem: ImModelRecentItem) => {
					const dialog: ImModelDialog = this.store.getters['dialogues/get'](recentItem.dialogId, true);
					const isMuted = dialog.muteList.includes(Core.getUserId());
					if (isMuted)
					{
						return;
					}
					loadedChatsCounter += dialog.counter;
				});

				let unloadedChatsCounter = 0;
				Object.values(state.unloadedCopilotCounters).forEach((counter) => {
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
		};
	}
}
