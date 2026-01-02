import { Runtime } from 'main.core';

import { Core } from 'im.v2.application.core';
import { Logger } from 'im.v2.lib.logger';
import { RestMethod, AutoDeleteDelay } from 'im.v2.const';
import { runAction } from 'im.v2.lib.rest';
import { FeatureManager } from 'im.v2.lib.feature';

import type { Store } from 'ui.vue3.vuex';
import type { RawMessagesAutoDeleteConfig } from 'im.v2.provider.service.types';

type SetDelayRequestResult = {
	messagesAutoDeleteConfigs: Array<RawMessagesAutoDeleteConfig>;
}

export class MessagesAutoDeleteService
{
	#store: Store;
	#sendRequestDebounced: Function;

	constructor()
	{
		this.#store = Core.getStore();

		const DEBOUNCE_TIME = 500;
		this.#sendRequestDebounced = Runtime.debounce(this.#sendRequest, DEBOUNCE_TIME);
	}

	setDelay(dialogId: string, delay: number): void
	{
		Logger.warn('MessagesAutoDeleteService: setDelay', dialogId, delay);

		const chatId = this.#getChatId(dialogId);
		const previousDelay = this.#store.getters['chats/autoDelete/getDelay'](chatId);
		if (previousDelay === delay)
		{
			return;
		}

		void this.#store.dispatch('chats/autoDelete/set', { chatId, delay });

		this.#sendRequestDebounced({ dialogId, delay, previousDelay });
	}

	async #sendRequest(queryParams: { dialogId: string, delay: number, previousDelay: number }): Promise
	{
		const { dialogId, delay, previousDelay } = queryParams;

		try
		{
			const response = await runAction(RestMethod.imV2ChatSetMessagesAutoDeleteDelay, {
				data: { dialogId, hours: delay },
			});

			this.#handleResponse(delay, response);
		}
		catch (error)
		{
			console.error('MessagesAutoDeleteService: Error setting auto delete delay', error);
			void this.#store.dispatch('chats/autoDelete/set', {
				chatId: this.#getChatId(dialogId),
				delay: previousDelay,
			});
		}
	}

	#handleResponse(delay: number, response: SetDelayRequestResult): boolean
	{
		const [config] = response.messagesAutoDeleteConfigs;
		// if we set some delay and server returns 0 delay, then auto delete is disabled by admin
		if (delay !== config.delay && config.delay === AutoDeleteDelay.Off)
		{
			FeatureManager.messagesAutoDelete.openFeatureSlider();
		}

		void this.#store.dispatch('chats/autoDelete/set', {
			chatId: config.chatId,
			delay: config.delay,
		});
	}

	#getChatId(dialogId: string): number
	{
		return this.#store.getters['chats/get'](dialogId).chatId;
	}
}
