import {RestClient} from 'rest.client';
import {Store} from 'ui.vue3.vuex';

import {Core} from 'im.v2.application.core';
import {RestMethod} from 'im.v2.const';

export class LinkManager
{
	store: Store;
	restClient: RestClient;

	constructor()
	{
		this.store = Core.getStore();
		this.restClient = Core.getRestClient();
	}

	delete(link: Object)
	{
		this.store.dispatch('sidebar/links/delete', {
			chatId: link.chatId,
			id: link.id
		});

		const queryParams = {'LINK_ID': link.id};
		this.restClient.callMethod(RestMethod.imChatUrlDelete, queryParams).catch(error => {
			console.error('Im.Sidebar: error deleting link', error);
		});
	}
}