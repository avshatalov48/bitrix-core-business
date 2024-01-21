import { Dom, Tag, Type } from 'main.core';

type WeekdayOptions = {
	name: string,
	index: number,
	active: boolean,
	onSelected: void,
	onDiscarded: void,
	onMouseDown: void,
	canBeDiscarded: void,
}

export default class Weekday
{
	constructor(options: WeekdayOptions)
	{
		this.wrap = null;
		this.name = options.name;
		this.index = options.index;
		this.active = options.active;
		this.onSelected = Type.isFunction(options.onSelected) ? options.onSelected : () => {};

		this.onDiscarded = Type.isFunction(options.onDiscarded) ? options.onDiscarded : () => {};

		this.onMouseDown = Type.isFunction(options.onMouseDown) ? options.onMouseDown : () => {};

		this.canBeDiscarded = Type.isFunction(options.canBeDiscarded) ? options.canBeDiscarded : () => {};
	}

	render()
	{
		const className = this.active ? '--selected' : '';

		this.wrap = Tag.render`
			<div class="calendar-sharing__settings-popup-weekday ${className}" onmousedown="${(e) => this.handleMouseDown(e)}">
				<div class="calendar-sharing__settings-popup-weekday-text">${this.name}</div>
				<div class="calendar-sharing__settings-popup-weekday-icon"></div>
			</div>
		`;

		return this.wrap;
	}

	handleMouseDown(event)
	{
		if (this.active)
		{
			this.discard();
		}
		else
		{
			this.select();
		}
		this.onMouseDown(event, this);
	}

	select()
	{
		this.active = true;
		Dom.addClass(this.wrap, '--selected');
		this.onSelected();
	}

	discard()
	{
		if (!this.canBeDiscarded())
		{
			return;
		}

		this.active = false;
		Dom.removeClass(this.wrap, '--selected');
		this.onDiscarded();
	}
}
