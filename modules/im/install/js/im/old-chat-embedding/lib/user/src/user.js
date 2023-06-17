import {Type} from 'main.core';

import {Core} from 'im.old-chat-embedding.application.core';
import {DialogType} from 'im.old-chat-embedding.const';

export class UserManager
{
	store: Object = null;

	constructor()
	{
		this.store = Core.getStore();
	}

	static getDialogForUser(user: Object): Object
	{
		return {
			dialogId: user.id,
			avatar: user.avatar,
			color: user.color,
			name: user.name,
			type: DialogType.user
		};
	}

	setUsersToModel(rawUsers: Object[]): Promise
	{
		const {users, dialogues} = this.#prepareUsersForStore(rawUsers);

		const usersPromise = this.store.dispatch('users/set', users);
		const dialoguesPromise = this.store.dispatch('dialogues/set', dialogues);

		return Promise.all([usersPromise, dialoguesPromise]);
	}

	addUsersToModel(rawUsers: Object[]): Promise
	{
		const {users, dialogues} = this.#prepareUsersForStore(rawUsers);

		const usersPromise = this.store.dispatch('users/add', users);
		const dialoguesPromise = this.store.dispatch('dialogues/add', dialogues);

		return Promise.all([usersPromise, dialoguesPromise]);
	}

	#prepareUsersForStore(users: Object[])
	{
		if (Type.isPlainObject(users))
		{
			users = [users];
		}

		const dialogues = [];
		users.forEach(user => {
			dialogues.push(UserManager.getDialogForUser(user));
		});

		return {users, dialogues};
	}
}