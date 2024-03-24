import {Tag, Type, Loc, Event, Dom} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {Menu} from 'main.popup';

import {Collection} from './collection.js';

export type ItemOptions = {
	label: string;
	active: ?boolean;
	notice: ?boolean;
	onclick: ?Function;
	id: ?string;
	items: ?Array<ItemOptions>;
	actions?: Array<ActionOptions>;
	moduleId: ?string;
};

type ActionOptions = {
	id: number | string;
	label: string,
	onclick: Function;
}

export class Item extends EventEmitter
{
	#id: ?string;
	#label: string;
	#active: ?boolean;
	#notice: ?boolean;
	#onclick: ?Function;
	#collection: Collection;
	#node: HTMLElement;
	#actions: Array<ActionOptions>;
	#moduleId: ?string;

	constructor(options: ItemOptions)
	{
		super(options);

		this.setEventNamespace('ui:sidepanel:menu:item');

		this.#collection = new Collection();
		this.setLabel(options.label)
			.setActive(options.active)
			.setNotice(options.notice)
			.setId(options.id)
			.setItems(options.items)
			.setClickHandler(options.onclick)
			.setActions(options.actions)
			.setModuleId(options.moduleId)
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

	setModuleId(moduleId: ?string): Item
	{
		this.#moduleId = moduleId;

		return this;
	}

	setActions(actions: Array<ActionOptions> = []): Item
	{
		this.#actions = actions;

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

	getModuleId(): ?string
	{
		return this.#moduleId;
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

	hasActions(): boolean
	{
		return this.#actions.length > 0;
	}

	change(options: ItemOptions)
	{
		if (!Type.isUndefined(options.label))
		{
			this.setLabel(options.label);
		}

		if (!Type.isUndefined(options.active))
		{
			this.setActive(options.active);
		}

		if (!Type.isUndefined(options.notice))
		{
			this.setNotice(options.notice);
		}

		if (!Type.isUndefined(options.id))
		{
			this.setId(options.id);
		}

		if (!Type.isUndefined(options.items))
		{
			this.setItems(options.items);
		}

		if (!Type.isUndefined(options.onclick))
		{
			this.setClickHandler(options.onclick);
		}

		if (!Type.isUndefined(options.actions))
		{
			this.setActions(options.actions);
		}
	}

	remove()
	{
		Dom.remove(this.#node);

		this.#node = null;
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

	#showActionMenu(event: Event)
	{
		event.preventDefault();
		event.stopPropagation();

		if (this.actionsMenu)
		{
			this.actionsMenu.getPopupWindow().close();

			return;
		}

		const targetIcon: HTMLElement = event.currentTarget;

		Dom.addClass(targetIcon, '--hover');
		Dom.addClass(targetIcon.parentNode, '--hover');

		this.actionsMenu = new Menu({
			id: `ui-sidepanel-menu-item-actions-${this.getId()}`,
			bindElement: targetIcon
		});

		this.#actions.forEach((action: ActionOptions) => {
			this.actionsMenu.addMenuItem({
				text: action.label,
				onclick: (event, menuItem) => {
					menuItem.getMenuWindow().close();
					action.onclick(this);
				}
			});
		});

		this.actionsMenu.getPopupWindow()
			.subscribe('onClose', () => {
				Dom.removeClass(targetIcon, '--hover');
				Dom.removeClass(targetIcon.parentNode, '--hover');

				this.actionsMenu.destroy();
				this.actionsMenu = null;
			})
		;

		this.actionsMenu.show();
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
					${
						this.hasActions()
							? '<span class="ui-sidepanel-menu-action-icon ui-btn ui-btn-link ui-btn-icon-edit"></span>'
							: ''
					}
				</a>
			</li>
		`;

		if (this.hasActions())
		{
			Event.bind(
				this.#node.querySelector('.ui-sidepanel-menu-action-icon'),
				'click',
				this.#showActionMenu.bind(this)
			);
		}

		if (!this.#collection.isEmpty())
		{
			Dom.append(this.#collection.render(), this.#node);
		}

		return this.#node;
	}
}
