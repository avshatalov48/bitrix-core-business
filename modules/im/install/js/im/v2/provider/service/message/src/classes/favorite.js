import { Store } from 'ui.vue3.vuex';
import { RestClient } from 'rest.client';

import { Core } from 'im.v2.application.core';
import { RestMethod } from 'im.v2.const';
import { Logger } from 'im.v2.lib.logger';
import { Notifier } from 'im.v2.lib.notifier';

export class FavoriteService
{
	#chatId: number;
	#store: Store;
	#restClient: RestClient;

	constructor(chatId: number)
	{
		this.#chatId = chatId;
		this.#store = Core.getStore();
		this.#restClient = Core.getRestClient();
	}

	addMessageToFavorite(messageId: number): void
	{
		Logger.warn('MessageService: addMessageToFavorite', messageId);
		this.#restClient.callMethod(RestMethod.imChatFavoriteAdd, {
			MESSAGE_ID: messageId,
		}).catch((result: RestResult) => {
			console.error('MessageService: error adding message to favorite', result.error());
		});

		Notifier.message.onAddToFavoriteComplete();
	}

	removeMessageFromFavorite(messageId: number): void
	{
		Logger.warn('MessageService: removeMessageFromFavorite', messageId);
		void this.#store.dispatch('sidebar/favorites/deleteByMessageId', {
			chatId: this.#chatId,
			messageId,
		});
		this.#restClient.callMethod(RestMethod.imChatFavoriteDelete, {
			MESSAGE_ID: messageId,
		}).catch((result: RestResult) => {
			console.error('MessageService: error removing message from favorite', result.error());
		});
	}
}
