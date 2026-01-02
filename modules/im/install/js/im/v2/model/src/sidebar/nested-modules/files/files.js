import { Type } from 'main.core';
import { BuilderModel } from 'ui.vue3.vuex';

import { Core } from 'im.v2.application.core';
import { SidebarFileGroups } from 'im.v2.const';

import { formatFieldsWithConfig } from '../../../utils/validate';
import { sidebarFilesFieldsConfig } from './format/field-config';

import type { JsonObject } from 'main.core';
import type { ImModelSidebarFileItem } from '../../../registry';
import type { GetterTree, ActionTree, MutationTree } from 'ui.vue3.vuex';

type FilesState = {
	collection: {
		[chatId: number]: {
			[string]: ChatState
		}
	},
};

type ChatState = {
	items: Map<number, ImModelSidebarFileItem>,
	hasNextPage: boolean,
	lastId: number,
};

type FilesPayload = {
	chatId?: number,
	files?: Object[],
	group?: string,
	hasNextPage?: boolean,
}

/* eslint-disable no-param-reassign */
export class FilesModel extends BuilderModel
{
	getState(): FilesState
	{
		return {
			collection: {},
			collectionSearch: {},
			historyLimitExceededCollection: {},
		};
	}

	getElementState(): ImModelSidebarFileItem
	{
		return {
			id: 0,
			messageId: 0,
			chatId: 0,
			authorId: 0,
			date: new Date(),
			fileId: 0,
		};
	}

	getChatState(): ChatState
	{
		return {
			items: new Map(),
			hasNextPage: true,
			lastId: 0,
		};
	}

	// eslint-disable-next-line max-lines-per-function
	getGetters(): GetterTree
	{
		return {
			/** @function sidebar/files/get */
			get: (state) => (chatId: number, group: string): ImModelSidebarFileItem[] => {
				if (!state.collection[chatId] || !state.collection[chatId][group])
				{
					return [];
				}

				return [...state.collection[chatId][group].items.values()].sort((a, b) => b.id - a.id);
			},
			/** @function sidebar/files/getSearchResultCollection */
			getSearchResultCollection: (state) => (chatId: number, group: string): ImModelSidebarFileItem[] => {
				if (!state.collectionSearch[chatId] || !state.collectionSearch[chatId][group])
				{
					return [];
				}

				return [...state.collectionSearch[chatId][group].items.values()].sort((a, b) => b.id - a.id);
			},
			/** @function sidebar/files/getLatest */
			getLatest: (state, getters, rootState, rootGetters) => (chatId: number): ImModelSidebarFileItem[] => {
				if (!state.collection[chatId])
				{
					return [];
				}

				let media = [];
				let audio = [];
				let files = [];
				let briefs = [];

				if (state.collection[chatId][SidebarFileGroups.media])
				{
					media = [...state.collection[chatId][SidebarFileGroups.media].items.values()];
				}

				if (state.collection[chatId][SidebarFileGroups.audio])
				{
					audio = [...state.collection[chatId][SidebarFileGroups.audio].items.values()];
				}

				if (state.collection[chatId][SidebarFileGroups.file])
				{
					files = [...state.collection[chatId][SidebarFileGroups.file].items.values()];
				}

				if (state.collection[chatId][SidebarFileGroups.brief])
				{
					briefs = [...state.collection[chatId][SidebarFileGroups.brief].items.values()];
				}

				const sortedFlatCollection = [media, audio, files, briefs]
					.flat()
					.sort((a, b) => b.id - a.id)
				;

				return this.getTopThreeCompletedFiles(sortedFlatCollection, rootGetters);
			},
			/** @function sidebar/files/getLatestUnsorted */
			getLatestUnsorted: (state, getters, rootState, rootGetters) => (chatId: number): ImModelSidebarFileItem[] => {
				if (!state.collection[chatId])
				{
					return [];
				}

				let unsorted = [];

				if (state.collection[chatId][SidebarFileGroups.fileUnsorted])
				{
					unsorted = [...state.collection[chatId][SidebarFileGroups.fileUnsorted].items.values()];
				}

				const sortedCollection = unsorted.sort((a, b) => b.id - a.id);

				return this.getTopThreeCompletedFiles(sortedCollection, rootGetters);
			},
			/** @function sidebar/files/getSize */
			getSize: (state) => (chatId: number, group: string): number => {
				if (!state.collection[chatId] || !state.collection[chatId][group])
				{
					return 0;
				}

				return state.collection[chatId][group].items.size;
			},
			/** @function sidebar/files/hasNextPage */
			hasNextPage: (state) => (chatId: number, group: string): boolean => {
				if (!state.collection[chatId] || !state.collection[chatId][group])
				{
					return false;
				}

				return state.collection[chatId][group].hasNextPage;
			},
			/** @function sidebar/files/hasNextPageSearch */
			hasNextPageSearch: (state) => (chatId: number, group: string): boolean => {
				if (!state.collectionSearch[chatId] || !state.collectionSearch[chatId][group])
				{
					return false;
				}

				return state.collectionSearch[chatId][group].hasNextPage;
			},
			/** @function sidebar/files/getLastId */
			getLastId: (state) => (chatId: number, group: string): boolean => {
				if (!state.collection[chatId] || !state.collection[chatId][group])
				{
					return false;
				}

				return state.collection[chatId][group].lastId;
			},
			/** @function sidebar/files/getSearchResultCollectionLastId */
			getSearchResultCollectionLastId: (state) => (chatId: number, group: string): boolean => {
				if (!state.collectionSearch[chatId] || !state.collectionSearch[chatId][group])
				{
					return false;
				}

				return state.collectionSearch[chatId][group].lastId;
			},
			/** @function sidebar/files/isHistoryLimitExceeded */
			isHistoryLimitExceeded: (state) => (chatId: number): boolean => {
				const isAvailable = Core.getStore().getters['application/tariffRestrictions/isHistoryAvailable'];
				if (isAvailable)
				{
					return false;
				}

				return state.historyLimitExceededCollection[chatId] ?? false;
			},
		};
	}

