import {Store} from 'ui.vue3.vuex';
import {RestClient} from 'rest.client';

import {Core} from 'im.v2.application.core';
import {RestMethod} from 'im.v2.const';
import {Logger} from 'im.v2.lib.logger';

export class PinService
{
	#store: Store;
	#restClient: RestClient;

	constructor()
	{
		this.#store = Core.getStore();
		this.#restClient = Core.getRestClient();
	}

	pinChat(dialogId: string)
	{
		Logger.warn('PinService: pinChat', dialogId);
		this.#store.dispatch('recent/pin', {id: dialogId, action: true});
		const queryParams = {'DIALOG_ID': dialogId, 'ACTION': 'Y'};
		this.#restClient.callMethod(RestMethod.imRecentPin, queryParams).catch(error => {
			console.error('PinService: error pinning chat', error);
			this.#store.dispatch('recent/pin', {id: dialogId, action: false});
		});
	}

	unpinChat(dialogId: string)
	{
		Logger.warn('PinService: unpinChat', dialogId);
		this.#store.dispatch('recent/pin', {id: dialogId, action: false});
		const queryParams = {'DIALOG_ID': dialogId, 'ACTION': 'N'};
		this.#restClient.callMethod(RestMethod.imRecentPin, queryParams).catch(error => {
			console.error('PinService: error unpinning chat', error);
			this.#store.dispatch('recent/pin', {id: dialogId, action: true});
		});
	}
}