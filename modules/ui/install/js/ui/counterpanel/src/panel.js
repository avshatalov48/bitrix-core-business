import { Dom, Tag, Type } from 'main.core';
import CounterItem from './item';
import './style.css';

export default class CounterPanel
{
	constructor(options: {
		target: HTMLElement,
		items: Array,
		multiselect: Boolean
	})
	{
		this.target = Type.isDomNode(options.target) ? options.target : null;
		this.items = Type.isArray(options.items) ? options.items : [];
		this.multiselect = Type.isBoolean(options.multiselect) ? options.multiselect : null;
		this.container = null;
		this.keys = [];
	}

	#adjustData()
	{
		this.items = this.items.map((item) => {

			this.keys.push(item.id);

			return new CounterItem({
				id: item.id ? item.id : null,
				title: item.title ? item.title : null,
				value: item.value ? parseInt(item.value, 10) : null,
				cross: item.cross ? item.cross : null,
				color: item.color ? item.color : null,
				eventsForActive: item.eventsForActive ? item.eventsForActive : null,
				eventsForUnActive: item.eventsForUnActive ? item.eventsForUnActive : null,
				panel: this
			});
		});

		this.getItemById();
	}

	isMultiselect()
	{
		return this.multiselect;
	}

	getItems()
	{
		return this.items;
	}

	getItemById(param)
	{
		if (param)
		{
			const index = this.keys.indexOf(param);
			return this.items[index];
		}
	}

	#getContainer()
	{
		if (!this.container)
		{
			this.container = Tag.render`
				<div class="ui-counter-panel ui-counter-panel__scope"></div>
			`;
		}

		return this.container;
	}

	#render()
	{
		if (this.target && this.items.length > 0)
		{
			this.items.map((item, key) => {
				if (item instanceof CounterItem)
				{
					this.#getContainer().appendChild(item.getContainer());

					if (
						this.items.length !== key + 1
						&& this.items.length > 1
					)
					{
						this.#getContainer().appendChild(Tag.render`
							<div class="ui-counter-panel__item-separator"></div>
						`);
					}
				}
			});

			Dom.clean(this.target);
			this.target.appendChild(this.#getContainer());
		}
	}

	init()
	{
		this.#adjustData();
		this.#render();
	}
}
