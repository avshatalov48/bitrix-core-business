import {Core} from 'im.v2.application.core';
import {RestMethod} from 'im.v2.const';
import {UserManager} from 'im.v2.lib.user';
import {Logger} from 'im.v2.lib.logger';

import type {Store} from 'ui.vue3.vuex';
import type {RestClient} from 'rest.client';

export class UserService
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

	loadReadUsers(messageId): Promise<number[]>
	{
		let users = [];
		Logger.warn('Dialog: UserService: loadReadUsers', messageId);
		return this.#restClient.callMethod(RestMethod.imV2ChatMessageTailViewers, {id: messageId})
			.then(response => {
				users = response.data().users;
				return this.#userManager.setUsersToModel(Object.values(users));
			})
			.then(() => {
				return users.map(user => user.id);
			})
			.catch(error => {
				console.error('Dialog: UserService: loadReadUsers error', error);
				throw new Error(error);
			});
	}
}