import { Tag, Loc, Event, Dom } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Util } from 'calendar.util';

type SlotItemOptions = {
	value: {
		from: Date,
		to: Date,
	},
}

export default class SlotItem
{
	#layout;
	#selected;
	#value;
	BUTTON_MAX_WIDTH = 123;

	constructor(options: SlotItemOptions)
	{
		this.#selected = null;
		this.#layout = {
			wrapper: null,
			value: null,
			select: null,
		};
		this.#value = options.value;

		this.#bindEvents();
	}

	#bindEvents()
	{
		Event.bind(this.#getNodeWrapper(), 'click', this.select.bind(this));
		Event.bind(this.#getNodeSelect(), 'click', this.showForm.bind(this));
	}

	isSelected()
	{
		return this.#selected;
	}

	select()
	{
		this.#selected = true;
		Dom.addClass(this.#getNodeWrapper(), '--selected');
		EventEmitter.emit('selectSlot', this);
	}

	unSelect()
	{
		this.#selected = null;
		Dom.removeClass(this.#getNodeWrapper(), '--selected');
	}

	showForm()
	{
		EventEmitter.emit('confirmedSelectSlot', { value: this.#value });
	}

	#getNodeSelect(): HTMLElement
	{
		if (!this.#layout.select)
		{
			this.#layout.select = Tag.render`
				<div class="calendar-sharing__slot-select">${Loc.getMessage('CALENDAR_SHARING_SELECT_SLOT')}</div>
			`;

			document.body.append(this.#layout.select);
			if (this.#layout.select.offsetWidth > this.BUTTON_MAX_WIDTH)
			{
				Dom.addClass(this.#layout.select, '--compact');
			}
			this.#layout.select.remove();
		}

		return this.#layout.select;
	}

	#getNodeValue(): HTMLElement
	{
		if (!this.#layout.value)
		{
			let value = Util.formatTimeInterval(this.#value.from, this.#value.to);
			value = value.replace(/(am|pm)/g, '<span class="calendar-sharing-am-pm">$1</span>');

			this.#layout.value = Tag.render`
				<div class="calendar-sharing__slot-value">${value}</div>
			`;
		}

		return this.#layout.value;
	}

	#getNodeWrapper(): HTMLElement
	{
		if (!this.#layout.wrapper)
		{
			this.#layout.wrapper = Tag.render`
				<div class="calendar-sharing__slot-item">
					${this.#getNodeValue()}
					${this.#getNodeSelect()}
				</div>
			`;
		}

		return this.#layout.wrapper;
	}

	render(): HTMLElement
	{
		return this.#getNodeWrapper();
	}
}
