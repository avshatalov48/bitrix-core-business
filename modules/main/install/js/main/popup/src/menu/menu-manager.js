import Menu from './menu';
import { Type } from 'main.core';
import { MenuOptions } from './menu-types';

export default class MenuManager
{
	/**
	 * @private
	 */
	static Data: { [id: string]: Menu } = {};

	/**
	 * @private
	 */
	static currentItem: ? Menu = null;

	constructor()
	{
		throw new Error('You cannot make an instance of MenuManager.');
	}

	static show(...args)
	{
		if (this.currentItem !== null)
		{
			this.currentItem.popupWindow.close();
		}

		this.currentItem = this.create.apply(this, args);
		this.currentItem.popupWindow.show();
	}

	static create(options: MenuOptions)
	{
		let menuId = null;

		//Compatibility
		const bindElement = arguments[1];
		const menuItems = arguments[2];
		const params = arguments[3];

		if (Type.isPlainObject(options) && !bindElement && !menuItems && !params)
		{
			menuId = options.id;
			if (!Type.isStringFilled(menuId))
			{
				throw new Error('BX.Main.Menu.create: "id" parameter is required.');
			}
		}
		else
		{
			menuId = options;
		}

		if (!this.Data[menuId])
		{
			const menu = new Menu(options, bindElement, menuItems, params);
			menu.getPopupWindow().subscribe('onDestroy', () => {
				MenuManager.destroy(menuId);
			});

			this.Data[menuId] = menu;
		}

		return this.Data[menuId];
	}

	static getCurrentMenu(): Menu | null
	{
		return this.currentItem;
	}

	static getMenuById(id): Menu | null
	{
		return this.Data[id] ? this.Data[id] : null;
	}

	/**
	 * compatibility
	 * @private
	 */
	static onPopupDestroy(popupMenuWindow: Menu)
	{
		this.destroy(popupMenuWindow.id);
	}

	static destroy(id)
	{
		const menu = this.getMenuById(id);
		if (menu)
		{
			if (this.currentItem === menu)
			{
				this.currentItem = null;
			}

			delete this.Data[id];
			menu.getPopupWindow().destroy();
		}
	}
}