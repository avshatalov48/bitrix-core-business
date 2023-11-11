import { RestClient } from 'rest.client';

import { Core } from 'im.v2.application.core';
import { RestMethod } from 'im.v2.const';

import type { ImModelMessage } from 'im.v2.model';
import type { Store } from 'ui.vue3.vuex';

export class RichService
{
	#restClient: RestClient;
	#store: Store;
	#message: ImModelMessage;

	constructor(message: ImModelMessage)
	{
		this.#restClient = Core.getRestClient();
		this.#store = Core.getStore();
		this.#message = message;
	}

	deleteRichLink(attachId: string): Promise
	{
		this.#store.dispatch('messages/deleteAttach', {
			messageId: this.#message.id,
			attachId,
		});

		this.#restClient.callMethod(RestMethod.imV2ChatMessageDeleteRichUrl, {
			messageId: this.#message.id,
		}).catch((error) => {
			console.error('RichService: error deleting rich link', error);
		});
	}
}
