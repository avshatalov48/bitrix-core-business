import { Type } from 'main.core';
import { BuilderModel } from 'ui.vue3.vuex';

import { MultidialogStatus } from 'im.v2.const';
import { Core } from 'im.v2.application.core';

import { formatFieldsWithConfig } from '../../../utils/validate';
import { sidebarMultidialogFieldsConfig } from './format/field-config';

import type { JsonObject } from 'main.core';
import type { GetterTree, ActionTree, MutationTree } from 'ui.vue3.vuex';
import type { ImModelSidebarMultidialogItem } from '../../../registry';

type MultidialogModelState = {
	isInited: boolean,
	isInitedDetail: boolean,
	chatsCount: number,
	unreadChats: Set<number>,
	openSessionsLimit: number,
	multidialogs: Record<string, ImModelSidebarMultidialogItem>
}
type StatusType = $Values<typeof MultidialogStatus>

/* eslint-disable no-param-reassign */
export class MultidialogModel extends BuilderModel
{
	getState(): MultidialogModelState
	{
		return {
			isInited: false,
			isInitedDetail: false,
			chatsCount: 0,
			unreadChats: new Set(),
			openSessionsLimit: 0,
			multidialogs: {},
		};
	}

	getElementState(): ImModelSidebarMultidialogItem
	{
		return {
			dialogId: '',
			chatId: 0,
			status: '',
			date: new Date(),
		};
	}

	getGetters(): GetterTree
	{
		return {
			/** @function sidebar/multidialog/isInited */
			isInited: ({ isInited }): boolean => {
				return isInited;
			},
			/** @function sidebar/multidialog/isInitedDetail */
			isInitedDetail: ({ isInitedDetail }): boolean => {
				return isInitedDetail;
			},
			/** @function sidebar/multidialog/getOpenSessionsLimit */
			getOpenSessionsLimit: ({ openSessionsLimit }): number => {
				return openSessionsLimit;
			},
			/** @function sidebar/multidialog/getChatsCount */
			getChatsCount: ({ chatsCount }): number => {
				return chatsCount;
			},
			/** @function sidebar/multidialog/getTotalChatCounter */
			getTotalChatCounter: ({ unreadChats }): number => {
				let count = 0;
				unreadChats.forEach((chatId: number) => {
					count += Core.getStore().getters['counters/getChatCounterByChatId'](chatId);
				});

				return count;
			},
			/** @function sidebar/multidialog/get */
			get: ({ multidialogs }) => (chatId): ImModelSidebarMultidialogItem => {
				return multidialogs[chatId];
			},
			/** @function sidebar/multidialog/isSupport */
			isSupport: () => (dialogId): boolean => {
				const isSupportBot = Core.getStore().getters['users/bots/isSupport'](dialogId);
				const isSupportChat = Core.getStore().getters['chats/isSupport'](dialogId);

				return isSupportChat || isSupportBot;
			},
			/** @function sidebar/multidialog/hasNextPage */
			hasNextPage: ({ chatsCount, multidialogs }): boolean => {
				return chatsCount > Object.keys(multidialogs).length;
			},
			/** @function sidebar/multidialog/getNumberMultidialogs */
			getNumberMultidialogs: ({ multidialogs }): number => {
				return Object.keys(multidialogs).length;
			},
			/** @function sidebar/multidialog/getMultidialogsByStatus */
			getMultidialogsByStatus: ({ multidialogs }) => (status: StatusType[]): ImModelSidebarMultidialogItem[] => {
				return Object.values(multidialogs)
					.filter((multidialog: ImModelSidebarMultidialogItem) => status.includes(multidialog.status));
			},
		};
	}

	getActions(): ActionTree
	{
		return {
			/** @function sidebar/multidialog/setInited */
			setInited: (store, isInited: boolean) => {
				store.commit('setInited', isInited);
			},
			/** @function sidebar/multidialog/setInitedDetail */
			setInitedDetail: (store, isInitedDetail: boolean) => {
				store.commit('setInitedDetail', isInitedDetail);
			},
			/** @function sidebar/multidialog/addMultidialogs */
			addMultidialogs: (store, multidialogs: ImModelSidebarMultidialogItem[]) => {
				if (!Type.isArray(multidialogs))
				{
					return;
				}

				multidialogs.forEach((multidialog) => {
					const preparedTicket = { ...this.getElementState(), ...this.formatFields(multidialog) };
					store.commit('addMultidialog', preparedTicket);
				});
			},
			/** @function sidebar/multidialog/setOpenSessionsLimit */
			setOpenSessionsLimit: (store, openSessionsLimit: number) => {
				if (Type.isNumber(openSessionsLimit))
				{
					store.commit('setOpenSessionsLimit', openSessionsLimit);
				}
			},
			/** @function sidebar/multidialog/setChatsCount */
			setChatsCount: (store, chatsCount: number) => {
				if (Type.isNumber(chatsCount))
				{
					store.commit('setChatsCount', chatsCount);
				}
			},
			/** @function sidebar/multidialog/setUnreadChats */
			setUnreadChats: (store, unreadChats: number[]) => {
				if (Type.isArray(unreadChats))
				{
					store.commit('setUnreadChats', unreadChats);
				}
			},
			/** @function sidebar/multidialog/set */
			set: (store, payload) => {
				const { unreadChats, multidialogs, chatsCount, openSessionsLimit } = payload;

				store.dispatch('setUnreadChats', unreadChats);
				store.dispatch('setChatsCount', chatsCount);
				store.dispatch('setOpenSessionsLimit', openSessionsLimit);
				store.dispatch('addMultidialogs', multidialogs);
			},
			/** @function sidebar/multidialog/deleteUnreadChats */
			deleteUnreadChats: (store, chatId: number) => {
				store.commit('deleteUnreadChats', chatId);
			},
		};
	}

	getMutations(): MutationTree
	{
		return {
			/** @function sidebar/multidialog/setInited */
			setInited: (state, isInited) => {
				state.isInited = isInited;
			},
			/** @function sidebar/multidialog/setInitedDetail */
			setInitedDetail: (state, isInitedDetail) => {
				state.isInitedDetail = isInitedDetail;
			},
			addMultidialog: (state, multidialog: ImModelSidebarMultidialogItem) => {
				state.multidialogs[multidialog.chatId] = multidialog;
			},
			setChatsCount: (state, chatsCount) => {
				state.chatsCount = chatsCount;
			},
			setOpenSessionsLimit: (state, openSessionsLimit) => {
				state.openSessionsLimit = openSessionsLimit;
			},
			setUnreadChats: (state, unreadChats) => {
				unreadChats.forEach((chatId) => {
					state.unreadChats.add(chatId);
				});
			},
			deleteUnreadChats: ({ unreadChats }, chatId) => {
				unreadChats.delete(chatId);
			},
		};
	}

	formatFields(fields: JsonObject): JsonObject
	{
		return formatFieldsWithConfig(fields, sidebarMultidialogFieldsConfig);
	}
}
