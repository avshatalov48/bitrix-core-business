import { Core } from 'im.v2.application.core';
import { RestMethod } from 'im.v2.const';

import { VoteType } from '../const/vote-type';
import { VoteParamKey } from '../const/vote-params-keys';

import type { ImModelMessage } from 'im.v2.model';

export class VoteService
{
	#messageId: number;
	#dialogId: string;

	constructor(messageId: number, dialogId: string)
	{
		this.#messageId = messageId;
		this.#dialogId = dialogId;
	}

	like(): void
	{
		this.#updateModel({
			vote: VoteType.like,
		});
		Core.getRestClient().callMethod(RestMethod.imBotDialogVote, {
			MESSAGE_ID: this.#messageId,
			DIALOG_ID: this.#dialogId,
			RATING: VoteType.like,
		})
			.catch((error) => {
				// eslint-disable-next-line no-console
				console.error('VoteService: error in dialog vote', error);
			});
	}

	dislike(): void
	{
		this.#updateModel({
			vote: VoteType.dislike,
		});
		Core.getRestClient().callMethod(RestMethod.imBotDialogVote, {
			MESSAGE_ID: this.#messageId,
			DIALOG_ID: this.#dialogId,
			RATING: VoteType.dislike,
		})
			.catch((error) => {
				// eslint-disable-next-line no-console
				console.error('VoteService: error in dialog vote', error);
			});
	}

	#updateModel(params: {vote: $Values<typeof VoteType>})
	{
		const { vote } = params;
		const currentMessage: ImModelMessage = Core.getStore().getters['messages/getById'](this.#messageId);
		const newComponentParams = {
			...currentMessage.componentParams,
			[VoteParamKey.currentVote]: vote,
		};
		Core.getStore().dispatch('messages/update', {
			id: this.#messageId,
			fields: { componentParams: newComponentParams },
		});
	}
}
