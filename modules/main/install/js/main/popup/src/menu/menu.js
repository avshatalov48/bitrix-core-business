import MenuItem from './menu-item';
import Popup from '../popup/popup';
import { Type, Text, Tag } from 'main.core';
import { type MenuOptions, type MenuItemOptions } from './menu-types';
import { type PopupTargetOptions } from '../popup/popup-types';

/**
 * @memberof BX.Main
 */
export default class Menu
{
	constructor(options: MenuOptions)
	{
		let [
			id: string,
			bindElement: PopupTargetOptions,
			menuItems: MenuItemOptions[],
			params: MenuOptions
		] = arguments;

		if (Type.isPlainObject(options) && !bindElement && !menuItems && !params)
		{
			params = options;
			params.compatibleMode = false;

			id = options.id;
			bindElement = options.bindElement;
			menuItems = options.items;

			if (!Type.isStringFilled(id))
			{
				id = 'menu-popup-' + Text.getRandom();
			}
		}

		this.id = id;
		this.bindElement = bindElement;

		/**
		 *
		 * @type {MenuItem[]}
		 */
		this.menuItems = [];
		this.itemsContainer = null;
		this.params = params && typeof (params) === 'object' ? params : {};
		this.parentMenuWindow = null;
		this.parentMenuItem = null;

		if (menuItems && Type.isArray(menuItems))
		{
			for (let i = 0; i < menuItems.length; i++)
			{
				this.addMenuItemInternal(menuItems[i], null);
			}
		}

		this.layout = {
			menuContainer: null,
			itemsContainer: null
		};

		this.popupWindow = this.__createPopup();
	}

	/**
	 * @private
	 */
	__createPopup(): Popup
	{
		const domItems = [];
		for (let i = 0; i < this.menuItems.length; i++)
		{
			const item = this.menuItems[i];
			const itemLayout = item.getLayout();
			domItems.push(itemLayout.item);
		}

		const defaults = {
			closeByEsc: false,
			angle: false,
			autoHide: true,
			offsetTop: 1,
			offsetLeft: 0,
			animation: 'fading'
		};

		const options = Object.assign(defaults, this.params);

		//Override user params
		options.noAllPaddings = true;
		options.darkMode = false;
		options.autoHideHandler = this.handleAutoHide.bind(this);

		this.layout.itemsContainer = Tag.render`
			<div class="menu-popup-items">${domItems}</div>
		`;

		this.layout.menuContainer = Tag.render`
			<div class="menu-popup">${this.layout.itemsContainer}</div>
		`;

		this.itemsContainer = this.layout.itemsContainer;
		options.content = this.layout.menuContainer;

		//Make internal event handlers first in the queue.
		options.events = {
			onClose: this.handlePopupClose.bind(this),
			onDestroy: this.handlePopupDestroy.bind(this)
		};

		const id = options.compatibleMode === false ? this.getId() : 'menu-popup-' + this.getId();
		const popup = new Popup(id, this.bindElement, options);
		if (this.params && this.params.events)
		{
			popup.subscribeFromOptions(this.params.events);
		}

		return popup;
	}

	getPopupWindow(): Popup
	{
		return this.popupWindow;
	}

	show(): void
	{
		this.getPopupWindow().show();
	}

	close(): void
	{
		this.getPopupWindow().close();
	}

	destroy(): void
	{
		this.getPopupWindow().destroy();
	}

	toggle(): void
	{
		if (this.getPopupWindow().isShown())
		{
			this.close();
		}
		else
		{
			this.show();
		}
	}

	getId(): string
	{
		return this.id;
	}

	/**
	 * @private
	 */
	handlePopupClose(): void
	{
		for (let i = 0; i < this.menuItems.length; i++)
		{
			const item = this.menuItems[i];
			item.closeSubMenu();
		}
	}

	/**
	 * @private
	 */
	handlePopupDestroy(): void
	{
		for (let i = 0; i < this.menuItems.length; i++)
		{
			const item = this.menuItems[i];
			item.destroySubMenu();
		}
	}

	/**
	 * @private
	 */
	handleAutoHide(event): boolean
	{
		return !this.containsTarget(event.target);
	}

