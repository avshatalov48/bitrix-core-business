import {Store} from 'ui.vue3.vuex';
import {RestClient} from 'rest.client';

import {Core} from 'im.v2.application.core';
import {Logger} from 'im.v2.lib.logger';
import {RestMethod} from 'im.v2.const';

export class RenameService
{
	#store: Store;
	#restClient: RestClient;

	constructor()
	{
		this.#store = Core.getStore();
		this.#restClient = Core.getRestClient();
	}

	renameChat(dialogId: string, newName: string): Promise
	{
		Logger.warn('ChatService: renameChat', dialogId, newName);
		if (newName === '')
		{
			return Promise.resolve();
		}
		const dialog = this.#store.getters['dialogues/get'](dialogId);
		const oldName = dialog.name;

		this.#updateChatTitleInModel(dialogId, newName);

		return this.#restClient.callMethod(RestMethod.imChatUpdateTitle, {
			dialog_id: dialogId,
			title: newName
		}).then(result => {
			Logger.warn('ChatService: renameChat result', result.data());
			return Promise.resolve();
		}).catch(() => {
			this.#updateChatTitleInModel(dialogId, oldName);

			throw new Error('Chat rename error');
		});
	}

	#updateChatTitleInModel(dialogId: string, title: string)
	{
		this.#store.dispatch('dialogues/update', {
			dialogId,
			fields: {
				name: title
			}
		});
	}
}