import { Dom, Tag, Type } from 'main.core';
import { PopupMenuWindow } from 'main.popup';
import CounterItem from './item';
import 'ui.fonts.opensans';
import './style.css';

export default class CounterPanel
{
	constructor(options: {
		target: HTMLElement,
		items: Array,
		multiselect: Boolean,
		title: String
	})
	{
		this.target = Type.isDomNode(options.target) ? options.target : null;
		this.items = Type.isArray(options.items) ? options.items : [];
		this.multiselect = Type.isBoolean(options.multiselect) ? options.multiselect : null;
		this.title = Type.isStringFilled(options.title) ? options.title : null;
		this.container = null;
		this.keys = [];
		this.hasParent = [];
		this.childKeys = [];
	}

	#adjustData()
	{
		this.items = this.items.map(item => {
			item.panel = this;
			this.keys.push(item.id);
			if (item.parentId)
			{
				this.hasParent.push(item.parentId);
			}
			return new CounterItem(item);
		});

		this.hasParent.forEach(item => {
			let index = this.keys.indexOf(item);
			this.items[index].parent = true;
		});

		this.items.map(item => {
			if (item.parentId)
			{
				let index = this.keys.indexOf(item.parentId);
				this.items[index].items.push(item.id);
			}
		});
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
					if (!item.hasParentId())
					{
						this.#getContainer().appendChild(item.getContainer());

						if (
							this.items.length !== key + 1
							&& this.items.length > 1
						)
						{
							this.#getContainer().appendChild(Tag.render`
								<div class="ui-counter-panel__item-separator ${!item.getSeparator() ? '--invisible' : ''}"></div>
							`);
						}
					}

					if (item.parent)
					{
						item.getContainer().addEventListener('click', () => {
							const itemsArr = [];
							item.getItems().forEach(item => {
								const itemCounter = this.getItemById(item);
								let test = {
									html: itemCounter.getContainerMenu(),
									className: `ui-counter-panel__popup-item menu-popup-no-icon ${itemCounter.isActive ? '--active' : ''}`,
									onclick: () => {
										itemCounter.isActive
											? itemCounter.deactivate()
											: itemCounter.activate();
									}
								}
								itemsArr.push(test);
							});

							const popup = new PopupMenuWindow({
								className: 'ui-counter-panel__popup ui-counter-panel__scope',
								bindElement: item.getArrowDropdown(),
								autoHide: true,
								closeByEsc : true,
								items: itemsArr,
								angle: true,
								offsetLeft: 6,
								offsetTop: -7,
								animation: 'fading-slide',
								events: {
									onPopupShow: () => {
										item.getContainer().classList.add('--hover');
										item.getContainer().classList.add('--pointer-events-none');
									},
									onPopupClose: () => {
										item.getContainer().classList.remove('--hover');
										item.getContainer().classList.remove('--pointer-events-none');
										popup.destroy();
									}
								}
							});

							popup.show();
						});
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
