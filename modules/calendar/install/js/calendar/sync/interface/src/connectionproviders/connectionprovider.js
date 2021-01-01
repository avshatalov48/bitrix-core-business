import {ConnectionItem} from "./connectionitem";
import {Reflection, Type} from "main.core";

export class ConnectionProvider
{
	SLIDER_WIDTH = 606;
	constructor(options)
	{
		this.status = options.status;
		this.connected = options.connected;
		this.connections = options.connections;
		this.gridTitle = options.gridTitle;
		this.gridColor = options.gridColor;
		this.gridIcon = options.gridIcon;
		this.type = options.type;
		this.viewClassification = options.viewClassification;
		this.templateClass = options.templateClass;
		this.connections = [];
	}

	static createInstance(options)
	{
		return new this(options);
	}

	isActive()
	{
		return this.connected;
	}

	hasMenu()
	{
		return false;
	}

	setAdditionalParams (options)
	{
		this.additionalParams = options;
	}

	getGridTitle()
	{
		return this.gridTitle;
	}

	getGridColor()
	{
		return this.gridColor;
	}

	getGridIcon()
	{
		return this.gridIcon;
	}

	setConnections()
	{
		this.connections.push(ConnectionItem.createInstance({
			syncTimestamp: this.syncTimestamp,
			connectionName: this.connectionName,
			status: this.status,
			connected: this.connected,
			addParams: {
				sections: this.sections,
				id: this.id || this.type,
			},
			type: this.type,
		}));
	}

	getConnections()
	{
		return this.connections;
	}

	getConnection()
	{
		return this.connections[0];
	}

	getType()
	{
		return this.type;
	}

	getViewClassification()
	{
		return this.viewClassification;
	}

	getConnectStatus()
	{
		return this.connected;
	}

	getSyncStatus()
	{
		return this.status;
	}

	getStatus()
	{
		if (this.connected)
		{
			return this.status
				? "success"
				: "failed";
		}
		else
		{
			return 'not_connected';
		}
	}

	getTemplateClass()
	{
		return this.templateClass;
	}

	openSlider(options)
	{
		BX.SidePanel.Instance.open(options.sliderId, {
			contentCallback(slider)
			{
				return new Promise((resolve, reject) => {
					resolve(options.content);
				});
			},
			data: options.data || {},
			cacheable: options.cacheable,
			width: this.SLIDER_WIDTH,
			allowChangeHistory: false,
			events: {
				onLoad: event => {
					this.itemSlider = event.getSlider();
				}
			}
		});
	}

	openInfoConnectionSlider()
	{
		const content = this.getClassTemplateItem().createInstance(this).getInfoConnectionContent();
		this.openSlider({
			sliderId: 'calendar:item-sync-connect-' + this.type,
			content: content,
			cacheable: false,
			data: {
				provider: this,
			},
		});
	}

	openActiveConnectionSlider(connection)
	{
		const itemInterface = this.getClassTemplateItem().createInstance(this, connection);
		const content = itemInterface.getActiveConnectionContent();
		this.openSlider({
			sliderId: 'calendar:item-sync-' + connection.id,
			content: content,
			cacheable: false,
			data: {
				provider: this,
				connection: connection,
				itemInterface: itemInterface,
			},
		});
	}

	getClassTemplateItem()
	{
		const itemClass = Reflection.getClass(this.getTemplateClass());
		if (Type.isFunction(itemClass))
		{
			return itemClass;
		}

		return null;
	}

	getConnectionById(id)
	{
		const connections = this.getConnections();
		if (connections.length > 0)
		{
			connections.forEach(connection => {
				if (connection.getId() === id)
				{
					return connection;
				}
			})
		}

		return this.getConnection();
	}
}