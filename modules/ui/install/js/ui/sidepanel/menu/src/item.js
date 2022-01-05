import {Tag, Type, Loc} from 'main.core';
import {EventEmitter} from 'main.core.events';

import {Collection} from './collection.js';

export type ItemOptions = {
	label: string;
	active: ?boolean;
	notice: ?boolean;
	onclick: ?Function;
	id: ?string;
	items: ?Array<ItemOptions>;
};

export class Item extends EventEmitter
{
	#id: ?string;
	#label: string;
	#active: ?boolean;
	#notice: ?boolean;
	#onclick: ?Function;
	#collection: Collection;
	#node: HTMLElement;

	constructor(options: ItemOptions)
	{
		super();
		this.setEventNamespace('ui:sidepanel:menu:item');

		this.#collection = new Collection();
		this.setLabel(options.label)
			.setActive(options.active)
			.setNotice(options.notice)
			.setId(options.id)
			.setItems(options.items)
			.setClickHandler(options.onclick)
		;

		this.#collection.subscribe('sync:active', () => this.emit('sync:active'));
		this.#collection.subscribe('click', event => this.emit('click', event));
	}

	setLabel(label: string = ''): Item
	{
		if (this.#label === label)
		{
			return this;
		}

		this.#label = label;
		this.#emitChange();
		return this;
	}

	setId(id: string): Item
	{
		if (this.#id === id)
		{
			return this;
		}

		this.#id = id;
		this.#emitChange();
		return this;
	}

	setActive(mode: boolean = true): Item
	{
		mode = !!mode;
		if (this.#active === mode)
		{
			return this;
		}

		this.#active = mode;
		this.#emitChange({active: this.#active}, 'active');

		return this;
	}

	setNotice(mode: boolean = false): Item
	{
		this.#notice = !!mode;
		this.#emitChange();
		return this;
	}

	setClickHandler(handler: Function): Item
	{
		this.#onclick = handler;
		return this;
	}

	setItems(items: Array<ItemOptions> = []): Item
	{
		this.#collection.setItems(items || []);
		this.#emitChange();
		return this;
	}

	getCollection(): Collection
	{
		return this.#collection;
	}

	getLabel(): string
	{
		return this.#label;
	}

	getId(): ?string
	{
		return this.#id;
	}

	getClickHandler(): ?Function
	{
		return this.#onclick;
	}

	isActive(): boolean
	{
		return this.#active;
	}

	hasNotice(): boolean
	{
		return this.#notice;
	}

	#emitChange(data = {}, type: string = null): void
	{
		this.emit('change', data);
		if (type)
		{
			this.emit('change:' + type, data);
		}
	}

	#handleClick(event: Event)
	{
		event.preventDefault();
		event.stopPropagation();

		this.setActive(this.#collection.isEmpty() || !this.isActive());
		this.emit('click', {item: this});
		if (Type.isFunction(this.#onclick))
		{
			this.#onclick.apply(this);
		}
	}

	render(): HTMLElement
	{
		const isEmpty = this.#collection.isEmpty();

		const classes = [];
		if (this.#active)
		{
			if (isEmpty)
			{
				classes.push('ui-sidepanel-menu-active');
			}
			else
			{
				classes.push('ui-sidepanel-menu-expand');
			}
		}

		const actionText = Loc.getMessage('UI_SIDEPANEL_MENU_JS_' + (this.isActive() ? 'COLLAPSE' : 'EXPAND'));
		this.#node = Tag.render`
			<li class="ui-sidepanel-menu-item ${classes.join(' ')}">
				<a
					class="ui-sidepanel-menu-link"
					onclick="${this.#handleClick.bind(this)}"
					title="${Tag.safe`${this.#label}`}"
				>
					<div class="ui-sidepanel-menu-link-text">${Tag.safe`${this.#label}`}</div>
					${!isEmpty ? `<div class="ui-sidepanel-toggle-btn">${actionText}</div>` : ''}
					${this.#notice ? '<span class="ui-sidepanel-menu-notice-icon"></span>' : ''}
				</a>
			</li>
		`;


		if (!this.#collection.isEmpty())
		{
			this.#node.appendChild(this.#collection.render());
		}

		return this.#node;
	}
}
