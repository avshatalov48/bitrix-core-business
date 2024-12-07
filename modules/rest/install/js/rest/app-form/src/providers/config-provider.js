import { ajax } from 'main.core';
import type { EventType } from './event-type';
import { Provider } from './provider';

export class ConfigProvider extends Provider
{
	#clientId: string;
	#type: EventType;

	constructor(clientId: string, eventType: EventType)
	{
		super();
		this.#clientId = clientId;
		this.#type = eventType;
	}

	fetch(): Promise
	{
		return ajax.runAction('rest.controller.appform.getConfig', {
			data: {
				clientId: this.#clientId,
				type: this.#type,
			},
		});
	}
}
