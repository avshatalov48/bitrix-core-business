import {Cache, Dom, Tag, Text, Type, Event} from 'main.core';
import {EventEmitter} from 'main.core.events';

import './css/base_card.css';

/**
 * @memberOf BX.Landing.UI.Card
 */
export class BaseCard extends EventEmitter
{
	constructor(options = {})
	{
		super();
		this.setEventNamespace('BX.Landing.UI.Card.BaseCard');
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
				<div class="landing-ui-card">
					<div class="landing-ui-card-header-wrapper">
						${this.getHeader()}
					</div>
					${this.getBody()}
				</div>
			`;
		});
	}

	getRemoveButton(): HTMLDivElement
	{
		return this.cache.remember('remove', () =>
		{
			return Tag.render`
				<div class="landing-ui-card-block-remove"></div>
			`;
		});
	}

	getHeader(): HTMLDivElement
	{
		return this.cache.remember('header', () => {
			return Tag.render`
				<div class="landing-ui-card-header"></div>
			`;
		});
	}

	getBody(): HTMLDivElement
	{
		return this.cache.remember('body', () => {
			return Tag.render`
				<div class="landing-ui-card-body"></div>
			`;
		});
	}

	addWarning(warning: string)
	{
		Dom.append(
			Tag.render`
				<div class="landing-ui-card-body-warning">${warning}</div>
			`,
			this.getBody()
		);
		Dom.addClass(this.getBody(), '--warning');
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