import { Dom, Tag, Type } from 'main.core';
import { Counter } from 'ui.cnt';
import { EventEmitter } from "main.core.events";

export default class CounterItem
{
	constructor(args)
	{
		this.id = args.id ? args.id : null;
		this.separator = Type.isBoolean(args.separator) ? args.separator : true;
		this.items = Type.isArray(args.items) ? args.items : [];
		this.popupMenu = null;
		this.isActive = Type.isBoolean(args.isActive) ? args.isActive : false;
		this.isRestricted = Type.isBoolean(args.isRestricted) ? args.isRestricted : false;
		this.panel = args.panel ? args.panel : null;
		this.title = args.title ? args.title : null;
		this.value = (Type.isNumber(args.value) && args.value !== undefined) ? args.value : null;
		this.titleOrder = null;
		this.valueOrder = null;
		this.color = args.color ? args.color : null;
		this.parent = Type.isBoolean(args.parent) ? args.parent : null;
		this.parentId = args.parentId ? args.parentId : null;
		this.locked = false;
		this.type = Type.isString(args.type) ? args.type.toLowerCase() : null;
		this.eventsForActive = Type.isObject(args.eventsForActive) ? args.eventsForActive : {};
		this.eventsForUnActive = Type.isObject(args.eventsForUnActive) ? args.eventsForUnActive : {};

		if (Type.isObject(args.title))
		{
			this.title = args.title.value ? args.title.value : null;
			this.titleOrder = Type.isNumber(args.title.order) ? args.title.order : null;
		}

		if (Type.isObject(args.value))
		{
			this.value = Type.isNumber(args.value.value) ? args.value.value : null;
			this.valueOrder = Type.isNumber(args.value.order) ? args.value.order : null;
		}

		this.layout = {
			container: null,
			value: null,
			title: null,
			cross: null,
			dropdownArrow: null,
			menuItem: null
		};

		this.counter = this.#getCounter();

		if (!this.#getPanel().isMultiselect())
		{
			this.#bindEvents();
		}
	}

	getItems()
	{
		return this.items;
	}

	hasParentId()
	{
		return this.parentId;
	}

	#bindEvents()
	{
		EventEmitter.subscribe('BX.UI.CounterPanel.Item:activate', (item) => {
			const isLinkedItems = item.data.parentId === this.id;
			if (item.data !== this && !isLinkedItems)
			{
				this.deactivate();
			}
		});
	}

	updateValue(param: Number)
	{
		if (Type.isNumber(param))
		{
			this.value = param;
			this.#getCounter().update(param);

			if (param === 0)
			{
				this.updateColor(this.parentId ? 'GRAY' : 'THEME');
			}
		}
	}

	updateValueAnimate(param: Number)
	{
		if (Type.isNumber(param))
		{
			this.value = param;
			this.#getCounter().update(param);
			this.#getCounter().show();

			if (param === 0)
			{
				this.updateColor(this.parentId ? 'GRAY' : 'THEME');
			}
		}
	}

	updateColor(param: string)
	{
		if (Type.isString(param))
		{
			this.color = param;
			this.#getCounter().setColor(Counter.Color[param]);
		}
	}

	activate(isEmitEvent: boolean = true)
	{
		this.isActive = true;
		if (this.parentId)
		{
			const target = BX.findParent(
				this.getContainerMenu(),
				{
					'className': 'ui-counter-panel__popup-item'
				}
			);

			if (target)
			{
				target.classList.add('--active');
			}
		}
		else
		{
			this.getContainer().classList.add('--active');
		}

		if (isEmitEvent)
		{
			EventEmitter.emit('BX.UI.CounterPanel.Item:activate', this);
		}
	}

	deactivate(isEmitEvent: boolean = true)
	{
		this.isActive = false;
		if (this.parentId)
		{
			const target = BX.findParent(
				this.getContainerMenu(),
				{
					'className': 'ui-counter-panel__popup-item'
				}
			);

			if (target)
			{
				target.classList.remove('--active');
				target.classList.remove('--hover');
			}
		}
		else
		{
			this.getContainer().classList.remove('--active');
			this.getContainer().classList.remove('--hover');
		}

		if (isEmitEvent)
		{
			EventEmitter.emit('BX.UI.CounterPanel.Item:deactivate', this);
		}
	}

	getSeparator()
	{
		return this.separator;
	}

	#getPanel()
	{
		return this.panel;
	}

	#getCounter(value: Number, color: String)
	{
		if (!this.counter)
		{
			this.counter = new Counter({
				value: this.value,
				color: this.color ? Counter.Color[this.color.toUpperCase()] : (this.parentId ? Counter.Color.GRAY : Counter.Color.THEME),
				animation: false
			});
		}

		return this.counter;
	}

	#getValue()
	{
		if (!this.layout.value)
		{
			const counterValue = this.isRestricted
				? Tag.render`<div class="ui-counter-panel__item-lock"></div>`
				: this.#getCounter().getContainer();

			this.layout.value = Tag.render`
				<div class="ui-counter-panel__item-value">
					${counterValue}
				</div>
			`;

			this.layout.value.style.setProperty('order', this.valueOrder);
		}

		return this.layout.value;
	}

	#getTitle()
	{
		if (!this.layout.title)
		{
			this.layout.title = Tag.render`
				<div class="ui-counter-panel__item-title">${this.title}</div>
			`;

			this.layout.title.style.setProperty('order', this.titleOrder);
		}

		return this.layout.title;
	}

	#getCross()
	{
		if (!this.layout.cross)
		{
			this.layout.cross = Tag.render`
				<div class="ui-counter-panel__item-cross">
					<i></i>
				</div>
			`;
		}

		return this.layout.cross;
	}

	setEvents(container)
	{
		if (!container) 
		{
			container = this.getContainer();
		}
		
		if (this.eventsForActive)
		{
			const eventKeys = Object.keys(this.eventsForActive);

			for (let i = 0; i < eventKeys.length; i++)
			{
				let event = eventKeys[i];
				container.addEventListener(event, () => {
					if (this.isActive)
					{
						this.eventsForActive[event]();
					}
				})
			}
		}

		if (this.eventsForUnActive)
		{
			const eventKeys = Object.keys(this.eventsForUnActive);

			for (let i = 0; i < eventKeys.length; i++)
			{
				let event = eventKeys[i];
				container.addEventListener(event, () => {
					if (!this.isActive)
					{
						this.eventsForUnActive[event]();
					}
				})
			}
		}
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

	getArrowDropdown()
	{
		if (!this.layout.dropdownArrow)
		{
			this.layout.dropdownArrow = Tag.render`
				<div class="ui-counter-panel__item-dropdown">
					<i></i>
				</div>
			`;
		}

		return this.layout.dropdownArrow;
	}

	getContainerMenu()
	{
		if (!this.layout.menuItem)
		{
			this.layout.menuItem = Tag.render`
				<span>
					${this.#getValue()}
					${this.title}
					${this.#getCross()}
				</span>
			`;
		}

		return this.layout.menuItem;
	}

	getContainer()
	{
		if (!this.layout.container)
		{
			const type = this.type ? `id="ui-counter-panel-item-${this.type}"` : '';
			const isValue = Type.isNumber(this.value);

			this.layout.container = Tag.render`
				<div ${type} class="ui-counter-panel__item">
					${isValue ? this.#getValue() : ''}
					${this.title ? this.#getTitle() : ''}
					${isValue ? this.#getCross() : ''}
				</div>
			`;

			if (this.parent)
			{
				this.layout.container = Tag.render`
					<div class="ui-counter-panel__item">
						${this.title ? this.#getTitle() : ''}
						${isValue ? this.#getValue() : ''}
						${this.#getCross()}
					</div>
				`;

				this.#getCross().addEventListener('click', (ev) => {
					this.deactivate();
					ev.stopPropagation();
				});

				Dom.addClass(this.layout.container, '--dropdown');
			}

			if (!isValue)
			{
				this.layout.container.classList.add('--string');
			}

			if (!isValue && !this.eventsForActive && !this.eventsForUnActive)
			{
				this.layout.container.classList.add('--title');
			}

			if (!this.separator)
			{
				this.layout.container.classList.add('--without-separator');
			}

			if (this.locked)
			{
				this.layout.container.classList.add('--locked');
			}

			if (this.isActive)
			{
				this.activate();
			}

			if (this.isRestricted)
			{
				this.layout.container.classList.add('--restricted');
			}

			this.setEvents(this.layout.container);

			if (isValue && this.items.length === 0)
			{
				if (!this.parent)
				{
					this.layout.container.addEventListener('mouseenter', () => {
						if (!this.isActive)
						{
							this.layout.container.classList.add('--hover');
						}
					});

					this.layout.container.addEventListener('mouseleave', () => {
						if (!this.isActive)
						{
							this.layout.container.classList.remove('--hover');
						}
					});

					this.layout.container.addEventListener('click', () => {
						this.isActive
							? this.deactivate()
							: this.activate();
					});
				}
			}

			if (this.parent)
			{
				Dom.append(this.getArrowDropdown(), this.layout.container);
			}
		}

		return this.layout.container;
	}
}
