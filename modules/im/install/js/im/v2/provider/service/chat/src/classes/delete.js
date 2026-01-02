import { Logger } from 'im.v2.lib.logger';
import { RestMethod } from 'im.v2.const';
import { runAction } from 'im.v2.lib.rest';
import { Notifier } from 'im.v2.lib.notifier';
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
		}).catch(([error]) => {
			console.error('ChatService: deleteChat error:', error);
			Notifier.chat.onDeleteError();
		});

		await this.#updateModels(dialogId);

		return deleteResult;
	}

	async deleteCollab(dialogId: string): Promise
	{
		Logger.warn(`ChatService: deleteCollab, dialogId: ${dialogId}`);

		try
		{
			await runAction(RestMethod.socialnetworkCollabDelete, {
				data: { dialogId },
			});
			await this.#updateModels(dialogId);

			return Promise.resolve();
		}
		catch (errors)
		{
			const [firstError] = errors;
			console.error('ChatService: deleteCollab error:', firstError);
			Notifier.collab.handleDeleteError(firstError);

			return Promise.resolve();
		}
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
