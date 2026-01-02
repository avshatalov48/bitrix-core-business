import { Type, type JsonObject } from 'main.core';

import { Slider } from './slider';
import { SliderEvent } from './slider-event';

export class MessageEvent extends SliderEvent
{
	constructor(eventOptions)
	{
		super();

		const options = Type.isPlainObject(eventOptions) ? eventOptions : {};

		if (!(options.sender instanceof Slider))
		{
			throw new TypeError('\'sender\' is not an instance of BX.SidePanel.Slider');
		}

		this.setName('onMessage');
		this.setSlider(options.slider);

		this.sender = options.sender;
		this.data = 'data' in options ? options.data : null;
		this.eventId = Type.isStringFilled(options.eventId) ? options.eventId : null;
	}

	getSlider(): Slider | null
	{
		return this.slider;
	}

	getSender(): Slider
	{
		return this.sender;
	}

	getData(): JsonObject
	{
		return this.data;
	}

	getEventId(): string | null
	{
		return this.eventId;
	}
}
