import { Type } from 'main.core';
import { BuilderModel } from 'ui.vue3.vuex';

import { LinksModel } from './nested-modules/links/links';
import { FavoritesModel } from './nested-modules/favorites/favorites';
import { MembersModel } from './nested-modules/members';
import { TasksModel } from './nested-modules/tasks/tasks';
import { MeetingsModel } from './nested-modules/meeting/meeting';
import { FilesModel } from './nested-modules/files/files';

import type { GetterTree, ActionTree, MutationTree, NestedModuleTree } from 'ui.vue3.vuex';

/* eslint-disable no-param-reassign */
export class SidebarModel extends BuilderModel
{
	getName(): string
	{
		return 'sidebar';
	}

	getNestedModules(): NestedModuleTree
	{
		return {
			members: MembersModel,
			links: LinksModel,
			favorites: FavoritesModel,
			tasks: TasksModel,
			meetings: MeetingsModel,
			files: FilesModel,
		};
	}

	getState()
	{
		return {
			initedList: new Set(),
			isFilesMigrated: false,
			isLinksMigrated: false,
		};
	}

	getGetters(): GetterTree
	{
		return {
			isInited: (state) => (chatId: number): boolean => {
				return state.initedList.has(chatId);
			},
		};
	}

	getActions(): ActionTree
	{
		return {
			setInited: (store, chatId: number) => {
				if (!Type.isNumber(chatId))
				{
					return;
				}

				store.commit('setInited', chatId);
			},
			setFilesMigrated: (store, value: boolean) => {
				if (!Type.isBoolean(value))
				{
					return;
				}

				store.commit('setFilesMigrated', value);
			},
			setLinksMigrated: (store, value: boolean) => {
				if (!Type.isBoolean(value))
				{
					return;
				}

				store.commit('setLinksMigrated', value);
			},
		};
	}

	getMutations(): MutationTree
	{
		return {
			setInited: (state, chatId: number) => {
				state.initedList.add(chatId);
			},
			setFilesMigrated: (state, payload: boolean) => {
				state.isFilesMigrated = payload;
			},
			setLinksMigrated: (state, payload: boolean) => {
				state.isLinksMigrated = payload;
			},
		};
	}
}
