import { Core } from 'im.v2.application.core';

import { ChatType } from 'im.v2.const';
import { RecentStateSearch } from './search/recent-state-search';
import { BaseServerSearch } from './search/base-server-search';
import { ChatParticipants } from './search/chat-participants';

import type { Store } from 'ui.vue3.vuex';
import type { ImModelUser, ImModelChat } from 'im.v2.model';

export class SearchService
{
	#store: Store;
	#recentStateSearch: RecentStateSearch;
	#baseServerSearch: BaseServerSearch;
	#chatParticipants: ChatParticipants;

	constructor(options: {findByParticipants: boolean})
	{
		this.#recentStateSearch = new RecentStateSearch();
		this.#baseServerSearch = new BaseServerSearch(options);
		this.#chatParticipants = new ChatParticipants();
		this.#store = Core.getStore();
	}

	loadLatestResults(): Promise<string[]>
	{
		return this.#baseServerSearch.loadLatestResults();
	}

	loadChatParticipants(dialogId: string): Promise<string[]>
	{
		return this.#chatParticipants.load(dialogId).then((dialogIds) => {
			if (this.#isSelfDialogId(dialogId))
			{
				return dialogIds;
			}

			return dialogIds.filter((element) => !this.#isSelfDialogId(element));
		});
	}

	searchLocal(query: string): Promise<string[]>
	{
		return Promise.resolve(this.#recentStateSearch.search(query));
	}

	searchOnServer(query: string): Promise<string[]>
	{
		return this.#baseServerSearch.search(query);
	}

	addItemToRecent(dialogId: string): Promise
	{
		return this.#baseServerSearch.addItemsToRecentSearchResults(dialogId);
	}

	clearSessionResult()
	{
		this.#baseServerSearch.clearSessionSearch();
	}

	sortByDate(items: string[]): string[]
	{
		items.sort((firstItem, secondItem) => {
			const messageDate1 = this.#store.getters['recent/get'](firstItem, true).message?.date;
			const messageDate2 = this.#store.getters['recent/get'](secondItem, true).message?.date;

			if (!messageDate1 || !messageDate2)
			{
				if (!messageDate1 && !messageDate2)
				{
					if (this.#isExtranet(firstItem))
					{
						return 1;
					}

					if (this.#isExtranet(secondItem))
					{
						return -1;
					}

					return 0;
				}

				return messageDate1 ? -1 : 1;
			}

			return messageDate2 - messageDate1;
		});

		return items;
	}

	#isExtranet(dialogId: string): boolean
	{
		const dialog: ImModelChat = this.#store.getters['chats/get'](dialogId);
		if (!dialog)
		{
			return false;
		}

		if (dialog.type === ChatType.user)
		{
			const user: ImModelUser = this.#store.getters['users/get'](dialogId);

			return user && user.extranet;
		}

		return dialog.extranet;
	}

	#isSelfDialogId(dialogId: string): boolean
	{
		return dialogId === Core.getUserId().toString();
	}
}
