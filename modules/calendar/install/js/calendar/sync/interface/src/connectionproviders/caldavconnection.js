import {Connection} from "./connection";
import {Loc} from "main.core";
import {ConnectionItem} from "./connectionitem";
import {InterfaceTemplate} from "../itemstemplate/interfacetemplate";
import {Menu} from "main.popup";
import GridUnit from "../gridunit";

export class CaldavConnection extends Connection
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
			if (connections[key].connected === true
				&& connections[key].status === false)
			{
				return false
			}
		}

		return true;
	}

	hasMenu()
	{
		return this.connected;
	}

	showMenu(bindElement)
	{
		if (this.menu)
		{
			this.menu.getPopupWindow().setBindElement(bindElement);
			this.menu.show();
			return;
		}

		const menuItems = this.connections;
		menuItems.forEach(item => {
			item.type = this.type;
			item.id = item.addParams.id;
			item.text = item.connectionName;
			item.onclick = () => {
				this.openActiveConnectionSlider(item);
			};
		});

		menuItems.push(
			{delimiter: true},
			{
				id: 'connect',
				text: Loc.getMessage('ADD_MENU_CONNECTION'),
				onclick: () => {
					this.openInfoConnectionSlider();
				},
			}
		);

		this.menu = new Menu({
			className: 'calendar-sync-popup-status',
			bindElement: bindElement,
			items: menuItems,
			width: GridUnit.MENU_WIDTH,
			padding: GridUnit.MENU_PADDING,
			autoHide: true,
			closeByEsc: true,
			zIndexAbsolute: GridUnit.MENU_INDEX,
			id: this.getType() + '-menu',
		});

		this.menu.getMenuContainer().addEventListener('click', () => {
			this.menu.close();
		});

		this.menu.show();
	}

	setConnections()
	{
		if (this.connectionsSyncInfo.length > 0)
		{
			this.connectionsSyncInfo.forEach(connection => {
				this.connections.push(ConnectionItem.createInstance({
					syncTimestamp: connection.syncInfo.syncTimestamp,
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