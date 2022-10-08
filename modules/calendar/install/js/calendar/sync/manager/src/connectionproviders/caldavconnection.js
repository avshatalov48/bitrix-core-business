import {ConnectionProvider} from "./connectionprovider";
import {Loc} from "main.core";
import ConnectionItem from "./connectionitem";

export class CaldavConnection extends ConnectionProvider
{
	constructor(options)
	{
		super(options);
	}

	static calculateStatus(connections)
	{
		if (connections.length === 0)
		{
			return false;
		}

		for (let key in connections)
		{
			if (this.isFailedConnections(connections[key]))
			{
				return false;
			}
		}

		return true;
	}

	static isFailedConnections(connection)
	{
		if (connection.syncInfo.connected === true
			&& connection.syncInfo.status === false)
		{
			return true;
		}

		return false;
	}

	hasMenu()
	{
		return this.connected;
	}

	showMenu(bindElement)
	{
		if (this.menu)
		{
			this.menu.destroy();
		}

		const menuItems = this.getMenuItems();
		menuItems.push(...this.getMenuItemConnect());
		this.menu = this.getMenu(bindElement, menuItems);
		this.addMenuHandler();
		this.menu.show();
	}

	addMenuHandler()
	{
		if (this.menu)
		{
			this.menu.getMenuContainer().addEventListener('click', () =>
			{
				this.menu.close();
			});
		}
	}

	getMenuItems()
	{
		const menuItems = [];
		this.connections.forEach(item =>
		{
			item.type = this.type;
			item.id = item.addParams.id;
			item.text = item.connectionName;
			item.onclick = () =>
			{
				this.openActiveConnectionSlider(item);
			};
			menuItems.push(item);
		});

		return menuItems;
	}

	getMenuItemConnect()
	{
		return [
			{delimiter: true},
			{
				id: 'connect',
				text: Loc.getMessage('ADD_MENU_CONNECTION'),
				onclick: () => {
					this.openInfoConnectionSlider();
				}
			}
		];
	}

	getMenu(bindElement, menuItems)
	{
		return new (window.top.BX || window.BX).Main.Menu({
			className: 'calendar-sync-popup-status',
			bindElement: bindElement,
			items: menuItems,
			width: this.MENU_WIDTH,
			padding: this.MENU_PADDING,
			zIndexAbsolute: this.MENU_INDEX,
			autoHide: true,
			closeByEsc: true,
			id: this.getType() + '-menu',
			offsetLeft: -40,
		});
	}

	setConnections()
	{
		if (this.connectionsSyncInfo.length > 0)
		{
			this.connectionsSyncInfo.forEach(connection => {
				this.connections.push(ConnectionItem.createInstance({
					connectionName: connection.syncInfo.connectionName,
					status: connection.syncInfo.status,
					connected: connection.syncInfo.connected,
					addParams: {
						sections: connection.sections,
						id: connection.syncInfo.id,
						userName: connection.syncInfo.userName,
						server: connection.syncInfo.server,
					},
					type: this.type,
				}));
			});
		}
	}
}