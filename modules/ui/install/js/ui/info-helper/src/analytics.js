import { Uri } from 'main.core';
import { sendData } from 'ui.analytics';

export class Analytics
{
	#code: string;
	#category: string;

	constructor(code: string, category: string)
	{
		this.#code = code;
		this.#category = category;
	}

	sendByEventName(event: string, additionalParameter: ?string = null): void
	{
		sendData({
			tool: 'InfoHelper',
			category: this.#category,
			type: this.#code,
			event: event,
			c_section: (new Uri(document.location.href)).getPath(),
			p1: additionalParameter,
		});
	}
}