import { Type } from 'main.core';
import { BuilderModel } from 'ui.vue3.vuex';

import { Core } from 'im.v2.application.core';

import { LinksModel } from './nested-modules/links/links';
import { FavoritesModel } from './nested-modules/favorites/favorites';
import { MembersModel } from './nested-modules/members';
import { MessageSearchModel } from './nested-modules/message-search/message-search';
import { TasksModel } from './nested-modules/tasks/tasks';
import { MeetingsModel } from './nested-modules/meeting/meeting';
import { FilesModel } from './nested-modules/files/files';
import { MultidialogModel } from './nested-modules/multidialog/multidialog';

import type { GetterTree, ActionTree, MutationTree, NestedModuleTree } from 'ui.vue3.vuex';

type SidebarState = {
	initedList: Set<number>,
	isFilesMigrated: boolean,
	isLinksMigrated: boolean,
};

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
			multidialog: MultidialogModel,
			messageSearch: MessageSearchModel,
		};
	}

	getState(): SidebarState
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
			/** @function sidebar/isInited */
			isInited: (state: SidebarState) => (chatId: number): boolean => {
				return state.initedList.has(chatId);
			},
			/** @function sidebar/hasHistoryLimit */
			hasHistoryLimit: () => (chatId: number): boolean => {
				const limitsByPanel = [
					'sidebar/links/isHistoryLimitExceeded',
					'sidebar/files/isHistoryLimitExceeded',
					'sidebar/favorites/isHistoryLimitExceeded',
					'sidebar/meetings/isHistoryLimitExceeded',
					'sidebar/tasks/isHistoryLimitExceeded',
					'sidebar/messageSearch/isHistoryLimitExceeded',
				].map((getterName) => Core.getStore().getters[getterName](chatId));

				return limitsByPanel.some((hasLimit) => hasLimit);
			},
		};
	}

	getActions(): ActionTree
	{
		return {
			/** @function sidebar/setInited */
			setInited: (store, chatId: number) => {
				if (!Type.isNumber(chatId))
				{
					return;
				}

				store.commit('setInited', chatId);
			},
			/** @function sidebar/setFilesMigrated */
			setFilesMigrated: (store, value: boolean) => {
				if (!Type.isBoolean(value))
				{
					return;
				}

				store.commit('setFilesMigrated', value);
			},
			/** @function sidebar/setLinksMigrated */
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
			setInited: (state: SidebarState, chatId: number) => {
				state.initedList.add(chatId);
			},
			setFilesMigrated: (state: SidebarState, payload: boolean) => {
				state.isFilesMigrated = payload;
			},
			setLinksMigrated: (state: SidebarState, payload: boolean) => {
				state.isLinksMigrated = payload;
			},
		};
	}
}