	/**
	 * @private
	 */
	containsTarget(target: Element): boolean
	{
		const el = this.getPopupWindow().getPopupContainer();
		if (this.getPopupWindow().isShown() && (target === el || el.contains(target)))
		{
			return true;
		}

		return this.getMenuItems().some(function(item: MenuItem) {

			return item.getSubMenu() && item.getSubMenu().containsTarget(target);

		});
	}

	setParentMenuWindow(parentMenu: Menu): void
	{
		if (parentMenu instanceof Menu)
		{
			this.parentMenuWindow = parentMenu;
		}
	}

	getParentMenuWindow(): Menu | null
	{
		return this.parentMenuWindow;
	}

	getRootMenuWindow(): Menu | null
	{
		let root = null;
		let parent = this.getParentMenuWindow();
		while (parent !== null)
		{
			root = parent;
			parent = parent.getParentMenuWindow();
		}

		return root;
	}

	setParentMenuItem(parentItem: MenuItem): void
	{
		if (parentItem instanceof MenuItem)
		{
			this.parentMenuItem = parentItem;
		}
	}

	getParentMenuItem(): MenuItem | null
	{
		return this.parentMenuItem;
	}

	addMenuItem(menuItemJson: any, targetItemId: string): MenuItem
	{
		const menuItem = this.addMenuItemInternal(menuItemJson, targetItemId);
		if (!menuItem)
		{
			return null;
		}

		const itemLayout = menuItem.getLayout();
		const targetItem = this.getMenuItem(targetItemId);
		if (targetItem !== null)
		{
			const targetLayout = targetItem.getLayout();
			this.itemsContainer.insertBefore(itemLayout.item, targetLayout.item);
		}
		else
		{
			this.itemsContainer.appendChild(itemLayout.item);
		}

		return menuItem;
	}

	/**
	 * @private
	 */
	addMenuItemInternal(menuItemJson: any, targetItemId: string): MenuItem
	{
		if (
			!menuItemJson ||
			(
				!menuItemJson.delimiter &&
				!Type.isStringFilled(menuItemJson.text) &&
				!Type.isStringFilled(menuItemJson.html) &&
				!Type.isElementNode(menuItemJson.html)
			) ||
			(menuItemJson.id && this.getMenuItem(menuItemJson.id) !== null)
		)
		{
			return null;
		}

		if (Type.isNumber(this.params.menuShowDelay))
		{
			menuItemJson.menuShowDelay = this.params.menuShowDelay;
		}

		const menuItem = new MenuItem(menuItemJson);
		menuItem.setMenuWindow(this);

		const position = this.getMenuItemPosition(targetItemId);
		if (position >= 0)
		{
			this.menuItems.splice(position, 0, menuItem);
		}
		else
		{
			this.menuItems.push(menuItem);
		}

		return menuItem;
	}

	removeMenuItem(itemId: string, options = {
		destroyEmptyPopup: true,
	}): void
	{
		const item = this.getMenuItem(itemId);
		if (!item)
		{
			return;
		}

		for (let position = 0; position < this.menuItems.length; position++)
		{
			if (this.menuItems[position] === item)
			{
				item.destroySubMenu();
				this.menuItems.splice(position, 1);
				break;
			}
		}

		if (!this.menuItems.length)
		{
			const menuWindow = item.getMenuWindow();
			if (menuWindow)
			{
				const parentMenuItem = menuWindow.getParentMenuItem();
				if (parentMenuItem)
				{
					parentMenuItem.destroySubMenu();
				}
				else if (options.destroyEmptyPopup)
				{
					menuWindow.destroy();
				}
			}
		}

		item.layout.item.parentNode.removeChild(item.layout.item);
		item.layout = {
			item: null,
			text: null
		};
	}

	getMenuItem(itemId: string): MenuItem | null
	{
		for (let i = 0; i < this.menuItems.length; i++)
		{
			if (this.menuItems[i].id && this.menuItems[i].id === itemId)
			{
				return this.menuItems[i];
			}
		}

		return null;
	}

	getMenuItems(): MenuItem[]
	{
		return this.menuItems;
	}

	getMenuItemPosition(itemId: string): number
	{
		if (itemId)
		{
			for (let i = 0; i < this.menuItems.length; i++)
			{
				if (this.menuItems[i].id && this.menuItems[i].id === itemId)
				{
					return i;
				}
			}
		}

		return -1;
	}

	getMenuContainer()
	{
		return this.getPopupWindow().getPopupContainer();
	}
}