import {Tag, Type} from 'main.core';
import {EventEmitter, BaseEvent} from 'main.core.events';
import {Item, type ItemOptions} from './item.js';

type CollectionOptions = {
	items: Array<ItemOptions>;
};

export class Collection extends EventEmitter
{
	#list: Array<Item> = [];
	#node: HTMLElement;
	#sync: boolean = false;

	constructor(options: CollectionOptions = {})
	{
		super();
		this.setEventNamespace('ui:sidepanel:menu:collection');
		this.setItems(options.items);
	}

	#addSilent(itemOptions: ItemOptions): Item
	{
		if (itemOptions.active)
		{
			itemOptions.active = !this.hasActive();
		}
		else
		{
			itemOptions.active = false;
		}

		const item = new Item(itemOptions);
		this.#list.push(item);
		item.subscribe('change:active', () => {
			if (item.isActive() && item.getCollection().isEmpty())
			{
				this.syncActive(item);
			}
		});
		item.subscribe('sync:active', () => this.syncActive(item));
		item.subscribe('click', data => this.emit('click', data));
		item.subscribe('change', () => setTimeout(() => this.render(), 0));

		return item;
	}

	setActiveFirstItem(): void
	{
		const item = this.list()[0];
		if (!item)
		{
			return;
		}

		item.setActive(true);
		item.getCollection().setActiveFirstItem();
	}

	getActiveItem(): Item
	{
		return this.list().filter(item => item.isActive())[0];
	}

	syncActive(excludeItem): Collection
	{
		if (this.#sync)
		{
			return this;
		}

		this.#sync = true;
		this.list()
			.filter(otherItem => otherItem !== excludeItem)
			.forEach(otherItem => {
				otherItem.getCollection().isEmpty()
					? otherItem.setActive(false)
					: otherItem.getCollection().syncActive(otherItem)

			})
		;

		this.emit('sync:active');
		this.#sync = false;
		return this;
	}

	add(itemOptions: ItemOptions): Item
	{
		const item = this.#addSilent(itemOptions);
		this.emit('change');

		if (this.#node)
		{
			this.render();
		}

		return item;
	}

	get(id: number | string): ?Item
	{
		return this.list().filter(item => item.getId() === id)[0];
	}

	change(id: number | string, options: ItemOptions): ?Item
	{
		const foundItem = this.list().find((item: Item) => item.getId() === id);

		if (foundItem)
		{
			foundItem.change(options);

			return foundItem;
		}

		return null;
	}

	remove(id: number | string)
	{
		const foundItem = this.list().find((item: Item) => item.getId() === id);
		if (foundItem)
		{
			this.emit('change');

			this.#list = this.list().filter(otherItem => otherItem !== foundItem);

			foundItem.remove();
		}
	}

	setItems(items: Array<ItemOptions> = []): Collection
	{
		this.#list = items.map(itemOptions => this.#addSilent(itemOptions));
		this.emit('change');

		if (this.#node)
		{
			this.render();
		}

		return this;
	}

	list(): Array<Item>
	{
		return this.#list;
	}

	isEmpty(): boolean
	{
		return this.list().length === 0;
	}

	hasActive(recursively: boolean = true): boolean
	{
		const has = this.list().some(item => item.isActive());
		if (has)
		{
			return true;
		}

		if (!recursively)
		{
			return false;
		}

		return this.list().some(item => item.getCollection().hasActive());
	}

	render(): HTMLElement
	{
		if (!this.#node)
		{
			this.#node = Tag.render`<div class="ui-sidepanel-menu-items"></div>`;
		}

		this.#node.innerHTML = '';
		this.#list.forEach((item: Item) => this.#node.appendChild(item.render()));

		return this.#node;
	}
}
