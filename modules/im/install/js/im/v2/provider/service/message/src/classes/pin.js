import { RestClient } from 'rest.client';
import { Store } from 'ui.vue3.vuex';

import { Core } from 'im.v2.application.core';
import { Logger } from 'im.v2.lib.logger';
import { RestMethod } from 'im.v2.const';

export class PinService
{
	#store: Store;
	#restClient: RestClient;

	constructor()
	{
		this.#store = Core.getStore();
		this.#restClient = Core.getRestClient();
	}

	pinMessage(chatId: number, messageId: number): void
	{
		Logger.warn(`Dialog: PinManager: pin message ${messageId}`);
		const payload = {
			chatId,
			messageId,
		};
		void this.#store.dispatch('messages/pin/add', payload);
		this.#restClient.callMethod(RestMethod.imV2ChatMessagePin, { id: messageId })
			.catch((result: RestResult) => {
				console.error('Dialog: PinManager: error pinning message', result.error());
				void this.#store.dispatch('messages/pin/delete', payload);
			});
	}

	unpinMessage(chatId: number, messageId: number): void
	{
		Logger.warn(`Dialog: PinManager: unpin message ${messageId}`);
		const payload = {
			chatId,
			messageId,
		};
		void this.#store.dispatch('messages/pin/delete', payload);
		this.#restClient.callMethod(RestMethod.imV2ChatMessageUnpin, { id: messageId })
			.catch((result: RestResult) => {
				console.error('Dialog: PinManager: error unpinning message', result.error());
				void this.#store.dispatch('messages/pin/add', payload);
			});
	}
}
