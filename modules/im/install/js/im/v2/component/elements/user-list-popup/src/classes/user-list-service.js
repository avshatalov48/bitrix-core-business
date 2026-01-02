import { Core } from 'im.v2.application.core';
import { RestMethod } from 'im.v2.const';
import { UserManager } from 'im.v2.lib.user';

import type { RestClient } from 'rest.client';

export class UserListService
{
	#restClient: RestClient;
	#userManager: UserManager;

	constructor()
	{
		this.#restClient = Core.getRestClient();
		this.#userManager = new UserManager();
	}

	async loadUsers(userIds: number[]): Promise
	{
		const result: RestResult = await this.#restClient.callMethod(RestMethod.imUserListGet, { ID: userIds })
			.catch((errorResult: RestResult) => {
				console.error('UserListService: loadUsers error', errorResult.error());
			});

		return this.#userManager.setUsersToModel(Object.values(result.data()));
	}
}