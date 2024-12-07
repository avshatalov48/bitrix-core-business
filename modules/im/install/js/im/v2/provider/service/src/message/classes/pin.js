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

	pinMessage(chatId: number, messageId: number)
	{
		Logger.warn(`Dialog: PinManager: pin message ${messageId}`);
		this.#store.dispatch('messages/pin/add', {
			chatId,
			messageId,
		});
		this.#restClient.callMethod(RestMethod.imV2ChatMessagePin, { id: messageId })
			.catch((error) => {
				// eslint-disable-next-line no-console
				console.error('Dialog: PinManager: error pinning message', error);
				this.#store.dispatch('messages/pin/delete', {
					chatId,
					messageId,
				});
			});
	}

	unpinMessage(chatId: number, messageId: number)
	{
		Logger.warn(`Dialog: PinManager: unpin message ${messageId}`);
		this.#store.dispatch('messages/pin/delete', {
			chatId,
			messageId,
		});
		this.#restClient.callMethod(RestMethod.imV2ChatMessageUnpin, { id: messageId })
			.catch((error) => {
				// eslint-disable-next-line no-console
				console.error('Dialog: PinManager: error unpinning message', error);
				this.#store.dispatch('messages/pin/add', {
					chatId,
					messageId,
				});
			});
	}
}
