import {BuilderModel} from 'ui.vue3.vuex';
import {Type} from 'main.core';
import {LinksModel} from './sidebar/links';
import {FavoritesModel} from './sidebar/favorites';
import {MembersModel} from './sidebar/members';
import {TasksModel} from './sidebar/tasks';
import {MeetingsModel} from './sidebar/meeting';
import {FilesModel} from './sidebar/files';

export class SidebarModel extends BuilderModel
{
	getName(): string
	{
		return 'sidebar';
	}

	getNestedModules(): { [moduleName: string]: BuilderModel }
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
			isLinksMigrated: false
		};
	}

	getGetters()
	{
		return {
			isInited: (state) => (chatId: number): boolean =>
			{
				return state.initedList.has(chatId);
			},
		};
	}

	getActions()
	{
		return {
			setInited: (store, chatId) =>
			{
				if (!Type.isNumber(chatId))
				{
					return;
				}

				store.commit('setInited', chatId);
			},
			setFilesMigrated: (store, value: boolean) =>
			{
				if (!Type.isBoolean(value))
				{
					return;
				}

				store.commit('setFilesMigrated', value);
			},
			setLinksMigrated: (store, value: boolean) =>
			{
				if (!Type.isBoolean(value))
				{
					return;
				}

				store.commit('setLinksMigrated', value);
			},
		};
	}

	getMutations()
	{
		return {
			setInited: (state, payload) =>
			{
				state.initedList.add(payload);
			},
			setFilesMigrated: (state, payload: boolean) =>
			{
				state.isFilesMigrated = payload;
			},
			setLinksMigrated: (state, payload: boolean) =>
			{
				state.isLinksMigrated = payload;
			},
		};
	}
}