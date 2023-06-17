import {Store} from 'ui.vue3.vuex';
import {RestClient} from 'rest.client';

import {Messenger} from 'im.public';
import {Core} from 'im.v2.application.core';
import {Layout, RestMethod} from 'im.v2.const';
import {Logger} from 'im.v2.lib.logger';

export class UserService
{
	#store: Store;
	#restClient: RestClient;

	constructor()
	{
		this.#store = Core.getStore();
		this.#restClient = Core.getRestClient();
	}

	addToChat(addConfig: {chatId: number, members: string[], showHistory: boolean}): Promise
	{
		const queryParams = {
			chat_id: addConfig.chatId,
			users: addConfig.members,
			hide_history: !addConfig.showHistory
		};

		return this.#restClient.callMethod(RestMethod.imChatUserAdd, queryParams);
	}

	kickUserFromChat(dialogId: string, userId: number)
	{
		Logger.warn(`UserService: kick user ${userId} from chat ${dialogId}`);
		const chatId = dialogId.slice(4);
		const queryParams = {user_id: userId, chat_id: chatId};
		this.#restClient.callMethod(RestMethod.imChatUserDelete, queryParams).catch(error => {
			console.error('Im.Lib.Menu: error kicking user from chat', error);
		});
	}

	leaveChat(dialogId: string)
	{
		this.kickUserFromChat(dialogId, Core.getUserId());

		this.#store.dispatch('recent/delete', {
			id: dialogId
		});

		const chatIsOpened = this.#store.getters['application/isChatOpen'](dialogId);
		if (chatIsOpened)
		{
			Messenger.openChat();
		}
	}
}