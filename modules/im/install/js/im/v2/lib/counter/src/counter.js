import { Type } from 'main.core';
import { EventEmitter, BaseEvent } from 'main.core.events';
import { Store } from 'ui.vue3.vuex';

import { Core } from 'im.v2.application.core';
import { DesktopManager } from 'im.v2.lib.desktop';
import { Logger } from 'im.v2.lib.logger';
import { EventType } from 'im.v2.const';

type InitialCounters = {
	CHAT: {[chatId: string]: number},
	LINES: {[chatId: string]: number},
	COPILOT: {[chatId: string]: number},
	CHANNEL_COMMENT: {
		[channelChatId: string]: {
			[commentChatId: string]: number,
		}
	},
	CHAT_MUTED: number[],
	CHAT_UNREAD: number[],
	TYPE: {
		'ALL': number,
		'CHAT': number,
		'NOTIFY': number,
		'LINES': number,
	}
};

export class CounterManager
{
	static #instance: CounterManager;

	#store: Store;

	static getInstance(): CounterManager
	{
		if (!this.#instance)
		{
			this.#instance = new this();
		}

		return this.#instance;
	}

	static init()
	{
		CounterManager.getInstance();
	}

	constructor()
	{
		this.#store = Core.getStore();
		const { counters } = Core.getApplicationData();
		Logger.warn('CounterManager: counters', counters);
		this.#init(counters);
	}

	removeBrowserTitleCounter()
	{
		const regexp = /^(?<counterWithWhitespace>\(\d+\)\s).*/;
		const matchResult: ?RegExpMatchArray = document.title.match(regexp);
		if (!matchResult?.groups.counterWithWhitespace)
		{
			return;
		}

		const counterPrefixLength = matchResult.groups.counterWithWhitespace;
		document.title = document.title.slice(counterPrefixLength);
	}

	#init(counters: InitialCounters)
	{
		this.#store.dispatch('counters/setUnloadedChatCounters', this.#prepareChatCounters(counters));
		this.#store.dispatch('counters/setUnloadedLinesCounters', counters.LINES);
		this.#store.dispatch('counters/setUnloadedCopilotCounters', counters.COPILOT);
		this.#store.dispatch('counters/setCommentCounters', counters.CHANNEL_COMMENT);
		this.#store.dispatch('notifications/setCounter', counters.TYPE.NOTIFY);

		this.#subscribeToCountersChange();
		this.#sendChatCounterChangeEvent(counters.TYPE.CHAT);
		this.#sendNotificationCounterChangeEvent(counters.TYPE.NOTIFY);
		this.#sendLinesCounterChangeEvent(counters.TYPE.LINES);
	}

	#prepareChatCounters(counters: InitialCounters): {[chatId: string]: number}
	{
		const chatCounters = Type.isArray(counters.CHAT) ? {} : counters.CHAT;
		const markedChats = counters.CHAT_UNREAD;
		markedChats.forEach((markedChatId) => {
			const unreadChatHasCounter = Boolean(chatCounters[markedChatId]);
			if (unreadChatHasCounter)
			{
				return;
			}

			chatCounters[markedChatId] = 1;
		});

		return chatCounters;
	}

	#subscribeToCountersChange()
	{
		this.#store.watch(notificationCounterWatch, (newValue: number) => {
			this.#sendNotificationCounterChangeEvent(newValue);
			this.#onTotalCounterChange();
		});

		this.#store.watch(chatCounterWatch, (newValue: number) => {
			this.#sendChatCounterChangeEvent(newValue);
			this.#onTotalCounterChange();
		});

		this.#store.watch(linesCounterWatch, (newValue: number) => {
			this.#sendLinesCounterChangeEvent(newValue);
			this.#onTotalCounterChange();
		});
	}

	#sendNotificationCounterChangeEvent(notificationsCounter: number)
	{
		const event = new BaseEvent({ compatData: [notificationsCounter] });
		EventEmitter.emit(window, EventType.counter.onNotificationCounterChange, event);
	}

	#sendChatCounterChangeEvent(chatCounter: number)
	{
		const event = new BaseEvent({ compatData: [chatCounter] });
		EventEmitter.emit(window, EventType.counter.onChatCounterChange, event);
	}

	#sendLinesCounterChangeEvent(linesCounter: number)
	{
		const LINES_TYPE = 'LINES';
		const event = new BaseEvent({ compatData: [linesCounter, LINES_TYPE] });
		EventEmitter.emit(window, EventType.counter.onLinesCounterChange, event);
	}

	#onTotalCounterChange()
	{
		const notificationCounter = this.#store.getters['notifications/getCounter'];
		const chatCounter = this.#store.getters['counters/getTotalChatCounter'];
		const linesCounter = this.#store.getters['counters/getTotalLinesCounter'];
		const totalCounter = notificationCounter + chatCounter + linesCounter;

		if (DesktopManager.getInstance().isDesktopActive())
		{
			return;
		}

		this.#updateBrowserTitleCounter(totalCounter);
	}

	#updateBrowserTitleCounter(newCounter: number)
	{
		const regexp = /^\((?<currentCounter>\d+)\)\s(?<text>.*)+/;
		const matchResult: ?RegExpMatchArray = document.title.match(regexp);
		if (matchResult?.groups.currentCounter)
		{
			const currentCounter = Number.parseInt(matchResult.groups.currentCounter, 10);
			if (newCounter !== currentCounter)
			{
				const counterPrefix = newCounter > 0 ? `(${newCounter}) ` : '';
				document.title = `${counterPrefix}${matchResult.groups.text}`;
			}
		}
		else if (newCounter > 0)
		{
			document.title = `(${newCounter}) ${document.title}`;
		}
	}
}

const notificationCounterWatch = (state, getters) => {
	return getters['notifications/getCounter'];
};

const chatCounterWatch = (state, getters) => {
	return getters['counters/getTotalChatCounter'];
};

const linesCounterWatch = (state, getters) => {
	return getters['counters/getTotalLinesCounter'];
};
