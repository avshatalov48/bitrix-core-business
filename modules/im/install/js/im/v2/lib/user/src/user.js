import { Type, type JsonObject } from 'main.core';

import { Core } from 'im.v2.application.core';
import { ChatType, UserRole } from 'im.v2.const';

export class UserManager
{
	store: Object = null;

	constructor()
	{
		this.store = Core.getStore();
	}

	static getDialogForUser(user: JsonObject): JsonObject
	{
		return {
			dialogId: UserManager.getUserId(user),
			avatar: user.avatar,
			color: user.color,
			name: user.name,
			type: ChatType.user,
			role: UserRole.member,
		};
	}

	static getUserId(user: JsonObject): number | string
	{
		return user.id ?? user.networkId ?? 0;
	}

	setUsersToModel(rawUsers: JsonObject | JsonObject[]): Promise
	{
		const { users, chats } = this.#prepareUsersForStore(rawUsers);

		const usersPromise = this.store.dispatch('users/set', users);
		const chatsPromise = this.store.dispatch('chats/set', chats);

		return Promise.all([usersPromise, chatsPromise]);
	}

	addUsersToModel(rawUsers: JsonObject | JsonObject[]): Promise
	{
		const { users, chats } = this.#prepareUsersForStore(rawUsers);

		const usersPromise = this.store.dispatch('users/add', users);
		const chatsPromise = this.store.dispatch('chats/add', chats);

		return Promise.all([usersPromise, chatsPromise]);
	}

	#prepareUsersForStore(rawUsers: JsonObject | JsonObject[]): { chats: JsonObject[], users: JsonObject[] }
	{
		let users = rawUsers;
		if (Type.isPlainObject(users))
		{
			users = [users];
		}

		const chats = [];
		users.forEach((user) => {
			chats.push(UserManager.getDialogForUser(user));
		});

		return { users, chats };
	}
}
