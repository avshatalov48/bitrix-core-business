import { Logger } from 'im.v2.lib.logger';
import { RestMethod } from 'im.v2.const';
import { runAction } from 'im.v2.lib.rest';
import { Core } from 'im.v2.application.core';

import type { Store } from 'ui.vue3.vuex';

type RestResult = {
	result: boolean,
};

export class DeleteService
{
	#store: Store;

	constructor()
	{
		this.#store = Core.getStore();
	}

	async deleteChat(dialogId: string): Promise<RestResult>
	{
		Logger.warn(`ChatService: deleteChat, dialogId: ${dialogId}`);

		const deleteResult = await runAction(RestMethod.imV2ChatDelete, {
			data: { dialogId },
		}).catch((error) => {
			// eslint-disable-next-line no-console
			console.error('ChatService: deleteChat error:', error);
			throw new Error(error);
		});

		await this.#updateModels(dialogId);

		return deleteResult;
	}

	async deleteCollab(dialogId: string): Promise<RestResult>
	{
		Logger.warn(`ChatService: deleteCollab, dialogId: ${dialogId}`);

		const deleteResult = await runAction(RestMethod.socialnetworkCollabDelete, {
			data: { dialogId },
		}).catch((error) => {
			// eslint-disable-next-line no-console
			console.error('ChatService: deleteCollab error:', error);
			throw error;
		});

		await this.#updateModels(dialogId);

		return deleteResult;
	}

	#updateModels(dialogId: string): Promise
	{
		void this.#store.dispatch('chats/update', {
			dialogId,
			fields: { inited: false },
		});

		void this.#store.dispatch('recent/delete', { id: dialogId });

		const chat = this.#store.getters['chats/get'](dialogId, true);
		void this.#store.dispatch('messages/clearChatCollection', { chatId: chat.chatId });
	}
}
