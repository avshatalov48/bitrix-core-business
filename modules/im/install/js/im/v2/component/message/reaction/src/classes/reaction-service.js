import {Core} from 'im.v2.application.core';
import {RestMethod} from 'im.v2.const';
import {Logger} from 'im.v2.lib.logger';

import type {Store} from 'ui.vue3.vuex';
import type {reactionType as ReactionType} from 'ui.reactions-select';
import type {RestClient} from 'rest.client';

export class ReactionService
{
	#store: Store;
	#restClient: RestClient;

	constructor()
	{
		this.#store = Core.getStore();
		this.#restClient = Core.getRestClient();
	}

	setReaction(messageId: number, reaction: $Values<typeof ReactionType>)
	{
		Logger.warn('ReactionService: setReaction', messageId, reaction);
		this.#store.dispatch('messages/reactions/setReaction', {
			messageId,
			reaction,
			userId: Core.getUserId()
		});
		this.#restClient.callMethod(RestMethod.imV2ChatMessageReactionAdd, {
			messageId,
			reaction
		})
			.catch(error => {
				console.error('ReactionService: error setting reaction', error);
			});
	}

	removeReaction(messageId: number, reaction: $Values<typeof ReactionType>)
	{
		Logger.warn('ReactionService: removeReaction', messageId, reaction);
		this.#store.dispatch('messages/reactions/removeReaction', {
			messageId,
			reaction,
			userId: Core.getUserId()
		});
		this.#restClient.callMethod(RestMethod.imV2ChatMessageReactionDelete, {
				messageId,
				reaction
			})
			.catch(error => {
				console.error('ReactionService: error removing reaction', error);
			});
	}
}