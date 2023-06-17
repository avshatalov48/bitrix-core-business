import { Tag, Type } from 'main.core';
import { EventEmitter } from "main.core.events";

export default class NavigationItem
{
	constructor({ id, title, active, events, link, locked })
	{
		this.id = id ? id : null;
		this.title = Type.isString(title) ? title : null;
		this.active = Type.isBoolean(active) ? active : false;
		this.events = events ? events : null;
		this.link = link ? link : null;
		this.locked = Type.isBoolean(locked) ? locked : false;

		this.linkContainer = null;

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
		if (!this.linkContainer)
		{
			const id = this.id ? `id="ui-nav-panel-item-${this.id}"` : '';
			this.linkContainer = Tag.render`
				<div ${id} class="ui-nav-panel__item">
					${this.title ? this.getTitle() : ''}
				</div>
			`;

			this.active ? this.activate() : this.inactivate();
			this.locked ? this.lock() : this.unLock();

			this.setEvents();
		}

		return this.linkContainer;
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

	isLocked()
	{
		return this.locked;
	}

	lock()
	{
		this.locked = true;
		this.getContainer().classList.add('--locked');
	}

	unLock()
	{
		this.locked = false;
		this.getContainer().classList.remove('--locked');
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
