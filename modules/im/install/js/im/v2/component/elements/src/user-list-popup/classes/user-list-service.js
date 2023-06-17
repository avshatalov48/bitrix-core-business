import {Core} from 'im.v2.application.core';
import {RestMethod} from 'im.v2.const';
import {UserManager} from 'im.v2.lib.user';

import type {Store} from 'ui.vue3.vuex';
import type {RestClient} from 'rest.client';

export class UserListService
{
	#store: Store;
	#restClient: RestClient;
	#userManager: UserManager;

	constructor()
	{
		this.#store = Core.getStore();
		this.#restClient = Core.getRestClient();
		this.#userManager = new UserManager();
	}

	loadUsers(userIds: number[]): Promise
	{
		return this.#restClient.callMethod(RestMethod.imUserListGet, {ID: userIds})
			.then(response => {
				return this.#userManager.setUsersToModel(Object.values(response.data()));
			});
	}
}