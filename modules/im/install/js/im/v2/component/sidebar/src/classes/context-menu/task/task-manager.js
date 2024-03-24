import { Store } from 'ui.vue3.vuex';
import { RestClient } from 'rest.client';

import { RestMethod } from 'im.v2.const';
import { Core } from 'im.v2.application.core';

export class TaskManager
{
	store: Store;
	restClient: RestClient;

	constructor()
	{
		this.store = Core.getStore();
		this.restClient = Core.getRestClient();
	}

	delete({ id, chatId })
	{
		this.store.dispatch('sidebar/tasks/delete', { chatId, id });

		const queryParams = { LINK_ID: id };
		this.restClient.callMethod(RestMethod.imChatTaskDelete, queryParams).catch((error) => {
			console.error('Im.Sidebar: error deleting task', error);
		});
	}
}
