import {RestClient} from 'rest.client';
import {Store} from 'ui.vue3.vuex';

import {Core} from 'im.v2.application.core';
import {RestMethod} from 'im.v2.const';

export class MeetingManager
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
		this.store.dispatch('sidebar/meetings/delete', {
			chatId: chatId,
			id: id
		});

		const queryParams = {'LINK_ID': id};
		this.restClient.callMethod(RestMethod.imChatCalendarDelete, queryParams).catch(error => {
			console.error('Im.Sidebar: error deleting meeting', error);
		});
	}
}