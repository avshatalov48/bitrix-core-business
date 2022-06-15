import { Tag, Type } from 'main.core';
import { EventEmitter } from "main.core.events";

export default class NavigationItem
{
	constructor(options)
	{
		this.id = options.id;
		this.title = Type.isString(options.title) ? options.title : null;
		this.active = Type.isBoolean(options.active) ? options.active : false;
		this.events = options.events ? options.events : null;
		this.link = options.link ? options.link : null;

		this.container = null;

		this.bindEvents();
	}

	getTitle()
	{
		if (!this.title)
		{
			this.title = Tag.render`
				<div class="ui-nav-panel__item-title">${this.title}</div>	
			`;
		}

		return this.title;
	}

	getContainer()
	{
		if (!this.container)
		{
			this.container = Tag.render`
				<div class="ui-nav-panel__item">
					${this.title ? this.getTitle() : ''}
				</div>
			`;

			this.active ? this.activate() : this.inactivate();

			this.setEvents();
		}

		return this.container;
	}

	bindEvents()
	{
		EventEmitter.subscribe('BX.UI.NavigationPanel.Item:active', item => {
			if (item.data !== this)
			{
				this.inactivate();
			}
		});
	}

	setEvents()
	{
		if (this.events)
		{
			const eventsKeys = Object.keys(this.events);
			for (let i = 0; i < eventsKeys.length; i++)
			{
				let eventKey = eventsKeys[i];
				this.getContainer().addEventListener(eventKey, () => {
					this.events[eventKey]();
				})
			}
		}

		if (this.link)
		{
			this.container = Tag.render`
				<a class="ui-nav-panel__item">
					${this.title ? this.getTitle() : ''}
				</a>
			`;

			const linksKeys = Object.keys(this.link);
			for (let i = 0; i < linksKeys.length; i++)
			{
				const linksKey = linksKeys[i];
				this.container.setAttribute(linksKey, this.link[linksKey]);
			}
		}
	}

	activate()
	{
		this.active = true;
		this.getContainer().classList.add('--active');
		EventEmitter.emit('BX.UI.NavigationPanel.Item:active', this);
	}

	inactivate()
	{
		this.active = false;
		this.getContainer().classList.remove('--active');
		EventEmitter.emit('BX.UI.NavigationPanel.Item:inactive', this);
	}
}
