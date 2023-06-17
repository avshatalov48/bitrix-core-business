import {Runtime} from 'main.core';
import {Store} from 'ui.vue3.vuex';
import {RestClient} from 'rest.client';

import {Core} from 'im.v2.application.core';
import {Logger} from 'im.v2.lib.logger';
import {RestMethod} from 'im.v2.const';

import {ChatService} from '../../chat';

export class MuteService
{
	#store: Store;
	#restClient: RestClient;

	#sendMuteRequestDebounced: Function;

	constructor()
	{
		this.#store = Core.getStore();
		this.#restClient = Core.getRestClient();

		this.#sendMuteRequestDebounced = Runtime.debounce(this.#sendMuteRequest, ChatService.DEBOUNCE_TIME);
	}

	muteChat(dialogId: string)
	{
		Logger.warn('ChatService: muteChat', dialogId);
		this.#store.dispatch('dialogues/mute', {dialogId});
		const queryParams = {'dialog_id': dialogId, 'action': 'Y'};

		this.#sendMuteRequestDebounced(queryParams);
	}

	unmuteChat(dialogId: string)
	{
		Logger.warn('ChatService: unmuteChat', dialogId);
		this.#store.dispatch('dialogues/unmute', {dialogId});
		const queryParams = {'dialog_id': dialogId, 'action': 'N'};

		this.#sendMuteRequestDebounced(queryParams);
	}

	#sendMuteRequest(queryParams: {dialog_id: string, action: 'Y' | 'N'}): Promise
	{
		const {dialog_id: dialogId, action} = queryParams;
		return this.#restClient.callMethod(RestMethod.imChatMute, queryParams).catch(error => {
			const actionText = action === 'Y' ? 'muting' : 'unmuting';
			console.error(`Im.RecentList: error ${actionText} chat`, error);
			const actionType = action === 'Y' ? 'dialogues/unmute' : 'dialogues/mute';
			this.#store.dispatch(actionType, {dialogId});
		});
	}
}