import { Store } from 'ui.vue3.vuex';
import { RestClient } from 'rest.client';

import { Core } from 'im.v2.application.core';
import { Logger } from 'im.v2.lib.logger';
import { Notifier } from 'im.v2.lib.notifier';
import { RestMethod } from 'im.v2.const';

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
		const dialog = this.#store.getters['chats/get'](dialogId);
		const oldName = dialog.name;

		this.#updateChatTitleInModel(dialogId, newName);

		return this.#restClient.callMethod(RestMethod.imChatUpdateTitle, {
			dialog_id: dialogId,
			title: newName,
		}).catch((result: RestResult) => {
			this.#updateChatTitleInModel(dialogId, oldName);
			console.error('ChatService: renameChat error', result.error());
			Notifier.chat.onRenameError();
		});
	}

	#updateChatTitleInModel(dialogId: string, title: string)
	{
		this.#store.dispatch('chats/update', {
			dialogId,
			fields: { name: title },
		});
	}
}
