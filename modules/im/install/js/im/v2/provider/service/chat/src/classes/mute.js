import { Runtime } from 'main.core';
import { Store } from 'ui.vue3.vuex';
import { RestClient } from 'rest.client';

import { Core } from 'im.v2.application.core';
import { Logger } from 'im.v2.lib.logger';
import { RestMethod } from 'im.v2.const';

export class MuteService
{
	#store: Store;
	#restClient: RestClient;

	#sendMuteRequestDebounced: Function;

	constructor()
	{
		this.#store = Core.getStore();
		this.#restClient = Core.getRestClient();

		const DEBOUNCE_TIME = 500;
		this.#sendMuteRequestDebounced = Runtime.debounce(this.#sendMuteRequest, DEBOUNCE_TIME);
	}

	muteChat(dialogId: string)
	{
		Logger.warn('ChatService: muteChat', dialogId);
		void this.#store.dispatch('chats/mute', { dialogId });
		const queryParams = { dialog_id: dialogId, action: 'Y' };

		this.#sendMuteRequestDebounced(queryParams);
	}

	unmuteChat(dialogId: string)
	{
		Logger.warn('ChatService: unmuteChat', dialogId);
		void this.#store.dispatch('chats/unmute', { dialogId });
		const queryParams = { dialog_id: dialogId, action: 'N' };

		this.#sendMuteRequestDebounced(queryParams);
	}

	#sendMuteRequest(queryParams: {dialog_id: string, action: 'Y' | 'N'}): Promise
	{
		const { dialog_id: dialogId, action } = queryParams;

		return this.#restClient.callMethod(RestMethod.imChatMute, queryParams)
			.catch((result: RestResult) => {
				const actionText = action === 'Y' ? 'muting' : 'unmuting';
				console.error(`Im.RecentList: error ${actionText} chat`, result.error());

				const actionType = action === 'Y' ? 'chats/unmute' : 'chats/mute';
				void this.#store.dispatch(actionType, { dialogId });
			});
	}
}
