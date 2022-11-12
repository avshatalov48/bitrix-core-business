import {Event, Dom} from 'main.core';
import {EventEmitter} from 'main.core.events';

export default class CanvasZooming extends EventEmitter
{
	#stepSize = 0.01;
	#value = 0;
	#defaultValue = 0;
	#containerWidth = 0;
	#scale: Element;
	#knob: Element;

	constructor({knob, scale, minus, plus}, defaultValue: ?Number)
	{
		super();
		this.setEventNamespace('Main.Avatar.Editor');

		Event.bind(minus, 'click', () => {
			this.#makeAStep(false)
		});
		Event.bind(plus, 'click', () => {
			this.#makeAStep(true)
		});

		this.stopMoving = this.stopMoving.bind(this);
		this.move = this.move.bind(this);

		Event.bind(knob, 'mousedown', (event) => {
			this.startMoving(event);
		});
		if (defaultValue)
		{
			this.setDefaultValue(defaultValue);
		}
		this.#scale = scale;
		this.#knob = knob;
		this.reset();
	}

	setDefaultValue(defaultValue)
	{
		this.#defaultValue = defaultValue > 0 && defaultValue <= 1 ? defaultValue : 0;
		return this;
	}

	getValue()
	{
		return this.#value;
	}

	#getContainerWidth(): Number
	{
		if (this.#containerWidth > 0)
		{
			return this.#containerWidth;
		}
		const containerPos = Dom.getPosition(this.#scale);
		let width = containerPos.width - Dom.getPosition(this.#knob).width;
		if (width > 0)
		{
			this.#containerWidth = width;
			return this.#containerWidth;
		}
		return 0;
	}

	reset()
	{
		this.#value = this.#defaultValue;
		this.#adjust();
	}

	setValue(value)
	{
		value = Math.ceil(value * 1000) / 1000;
		if (value !== this.#value && value >= 0 && value <= 1)
		{
			this.#value = value;
			this.#adjust();
			this.emit('onChange', this.#value - this.#defaultValue);
		}
	}

	#makeAStep(increase)
	{
		const value = Math.min(
			Math.max(
				this.getValue() + (increase === false ? (-1) : 1) * this.#stepSize,
				0),
			1
		);
		this.setValue(value);
	}

	#adjust()
	{
		Dom.adjust(this.#knob, {
			style: {
				left: [Math.ceil(this.#getContainerWidth() * this.getValue()), 'px'].join('')
			}
		});
	}

	move({pageX})
	{
		if (pageX > 0 && this.#getContainerWidth() > 0)
		{
			const percent = (pageX - this.#knob.startPageX) / this.#getContainerWidth();
			this.#knob.startPageX = pageX;
			this.setValue(this.getValue() + percent);
		}
	}

	startMoving({pageX})
	{
		this.#knob.startPageX = pageX;

		Event.bind(document, 'mousemove', this.move);
		Event.bind(document, 'mouseup', this.stopMoving);
	}

	stopMoving()
	{
		Event.unbind(document, 'mousemove', this.move);
		Event.unbind(document, 'mouseup', this.stopMoving);
	}
}
