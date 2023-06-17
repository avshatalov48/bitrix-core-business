import {Type} from 'main.core';
import {BuilderModel} from 'ui.vue3.vuex';

import {SidebarFileTypes} from 'im.v2.const';
import {Utils} from 'im.v2.lib.utils';

import type {ImModelSidebarFileItem} from '../registry';

type FilesState = {
	collection: {
		[chatId: number]: {
			[SidebarFileTypes: string]: Map<number, ImModelSidebarFileItem>
		}
	},
};

export class FilesModel extends BuilderModel
{
	getState(): FilesState
	{
		return {
			collection: {},
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

	getGetters(): Object
	{
		return {
			get: (state) => (chatId: number, subType: string): ImModelSidebarFileItem[] => {
				if (!state.collection[chatId] || !state.collection[chatId][subType])
				{
					return [];
				}

				return [...state.collection[chatId][subType].values()].sort((a, b) => b.id - a.id);
			},

			getLatest: (state, getters, rootState, rootGetters) => (chatId: number): ImModelSidebarFileItem[] => {
				if (!state.collection[chatId])
				{
					return [];
				}

				let media = [];
				let audio = [];
				let documents = [];
				let other = [];

				if (state.collection[chatId][SidebarFileTypes.media])
				{
					media = [...state.collection[chatId][SidebarFileTypes.media].values()];
				}
				if (state.collection[chatId][SidebarFileTypes.audio])
				{
					audio = [...state.collection[chatId][SidebarFileTypes.audio].values()];
				}
				if (state.collection[chatId][SidebarFileTypes.document])
				{
					documents = [...state.collection[chatId][SidebarFileTypes.document].values()];
				}
				if (state.collection[chatId][SidebarFileTypes.other])
				{
					other = [...state.collection[chatId][SidebarFileTypes.other].values()];
				}

				const sortedFlatCollection = [media, audio, documents, other]
					.flat()
					.sort((a, b) => b.id - a.id)
				;

				return this.getTopThreeCompletedFiles(sortedFlatCollection, rootGetters);
			},
			getLatestUnsorted: (state, getters, rootState, rootGetters) => (chatId: number): ImModelSidebarFileItem[] => {
				if (!state.collection[chatId])
				{
					return [];
				}

				let unsorted = [];

				if (state.collection[chatId][SidebarFileTypes.fileUnsorted])
				{
					unsorted = [...state.collection[chatId][SidebarFileTypes.fileUnsorted].values()];
				}

				const sortedCollection = unsorted.sort((a, b) => b.id - a.id);

				return this.getTopThreeCompletedFiles(sortedCollection, rootGetters);
			},
			getSize: (state) => (chatId: string, subType: string): number => {
				if (!state.collection[chatId] || !state.collection[chatId][subType])
				{
					return 0;
				}

				return state.collection[chatId][subType].size;
			}
		};
	}

	getActions(): Object
	{
		return {
			set: (store, payload) => {
				const {chatId, files} = payload;
				if (!Type.isArrayFilled(files) || !Type.isNumber(chatId))
				{
					return;
				}

				if (!store.state.collection[chatId])
				{
					store.state.collection[chatId] = {};
				}

				files.forEach(file => {
					const preparedFile = {...this.getElementState(), ...this.validate(file)};
					const {subType} = file;
					store.commit('add', {chatId, subType, file: preparedFile});
				});
			},
			delete: (store, payload) => {
				const {chatId, id} = payload;
				if (!Type.isNumber(id) || !Type.isNumber(chatId))
				{
					return;
				}

				if (!store.state.collection[chatId])
				{
					return;
				}

				store.commit('delete', {chatId, id});
			}
		};
	}

	getMutations(): Object
	{
		return {
			add: (state, payload: {chatId: number, subType: string, file: ImModelSidebarFileItem}) => {
				const {chatId, file, subType} = payload;
				if (!state.collection[chatId][subType])
				{
					state.collection[chatId][subType] = new Map();
				}
				state.collection[chatId][subType].set(file.id, file);
			},
			delete: (state, payload: {chatId: number, id: number}) => {
				const {chatId, id} = payload;

				Object.values(SidebarFileTypes).forEach(subType => {
					if (state.collection[chatId][subType] && state.collection[chatId][subType].has(id))
					{
						state.collection[chatId][subType].delete(id);
					}
				});
			}
		};
	}

	validate(fields: Object): ImModelSidebarFileItem
	{
		const result = {};

		if (Type.isNumber(fields.id))
		{
			result.id = fields.id;
		}

		if (Type.isNumber(fields.messageId))
		{
			result.messageId = fields.messageId;
		}

		if (Type.isNumber(fields.chatId))
		{
			result.chatId = fields.chatId;
		}

		if (Type.isNumber(fields.authorId))
		{
			result.authorId = fields.authorId;
		}

		if (Type.isString(fields.dateCreate))
		{
			result.date = Utils.date.cast(fields.dateCreate);
		}
		else if (Type.isString(fields.date))
		{
			result.date = Utils.date.cast(fields.date);
		}

		result.fileId = Type.isNumber(fields.fileId) ? fields.fileId : result.id;

		return result;
	}

	getTopThreeCompletedFiles(collection: ImModelSidebarFileItem[], rootGetters): ImModelSidebarFileItem[]
	{
		return collection.filter((sidebarFile: ImModelSidebarFileItem) => {
			const file = rootGetters['files/get'](sidebarFile.fileId, true);

			return file.progress === 100;
		}).slice(0, 3);
	}
}
