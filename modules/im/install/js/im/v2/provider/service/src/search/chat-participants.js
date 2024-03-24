import { Core } from 'im.v2.application.core';
import { RestMethod } from 'im.v2.const';

import { StoreUpdater } from './store-updater';

import type { RestClient } from 'rest.client';

export class ChatParticipants
{
	#restClient: RestClient;
	#storeUpdater: StoreUpdater;

	constructor()
	{
		this.#restClient = Core.getRestClient();
		this.#storeUpdater = new StoreUpdater();
	}

	load(dialogId: string): Promise<string[]>
	{
		const queryParams = {
			order: {
				lastSendMessageId: 'desc',
			},
			dialogId,
			limit: 50,
		};

		return this.#restClient.callMethod(RestMethod.imV2ChatUserList, queryParams)
			.then((response) => {
				const users = response.data();

				void this.#storeUpdater.updateRecentWithChatParticipants(users);

				return users.map((user) => user.id.toString());
			}).catch((error) => {
				console.error('MentionService: error', error);
			});
	}
}
