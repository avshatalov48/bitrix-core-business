import {Type, Cache, Tag, Text, Dom, Event} from 'main.core';
import {EventEmitter} from 'main.core.events';
import type BaseButtonOptions from './types/button-options';
import defaultOptions from './internal/default-options';

import 'ui.fonts.opensans';
import './css/base_button.css';

/**
 * @memberOf BX.Landing.UI.Button
 */
export class BaseButton extends EventEmitter
{
	id: string;
	options: {[key: string]: any};
	layout: HTMLElement;
	cache: Cache.MemoryCache;

	constructor(id, options: BaseButtonOptions)
	{
		super();
		this.setEventNamespace('BX.Landing.UI.Button.BaseButton');

		const compatOptions = (() => {
			if (Type.isPlainObject(options))
			{
				return options;
			}

			if (Type.isPlainObject(id))
			{
				return id;
			}

			return {};
		})();

		const compatId = (() => {
			if (Type.isStringFilled(id))
			{
				return id;
			}

			if (Type.isStringFilled(compatOptions.id))
			{
				return compatOptions.id;
			}

			return Text.getRandom();
		})();

		this.options = {...defaultOptions, ...compatOptions};
		this.id = compatId;

		this.cache = new Cache.MemoryCache();
		this.layout = this.getLayout();

		if (Type.isStringFilled(this.options.html))
		{
			this.setHtml(this.options.html);
		}
		else
		{
			this.setText(this.options.text);
		}

		if (Type.isFunction(this.options.onClick))
		{
			Event.bind(this.getLayout(), 'click', this.options.onClick);
		}

		if (Type.isPlainObject(this.options.attrs))
		{
			Dom.attr(this.getLayout(), this.options.attrs);
		}

		if (
			Type.isArray(this.options.className)
			|| Type.isStringFilled(this.options.className)
		)
		{
			Dom.addClass(this.layout, this.options.className);
		}

		if (this.options.active)
		{
			this.activate();
		}

		if (this.options.disabled)
		{
			this.disable();
		}

		Event.bind(this.getLayout(), 'click', (event) => {
			event.preventDefault();
			this.emit('onClick');
		});
	}

	getLayout(): HTMLElement
	{
		return this.cache.remember('layout', () => {
			return Tag.render`
				<button 
					class="landing-ui-button" 
					type="button"
					data-id="${this.id}"
				>${this.getTextLayout()}</button>
			`;
		});
	}

	getTextLayout(): HTMLSpanElement
	{
		return this.cache.remember('textLayout', () => {
			return Tag.render`<span class="landing-ui-button-text"></span>`;
		});
	}

	setHtml(html: string)
	{
		this.getTextLayout().innerHTML = html;
	}

	setText(text: string)
	{
		this.getTextLayout().innerHTML = Text.encode(text);
	}

	/**
	 * @deprecated
	 */
	on(event, handler, context)
	{
		if (Type.isString(event) && Type.isFunction(handler))
		{
			Event.bind(this.layout, event, BX.proxy(handler, context));
		}
	}

	setAttributes(attrs)
	{
		Dom.attr(this.layout, attrs);
	}

	setAttribute(key, value)
	{
		Dom.attr(this.layout, key, value);
	}

	disable()
	{
		Dom.addClass(this.layout, 'landing-ui-disabled');
	}

	enable()
	{
		Dom.removeClass(this.layout, 'landing-ui-disabled');
		Dom.attr(this.layout, 'disabled', null);
	}

	isEnabled()
	{
		return !Dom.hasClass(this.layout, 'landing-ui-disabled');
	}

	show()
	{
		return BX.Landing.Utils.show(this.layout);
	}

	hide()
	{
		return BX.Landing.Utils.hide(this.layout);
	}

	activate()
	{
		Dom.addClass(this.layout, 'landing-ui-active');
	}

	deactivate()
	{
		Dom.removeClass(this.layout, 'landing-ui-active');
	}

	isActive()
	{
		return Dom.hasClass(this.layout, 'landing-ui-active');
	}
}