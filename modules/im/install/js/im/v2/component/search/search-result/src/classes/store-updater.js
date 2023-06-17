import {Core} from 'im.v2.application.core';
import {UserManager} from 'im.v2.lib.user';
import {SearchItem} from './search-item';
import {SearchUtils} from './search-utils';

export class StoreUpdater
{
	#store: Object;
	#userManager: UserManager;

	constructor()
	{
		this.#store = Core.getStore();
		this.#userManager = new UserManager();
	}

	update(updateConfig: {items: Map<string, SearchItem>, onlyAdd: boolean}): Promise
	{
		const {items, onlyAdd} = updateConfig;
		const {users, dialogues} = this.#prepareDataForModels(items);

		if (onlyAdd)
		{
			const cleanedUsers = this.#removeActivityData(users);

			return Promise.all([
				this.#userManager.addUsersToModel(cleanedUsers),
				this.#addDialoguesToModel(dialogues)
			]);
		}

		return Promise.all([
			this.#userManager.setUsersToModel(users),
			this.#setDialoguesToModel(dialogues)
		]);
	}

	#addDialoguesToModel(dialogues): Promise
	{
		return this.#store.dispatch('dialogues/add', dialogues);
	}

	#setDialoguesToModel(dialogues): Promise
	{
		return this.#store.dispatch('dialogues/set', dialogues);
	}

	#removeActivityData(users: Object[])
	{
		return users.map(user => {
			return {
				...user,
				last_activity_date: false,
				mobile_last_date: false,
				status: '',
				idle: false,
				absent: false,
				birthday: ''
			};
		});
	}

	#prepareDataForModels(items: Map<string, SearchItem>): { users: Array<Object>, dialogues: Array<Object> }
	{
		const result = {
			users: [],
			dialogues: [],
		};

		items.forEach(item => {
			if (!item.getCustomData() || item.fromStore)
			{
				return;
			}

			if (item.isUser())
			{
				const preparedUser = SearchUtils.convertKeysToLowerCase(item.getUserCustomData());
				result.users.push(preparedUser);
			}

			if (item.isChat() && !item.isOpeLinesType())
			{
				const chat = SearchUtils.convertKeysToLowerCase(item.getChatCustomData());

				result.dialogues.push({
					...chat,
					dialogId: `chat${chat.id}`
				});
			}
		});

		return result;
	}
}