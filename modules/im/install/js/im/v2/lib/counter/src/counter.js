import {Type} from 'main.core';
import {EventEmitter, BaseEvent} from 'main.core.events';
import {Store} from 'ui.vue3.vuex';

import {Core} from 'im.v2.application.core';
import {DesktopManager} from 'im.v2.lib.desktop';

type InitialCounters = {
	CHAT: {[chatId: string]: number},
	CHAT_MUTED: number[],
	CHAT_UNREAD: number[],
	TYPE: {
		'ALL': number,
		'NOTIFY': number,
	}
};

const NOTIFICATION_COUNTER_UPDATE_EVENT = 'onImUpdateCounterNotify';
const CHAT_COUNTER_UPDATE_EVENT = 'onImUpdateCounterMessage';

export class CounterManager
{
	static instance: CounterManager;

	#store: Store;

	static getInstance(): CounterManager
	{
		if (!this.instance)
		{
			this.instance = new this();
		}

		return this.instance;
	}

	static init(counters: InitialCounters)
	{
		CounterManager.getInstance().init(counters);
	}

	constructor()
	{
		this.#store = Core.getStore();
	}

	init(counters: InitialCounters)
	{
		this.#store.dispatch('recent/setUnloadedChatCounters', this.#prepareChatCounters(counters));
		this.#store.dispatch('notifications/setCounter', counters['TYPE']['NOTIFY']);

		this.#subscribeToCountersChange();
		this.#sendNotificationCounterChangeEvent(counters['TYPE']['NOTIFY']);
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

	#prepareChatCounters(counters: InitialCounters): {[chatId: string]: number}
	{
		const chatCounters = Type.isArray(counters['CHAT']) ? {} : counters['CHAT'];
		const markedChats = counters['CHAT_UNREAD'];
		markedChats.forEach(markedChatId => {
			const unreadChatHasCounter = !!chatCounters[markedChatId];
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
	}

	#sendNotificationCounterChangeEvent(notificationsCounter: number)
	{
		const event = new BaseEvent({compatData: [notificationsCounter]});
		EventEmitter.emit(window, NOTIFICATION_COUNTER_UPDATE_EVENT, event);
	}

	#sendChatCounterChangeEvent(chatCounter: number)
	{
		const event = new BaseEvent({compatData: [chatCounter]});
		EventEmitter.emit(window, CHAT_COUNTER_UPDATE_EVENT, event);
	}

	#onTotalCounterChange()
	{
		const notificationCounter = this.#store.getters['notifications/getCounter'];
		const chatCounter = this.#store.getters['recent/getTotalCounter'];
		const totalCounter = notificationCounter + chatCounter;

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
	return getters['recent/getTotalCounter'];
};