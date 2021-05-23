// @flow
'use strict';

import {Type} from "main.core";

const isConnectionItemProperty = Symbol.for('BX.Calendar.Sync.Manager.ConnectionItem.isConnectionItem');

export default class ConnectionItem
{
	constructor(options)
	{
		this[isConnectionItemProperty] = true;
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

	static isConnectionItem(target: Object)
	{
		return Type.isObject(target) && target[isConnectionItemProperty] === true;
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

	getClassLabel()
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