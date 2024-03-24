import { Core } from 'im.v2.application.core';
import { UserManager } from 'im.v2.lib.user';

import type { SearchItem } from './search-item';

export class StoreUpdater
{
	#store: Object;
	#userManager: UserManager;

	constructor()
	{
		this.#store = Core.getStore();
		this.#userManager = new UserManager();
	}

	update(items: Map<string, SearchItem>): Promise
	{
		const { users, dialogues, recentItems } = this.#prepareDataForModels(items);

		return Promise.all([
			this.#userManager.setUsersToModel(users),
			this.#setDialoguesToModel(dialogues),
			this.#setRecentItems(recentItems),
		]);
	}

	updateSearchSession(items: Map<string, SearchItem>): Promise
	{
		const recentSearchItems = this.#prepareSearchDataForModels(items);

		return this.#setRecentSearchItems(recentSearchItems);
	}

	updateRecentWithChatParticipants(users): Promise
	{
		const recentItems = [];

		users.forEach((user) => {
			recentItems.push({
				dialogId: user.id.toString(),
			});
		});

		return Promise.all([
			this.#userManager.setUsersToModel(users),
			this.#setRecentItems(recentItems),
		]);
	}

	#setRecentItems(items): Promise
	{
		return this.#store.dispatch('recent/store', items);
	}

	#setRecentSearchItems(items): Promise
	{
		return this.#store.dispatch('recent/search/set', items);
	}

	#setDialoguesToModel(dialogues): Promise
	{
		return this.#store.dispatch('chats/set', dialogues);
	}

	#prepareDataForModels(items: Map<string, SearchItem>): { users: Array<Object>, dialogues: Array<Object> }
	{
		const result = {
			users: [],
			dialogues: [],
			recentItems: [],
		};

		[...items.values()].forEach((item) => {
			const itemData = item.getCustomData();

			result.recentItems.push({
				dialogId: item.getDialogId(),
				message: {
					date: item.getDate(),
				},
			});

			if (item.isUser())
			{
				result.users.push(itemData);
			}

			if (item.isChat())
			{
				result.dialogues.push({
					...itemData,
					dialogId: item.getDialogId(),
				});
			}
		});

		return result;
	}

	#prepareSearchDataForModels(items: Map<string, SearchItem>): { recentItems: Array<Object> }
	{
		const recentSearchItems = [];

		[...items.values()].forEach((item) => {
			recentSearchItems.push({
				dialogId: item.getDialogId(),
				byUser: item.isFoundByUser(),
			});
		});

		return recentSearchItems;
	}

	clearSessionSearch(): Promise
	{
		return this.#store.dispatch('recent/search/clear');
	}
}
