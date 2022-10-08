import {Runtime, Dom, Type, Loc} from 'main.core';
import {EventEmitter, BaseEvent} from 'main.core.events';
import {Menu as MainMenu, MenuManager} from 'main.popup';
import type {MenuItemOptions} from "main.popup";
import {Draggable} from 'ui.draganddrop.draggable';

export type Parameters = {
	id: string,
	items: Item[],
	bindElement: ?HTMLElement,
	maxVisibleItems: ?number,
}

export type Item = {
	isHidden: boolean,
	text: ?string,
	html: ?string,
	id: ?string,
	onclick: ?Function,
}

export class Menu extends EventEmitter
{
	#id: string;
	#items: Array;
	#menu: MainMenu;
	#bindElement: ?HTMLElement;
	#draggable: Draggable;
	#promise: Promise;
	#closeResolver: Function;
	#maxVisibleItems: number = 0;

	constructor(parameters: Parameters)
	{
		super();

		this.#id = Type.isStringFilled(parameters.id) ? parameters.id : 'settings-popup-' + Math.random().toString().substring(2);
		this.#items = parameters.items;
		this.#bindElement = parameters.bindElement;
		this.#maxVisibleItems = Number(parameters.maxVisibleItems);
		this.#createMenu();

		this.setEventNamespace('BX.UI.MenuConfigurable.Menu');
	}

	open(bindElement: ?HTMLElement): Promise
	{
		if (bindElement)
		{
			this.#menu?.getPopupWindow().setBindElement(bindElement);
		}
		this.#menu?.show();

		if (!this.#promise)
		{
			this.#promise = new Promise((resolve) => {
				this.#closeResolver = resolve;
			});
		}

		return this.#promise;
	}

	#resolveWithCancel(): void
	{
		this.#promise = null;
		if (this.#closeResolver)
		{
			this.#closeResolver({isCanceled: true});
		}
		this.#closeResolver = null;
	}

	#resolveWithItems(): void
	{
		this.#promise = null;
		if (this.#closeResolver)
		{
			this.#closeResolver({items: this.#items});
		}
		this.#closeResolver = null;
	}

	close(): void
	{
		this.#createMenu();
		this.#resolveWithCancel();
	}

	setItems(items: Item[]): this
	{
		this.#items = items;

		return this;
	}

	#getItemById(id: string): ?Item
	{
		return this.#items.find(item => item.id === id);
	}

	getItemsFromMenu(): Item[]
	{
		const items = [];
		let isHidden = false;

		this.#menu.itemsContainer.querySelectorAll('.menu-configurable-item').forEach((node: HTMLElement) => {
			if (node.classList.contains('menu-configurable-hidden-section-title'))
			{
				isHidden = true;
			}
			const itemId = node.dataset.id;
			const item = this.#getItemById(itemId);
			if (item)
			{
				const clonedItem = Runtime.clone(item);
				clonedItem.isHidden = isHidden;
				items.push(clonedItem);
			}
		});

		return items;
	}

	#createMenu(bindElement: ?HTLMElement): Menu
	{
		if (this.#menu)
		{
			this.#menu.destroy();
			this.#draggable = null;
		}

		const menuItems = [];
		menuItems.push(this.#getVisibleSectionTitleItem());
		const visibleItems = this.#items.filter(item => !item.isHidden);
		const hiddenItems = this.#items.filter(item => item.isHidden);
		visibleItems.forEach((item) => {
			menuItems.push(this.#getMenuItem(item));
		});
		menuItems.push(this.#getHiddenSectionTitleItem());
		hiddenItems.forEach((item) => {
			menuItems.push(this.#getMenuItem(item));
		});
		menuItems.push(this.#getSaveItem());
		menuItems.push(this.#getCancelItem());

		this.#menu = MenuManager.create({
			id: this.#id,
			items: menuItems,
			bindElement: bindElement ?? this.#bindElement,
			events: {
				onClose: this.close.bind(this),
			}
		});

		this.#initDraggable();

		return this.#menu;
	}

	#getSaveItem(): MenuItemOptions
	{
		return {
			text: Loc.getMessage('UI_JS_MENU_CONFIGURABLE_SAVE'),
			onclick: this.#save.bind(this),
		}
	}

	#getCancelItem(): MenuItemOptions
	{
		return {
			text: Loc.getMessage('UI_JS_MENU_CONFIGURABLE_CANCEL'),
			onclick: this.#cancel.bind(this),
		}
	}

	#save(): void
	{
		const event = new BaseEvent();
		this.emit('Save', event);
		if (event.isDefaultPrevented())
		{
			return;
		}

		this.#saveItemsFromMenu();
		this.#resolveWithItems();
		this.#createMenu();
	}

	#cancel(): void
	{
		const event = new BaseEvent();
		this.emit('Cancel', event);
		if (event.isDefaultPrevented())
		{
			return;
		}

		this.close();
	}

	#getMenuItem(item: Item): MenuItemOptions
	{
		return {
			id: item.id,
			text: item.text,
			html: item.html,
			className: 'menu-configurable-item',
			dataset: {
				id: item.id,
			},
		}
	}

	#getVisibleSectionTitleItem(): MenuItemOptions
	{
		return {
			delimiter: true,
			html: '<span>' + Loc.getMessage('UI_JS_MENU_CONFIGURABLE_VISIBLE') + '</span>',
			className: 'menu-configurable-visible-section-title menu-configurable-delimiter-item',
		}
	}

	#getHiddenSectionTitleItem(): MenuItemOptions
	{
		return {
			delimiter: true,
			html: '<span>' + Loc.getMessage('UI_JS_MENU_CONFIGURABLE_HIDDEN') + '</span>',
			className: 'menu-configurable-hidden-section-title menu-configurable-delimiter-item menu-configurable-item',
		}
	}

	#initDraggable(): void
	{
		this.#draggable = new Draggable({
			container: this.#menu.itemsContainer,
			draggable: '.menu-configurable-item',
			dragElement: '.menu-popup-item-icon',
			type: Draggable.MOVE,
		});
		this.#draggable.subscribe('end', this.#adjustMaxVisibleItems.bind(this));
	}

	#saveItemsFromMenu(): void
	{
		this.setItems(this.getItemsFromMenu());
	}

	#getItemNode(item: Item): ?HTMLElement
	{
		return this.#menu.itemsContainer.querySelector('.menu-configurable-item[data-id="' + item.id + '"]');
	}

	#getHiddenSectionTitleNode(): ?HTMLElement
	{
		return this.#menu.itemsContainer.querySelector('.menu-configurable-hidden-section-title');
	}

	#adjustMaxVisibleItems(): void
	{
		if (this.#maxVisibleItems <= 0)
		{
			return;
		}

		const runtimeItems = this.getItemsFromMenu();
		const visibleItems = runtimeItems.filter(item => !item.isHidden);
		const visibleItemsCount = visibleItems.length;
		const hiddenSectionTitleNode = this.#getHiddenSectionTitleNode();
		if (hiddenSectionTitleNode && visibleItemsCount > this.#maxVisibleItems)
		{
			for (let index = this.#maxVisibleItems; index < visibleItemsCount; index++)
			{
				const item = visibleItems[index];
				const node = this.#getItemNode(item);
				if (node)
				{
					Dom.insertAfter(node, hiddenSectionTitleNode);
				}
			}
		}
	}
}
