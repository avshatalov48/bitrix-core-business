import { RestClient } from 'rest.client';

import { Core } from 'im.v2.application.core';
import { RestMethod } from 'im.v2.const';
import { UserManager } from 'im.v2.lib.user';

export class ChatParticipants
{
	#restClient: RestClient;
	#userManager: UserManager;

	constructor()
	{
		this.#restClient = Core.getRestClient();
		this.#userManager = new UserManager();
	}

	load(dialogId: string): Promise<string[]>
	{
		return this.#restClient.callMethod(RestMethod.imDialogUsersList, {
			DIALOG_ID: dialogId,
		}).then((response) => {
			const users = response.data();

			this.#userManager.setUsersToModel(users);

			return users.map((user) => user.id.toString());
		}).catch((error) => {
			console.error('MentionService: error', error);
		});
	}
}