	getActions(): ActionTree
	{
		return {
			/** @function sidebar/files/set */
			set: (store, payload) => {
				const { chatId, files, group } = payload;
				if (!Type.isArrayFilled(files) || !Type.isNumber(chatId))
				{
					return;
				}

				files.forEach((file) => {
					const preparedFile = { ...this.getElementState(), ...this.formatFields(file) };
					store.commit('add', { chatId, group, file: preparedFile });
				});
			},
			/** @function sidebar/files/setSearch */
			setSearch: (store, payload: FilesPayload) => {
				const { chatId, files, group } = payload;
				if (!Type.isArrayFilled(files) || !Type.isNumber(chatId))
				{
					return;
				}

				files.forEach((file) => {
					const preparedFile = { ...this.getElementState(), ...this.formatFields(file) };
					store.commit('addSearch', { chatId, group, file: preparedFile });
				});
			},
			/** @function sidebar/files/delete */
			delete: (store, payload) => {
				const { chatId, id } = payload;
				if (!Type.isNumber(id) || !Type.isNumber(chatId))
				{
					return;
				}

				if (!store.state.collection[chatId])
				{
					return;
				}

				store.commit('delete', { chatId, id });
			},
			/** @function sidebar/files/setHasNextPage */
			setHasNextPage: (store, payload) => {
				const { chatId, group, hasNextPage } = payload;
				if (!Type.isNumber(chatId))
				{
					return;
				}

				if (!store.state.collection[chatId])
				{
					return;
				}

				store.commit('setHasNextPage', { chatId, group, hasNextPage });
			},
			/** @function sidebar/files/setHasNextPageSearch */
			setHasNextPageSearch: (store, payload: FilesPayload) => {
				const { chatId, group, hasNextPage } = payload;
				if (!Type.isNumber(chatId))
				{
					return;
				}

				if (!store.state.collectionSearch[chatId])
				{
					return;
				}

				store.commit('setHasNextPageSearch', { chatId, group, hasNextPage });
			},
			/** @function sidebar/files/setLastId */
			setLastId: (store, payload) => {
				const { chatId, group, lastId } = payload;
				if (!Type.isNumber(chatId))
				{
					return;
				}

				if (!store.state.collection[chatId])
				{
					return;
				}

				store.commit('setLastId', { chatId, group, lastId });
			},
			/** @function sidebar/files/setLastIdSearch */
			setLastIdSearch: (store, payload) => {
				const { chatId, group, lastId } = payload;
				if (!Type.isNumber(chatId))
				{
					return;
				}

				if (!store.state.collectionSearch[chatId])
				{
					return;
				}

				store.commit('setLastIdSearch', { chatId, group, lastId });
			},
			/** @function sidebar/files/clearSearch */
			clearSearch: (store) => {
				store.commit('clearSearch', {});
			},
			/** @function sidebar/files/setHistoryLimitExceeded */
			setHistoryLimitExceeded: (store, payload) => {
				const { chatId, isHistoryLimitExceeded = false } = payload;
				store.commit('setHistoryLimitExceeded', { chatId, isHistoryLimitExceeded });
			},
		};
	}

