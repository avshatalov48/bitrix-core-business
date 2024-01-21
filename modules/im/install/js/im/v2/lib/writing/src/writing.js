import { Core } from 'im.v2.application.core';
import { ChatType } from 'im.v2.const';

import type { ImModelChat } from 'im.v2.model';

type WritingListItem = {
	userId: number,
	userName: string
};

const writingTimeByChatType = {
	[ChatType.copilot]: 180_000,
	default: 35000,
};

export class WritingManager
{
	static #instance: WritingManager;

	#writingTimers: {[timerId: string]: number} = {};

	static getInstance(): WritingManager
	{
		if (!this.#instance)
		{
			this.#instance = new this();
		}

		return this.#instance;
	}

	startWriting(payload: {dialogId: string, userId: number, userName: string}): void
	{
		const { dialogId, userId, userName } = payload;

		const chat: ImModelChat = this.#getChat(dialogId);
		if (!chat)
		{
			return;
		}

		const timerId = this.#buildTimerId(dialogId, userId);

		if (this.#alreadyWriting(chat, userId))
		{
			this.#clearTimer(timerId);
			this.#writingTimers[timerId] = this.#setTimer(dialogId, userId);

			return;
		}

		const newWritingList = [{ userId, userName }, ...chat.writingList];
		this.#updateChatWritingList(dialogId, newWritingList);

		this.#writingTimers[timerId] = this.#setTimer(dialogId, userId);
	}

	stopWriting(payload: {dialogId: string, userId: number}): void
	{
		const { dialogId, userId } = payload;

		const chat = this.#getChat(dialogId);
		if (!chat)
		{
			return;
		}

		const timerId = this.#buildTimerId(dialogId, userId);

		if (!this.#alreadyWriting(chat, userId))
		{
			return;
		}

		const newWritingList = chat.writingList.filter((item) => item.userId !== userId);
		this.#updateChatWritingList(dialogId, newWritingList);

		this.#clearTimer(timerId);
	}

	#alreadyWriting(chat: ImModelChat, userId: number): boolean
	{
		return chat.writingList.some((el) => el.userId === userId);
	}

	#buildTimerId(dialogId: string, userId: number): string
	{
		return `${dialogId}|${userId}`;
	}

	#setTimer(dialogId: string, userId: number): number
	{
		const writingStatusTime = this.#getWritingTime(dialogId);

		return setTimeout(() => {
			this.stopWriting({ dialogId, userId });
		}, writingStatusTime);
	}

	#clearTimer(timerId: string): void
	{
		clearTimeout(this.#writingTimers[timerId]);
		delete this.#writingTimers[timerId];
	}

	#updateChatWritingList(dialogId: string, writingList: WritingListItem[]): void
	{
		Core.getStore().dispatch('chats/update', {
			dialogId,
			fields: { writingList },
		});
	}

	#getWritingTime(dialogId: string): number
	{
		const chat = this.#getChat(dialogId);

		return writingTimeByChatType[chat.type] ?? writingTimeByChatType.default;
	}

	#getChat(dialogId: string): ImModelChat | null
	{
		return Core.getStore().getters['chats/get'](dialogId);
	}
}
