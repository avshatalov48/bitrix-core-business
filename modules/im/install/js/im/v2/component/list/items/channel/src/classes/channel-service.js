import { Core } from 'im.v2.application.core';
import { RestMethod } from 'im.v2.const';
import { runAction } from 'im.v2.lib.rest';
import { Logger } from 'im.v2.lib.logger';
import { UserManager } from 'im.v2.lib.user';

import type { ChannelRestResult } from 'im.v2.provider.service';

export class ChannelService
{
	firstPageIsLoaded: boolean = false;
	#itemsPerPage: number = 50;
	#isLoading: boolean = false;
	#pagesLoaded: number = 0;
	#hasMoreItemsToLoad: boolean = true;
	#lastMessageId: number = 0;

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
					lastMessageId: firstPage ? null : this.#lastMessageId,
				},
			},
		};

		const result: ChannelRestResult = await runAction(RestMethod.imV2RecentChannelTail, queryParams)
			.catch((error) => {
				// eslint-disable-next-line no-console
				console.error('Im.ChannelList: page request error', error);
			});

		this.#pagesLoaded++;
		Logger.warn(`Im.ChannelList: ${firstPage ? 'First' : this.#pagesLoaded} page request result`, result);
		const { messages, hasNextPage } = result;
		this.#lastMessageId = this.#getMinMessageId(messages);
		if (!hasNextPage)
		{
			this.#hasMoreItemsToLoad = false;
		}

		this.#isLoading = false;

		if (firstPage)
		{
			void Core.getStore().dispatch('recent/clearChannelCollection');
		}

		return this.#updateModels(result);
	}

	#updateModels(restResult: ChannelRestResult): Promise
	{
		const { users, chats, messages, files, recentItems } = restResult;

		const usersPromise = Core.getStore().dispatch('users/set', users);
		const dialoguesPromise = Core.getStore().dispatch('chats/set', chats);
		const messagesPromise = Core.getStore().dispatch('messages/store', messages);
		const filesPromise = Core.getStore().dispatch('files/set', files);
		const recentPromise = Core.getStore().dispatch('recent/setChannel', recentItems);

		return Promise.all([usersPromise, dialoguesPromise, messagesPromise, filesPromise, recentPromise]);
	}

	#getMinMessageId(messages: Array): string
	{
		if (messages.length === 0)
		{
			return 0;
		}

		const firstMessageId = messages[0].id;

		return messages.reduce((minId, nextMessage) => {
			return Math.min(minId, nextMessage.id);
		}, firstMessageId);
	}
}
