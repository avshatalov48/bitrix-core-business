import { Core } from 'im.v2.application.core';
import { EventType } from 'im.v2.const';

import { DesktopApi } from 'im.v2.lib.desktop-api';
import { EventEmitter } from 'main.core.events';

import type { Store } from 'ui.vue3.vuex';

export class CounterHandler
{
	#store: Store;

	static init(): CounterHandler
	{
		return new CounterHandler();
	}

	constructor()
	{
		this.#store = Core.getStore();

		this.#onCounterChange();
		this.#subscribeToCountersChange();
	}

	#subscribeToCountersChange()
	{
		EventEmitter.subscribe(EventType.counter.onNotificationCounterChange, this.#onCounterChange.bind(this));
		EventEmitter.subscribe(EventType.counter.onChatCounterChange, this.#onCounterChange.bind(this));
	}

	#onCounterChange()
	{
		const chatCounter = this.#store.getters['counters/getTotalChatCounter'];
		const notificationCounter = this.#store.getters['notifications/getCounter'];

		const isImportant = chatCounter > 0;
		DesktopApi.setCounter(chatCounter + notificationCounter, isImportant);
	}
}