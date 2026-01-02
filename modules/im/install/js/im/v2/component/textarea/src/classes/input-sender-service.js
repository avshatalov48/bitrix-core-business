import { Core } from 'im.v2.application.core';
import { RestMethod } from 'im.v2.const';
import { runAction } from 'im.v2.lib.rest';

import type { InputActionType } from 'im.v2.lib.input-action';

const ACTIVE_STATUS_DURATION = 15000;
const REQUEST_DELAY = 5000;

export class InputSenderService
{
	#dialogId: string;

	#statusTimerMap: { [InputActionType]: number } = {};
	#requestDelayMap: { [InputActionType]: number } = {};

	constructor(dialogId: string)
	{
		this.#dialogId = dialogId;
	}

	startAction(actionType: InputActionType): void
	{
		if (this.#isActive(actionType) || this.#isSelfChat())
		{
			return;
		}

		this.#statusTimerMap[actionType] = setTimeout(() => {
			delete this.#statusTimerMap[actionType];
		}, ACTIVE_STATUS_DURATION);

		this.#requestDelayMap[actionType] = setTimeout(() => {
			this.#sendRequest(actionType);
		}, REQUEST_DELAY);
	}

	stopAction(actionType: InputActionType): void
	{
		clearTimeout(this.#statusTimerMap[actionType]);
		delete this.#statusTimerMap[actionType];
		clearTimeout(this.#requestDelayMap[actionType]);
		delete this.#requestDelayMap[actionType];
	}

	#isActive(actionType: InputActionType): boolean
	{
		return Boolean(this.#statusTimerMap[actionType]);
	}

	#sendRequest(actionType: InputActionType): void
	{
		const queryParams = {
			dialogId: this.#dialogId,
			type: actionType,
		};

		runAction(RestMethod.imV2ChatInputActionNotify, {
			data: queryParams,
		}).catch(([error]) => {
			console.error('InputSenderService: sendRequest error', error);
		});
	}

	#isSelfChat(): boolean
	{
		return Number(this.#dialogId) === Core.getUserId();
	}
}
