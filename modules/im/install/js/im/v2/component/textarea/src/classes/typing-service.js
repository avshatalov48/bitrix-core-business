import {RestClient} from 'rest.client';

import {Core} from 'im.v2.application.core';
import {RestMethod} from 'im.v2.const';

const TYPING_DURATION = 15000;
const TYPING_REQUEST_TIMEOUT = 5000;

export class TypingService
{
	#dialogId: string;
	#restClient: RestClient;

	#isTyping: boolean = false;
	#typingTimeout: number;
	#typingRequestTimeout: number;

	constructor(dialogId: string)
	{
		this.#dialogId = dialogId;
		this.#restClient = Core.getRestClient();
	}

	startTyping()
	{
		if (this.#isTyping || this.#isSelfChat())
		{
			return;
		}

		this.#isTyping = true;
		this.#typingTimeout = setTimeout(() => {
			this.#isTyping = false;
		}, TYPING_DURATION);

		this.#typingRequestTimeout = setTimeout(() => {
			this.#restClient.callMethod(RestMethod.imDialogWriting, {
				'DIALOG_ID': this.#dialogId
			}).catch(error => {
				console.error('TypingService: startTyping error', error);
			});
		}, TYPING_REQUEST_TIMEOUT);
	}

	stopTyping()
	{
		clearTimeout(this.#typingTimeout);
		clearTimeout(this.#typingRequestTimeout);
		this.#isTyping = false;
	}

	#isSelfChat(): boolean
	{
		return Number.parseInt(this.#dialogId, 10) === Core.getUserId();
	}
}