import { Core } from 'im.v2.application.core';
import { UserManager } from 'im.v2.lib.user';
import { SearchEntityIdTypes } from 'im.v2.const';

import type { ImRecentProviderItem } from './types/recent-provider-item';

export class StoreUpdater
{
	#store: Object;
	#userManager: UserManager;

	constructor()
	{
		this.#store = Core.getStore();
		this.#userManager = new UserManager();
	}

	update(items: ImRecentProviderItem[]): Promise
	{
		const { users, chats } = this.#prepareDataForModels(items);

		return Promise.all([
			this.updateUsers(users),
			this.#updateChats(chats),
		]);
	}

	updateUsers(users): Promise
	{
		return this.#userManager.setUsersToModel(users);
	}

	#updateChats(dialogues): Promise
	{
		return this.#store.dispatch('chats/set', dialogues);
	}

	#prepareDataForModels(items: ImRecentProviderItem[]): { users: Object[], chats: Object[] }
	{
		const result = {
			users: [],
			chats: [],
		};

		items.forEach((item) => {
			const itemData = item.customData;

			if (item.entityType === SearchEntityIdTypes.imUser)
			{
				result.users.push(itemData);
			}

			if (item.entityType === SearchEntityIdTypes.chat)
			{
				result.chats.push({
					...itemData,
					dialogId: item.id,
				});
			}
		});

		return result;
	}
}
