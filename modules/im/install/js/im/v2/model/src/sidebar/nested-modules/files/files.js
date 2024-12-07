import { Type } from 'main.core';
import { BuilderModel } from 'ui.vue3.vuex';

import { Core } from 'im.v2.application.core';
import { SidebarFileTypes } from 'im.v2.const';

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
	subType?: string,
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
			get: (state) => (chatId: number, subType: string): ImModelSidebarFileItem[] => {
				if (!state.collection[chatId] || !state.collection[chatId][subType])
				{
					return [];
				}

				return [...state.collection[chatId][subType].items.values()].sort((a, b) => b.id - a.id);
			},
			/** @function sidebar/files/getSearchResultCollection */
			getSearchResultCollection: (state) => (chatId: number, subType: string): ImModelSidebarFileItem[] => {
				if (!state.collectionSearch[chatId] || !state.collectionSearch[chatId][subType])
				{
					return [];
				}

				return [...state.collectionSearch[chatId][subType].items.values()].sort((a, b) => b.id - a.id);
			},
			/** @function sidebar/files/getLatest */
			getLatest: (state, getters, rootState, rootGetters) => (chatId: number): ImModelSidebarFileItem[] => {
				if (!state.collection[chatId])
				{
					return [];
				}

				let media = [];
				let audio = [];
				let documents = [];
				let other = [];
				let briefs = [];

				if (state.collection[chatId][SidebarFileTypes.media])
				{
					media = [...state.collection[chatId][SidebarFileTypes.media].items.values()];
				}

				if (state.collection[chatId][SidebarFileTypes.audio])
				{
					audio = [...state.collection[chatId][SidebarFileTypes.audio].items.values()];
				}

				if (state.collection[chatId][SidebarFileTypes.document])
				{
					documents = [...state.collection[chatId][SidebarFileTypes.document].items.values()];
				}

				if (state.collection[chatId][SidebarFileTypes.brief])
				{
					briefs = [...state.collection[chatId][SidebarFileTypes.brief].items.values()];
				}

				if (state.collection[chatId][SidebarFileTypes.other])
				{
					other = [...state.collection[chatId][SidebarFileTypes.other].items.values()];
				}

				const sortedFlatCollection = [media, audio, documents, briefs, other]
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

				if (state.collection[chatId][SidebarFileTypes.fileUnsorted])
				{
					unsorted = [...state.collection[chatId][SidebarFileTypes.fileUnsorted].items.values()];
				}

				const sortedCollection = unsorted.sort((a, b) => b.id - a.id);

				return this.getTopThreeCompletedFiles(sortedCollection, rootGetters);
			},
			/** @function sidebar/files/getSize */
			getSize: (state) => (chatId: number, subType: string): number => {
				if (!state.collection[chatId] || !state.collection[chatId][subType])
				{
					return 0;
				}

				return state.collection[chatId][subType].items.size;
			},
			/** @function sidebar/files/hasNextPage */
			hasNextPage: (state) => (chatId: number, subType: string): boolean => {
				if (!state.collection[chatId] || !state.collection[chatId][subType])
				{
					return false;
				}

				return state.collection[chatId][subType].hasNextPage;
			},
			/** @function sidebar/files/hasNextPageSearch */
			hasNextPageSearch: (state) => (chatId: number, subType: string): boolean => {
				if (!state.collectionSearch[chatId] || !state.collectionSearch[chatId][subType])
				{
					return false;
				}

				return state.collectionSearch[chatId][subType].hasNextPage;
			},
			/** @function sidebar/files/getLastId */
			getLastId: (state) => (chatId: number, subType: string): boolean => {
				if (!state.collection[chatId] || !state.collection[chatId][subType])
				{
					return false;
				}

				return state.collection[chatId][subType].lastId;
			},
			/** @function sidebar/files/getSearchResultCollectionLastId */
			getSearchResultCollectionLastId: (state) => (chatId: number, subType: string): boolean => {
				if (!state.collectionSearch[chatId] || !state.collectionSearch[chatId][subType])
				{
					return false;
				}

				return state.collectionSearch[chatId][subType].lastId;
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
				const { chatId, files, subType } = payload;
				if (!Type.isArrayFilled(files) || !Type.isNumber(chatId))
				{
					return;
				}

				files.forEach((file) => {
					const preparedFile = { ...this.getElementState(), ...this.formatFields(file) };
					store.commit('add', { chatId, subType, file: preparedFile });
				});
			},
			/** @function sidebar/files/setSearch */
			setSearch: (store, payload: FilesPayload) => {
				const { chatId, files, subType } = payload;
				if (!Type.isArrayFilled(files) || !Type.isNumber(chatId))
				{
					return;
				}

				files.forEach((file) => {
					const preparedFile = { ...this.getElementState(), ...this.formatFields(file) };
					store.commit('addSearch', { chatId, subType, file: preparedFile });
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
				const { chatId, subType, hasNextPage } = payload;
				if (!Type.isNumber(chatId))
				{
					return;
				}

				if (!store.state.collection[chatId])
				{
					return;
				}

				store.commit('setHasNextPage', { chatId, subType, hasNextPage });
			},
			/** @function sidebar/files/setHasNextPageSearch */
			setHasNextPageSearch: (store, payload: FilesPayload) => {
				const { chatId, subType, hasNextPage } = payload;
				if (!Type.isNumber(chatId))
				{
					return;
				}

				if (!store.state.collectionSearch[chatId])
				{
					return;
				}

				store.commit('setHasNextPageSearch', { chatId, subType, hasNextPage });
			},
			/** @function sidebar/files/setLastId */
			setLastId: (store, payload) => {
				const { chatId, subType, lastId } = payload;
				if (!Type.isNumber(chatId))
				{
					return;
				}

				if (!store.state.collection[chatId])
				{
					return;
				}

				store.commit('setLastId', { chatId, subType, lastId });
			},
			/** @function sidebar/files/setLastIdSearch */
			setLastIdSearch: (store, payload) => {
				const { chatId, subType, lastId } = payload;
				if (!Type.isNumber(chatId))
				{
					return;
				}

				if (!store.state.collectionSearch[chatId])
				{
					return;
				}

				store.commit('setLastIdSearch', { chatId, subType, lastId });
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
			add: (state, payload: {chatId: number, subType: string, file: ImModelSidebarFileItem}) => {
				const { chatId, file, subType } = payload;

				if (!state.collection[chatId])
				{
					state.collection[chatId] = {};
				}

				if (!state.collection[chatId][subType])
				{
					state.collection[chatId][subType] = this.getChatState();
				}
				state.collection[chatId][subType].items.set(file.id, file);
			},
			addSearch: (state, payload: {chatId: number, subType: string, file: ImModelSidebarFileItem}) => {
				const { chatId, file, subType } = payload;

				if (!state.collectionSearch[chatId])
				{
					state.collectionSearch[chatId] = {};
				}

				if (!state.collectionSearch[chatId][subType])
				{
					state.collectionSearch[chatId][subType] = this.getChatState();
				}
				state.collectionSearch[chatId][subType].items.set(file.id, file);
			},
			delete: (state, payload: {chatId: number, id: number}) => {
				const { chatId, id } = payload;
				const hasCollectionSearch = !Type.isNil(state.collectionSearch[chatId]);
				Object.values(SidebarFileTypes).forEach((subType) => {
					if (state.collection[chatId][subType] && state.collection[chatId][subType].items.has(id))
					{
						state.collection[chatId][subType].items.delete(id);
						if (hasCollectionSearch)
						{
							state.collectionSearch[chatId][subType].items.delete(id);
						}
					}
				});
			},
			setHasNextPage: (state, payload) => {
				const { chatId, subType, hasNextPage } = payload;

				if (!state.collection[chatId])
				{
					state.collection[chatId] = {};
				}

				const hasCollection = !Type.isNil(state.collection[chatId][subType]);
				if (!hasCollection)
				{
					state.collection[chatId][subType] = this.getChatState();
				}

				state.collection[chatId][subType].hasNextPage = hasNextPage;
			},
			setHasNextPageSearch: (state, payload) => {
				const { chatId, subType, hasNextPage } = payload;

				if (!state.collectionSearch[chatId])
				{
					state.collectionSearch[chatId] = {};
				}

				const hasCollection = !Type.isNil(state.collectionSearch[chatId][subType]);
				if (!hasCollection)
				{
					state.collectionSearch[chatId][subType] = this.getChatState();
				}

				state.collectionSearch[chatId][subType].hasNextPage = hasNextPage;
			},
			setLastId: (state, payload) => {
				const { chatId, subType, lastId } = payload;

				if (!state.collection[chatId])
				{
					state.collection[chatId] = {};
				}

				const hasCollection = !Type.isNil(state.collection[chatId][subType]);
				if (!hasCollection)
				{
					state.collection[chatId][subType] = this.getChatState();
				}

				state.collection[chatId][subType].lastId = lastId;
			},
			setLastIdSearch: (state, payload) => {
				const { chatId, subType, lastId } = payload;

				if (!state.collectionSearch[chatId])
				{
					state.collectionSearch[chatId] = {};
				}

				const hasCollection = !Type.isNil(state.collectionSearch[chatId][subType]);
				if (!hasCollection)
				{
					state.collectionSearch[chatId][subType] = this.getChatState();
				}

				state.collectionSearch[chatId][subType].lastId = lastId;
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
