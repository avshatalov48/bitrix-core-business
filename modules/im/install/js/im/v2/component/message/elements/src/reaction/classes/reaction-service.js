import { Core } from 'im.v2.application.core';
import { RestMethod } from 'im.v2.const';
import { Logger } from 'im.v2.lib.logger';

import type { Store } from 'ui.vue3.vuex';
import type { reactionType as ReactionType } from 'ui.reactions-select';
import type { RestClient } from 'rest.client';

export class ReactionService
{
	#store: Store;
	#restClient: RestClient;

	constructor()
	{
		this.#store = Core.getStore();
		this.#restClient = Core.getRestClient();
	}

	setReaction(messageId: number, reaction: $Values<typeof ReactionType>): void
	{
		Logger.warn('ReactionService: setReaction', messageId, reaction);
		const payload = {
			messageId,
			reaction,
		};
		void this.#store.dispatch('messages/reactions/setReaction', {
			...payload,
			userId: Core.getUserId(),
		});
		this.#restClient.callMethod(RestMethod.imV2ChatMessageReactionAdd, payload)
			.catch((result: RestResult) => {
				console.error('ReactionService: error setting reaction', result.error());
			});
	}

	removeReaction(messageId: number, reaction: $Values<typeof ReactionType>): void
	{
		Logger.warn('ReactionService: removeReaction', messageId, reaction);
		const payload = {
			messageId,
			reaction,
		};
		void this.#store.dispatch('messages/reactions/removeReaction', {
			...payload,
			userId: Core.getUserId(),
		});

		this.#restClient.callMethod(RestMethod.imV2ChatMessageReactionDelete, payload)
			.catch((result: RestResult) => {
				console.error('ReactionService: error removing reaction', result.error());
			});
	}
}
