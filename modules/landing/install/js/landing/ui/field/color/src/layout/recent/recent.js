import {Cache, Event, Tag, Dom, Type} from 'main.core';
import {EventEmitter} from 'main.core.events';

import {Backend} from 'landing.backend';

import isHex from '../../internal/is-hex';
import './css/recent.css';

export default class Recent extends EventEmitter
{
	static +USER_OPTION_NAME = 'color_field_recent_colors';
	static +MAX_ITEMS = 6;

	static items: [] = [];
	static itemsLoaded: boolean = false;

	constructor()
	{
		super();
		this.cache = new Cache.MemoryCache();
		this.setEventNamespace('BX.Landing.UI.Field.Color.Recent');
	}

	getLayout(): HTMLDivElement
	{
		this.initItems();

		return this.getLayoutContainer();
	}

	getLayoutContainer(): HTMLDivElement
	{
		return this.cache.remember('layout', () => {
			return Tag.render`<div class="landing-ui-field-color-recent"></div>`;
		});
	}

	initItems()
	{
		if (Recent.itemsLoaded)
		{
			this.buildItemsLayout();
		}
		else
		{
			Backend.getInstance()
				.action("Utils::getUserOption", {name: Recent.USER_OPTION_NAME})
				.then(result => {
					if (result && Type.isString(result.items))
					{
						Recent.items = [];
						result.items.split(',').forEach(item => {
							if (isHex((item)) && Recent.items.length < Recent.MAX_ITEMS)
							{
								Recent.items.push(item);
							}
						});
						Recent.itemsLoaded = true;

						this.buildItemsLayout();
					}
				});
			// todo: what if ajax error?
		}
	}

	buildItemsLayout(): Recent
	{
		Dom.clean(this.getLayoutContainer());
		Recent.items.forEach(item => {
			if (isHex(item))
			{
				let itemLayout = Tag.render`<div 
					class="landing-ui-field-color-recent-item" 
					style="background:${item}"
					data-value="${item}"
				></div>`;
				Event.bind(itemLayout, 'click', () => this.onItemClick(event));
				Dom.append(itemLayout, this.getLayoutContainer());
			}
		});

		return this;
	}

	onItemClick(event: MouseEvent)
	{
		this.emit('onChange', {hex: event.currentTarget.dataset.value});
	}

	addItem(hex: string): Recent
	{
		if (isHex(hex))
		{
			let pos = Recent.items.indexOf(hex);
			if (pos !== -1)
			{
				Recent.items.splice(pos, 1);
			}
			Recent.items.unshift(hex);
			if (Recent.items.length > Recent.MAX_ITEMS)
			{
				Recent.items.splice(Recent.MAX_ITEMS);
			}

			this.buildItemsLayout();
			this.saveItems();
		}

		return this;
	}

	saveItems(): Recent
	{
		if (Recent.items.length > 0)
		{
			BX.userOptions.save('landing', Recent.USER_OPTION_NAME, 'items', Recent.items);
		}

		return this;
	}
}