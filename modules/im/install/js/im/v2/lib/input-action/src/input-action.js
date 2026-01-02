import { Core } from 'im.v2.application.core';
import { ChatType } from 'im.v2.const';

import type { ImModelChat } from 'im.v2.model';

export const InputAction = {
	writing: 'writing',
	recordingVoice: 'recordingVoice',
	sendingFile: 'sendingFile',
};

export type InputActionType = $Values<typeof InputAction>;

type ActionPayload = {
	type: InputActionType,
	dialogId: string,
	userId: number,
	userName?: string,
	statusMessageCode: string | null,
	userFirstName: string,
	duration: number | null,
};

const DEFAULT_ACTION_DURATION = 25000;
const ActionDurationMap = {
	[InputAction.writing]: {
		[ChatType.copilot]: 180_000,
		default: DEFAULT_ACTION_DURATION,
	},
	[InputAction.recordingVoice]: {
		default: DEFAULT_ACTION_DURATION,
	},
	[InputAction.sendingFile]: {
		default: DEFAULT_ACTION_DURATION,
	},
};

export class InputActionListener
{
	static #instance: InputActionListener;

	#actionTimers: {[timerId: string]: number} = {};

	static getInstance(): InputActionListener
	{
		if (!this.#instance)
		{
			this.#instance = new this();
		}

		return this.#instance;
	}

	startAction(actionPayload: ActionPayload)
	{
		if (this.#isAlreadyActive(actionPayload))
		{
			this.stopAction(actionPayload);
		}

		void Core.getStore().dispatch('chats/inputActions/start', actionPayload);
		const timerId = this.#buildTimerId(actionPayload);
		this.#actionTimers[timerId] = this.#setTimer(actionPayload);
	}

	stopAction(actionPayload: { userId: number, dialogId: string })
	{
		if (!this.#isAlreadyActive(actionPayload))
		{
			return;
		}

		const timerId = this.#buildTimerId(actionPayload);
		this.#clearTimer(timerId);

		void Core.getStore().dispatch('chats/inputActions/stop', actionPayload);
	}

	clear()
	{
		Object.values(this.#actionTimers).forEach((timerId) => {
			clearTimeout(timerId);
		});
		this.#actionTimers = {};
	}

	#isAlreadyActive(payload: ActionPayload): boolean
	{
		return Core.getStore().getters['chats/inputActions/isActionActive'](payload);
	}

	#buildTimerId(payload: ActionPayload): string
	{
		const { dialogId, userId } = payload;

		return `${dialogId}|${userId}`;
	}

	#setTimer(payload: ActionPayload): number
	{
		const actionDuration = this.#getActionDuration(payload);

		return setTimeout(() => {
			this.stopAction(payload);
		}, actionDuration);
	}

	#clearTimer(timerId: string): void
	{
		clearTimeout(this.#actionTimers[timerId]);
		delete this.#actionTimers[timerId];
	}

	#getActionDuration(payload: ActionPayload): number
	{
		const { type, dialogId, duration } = payload;
		if (duration && duration > 0)
		{
			return duration;
		}

		const typeDurationMap = ActionDurationMap[type];
		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId, true);

		return typeDurationMap[chat.type] ?? typeDurationMap.default;
	}
}
