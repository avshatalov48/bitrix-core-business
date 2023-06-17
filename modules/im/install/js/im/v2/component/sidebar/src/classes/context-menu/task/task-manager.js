import {RestClient} from 'rest.client';
import {Store} from 'ui.vue3.vuex';

import {Core} from 'im.v2.application.core';
import {RestMethod} from 'im.v2.const';

export class TaskManager
{
	store: Store;
	restClient: RestClient;

	constructor()
	{
		this.store = Core.getStore();
		this.restClient = Core.getRestClient();
	}

	delete({id, chatId})
	{
		this.store.dispatch('sidebar/tasks/delete', {
			chatId: chatId,
			id: id
		});

		const queryParams = {'LINK_ID': id};
		this.restClient.callMethod(RestMethod.imChatTaskDelete, queryParams).catch(error => {
			console.error('Im.Sidebar: error deleting task', error);
		});
	}
}