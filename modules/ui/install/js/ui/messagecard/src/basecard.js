import {Cache, Dom, Tag, Text, Type, Event} from 'main.core';
import {EventEmitter} from 'main.core.events';

export class BaseCard extends EventEmitter
{
	constructor(options = {})
	{
		super();
		this.cache = new Cache.MemoryCache();

		this.data = {...options};
		this.options = this.data;
		this.id = Type.isStringFilled(this.options.id) ? this.options.id : Text.getRandom();
		this.hidden = Text.toBoolean(this.options.hidden);
		this.onClickHandler = Type.isFunction(this.options.onClick) ? this.options.onClick : () => {};

		this.onClick = this.onClick.bind(this);

		this.layout = this.getLayout();
		this.header = this.getHeader();
		this.body = this.getBody();

		this.setTitle(this.options.title || '');
		this.setHidden(this.options.hidden);

		if (Type.isStringFilled(this.options.className))
		{
			Dom.addClass(this.layout, this.options.className);
		}

		if (Type.isObject(this.options.attrs))
		{
			Dom.adjust(this.layout, {attrs: this.options.attrs});
		}

		Event.bind(this.layout, 'click', this.onClick);
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('layout', () => {
			return Tag.render`
				<div class="ui-card">
					${this.getHeader()}
					${this.getBody()}
				</div>
			`;
		});
	}

	getHeader(): HTMLDivElement
	{
		return this.cache.remember('header', () => {
			return Tag.render`
				<div class="ui-card-header"></div>
			`;
		});
	}

	getBody(): HTMLDivElement
	{
		return this.cache.remember('body', () => {
			return Tag.render`
				<div class="ui-card-body"></div>
			`;
		});
	}

	setTitle(title: string)
	{
		this.getHeader().textContent = title;
	}

	setHidden(hidden: boolean)
	{
		Dom.attr(this.getLayout(), 'hidden', hidden || null);
	}

	onClick()
	{
		this.onClickHandler(this);
		this.emit('onClick');
	}

	show()
	{
		this.setHidden(false);
	}

	isShown()
	{
		return Dom.attr(this.getLayout(), 'hidden') === null;
	}

	hide()
	{
		this.setHidden(true);
	}

	getNode(): HTMLDivElement
	{
		return this.getLayout();
	}
}