import {Store} from 'ui.vue3.vuex';
import {RestClient} from 'rest.client';

import {Core} from 'im.v2.application.core';
import {RestMethod} from 'im.v2.const';
import {Logger} from 'im.v2.lib.logger';

import type {ImModelDialog} from 'im.v2.model';

export class EditService
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

	editMessageText(messageId: number, text: string)
	{
		Logger.warn('MessageService: editMessageText', messageId, text);
		this.#store.dispatch('messages/update', {
			id: messageId,
			fields: {
				text,
				isEdited: true
			}
		});

		const dialog: ImModelDialog = this.#store.getters['dialogues/getByChatId'](this.#chatId);
		if (messageId === dialog.lastMessageId)
		{
			this.#store.dispatch('recent/update', {
				id: dialog.dialogId,
				fields: {
					message: {text}
				}
			});
		}

		this.#restClient.callMethod(RestMethod.imMessageUpdate, {
			'ID': messageId,
			'MESSAGE': text
		}).catch(error => {
			console.error('MessageService: editMessageText error:', error);
		});
	}
}