import {Text, Type} from 'main.core';
import Tab from './tab';
import {EventEmitter} from "main.core.events";

export default class Manager
{
	constructor(id, settings)
	{
		this.id = Type.isStringFilled(id) ? id : Text.getRandom();
		this.settings = Type.isObjectLike(settings) ? settings : {};

		this.container = this.settings.container;
		this.menuContainer = this.settings.menuContainer;

		this.items = [];

		if (Type.isArray(this.settings.data))
		{
			this.settings.data.forEach(item => {
				this.items.push(
					new Tab(item.id, {
						manager: this,
						data: item,
						container: this.container.querySelector('[data-tab-id="' + item.id + '"]'),
						menuContainer: this.menuContainer.querySelector('[data-tab-id="' + item.id + '"]')
					})
				);
			});
		}

		EventEmitter.subscribe('BX.Catalog.EntityCard.TabManager:onOpenTab', (event) => {
			let tabId = event.data.tabId;
			let item = this.findItemById(tabId);
			if (item)
			{
				this.selectItem(item);
			}
		});
	}

	findItemById(id)
	{
		return this.items.find(item => item.id === id) || null;
	}

	selectItem(item)
	{
		EventEmitter.emit('BX.Catalog.EntityCard.TabManager:onSelectItem', {tabId: item.id});
		this.items.forEach(current => current.setActive(current === item));
	}
}
