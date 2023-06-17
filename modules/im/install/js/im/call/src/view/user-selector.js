import {Type} from 'main.core';
import {Menu, MenuManager} from 'main.popup';

export class UserSelector
{
	constructor(config)
	{
		this.userList = config.userList;
		this.current = config.current;
		this.parentElement = config.parentElement;
		this.zIndex = config.zIndex;

		this.menu = null;

		this.callbacks = {
			onSelect: Type.isFunction(config.onSelect) ? config.onSelect : BX.DoNothing
		}
	};

	static create(config)
	{
		return new UserSelector(config);
	}

	show()
	{
		let menuItems = [];

		this.userList.forEach((user) =>
		{
			menuItems.push({
				id: user.id,
				text: user.name || "unknown (" + user.id + ")",
				className: (this.current == user.id ? "menu-popup-item-accept" : "device-selector-empty"),
				onclick: () =>
				{
					this.menu.close();
					this.callbacks.onSelect(user.id);
				}
			})
		});

		this.menu = new Menu({
			id: 'call-view-select-user',
			bindElement: this.parentElement,
			items: menuItems,
			autoHide: true,
			zIndex: this.zIndex,
			closeByEsc: true,
			offsetTop: 0,
			offsetLeft: 0,
			bindOptions: {
				position: 'bottom'
			},
			angle: false,
			overlay: {
				backgroundColor: 'white',
				opacity: 0
			},
			events: {
				onPopupClose: () =>
				{
					this.menu.popupWindow.destroy();
					MenuManager.destroy('call-view-select-device');
				},
				onPopupDestroy: () =>
				{
					this.menu = null;
				}
			}
		});
		this.menu.popupWindow.show();
	}
}