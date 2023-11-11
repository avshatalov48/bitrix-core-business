import { Core } from 'im.v2.application.core';

import { DialogType } from 'im.v2.const';
import { RecentStateSearch } from './search/recent-state-search';
import { BaseServerSearch } from './search/base-server-search';
import { ChatParticipants } from './search/chat-participants';

import type { Store } from 'ui.vue3.vuex';
import type { ImModelUser, ImModelDialog } from 'im.v2.model';

export class SearchService
{
	#store: Store;
	#recentStateSearch: RecentStateSearch;
	#baseServerSearch: BaseServerSearch;
	#chatParticipants: ChatParticipants;

	constructor()
	{
		this.#recentStateSearch = new RecentStateSearch();
		this.#baseServerSearch = new BaseServerSearch();
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
			const dateUpdate1 = this.#store.getters['recent/get'](firstItem).dateUpdate;
			const dateUpdate2 = this.#store.getters['recent/get'](secondItem).dateUpdate;

			if (!dateUpdate1 || !dateUpdate2)
			{
				if (!dateUpdate1 && !dateUpdate2)
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

				return dateUpdate1 ? -1 : 1;
			}

			return dateUpdate2 - dateUpdate1;
		});

		return items;
	}

	#isExtranet(dialogId: string): boolean
	{
		const dialog: ImModelDialog = this.#store.getters['dialogues/get'](dialogId);
		if (!dialog)
		{
			return false;
		}

		if (dialog.type === DialogType.user)
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
