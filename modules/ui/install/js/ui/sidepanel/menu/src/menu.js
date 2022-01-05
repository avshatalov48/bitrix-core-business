import './style.css';
import {Item, ItemOptions} from './item.js';
import {Collection} from './collection.js';
import {Tag} from 'main.core';

export {Item};

export type MenuOptions = {
	items: Array<ItemOptions>;
};

export class Menu extends Collection
{
	#node: HTMLElement;

	constructor(options: MenuOptions = {})
	{
		super({items: options.items});

		if (!this.hasActive())
		{
			this.setActiveFirstItem();
		}
	}

	render(): HTMLElement
	{
		const itemsNode = super.render();
		if (!this.#node)
		{
			this.#node = Tag.render`<ul class="ui-sidepanel-menu"></ul>`;
			this.#node.appendChild(itemsNode);
		}

		return this.#node;
	}

	renderTo(target: HTMLElement): HTMLElement
	{
		const node = this.render();
		target.appendChild(node);
		return node;
	}
}