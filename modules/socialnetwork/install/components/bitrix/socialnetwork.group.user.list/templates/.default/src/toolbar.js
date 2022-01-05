import {Type} from 'main.core';
import {Popup, MenuManager} from 'main.popup';

export default class Toolbar
{
	constructor(
		params: {
			id: string,
			menuButtonId: string,
			menuItems: object,
		}
	)
	{
		this.id = params.id;
		this.menuItems = params.menuItems;
		this.componentName = params.componentName;

		if (Type.isStringFilled(params.menuButtonId))
		{
			const menuButton = document.getElementById(params.menuButtonId);
			if (menuButton)
			{
				menuButton.addEventListener('click', (e) => {
					this.menuButtonClick(e.currentTarget);
				});
			}
		}
	}

	getId()
	{
		return this._id;
	}

	getSetting(name, defaultval)
	{
		return this._settings.getParam(name, defaultval);
	}


	menuButtonClick(bindNode)
	{
		this.openMenu(bindNode);
	}

	openMenu(bindNode)
	{
		if (this.menuOpened)
		{
			this.closeMenu();
			return;
		}

		if (!Type.isArray(this.menuItems))
		{
			return;
		}

		const menuItems = [];

		this.menuItems.forEach((item) =>
		{
			if (
				!Type.isUndefined(item.SEPARATOR)
				&& item.SEPARATOR
			)
			{
				menuItems.push({
					SEPARATOR: true,
				});
				return;
			}

			if (!Type.isStringFilled(item.TYPE))
			{
				return;
			}

			menuItems.push({
				text: (Type.isStringFilled(item.TITLE) ? item.TITLE : ''),
				onclick: (Type.isStringFilled(item.LINK) ? `window.location.href = "${item.LINK}"; return false;` : ''),
			});
		});


		this.menuId = `${this.id.toLowerCase()}_menu`;

		Popup.show(
			this.menuId,
			bindNode,
			menuItems,
			{
				autoHide: true,
				closeByEsc: true,
				offsetTop: 0,
				offsetLeft: 0,
				events: {
					onPopupShow: this.onPopupShow.bind(this),
					onPopupClose: this.onPopupClose.bind(this),
					onPopupDestroy: this.onPopupDestroy.bind(this),
				}
			}
		);
		this.menuPopup = MenuManager.currentItem;
	}

	closeMenu()
	{
		if (
			!this.menuPopup
			|| !this.menuPopup.popupWindow
		)
		{
			return;
		}

		this.menuPopup.popupWindow.destroy();
	}

	onPopupShow()
	{
		this.menuOpened = true;
	}

	onPopupClose()
	{
		this.closeMenu();
	}

	onPopupDestroy()
	{
		this.menuOpened = false;
		this.menuPopup = null;

		if (!Type.isUndefined(MenuManager.Data[this.menuId]))
		{
			delete(MenuManager.Data[this.menuId]);
		}
	}
}
