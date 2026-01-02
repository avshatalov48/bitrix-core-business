import { Store } from 'ui.vue3.vuex';

import { Core } from 'im.v2.application.core';
import { RestMethod } from 'im.v2.const';
import { Logger } from 'im.v2.lib.logger';
import { runAction } from 'im.v2.lib.rest';
import { Notifier } from 'im.v2.lib.notifier';

export class PinService
{
	#store: Store;

	constructor()
	{
		this.#store = Core.getStore();
	}

	pinChat(dialogId: string): void
	{
		Logger.warn('PinService: pinChat', dialogId);
		void this.#store.dispatch('recent/pin', {
			id: dialogId,
			action: true,
		});
		runAction(RestMethod.imV2RecentPin, { data: { dialogId } })
			.catch(([error]) => {
				console.error('PinService: error pinning chat', error);
				Notifier.recent.handlePinError(error);

				void this.#store.dispatch('recent/pin', { id: dialogId, action: false });
			});
	}

	unpinChat(dialogId: string): void
	{
		Logger.warn('PinService: unpinChat', dialogId);
		void this.#store.dispatch('recent/pin', {
			id: dialogId,
			action: false,
		});
		runAction(RestMethod.imV2RecentUnpin, { data: { dialogId } })
			.catch(([error]) => {
				console.error('PinService: error unpinning chat', error);
				Notifier.recent.onUnpinError();

				void this.#store.dispatch('recent/pin', { id: dialogId, action: true });
			});
	}
}
