import { Tag, Dom, Event } from 'main.core';
import { EventEmitter } from 'main.core.events';

type DayOptions = {
	value: number,
	notCurrentMonth: boolean,
	today: boolean,
	selected: boolean,
	weekend: boolean,
	enableBooking: boolean,
	slots: Object,
}

export default class Day
{
	#layout;
	#value;
	#notCurrentMonth;
	#today;
	#selected;
	#weekend;
	#slots;
	#enableBooking;

	constructor(options: DayOptions)
	{
		this.#value = options.value || null;
		this.#notCurrentMonth = options.notCurrentMonth || null;
		this.#today = options.today || null;
		this.#slots = options.slots || null;
		this.#layout = {
			wrapper: null,
		};
		this.#selected = options.selected || null;
		this.#weekend = options.weekend || null;
		this.#enableBooking = options.enableBooking || null;

		if (this.#selected)
		{
			this.select();
		}

		this.#bindEvents();
	}

	#bindEvents()
	{
		Event.bind(this.#getNodeWrapper(), 'click', this.select.bind(this));
	}

	isSelected()
	{
		return this.#selected;
	}

	getDay()
	{
		return this.#value;
	}

	isEnableBooking()
	{
		return this.#enableBooking;
	}

	select()
	{
		this.highlight();
		EventEmitter.emit('switchSlots', {
			slots: this.#slots,
		});
	}

	highlight()
	{
		this.#selected = true;
		Dom.addClass(this.#getNodeWrapper(), '--selected');
		EventEmitter.emit('selectDate', this);
	}

	unSelect()
	{
		this.#selected = null;
		Dom.removeClass(this.#getNodeWrapper(), '--selected');
	}

	#getNodeWrapper(): HTMLElement
	{
		if (!this.#layout.wrapper)
		{
			this.#layout.wrapper = Tag.render`
				<div class="calendar-sharing__month-col --day">${this.#value}</div>
			`;

			if (this.#notCurrentMonth)
			{
				Dom.addClass(this.#layout.wrapper, '--not-current-month');
			}

			if (this.#weekend)
			{
				Dom.addClass(this.#layout.wrapper, '--weekend');
			}

			if (this.#enableBooking)
			{
				Dom.addClass(this.#layout.wrapper, '--enable-booking');
			}
		}

		return this.#layout.wrapper;
	}

	render(): HTMLElement
	{
		return this.#getNodeWrapper();
	}
}
