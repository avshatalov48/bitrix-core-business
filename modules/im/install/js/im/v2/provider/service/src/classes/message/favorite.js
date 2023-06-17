import {Loc} from 'main.core';
import {Store} from 'ui.vue3.vuex';
import {RestClient} from 'rest.client';

import {Core} from 'im.v2.application.core';
import {RestMethod} from 'im.v2.const';
import {Logger} from 'im.v2.lib.logger';

import 'ui.notification';

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

	addMessageToFavorite(messageId: number)
	{
		Logger.warn('MessageService: addMessageToFavorite', messageId);
		this.#restClient.callMethod(RestMethod.imChatFavoriteAdd, {
			MESSAGE_ID: messageId
		}).catch(error => {
			console.error('MessageService: error adding message to favorite', error);
		});
		BX.UI.Notification.Center.notify({
			content: Loc.getMessage('IM_MESSAGE_SERVICE_SAVE_MESSAGE_SUCCESS')
		});
	}

	removeMessageFromFavorite(messageId: number)
	{
		Logger.warn('MessageService: removeMessageFromFavorite', messageId);
		this.#store.dispatch('sidebar/favorites/deleteByMessageId', {
			chatId: this.#chatId,
			messageId: messageId
		});
		this.#restClient.callMethod(RestMethod.imChatFavoriteDelete, {
			MESSAGE_ID: messageId
		}).catch(error => {
			console.error('MessageService: error removing message from favorite', error);
		});
	}
}