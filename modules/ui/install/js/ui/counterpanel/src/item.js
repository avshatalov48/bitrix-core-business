import { Dom, Tag, Type, Event } from 'main.core';
import { Counter, CounterColor, CounterStyle } from 'ui.cnt';
import { EventEmitter } from 'main.core.events';
import 'ui.design-tokens';
import 'ui.icon-set.api.core';
import 'ui.icon-set.outline';

import { CounterPanel } from './index';

export type CounterItemOptions = {
	id?: string;
	panel: CounterPanel;
	title: string | { value: number, order: number };
	parentId: string;
	collapsedIcon?: string;
	collapsed?: string;
	locked?: boolean;
	dataAttributes?: { [key: string]: string };
	color?: CounterColor;
	value?: number;
	isActive: boolean;
	separator?: boolean;
	items?: CounterItemOptions[];
	type?: string;
	eventsForActive: Object;
	eventsForUnActive: Object;
	hideValue: boolean;
	isRestricted?: boolean;
	useAirDesign?: boolean;
}

export default class CounterItem
{
	#collapsedIcon: ?string;
	#collapsed: ?boolean;
	counter: ?Counter;
	#dataAttributes: ?{ [key: string]: string };
	#useAirDesign: boolean = false;

	constructor(args: CounterItemOptions)
	{
		this.id = args.id ?? null;
		this.separator = Type.isBoolean(args.separator) ? args.separator : true;
		this.items = Type.isArray(args.items) ? args.items : [];
		this.popupMenu = null;
		this.isActive = Type.isBoolean(args.isActive) ? args.isActive : false;
		this.isRestricted = Type.isBoolean(args.isRestricted) ? args.isRestricted : false;
		this.panel = args.panel ?? null;
		this.title = args.title ?? null;
		this.value = (Type.isNumber(args.value) && args.value !== undefined) ? args.value : null;
		this.titleOrder = null;
		this.valueOrder = null;
		this.color = args.color ?? null;
		this.parent = Type.isBoolean(args.parent) ? args.parent : null;
		this.parentId = args.parentId ?? null;
		this.locked = args.locked === true;
		this.type = Type.isString(args.type) ? args.type.toLowerCase() : null;
		this.eventsForActive = Type.isObject(args.eventsForActive) ? args.eventsForActive : {};
		this.eventsForUnActive = Type.isObject(args.eventsForUnActive) ? args.eventsForUnActive : {};
		this.hideValue = Type.isBoolean(args.hideValue) ? args.hideValue : false;
		this.#collapsedIcon = args.collapsedIcon ?? null;
		this.#collapsed = args.collapsed === true;
		this.#dataAttributes = Type.isPlainObject(args.dataAttributes) ? args.dataAttributes : {};
		this.#useAirDesign = args.useAirDesign === true;

		if (Type.isObject(args.title))
		{
			this.title = args.title.value ?? null;
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
			menuItem: null,
		};

		this.counter = this.#getCounter();

		if (!this.#getPanel().isMultiselect())
		{
			this.#bindEvents();
		}
	}

	getItems(): (CounterItemOptions | CounterItem)[]
	{
		return this.items;
	}

	getId(): ?string
	{
		return this.id;
	}

	hasParentId(): ?string
	{
		return this.parentId;
	}

	#bindEvents(): void
	{
		EventEmitter.subscribe('BX.UI.CounterPanel.Item:activate', (item) => {
			const isLinkedItems = item.data.parentId === this.id;
			if (item.data !== this && !isLinkedItems)
			{
				this.deactivate();
			}
		});
	}

	updateValue(param: number): void
	{
		if (Type.isNumber(param))
		{
			this.value = param;
			this.#getCounter().update(param);

			if (param === 0)
			{
				this.updateColor(this.parentId ? 'GRAY' : 'THEME');
				Dom.addClass(this.layout.container, this.#getZeroItemClassModifier());
			}
			else
			{
				Dom.removeClass(this.layout.container, this.#getZeroItemClassModifier());
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
				const color = this.parentId ? 'GRAY' : 'THEME';

				this.updateColor(color);
				this.#getCounter().setStyle(this.#getCounterStyleByColor(Counter.Color[color]));
			}
		}
	}

	updateColor(param: string)
	{
		if (Type.isString(param))
		{
			this.color = param;
			this.#getCounter().setColor(Counter.Color[param]);
			this.#getCounter().setStyle(this.#getCounterStyleByColor(Counter.Color[param]));
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
					className: 'ui-counter-panel__popup-item',
				},
			);

			if (target)
			{
				Dom.addClass(target, '-active');
			}
		}
		else
		{
			Dom.addClass(this.getContainer(), '--active');
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
					className: 'ui-counter-panel__popup-item',
				},
			);

			if (target)
			{
				Dom.removeClass(target, '--active');
				Dom.removeClass(target, '--hover');
			}
		}
		else
		{
			Dom.removeClass(this.getContainer(), '--active');
			Dom.removeClass(this.getContainer(), '--hover');
		}

