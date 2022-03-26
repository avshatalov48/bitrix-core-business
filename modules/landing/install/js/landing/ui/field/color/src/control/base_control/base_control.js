import {BaseEvent, EventEmitter} from 'main.core.events';
import {Cache, Dom, Tag} from 'main.core';

import ColorValue from "../../color_value";
import {IColorValue} from '../../types/i_color_value';

import './css/base_control.css';

export default class BaseControl extends EventEmitter
{
	static ACTIVE_CLASS: string = 'active';

	constructor(options: ?{})
	{
		super();
		this.cache = new Cache.MemoryCache();
	}

	getLayout(): HTMLElement
	{
		return this.cache.remember('layout', () => {
			return this.buildLayout();
		});
	}

	buildLayout(): HTMLElement
	{
		return Tag.render`
			<div class="landing-ui-field-base-control">
				Base control
			</div>
		`;
	}

	getValue(): ?IColorValue
	{
		return this.cache.remember('value', () => {
			return new ColorValue();
		});
	}

	isNeedSetValue(value): boolean
	{
		return value !== this.getValue();
	}

	setValue(value)
	{
		this.cache.set('value', value);
	}

	onChange(event: ?BaseEvent)
	{
		this.cache.delete('value');
		this.emit('onChange', {color: this.getValue()});
	}

	setActive(): void
	{
		Dom.addClass(this.getLayout(), BaseControl.ACTIVE_CLASS);
	}

	unsetActive(): void
	{
		Dom.removeClass(this.getLayout(), BaseControl.ACTIVE_CLASS);
	}

	isActive(): boolean
	{
		return Dom.hasClass(this.getLayout(), BaseControl.ACTIVE_CLASS);
	}
}
