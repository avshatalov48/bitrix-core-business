import { Dom, Tag, Type } from 'main.core';
import NavigationItem from './item';
import './style.css';

export default class NavigationPanel
{
	constructor(options)
	{
		this.target = Type.isDomNode(options.target) ? options.target : null;
		this.items = Type.isArray(options.items) ? options.items : [];
		this.container = null;
		this.keys = [];
	}

	adjustItem()
	{
		this.items = this.items.map((item) => {
			this.keys.push(item.id);

			return new NavigationItem({
				id: item.id ? item.id : null,
				title: item.title ? item.title : null,
				active: item.active ? item.active : false,
				events: item.events ? item.events : null,
				link: item.link ? item.link : null,
			});
		})
	}

	getItemById(value)
	{
		if (value)
		{
			const id  = this.keys.indexOf(value);
			return this.items[id];
		}
	}

	getContainer()
	{
		if (!this.container)
		{
			this.container = Tag.render`
				<div class="ui-nav-panel ui-nav-panel__scope"></div>
			`;
		}

		return this.container;
	}

	render()
	{
		this.items.forEach((item) => {
			if (item instanceof NavigationItem)
			{
				this.getContainer().appendChild(item.getContainer());
			}
		})

		Dom.clean(this.target);
		this.target.appendChild(this.getContainer());
	}

	init()
	{
		this.adjustItem();
		this.render();
	}
}
