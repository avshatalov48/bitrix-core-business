import { Dom, Tag, Type, Extension } from 'main.core';
import { MenuItemOptions } from 'main.popup';
import NavigationItem from './item';
import { Icon, Outline as OutlineIconSet } from 'ui.icon-set.api.core';
import 'ui.icon-set.outline';
import 'ui.fonts.opensans';
import './style.css';

export type NavigationPanelOptions = {
	target: HTMLElement;
	items: Object[];
	collapsed?: boolean;
};

export default class NavigationPanel
{
	keys: string[];
	#isCollapsed: boolean = false;

	constructor(options: NavigationPanelOptions)
	{
		this.target = Type.isDomNode(options.target) ? options.target : null;
		this.items = Type.isArray(options.items) ? options.items : [];
		this.container = null;
		this.keys = [];
		this.#isCollapsed = options.collapsed === true;
	}

	adjustItem()
	{
		this.items = this.items.map((item) => {
			this.keys.push(item.id);

			return new NavigationItem({
				id: item.id ?? null,
				title: item.title ?? null,
				active: item.active === true,
				events: item.events ?? null,
				link: item.link ?? null,
				locked: item.locked === true,
				dropdown: item.active === true && this.#isCollapsed,
				menuItems: item.active === true && this.#isCollapsed ? this.#getMenuItems() : [],
			});
		});
	}

	getItemById(value: string): ?NavigationItem
	{
		if (value)
		{
			const id = this.keys.indexOf(value);

			return this.items[id];
		}

		return null;
	}

	getContainer(): HTMLElement
	{
		if (!this.container)
		{
			this.container = Tag.render`
				<div class="ui-nav-panel ui-nav-panel__scope"></div>
			`;

			if (this.hasAirDesign())
			{
				Dom.addClass(this.container, '--air');
			}

			if (this.#isCollapsed)
			{
				Dom.addClass(this.container, '--collapsed');
			}
		}

		return this.container;
	}

	render()
	{
		this.items.forEach((item) => {
			if (this.#isCollapsed && item.active === false)
			{
				return;
			}

			if (item instanceof NavigationItem)
			{
				Dom.append(item.getContainer(), this.getContainer());
			}
		});

		Dom.clean(this.target);
		Dom.append(this.getContainer(), this.target);
	}

	init()
	{
		this.adjustItem();
		this.render();
	}

	hasAirDesign(): boolean
	{
		return Extension.getSettings('ui.navigationpanel').get('useAirDesign');
	}

	#getMenuItems(): MenuItemOptions[]
	{
		return this.items.map((item: NavigationItem): MenuItemOptions => {
			if (item.active)
			{
				return null;
			}

			return {
				id: Math.random(),
				text: item.title,
				href: item.link?.href,
				html: this.#renderMenuItem(item),
				className: item.locked ? '--locked' : '',
				onclick: () => {
					item.events?.click();
				},
			};
		}).filter((item) => Boolean(item));
	}

	#renderMenuItem(item: Object): HTMLElement
	{
		const airModifier = this.hasAirDesign() ? '--air' : '';

		return Tag.render`
			<div class="ui-nav-panel__menu-item ${item.locked ? '--locked' : ''} ${airModifier}">
				${item.locked ? this.#renderMenuItemLockedIcon() : ''}
				<span>${item.title}</span>
			</div>
		`;
	}

	#renderMenuItemLockedIcon(): HTMLElement
	{
		const icon = (new Icon({
			icon: OutlineIconSet.LOCK_L,
			size: 20,
		})).render();

		return Tag.render`
			<span class="ui-nav-panel__menu-item-icon ui-icon-set__scope">${icon}</span>
		`;
	}
}
