import { Core } from 'im.v2.application.core';
import { UserManager } from 'im.v2.lib.user';
import { SearchEntityIdTypes } from 'im.v2.const';

import type { ImRecentProviderItem } from './types/types';

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
			this.#userManager.setUsersToModel(users),
			this.#store.dispatch('chats/set', chats),
		]);
	}

	#prepareDataForModels(items: ImRecentProviderItem[]): { users: Object[], chats: Object[] }
	{
		const result = {
			users: [],
			chats: [],
		};

		items.forEach((item) => {
			const chatData = item.customData.chat;

			if (item.entityType === SearchEntityIdTypes.imUser)
			{
				result.users.push(item.customData.user);
			}

			if (item.entityType === SearchEntityIdTypes.chat)
			{
				const isUser = Boolean(item.customData.user);
				const userData = isUser ? UserManager.getDialogForUser(item.customData.user) : {};

				result.chats.push({
					...chatData,
					...userData,
					dialogId: item.id,
				});
			}
		});

		return result;
	}
}
