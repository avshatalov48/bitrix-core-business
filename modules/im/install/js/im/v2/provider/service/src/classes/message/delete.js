import {Store} from 'ui.vue3.vuex';
import {RestClient} from 'rest.client';

import {Core} from 'im.v2.application.core';
import {RestMethod} from 'im.v2.const';
import {Logger} from 'im.v2.lib.logger';

import type {ImModelDialog} from 'im.v2.model';

export class DeleteService
{
	#store: Store;
	#restClient: RestClient;
	#chatId: number;

	constructor(chatId: number)
	{
		this.#chatId = chatId;
		this.#store = Core.getStore();
		this.#restClient = Core.getRestClient();
	}

	deleteMessage(messageId: number)
	{
		Logger.warn('MessageService: deleteMessage', messageId);
		this.#store.dispatch('messages/update', {
			id: messageId,
			fields: {
				text: '',
				params: {'IS_DELETED': 'Y', 'FILE_ID': []}
			},
		});

		const dialog: ImModelDialog = this.#store.getters['dialogues/getByChatId'](this.#chatId);
		if (messageId === dialog.lastMessageId)
		{
			this.#store.dispatch('recent/update', {
				id: dialog.dialogId,
				fields: {
					message: {text: ''}
				}
			});
		}

		this.#restClient.callMethod(RestMethod.imMessageDelete, {
			'ID': messageId
		}).catch(error => {
			console.error('MessageService: deleteMessage error:', error);
		});
	}
}