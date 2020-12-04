export class ConnectionItem
{
	constructor(options)
	{
		this.syncTimestamp = options.syncTimestamp;
		this.connectionName = options.connectionName;
		this.status = options.status;
		this.connected = options.connected;
		this.addParams = options.addParams;
		this.type = options.type;
		this.id = options.type;
	}

	static createInstance(options)
	{
		return new this(options);
	}

	getSyncTimestamp()
	{
		return this.syncTimestamp;
	}

	getConnectionName()
	{
		return this.connectionName;
	}

	getSyncStatus()
	{
		return this.status;
	}

	getConnectStatus()
	{
		return this.connected;
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

	getClassLable()
	{
		return this.type;
	}

	getSections()
	{
		return this.addParams.sections;
	}

	getId()
	{
		return this.addParams.id;
	}

	getType()
	{
		return this.type;
	}
}