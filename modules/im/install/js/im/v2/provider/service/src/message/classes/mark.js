import { Store } from 'ui.vue3.vuex';
import { RestClient } from 'rest.client';

import { Core } from 'im.v2.application.core';
import { RestMethod } from 'im.v2.const';
import { Logger } from 'im.v2.lib.logger';

export class MarkService
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

	markMessage(messageId: number)
	{
		Logger.warn('MessageService: markMessage', messageId);
		const { dialogId } = this.#store.getters['chats/getByChatId'](this.#chatId);
		this.#store.dispatch('recent/unread', {
			id: dialogId,
			action: true,
		});
		this.#store.dispatch('chats/update', {
			dialogId,
			fields: { markedId: messageId },
		});
		this.#restClient.callMethod(RestMethod.imV2ChatMessageMark, {
			dialogId,
			id: messageId
		}).catch((error) => {
			// eslint-disable-next-line no-console
			console.error('MessageService: error marking message', error);
		});
	}
}