	// eslint-disable-next-line max-lines-per-function
	getMutations(): MutationTree
	{
		return {
			add: (state, payload: {chatId: number, group: string, file: ImModelSidebarFileItem}) => {
				const { chatId, file, group } = payload;

				if (!state.collection[chatId])
				{
					state.collection[chatId] = {};
				}

				if (!state.collection[chatId][group])
				{
					state.collection[chatId][group] = this.getChatState();
				}
				state.collection[chatId][group].items.set(file.id, file);
			},
			addSearch: (state, payload: {chatId: number, group: string, file: ImModelSidebarFileItem}) => {
				const { chatId, file, group } = payload;

				if (!state.collectionSearch[chatId])
				{
					state.collectionSearch[chatId] = {};
				}

				if (!state.collectionSearch[chatId][group])
				{
					state.collectionSearch[chatId][group] = this.getChatState();
				}
				state.collectionSearch[chatId][group].items.set(file.id, file);
			},
			delete: (state, payload: {chatId: number, id: number}) => {
				const { chatId, id } = payload;
				const hasCollectionSearch = !Type.isNil(state.collectionSearch[chatId]);
				Object.values(SidebarFileGroups).forEach((group) => {
					if (state.collection[chatId][group] && state.collection[chatId][group].items.has(id))
					{
						state.collection[chatId][group].items.delete(id);
						if (hasCollectionSearch)
						{
							state.collectionSearch[chatId][group].items.delete(id);
						}
					}
				});
			},
			setHasNextPage: (state, payload) => {
				const { chatId, group, hasNextPage } = payload;

				if (!state.collection[chatId])
				{
					state.collection[chatId] = {};
				}

				const hasCollection = !Type.isNil(state.collection[chatId][group]);
				if (!hasCollection)
				{
					state.collection[chatId][group] = this.getChatState();
				}

				state.collection[chatId][group].hasNextPage = hasNextPage;
			},
			setHasNextPageSearch: (state, payload) => {
				const { chatId, group, hasNextPage } = payload;

				if (!state.collectionSearch[chatId])
				{
					state.collectionSearch[chatId] = {};
				}

				const hasCollection = !Type.isNil(state.collectionSearch[chatId][group]);
				if (!hasCollection)
				{
					state.collectionSearch[chatId][group] = this.getChatState();
				}

				state.collectionSearch[chatId][group].hasNextPage = hasNextPage;
			},
			setLastId: (state, payload) => {
				const { chatId, group, lastId } = payload;

				if (!state.collection[chatId])
				{
					state.collection[chatId] = {};
				}

				const hasCollection = !Type.isNil(state.collection[chatId][group]);
				if (!hasCollection)
				{
					state.collection[chatId][group] = this.getChatState();
				}

				state.collection[chatId][group].lastId = lastId;
			},
			setLastIdSearch: (state, payload) => {
				const { chatId, group, lastId } = payload;

				if (!state.collectionSearch[chatId])
				{
					state.collectionSearch[chatId] = {};
				}

				const hasCollection = !Type.isNil(state.collectionSearch[chatId][group]);
				if (!hasCollection)
				{
					state.collectionSearch[chatId][group] = this.getChatState();
				}

				state.collectionSearch[chatId][group].lastId = lastId;
			},
			clearSearch: (state) => {
				state.collectionSearch = {};
			},
			setHistoryLimitExceeded: (state, payload) => {
				const { chatId, isHistoryLimitExceeded } = payload;
				if (state.historyLimitExceededCollection[chatId] && !isHistoryLimitExceeded)
				{
					return;
				}

				state.historyLimitExceededCollection[chatId] = isHistoryLimitExceeded;
			},
		};
	}

	formatFields(fields: JsonObject): JsonObject
	{
		return formatFieldsWithConfig(fields, sidebarFilesFieldsConfig);
	}

	getTopThreeCompletedFiles(collection: ImModelSidebarFileItem[], rootGetters): ImModelSidebarFileItem[]
	{
		return collection.filter((sidebarFile: ImModelSidebarFileItem) => {
			const file = rootGetters['files/get'](sidebarFile.fileId, true);

			return file.progress === 100;
		}).slice(0, 3);
	}
}
