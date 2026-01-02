import { Core } from 'im.v2.application.core';
import { CopilotManager } from 'im.v2.lib.copilot';
import { Logger } from 'im.v2.lib.logger';
import { runAction } from 'im.v2.lib.rest';
import { UserManager } from 'im.v2.lib.user';

import type { RawChat, RawMessage, RawRecentItem } from 'im.v2.provider.service.types';
import type { BaseRecentQueryParams, RecentRestResult } from './types/base-recent-types';

export class BaseRecentService
{
	#itemsPerPage: number = 50;
	#isLoading: boolean = false;
	#pagesLoaded: number = 0;
	#hasMoreItemsToLoad: boolean = true;
	#lastMessageDate: number = 0;

	loadFirstPage(): Promise
	{
		this.#isLoading = true;

		return this.#requestItems({ firstPage: true });
	}

	loadNextPage(): Promise
	{
		if (this.#isLoading || !this.#hasMoreItemsToLoad)
		{
			return Promise.resolve();
		}

		this.#isLoading = true;

		return this.#requestItems();
	}

	hasMoreItemsToLoad(): boolean
	{
		return this.#hasMoreItemsToLoad;
	}

	getItemsPerPage(): number
	{
		return this.#itemsPerPage;
	}

	getRestMethodName(): string
	{
		throw new Error('BaseRecentList: you should implement "getRestMethodName" for child class');
	}

	getRecentSaveActionName(): string
	{
		throw new Error('BaseRecentList: you should implement "getRecentSaveActionName" for child class');
	}

	getQueryParams(firstPage: boolean = false): BaseRecentQueryParams
	{
		return {
			limit: this.getItemsPerPage(),
			filter: this.getRequestFilter(firstPage),
		};
	}

	getRequestFilter(firstPage: boolean = false): Record
	{
		return {
			lastMessageDate: firstPage ? null : this.#lastMessageDate,
		};
	}

	handlePaginationField(result: RecentRestResult): void
	{
		this.#lastMessageDate = this.#getLastMessageDate(result);
	}

	onAfterRequest(firstPage: boolean): void
	{
		// The base class does nothing here
	}

	async #requestItems({ firstPage = false } = {}): Promise
	{
		const queryParams = {
			data: this.getQueryParams(firstPage),
		};

		const result: RecentRestResult = await runAction(this.getRestMethodName(), queryParams)
			.catch(([error]) => {
				console.error('BaseRecentList: page request error', error);
			});

		this.#pagesLoaded++;
		Logger.warn(`BaseRecentList: ${firstPage ? 'First' : this.#pagesLoaded} page request result`, result);
		const { hasNextPage } = result;
		this.handlePaginationField(result);
		this.#hasMoreItemsToLoad = hasNextPage;

		this.#isLoading = false;

		this.onAfterRequest(firstPage);

		return this.#updateModels(result);
	}

	#updateModels(restResult: RecentRestResult): Promise
	{
		const { users, chats, messages, files, recentItems, messagesAutoDeleteConfigs, copilot } = restResult;
		const chatsWithCounters = this.#getChatsWithCounters(chats, recentItems);

		// private chats objects are empty, so we should handle chats before users to not overwrite real info
		const chatsPromise = Core.getStore().dispatch('chats/set', chatsWithCounters);
		const usersPromise = (new UserManager()).setUsersToModel(users);
		const autoDeletePromise = Core.getStore().dispatch('chats/autoDelete/set', messagesAutoDeleteConfigs);
		const messagesPromise = Core.getStore().dispatch('messages/store', messages);
		const filesPromise = Core.getStore().dispatch('files/set', files);
		const recentPromise = Core.getStore().dispatch(this.getRecentSaveActionName(), recentItems);

		const copilotManager = new CopilotManager();
		const copilotPromise = copilotManager.handleRecentListResponse(copilot);

		return Promise.all([
			usersPromise,
			chatsPromise,
			messagesPromise,
			filesPromise,
			recentPromise,
			autoDeletePromise,
			copilotPromise,
		]);
	}

	#getChatsWithCounters(chats: RawChat[], recentItems: RawRecentItem[]): RawChat[]
	{
		const chatMap = {};
		chats.forEach((chat) => {
			chatMap[chat.id] = chat;
		});
		recentItems.forEach((recentItem) => {
			const { counter, chatId } = recentItem;
			if (counter === 0)
			{
				return;
			}

			chatMap[chatId] = { ...chatMap[chatId], counter };
		});

		return Object.values(chatMap);
	}

	#getLastMessageDate(restResult: RecentRestResult): string
	{
		const messages = this.#filterPinnedItemsMessages(restResult);
		if (messages.length === 0)
		{
			return '';
		}

		// comparing strings in atom format works correctly because the format is lexically sortable
		let firstMessageDate = messages[0].date;
		messages.forEach((message) => {
			if (message.date < firstMessageDate)
			{
				firstMessageDate = message.date;
			}
		});

		return firstMessageDate;
	}

	#filterPinnedItemsMessages(restResult: RecentRestResult): RawMessage[]
	{
		const { messages, recentItems } = restResult;

		return messages.filter((message) => {
			const chatId = message.chat_id;
			const recentItem: RawRecentItem = recentItems.find((item) => {
				return item.chatId === chatId;
			});

			return recentItem.pinned === false;
		});
	}
}
