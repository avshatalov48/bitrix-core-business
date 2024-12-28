import { Core } from 'im.v2.application.core';
import { RestMethod } from 'im.v2.const';
import { runAction } from 'im.v2.lib.rest';
import { Logger } from 'im.v2.lib.logger';
import { UserManager } from 'im.v2.lib.user';

import type { CollabRestResult, RawChat, RawMessage, RawRecentItem } from 'im.v2.provider.service';

export class CollabService
{
	firstPageIsLoaded: boolean = false;
	#itemsPerPage: number = 50;
	#isLoading: boolean = false;
	#pagesLoaded: number = 0;
	#hasMoreItemsToLoad: boolean = true;
	#lastMessageDate: number = 0;

	async loadFirstPage(): Promise
	{
		this.#isLoading = true;

		const result = await this.#requestItems({ firstPage: true });
		this.firstPageIsLoaded = true;

		return result;
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

	async #requestItems({ firstPage = false } = {}): Promise
	{
		const queryParams = {
			data: {
				limit: this.#itemsPerPage,
				filter: {
					lastMessageDate: firstPage ? null : this.#lastMessageDate,
				},
			},
		};

		const result: CollabRestResult = await runAction(RestMethod.imV2RecentCollabTail, queryParams)
			.catch((error) => {
				// eslint-disable-next-line no-console
				console.error('Im.CollabList: page request error', error);
			});

		this.#pagesLoaded++;
		Logger.warn(`Im.CollabList: ${firstPage ? 'First' : this.#pagesLoaded} page request result`, result);
		const { hasNextPage } = result;
		this.#lastMessageDate = this.#getLastMessageDate(result);
		if (!hasNextPage)
		{
			this.#hasMoreItemsToLoad = false;
		}

		this.#isLoading = false;

		return this.#updateModels(result);
	}

	#updateModels(restResult: CollabRestResult): Promise
	{
		const { users, chats, messages, files, recentItems } = restResult;
		const chatsWithCounters = this.#getChatsWithCounters(chats, recentItems);

		const usersPromise = (new UserManager()).setUsersToModel(users);
		const dialoguesPromise = Core.getStore().dispatch('chats/set', chatsWithCounters);
		const messagesPromise = Core.getStore().dispatch('messages/store', messages);
		const filesPromise = Core.getStore().dispatch('files/set', files);
		const recentPromise = Core.getStore().dispatch('recent/setCollab', recentItems);

		return Promise.all([usersPromise, dialoguesPromise, messagesPromise, filesPromise, recentPromise]);
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

	#getLastMessageDate(restResult: CollabRestResult): string
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

	#filterPinnedItemsMessages(restResult: CollabRestResult): RawMessage[]
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
