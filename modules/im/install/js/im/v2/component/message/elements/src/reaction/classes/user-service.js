import { Core } from 'im.v2.application.core';
import { RestMethod } from 'im.v2.const';
import { UserManager } from 'im.v2.lib.user';
import { Logger } from 'im.v2.lib.logger';

import type { RestClient } from 'rest.client';

export class UserService
{
	#restClient: RestClient;
	#userManager: UserManager;

	constructor()
	{
		this.#restClient = Core.getRestClient();
		this.#userManager = new UserManager();
	}

	async loadReactionUsers(messageId: number, reaction: string): Promise<number[]>
	{
		Logger.warn('Reactions: UserService: loadReactionUsers', messageId, reaction);
		const queryParams = {
			messageId,
			filter: { reaction },
		};

		const response = await this.#restClient.callMethod(RestMethod.imV2ChatMessageReactionTail, queryParams)
			.catch((result: RestResult) => {
				console.error('Reactions: UserService: loadReactionUsers error', result.error());
				throw result.error();
			});

		const users = response.data().users;
		await this.#userManager.setUsersToModel(Object.values(users));

		return users.map((user) => user.id);
	}
}
