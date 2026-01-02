import { EventEmitter, BaseEvent } from 'main.core.events';
import { Runtime } from 'main.core';

import { Core } from 'im.v2.application.core';
import { DesktopManager } from 'im.v2.lib.desktop';
import { Logger } from 'im.v2.lib.logger';
import { EventType, NavigationMenuItem } from 'im.v2.const';

import { updateBrowserTitleCounter } from './helpers/update-browser-title-counter';

import type { Store } from 'ui.vue3.vuex';

type CounterMap = {[chatId: string]: number};

type InitialCounters = {
	CHAT: CounterMap,
	LINES: CounterMap,
	COLLAB: CounterMap,
	COPILOT: CounterMap,
	TASKS_TASK: CounterMap,
	CHANNEL_COMMENT: {
		[channelChatId: string]: {
			[commentChatId: string]: number,
		}
	},
	CHAT_MUTED: number[],
	CHAT_UNREAD: number[],
	COLLAB_UNREAD: number[],
	COPILOT_UNREAD: number[],
	TASKS_TASK_UNREAD: number[],
	TYPE: {
		'ALL': number,
		'CHAT': number,
		'NOTIFY': number,
		'LINES': number,
		'COLLAB': number,
		'TASK': number,
		'COPILOT': number,
		'TASKS_TASK': number,
	}
};

type NavigationCountersPayload = {
	chat: number;
	copilot: number;
	collab: number;
	task: number;
	openlines: number;
	openlinesV2: number;
	notification: number;
};

export class CounterManager
{
	static #instance: CounterManager;

	#store: Store;
	#emitCountersUpdateWithDebounce: Function;

	static getInstance(): CounterManager
	{
		if (!this.#instance)
		{
			this.#instance = new this();
		}

		return this.#instance;
	}

	constructor()
	{
		this.#store = Core.getStore();
		this.#emitCountersUpdateWithDebounce = Runtime.debounce(this.#emitCountersUpdate, 0, this);
		const { counters } = Core.getApplicationData();
		Logger.warn('CounterManager: counters', counters);
		this.#init(counters);
	}

	static init()
	{
		CounterManager.getInstance();
	}

	emitCounters()
	{
		this.#emitCountersUpdate();
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
		const preparedChatCounters = this.#prepareChatCounters(counters.CHAT, counters.CHAT_UNREAD);
		this.#store.dispatch('counters/setUnloadedChatCounters', preparedChatCounters);
		this.#store.dispatch('counters/setUnloadedLinesCounters', counters.LINES);
		const preparedCollabCounters = this.#prepareChatCounters(counters.COLLAB, counters.COLLAB_UNREAD);
		this.#store.dispatch('counters/setUnloadedCollabCounters', preparedCollabCounters);
		const preparedCopilotCounters = this.#prepareChatCounters(counters.COPILOT, counters.COPILOT_UNREAD);
		this.#store.dispatch('counters/setUnloadedCopilotCounters', preparedCopilotCounters);
		const preparedTaskCounters = this.#prepareChatCounters(counters.TASKS_TASK, counters.TASKS_TASK_UNREAD);
		this.#store.dispatch('counters/setUnloadedTaskCounters', preparedTaskCounters);
		this.#store.dispatch('counters/setCommentCounters', counters.CHANNEL_COMMENT);
		this.#store.dispatch('notifications/setCounter', counters.TYPE.NOTIFY);

		this.#emitCountersUpdate();
		this.#subscribeToCountersChange();
		this.#emitLegacyChatCounterUpdate(counters.TYPE.CHAT);
		this.#emitLegacyNotificationCounterUpdate(counters.TYPE.NOTIFY);
		this.#emitLegacyLinesCounterUpdate(counters.TYPE.LINES);
		this.#onTotalCounterChange();
	}

	#prepareChatCounters(counters: CounterMap, unreadCounters: number[]): CounterMap
	{
		const resultCounters = { ...counters };
		unreadCounters.forEach((markedChatId) => {
			const unreadChatHasCounter = Boolean(counters[markedChatId]);
			if (unreadChatHasCounter)
			{
				return;
			}

			resultCounters[markedChatId] = 1;
		});

		return resultCounters;
	}

	#subscribeToCountersChange()
	{
		this.#store.watch(notificationCounterWatch, (newValue: number) => {
			this.#emitLegacyNotificationCounterUpdate(newValue);
			this.#emitCountersUpdateWithDebounce();
			this.#onTotalCounterChange();
		});

		this.#store.watch(chatCounterWatch, (newValue: number) => {
			this.#emitLegacyChatCounterUpdate(newValue);
			this.#emitCountersUpdateWithDebounce();
			this.#onTotalCounterChange();
		});

		this.#store.watch(linesCounterWatch, (newValue: number) => {
			this.#emitLegacyLinesCounterUpdate(newValue);
			this.#emitCountersUpdateWithDebounce();
			this.#onTotalCounterChange();
		});

		this.#store.watch(copilotCounterWatch, () => this.#emitCountersUpdateWithDebounce());
		this.#store.watch(collabCounterWatch, () => this.#emitCountersUpdateWithDebounce());
		this.#store.watch(taskCounterWatch, () => this.#emitCountersUpdateWithDebounce());
	}

	#emitLegacyNotificationCounterUpdate(notificationsCounter: number)
	{
		const event = new BaseEvent({ compatData: [notificationsCounter] });
		EventEmitter.emit(window, EventType.counter.onNotificationCounterChange, event);
	}

	#emitLegacyChatCounterUpdate(chatCounter: number)
	{
		const event = new BaseEvent({ compatData: [chatCounter] });
		EventEmitter.emit(window, EventType.counter.onChatCounterChange, event);
	}

	#emitLegacyLinesCounterUpdate(linesCounter: number)
	{
		const LINES_TYPE = 'LINES';
		const event = new BaseEvent({ compatData: [linesCounter, LINES_TYPE] });
		EventEmitter.emit(window, EventType.counter.onLinesCounterChange, event);
	}

	#emitCountersUpdate()
	{
		const payload: NavigationCountersPayload = {
			[NavigationMenuItem.chat]: this.#store.getters['counters/getTotalChatCounter'],
			[NavigationMenuItem.copilot]: this.#store.getters['counters/getTotalCopilotCounter'],
			[NavigationMenuItem.collab]: this.#store.getters['counters/getTotalCollabCounter'],
			[NavigationMenuItem.tasksTask]: this.#store.getters['counters/getTotalTaskCounter'],
			[NavigationMenuItem.openlines]: this.#store.getters['counters/getTotalLinesCounter'],
			[NavigationMenuItem.openlinesV2]: this.#store.getters['counters/getTotalLinesCounter'],
			[NavigationMenuItem.notification]: this.#store.getters['notifications/getCounter'],
		};

		Logger.warn('CounterManager: Emitting IM.Counters:onUpdate', payload);
		EventEmitter.emit(EventType.counter.onUpdate, payload);
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

		updateBrowserTitleCounter(totalCounter);
	}
}

const notificationCounterWatch = (state, getters) => getters['notifications/getCounter'];
const chatCounterWatch = (state, getters) => getters['counters/getTotalChatCounter'];
const linesCounterWatch = (state, getters) => getters['counters/getTotalLinesCounter'];
const copilotCounterWatch = (state, getters) => getters['counters/getTotalCopilotCounter'];
const collabCounterWatch = (state, getters) => getters['counters/getTotalCollabCounter'];
const taskCounterWatch = (state, getters) => getters['counters/getTotalTaskCounter'];
