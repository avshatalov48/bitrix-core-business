import { Dom, Tag, Type, Extension, Event } from 'main.core';
import { PopupMenuWindow } from 'main.popup';
import CounterItem from './item';
import './style.css';
import type { CounterItemOptions } from './item';

type CounterPanelOptions = {
	target: HTMLElement;
	items: Array;
	multiselect: boolean;
	title: string;
}

export default class CounterPanel
{
	constructor(options: CounterPanelOptions)
	{
		this.target = Type.isDomNode(options.target) ? options.target : null;
		this.items = Type.isArray(options.items) ? options.items : [];
		this.multiselect = Type.isBoolean(options.multiselect) ? options.multiselect : null;
		this.title = Type.isStringFilled(options.title) ? options.title : null;
		this.container = null;
		this.keys = [];
		this.hasParent = [];
	}

	#adjustData()
	{
		this.items = this.items.map((item) => {
			this.keys.push(item.id);
			if (item.parentId)
			{
				this.hasParent.push(item.parentId);
			}

			return new CounterItem({
				...item,
				useAirDesign: this.hasAirDesign(),
				panel: this,
			});
		});

		this.hasParent.forEach((item) => {
			const index = this.keys.indexOf(item);
			this.items[index].parent = true;
		});

		this.items.forEach((item) => {
			if (item.parentId)
			{
				const index = this.keys.indexOf(item.parentId);
				this.items[index].items.push(item.id);
			}
		});
	}

	isMultiselect(): boolean
	{
		return this.multiselect;
	}

	getItems(): (CounterItem | CounterItemOptions)[]
	{
		return this.items;
	}

	getItemById(param): CounterItem | undefined
	{
		if (param)
		{
			const index = this.keys.indexOf(param);

			return this.items[index];
		}

		return undefined;
	}

	#getContainer(): HTMLElement
	{
		if (!this.container)
		{
			let myHead = '';
			if (this.title)
			{
				myHead = Tag.render`
					<div class="ui-counter-panel__item-head">${this.title}</div>
				`;
			}

			this.container = Tag.render`
				<div class="ui-counter-panel ui-counter-panel__scope">${myHead}</div>
			`;

			if (this.hasAirDesign() === true)
			{
				Dom.addClass(this.container, '--air');
			}
		}

		return this.container;
	}

	#render(): void
	{
		if (this.target && this.items.length > 0)
		{
			this.items.forEach((item, key) => {
				if (item instanceof CounterItem)
				{
					if (!item.hasParentId())
					{
						Dom.append(item.getContainer(), this.#getContainer());

						if (
							this.items.length !== key + 1
							&& this.items.length > 1
						)
						{
							Dom.append(Tag.render`
								<div class="ui-counter-panel__item-separator ${item.getSeparator() ? '' : '--invisible'}"></div>
							`, this.#getContainer());
						}
					}

					if (item.parent)
					{
						Event.bind(item.getContainer(), 'click', () => {
							const itemsArr = [];
							item.getItems().forEach((item) => {
								const itemCounter = this.getItemById(item);
								const test = {
									html: itemCounter.getContainerMenu(),
									className: `ui-counter-panel__popup-item ${this.hasAirDesign() ? '--air' : ''} menu-popup-no-icon ${itemCounter.isActive ? '--active' : ''}`,
									onclick: () => {
										if (itemCounter.isActive)
										{
											itemCounter.deactivate();
										}
										else
										{
											itemCounter.activate();
										}
									},
								};

								itemsArr.push(test);
							});

							const popup = new PopupMenuWindow({
								className: 'ui-counter-panel__popup ui-counter-panel__scope',
								bindElement: item.getArrowDropdown(),
								autoHide: true,
								closeByEsc: true,
								items: itemsArr,
								angle: true,
								offsetLeft: 6,
								offsetTop: 5,
								animation: 'fading-slide',
								events: {
									onPopupShow: () => {
										Dom.addClass(item.getContainer(), '--hover');
										Dom.addClass(item.getContainer(), '--pointer-events-none');
									},
									onPopupClose: () => {
										Dom.removeClass(item.getContainer(), '--hover');
										Dom.removeClass(item.getContainer(), '--pointer-events-none');
										popup.destroy();
									},
								},
							});

							popup.show();
						});
					}
				}
			});

			Dom.clean(this.target);
			Dom.append(this.#getContainer(), this.target);
		}
	}

	init()
	{
		this.#adjustData();
		this.#render();
	}

	setItems(items)
	{
		this.items = items;
	}

	hasAirDesign(): boolean
	{
		return Extension.getSettings('ui.counterpanel').get('useAirDesign') === true;
	}
}
