import { Type } from 'main.core';

import { RestMethod } from 'im.v2.const';
import { Core } from 'im.v2.application.core';
import { Logger } from 'im.v2.lib.logger';
import { runAction } from 'im.v2.lib.rest';
import { UserManager } from 'im.v2.lib.user';

import type { Store } from 'ui.vue3.vuex';
import type { JsonObject } from 'main.core';

const REQUEST_ITEMS_LIMIT = 25;

type ChatsGetQueryParams = {
	limit: number,
	offset?: number
}
export class Multidialog
{
	store: Store;
	dialogId: string;
	chatId: number;

	constructor()
	{
		this.store = Core.getStore();
		this.userManager = new UserManager();
	}

	getInitialQuery(): {[$Values<typeof RestMethod>]: JsonObject}
	{
		if (this.isInitedMultidialogBlock())
		{
			return {};
		}

		return {
			[RestMethod.imBotNetworkChatCount]: {},
		};
	}

	getResponseHandler(): Function
	{
		return (response) => {
			if (this.isInitedMultidialogBlock())
			{
				return Promise.resolve();
			}

			if (!response[RestMethod.imBotNetworkChatCount])
			{
				return Promise.reject(new Error('SidebarInfo service error: no response'));
			}

			const setInitedPromise = this.store.dispatch('sidebar/multidialog/setInited', true);
			const updateModelsPromise = this.updateModels(response[RestMethod.imBotNetworkChatCount]);

			return Promise.all([setInitedPromise, updateModelsPromise]);
		};
	}

	loadNextPage(): Promise
	{
		const hasNextPage = this.store.getters['sidebar/multidialog/hasNextPage'];
		if (!hasNextPage)
		{
			return Promise.resolve();
		}

		const offset = this.store.getters['sidebar/multidialog/getNumberMultidialogs'];
		const config = { data: this.getQueryParams({ offset }) };

		return this.requestPage(config);
	}

	getQueryParams(params: ChatsGetQueryParams): ChatsGetQueryParams
	{
		const queryParams: ChatsGetQueryParams = {
			offset: 0,
			limit: REQUEST_ITEMS_LIMIT,
			...params,
		};

		Object.keys(queryParams).forEach((key) => {
			const value = queryParams[key];
			if (Type.isNumber(value) && value > 0)
			{
				queryParams[key] = value;
			}
		});

		return queryParams;
	}

	requestPage(config): Promise
	{
		return runAction(RestMethod.imBotNetworkChatList, config).then((response) => {
			return this.updateModels(response);
		}).catch((error) => {
			console.error('SidebarInfo: imBotNetworkChatList: page request error', error);
		});
	}

	createSupportChat(): Promise<string>
	{
		Logger.warn('SidebarInfo: imBotNetworkChatAdd');

		return runAction(RestMethod.imBotNetworkChatAdd)
			.then((response) => {
				void this.updateModels({ chats: response });
				const { dialogId } = response;
				Logger.warn('SidebarInfo: createSupportChat result', response);

				return dialogId;
			})
			.catch((error) => {
				console.error('SidebarInfo: createSupportChat error:', error);
			});
	}

	loadFirstPage(): Promise
	{
		const isInitedDetail = this.store.getters['sidebar/multidialog/isInitedDetail'];
		if (isInitedDetail)
		{
			return Promise.resolve();
		}

		const numberMultidialogs = this.store.getters['sidebar/multidialog/getNumberMultidialogs'];
		const limit = REQUEST_ITEMS_LIMIT < numberMultidialogs ? numberMultidialogs : REQUEST_ITEMS_LIMIT;
		const config = { data: this.getQueryParams({ limit }) };

		return this.requestPage(config)
			.then(() => {
				return this.store.dispatch('sidebar/multidialog/setInitedDetail', true);
			});
	}

	updateModels(resultData): Promise
	{
		const { count, chatIdsWithCounters, multidialogs, chats, users, openSessionsLimit } = resultData;

		const promises = [];
		if (chats)
		{
			const setChatsPromise = this.store.dispatch('chats/set', chats);
			promises.push(setChatsPromise);
		}

		if (users)
		{
			const setUsersPromise = this.userManager.setUsersToModel(users);
			promises.push(setUsersPromise);
		}

		const setSupportTicketPromise = this.store.dispatch('sidebar/multidialog/set', {
			chatsCount: count,
			unreadChats: chatIdsWithCounters,
			multidialogs,
			openSessionsLimit,
		});
		promises.push(setSupportTicketPromise);

		return Promise.all(promises);
	}

	isInitedMultidialogBlock(): boolean
	{
		return this.store.getters['sidebar/multidialog/isInited'];
	}
}