		if (isEmitEvent)
		{
			EventEmitter.emit('BX.UI.CounterPanel.Item:deactivate', this);
		}
	}

	collapse(): void
	{
		Dom.addClass(this.getContainer(), '--collapsed');
	}

	expand(): void
	{
		Dom.removeClass(this.getContainer(), '--collapsed');
	}

	getSeparator()
	{
		return this.separator;
	}

	#getPanel(): CounterPanel
	{
		return this.panel;
	}

	#getCounter(): Counter
	{
		if (!this.counter)
		{
			const counterColor = this.color
				? Counter.Color[this.color.toUpperCase()]
				: (this.parentId ? Counter.Color.GRAY : Counter.Color.THEME)
			;

			this.counter = new Counter({
				color: counterColor,
				value: this.value,
				animation: false,
				useAirDesign: this.#useAirDesign,
				style: this.#getCounterStyleByColor(counterColor),
			});
		}

		return this.counter;
	}

	getCounterContainer(): ?HTMLElement
	{
		return this.layout.value;
	}

	#getValue(): HTMLElement
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

			Dom.style(this.layout.value, 'order', this.valueOrder);
		}

		return this.layout.value;
	}

	#getTitle(): HTMLElement
	{
		if (!this.layout.title)
		{
			this.layout.title = Tag.render`
				<div class="ui-counter-panel__item-title">${this.title}</div>
			`;

			Dom.style(this.layout.title, 'order', this.titleOrder);
		}

		return this.layout.title;
	}

	#getCollapsedIcon(): HTMLElement
	{
		return Tag.render`
			<div class="ui-counter-panel__item-collapsed-icon ui-icon-set__scope --icon-${this.#collapsedIcon}"></div>
		`;
	}

	#getCross(): HTMLElement
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

			for (const event of eventKeys)
			{
				Event.bind(container, event, () => {
					if (this.isActive)
					{
						this.eventsForActive[event]();
					}
				});
			}
		}

		if (this.eventsForUnActive)
		{
			const eventKeys = Object.keys(this.eventsForUnActive);

			for (const event of eventKeys)
			{
				Event.bind(container, event, () => {
					if (!this.isActive)
					{
						this.eventsForUnActive[event]();
					}
				});
			}
		}
	}

	isLocked(): boolean
	{
		return this.locked;
	}

	lock(): void
	{
		this.locked = true;
		Dom.addClass(this.getContainer(), '--locked');
	}

	unLock(): void
	{
		this.locked = false;
		Dom.removeClass(this.getContainer(), '--locked');
	}

	getArrowDropdown(): HTMLElement
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

	getContainerMenu(): HTMLElement
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

	getContainer(): HTMLElement
	{
		if (!this.layout.container)
		{
			const type = this.type ? `id="ui-counter-panel-item-${this.type}"` : '';
			const isValue = Type.isNumber(this.value);

			this.layout.container = Tag.render`
				<div ${type} class="ui-counter-panel__item ${this.#getItemClassModifierByValue(this.value)}">
					${this.#collapsedIcon ? this.#getCollapsedIcon() : ''}
					${isValue && !this.hideValue ? this.#getValue() : ''}
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

				Event.bind(this.#getCross(), 'click', (ev) => {
					this.deactivate();
					ev.stopPropagation();
				});

				Dom.addClass(this.layout.container, '--dropdown');
			}

			if (!isValue)
			{
				Dom.addClass(this.layout.container, '--string');
			}

			if (!isValue && !this.eventsForActive && !this.eventsForUnActive)
			{
				Dom.addClass(this.layout.container, '--title');
			}

			if (!this.separator)
			{
				Dom.addClass(this.layout.container, '--without-separator');
			}

			if (this.locked)
			{
				Dom.addClass(this.layout.container, '--locked');
			}

			if (this.isActive)
			{
				this.activate();
			}

			if (this.isRestricted)
			{
				Dom.addClass(this.layout.container, '--restricted');
			}

			if (this.#collapsed)
			{
				this.collapse();
			}

			if (this.locked)
			{
				this.lock();
			}

			this.setEvents(this.layout.container);
			this.#setElementDataAttributes(this.layout.container);

			Event.bind(this.layout.container, 'click', () => {
				EventEmitter.emit('BX.UI.CounterPanel.Item:click', {
					item: this,
				});
			});

			if (isValue && this.items.length === 0 && !this.parent)
			{
				Event.bind(this.layout.container, 'mouseenter', () => {
					if (!this.isActive)
					{
						Dom.addClass(this.layout.container, '--hover');
					}
				});

				Event.bind(this.layout.container, 'mouseleave', () => {
					if (!this.isActive)
					{
						Dom.removeClass(this.layout.container, '--hover');
					}
				});

				Event.bind(this.layout.container, 'click', () => {
					if (this.isActive)
					{
						this.deactivate();
					}
					else
					{
						this.activate();
					}
				});
			}

			if (this.parent)
			{
				Dom.append(this.getArrowDropdown(), this.layout.container);
			}
		}

		return this.layout.container;
	}

	setDataAttributes(attributes: Object): void
	{
		this.#dataAttributes = Type.isPlainObject(attributes) || {};
		this.#setElementDataAttributes(this.getContainer());
	}

	#setElementDataAttributes(element?: HTMLElement): void
	{
		if (!element)
		{
			return;
		}

		Object.entries(this.#dataAttributes).forEach(([key, value]) => {
			Dom.attr(element, `data-${key}`, value);
		});
	}

	#getCounterStyleByColor(color: string): string
	{
		if (color === CounterColor.DANGER)
		{
			return CounterStyle.FILLED_ALERT;
		}

		if (color === CounterColor.SUCCESS)
		{
			return CounterStyle.FILLED_SUCCESS;
		}

		return CounterStyle.OUTLINE_NO_ACCENT;
	}

	#getItemClassModifierByValue(value: number): string
	{
		return value === 0 ? this.#getZeroItemClassModifier() : '';
	}

	#getZeroItemClassModifier(): string
	{
		return '--zero';
	}
}
