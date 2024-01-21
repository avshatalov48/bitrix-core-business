import { Loc, Type, Event } from 'main.core';
import { EventEmitter } from 'main.core.events';

import { EventType } from 'im.v2.const';
import { Core } from 'im.v2.application.core';

import type { JsonObject } from 'main.core';

const WORKER_PATH = '/bitrix/js/im/v2/lib/update-state/shared-worker/dist/shared-worker.bundle.js';
const WORKER_NAME = 'Bitrix24 UpdateState';
const CSRF_TOKEN_NAME = 'bitrix_sessid';

export class UpdateStateManager
{
	worker: SharedWorker;

	static init(): UpdateStateManager
	{
		return new UpdateStateManager();
	}

	constructor()
	{
		if (!('SharedWorker' in window))
		{
			return;
		}

		this.registerSharedWorker();
		this.subscribeToEvents();
	}

	registerSharedWorker()
	{
		this.worker = new SharedWorker(WORKER_PATH, WORKER_NAME);
		this.handleMessageFromWorker();
		this.startUpdateState();
		this.worker.port.start();
	}

	setCsrfToken(token: string)
	{
		if (!Type.isStringFilled(token))
		{
			return;
		}

		Loc.setMessage({ CSRF_TOKEN_NAME: token });
	}

	getCsrfToken(): string
	{
		return Loc.getMessage(CSRF_TOKEN_NAME);
	}

	getSessionTimeInMilliseconds(): number
	{
		const { sessionTime } = Core.getApplicationData();

		return sessionTime * 1000;
	}

	subscribeToEvents()
	{
		Event.bind(window, 'online', () => {
			this.worker.port.postMessage({
				force: true,
				sessionTime: this.getSessionTimeInMilliseconds(),
				csrfToken: this.getCsrfToken(),
			});
		});
	}

	handleMessageFromWorker()
	{
		Event.bind(this.worker.port, 'message', (event: MessageEvent) => {
			const { csrfToken, response } = event.data;
			this.setCsrfToken(csrfToken);
			this.fireCountersEvent(response);
		});
	}

	startUpdateState()
	{
		this.worker.port.postMessage({
			force: false,
			sessionTime: this.getSessionTimeInMilliseconds(),
			csrfToken: this.getCsrfToken(),
		});
	}

	fireCountersEvent(response: ?JsonObject)
	{
		if (!response?.counters)
		{
			return;
		}

		EventEmitter.emit(window, EventType.counter.onImUpdateCounter, [response.counters]);
	}
}
