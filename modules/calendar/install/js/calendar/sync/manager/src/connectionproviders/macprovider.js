import {ConnectionProvider} from "./connectionprovider";
import {Loc} from "main.core";

export class MacProvider extends ConnectionProvider
{
	constructor(options)
	{
		super({
			status: options.syncInfo.status,
			connected: options.syncInfo.connected,
			gridTitle: Loc.getMessage('CALENDAR_TITLE_MAC'),
			gridColor: '#ff5752',
			gridIcon: '/bitrix/images/calendar/sync/mac.svg',
			type: 'mac',
			viewClassification: 'web',
			templateClass: 'BX.Calendar.Sync.Interface.MacTemplate',
		});

		this.syncTimestamp = options.syncInfo.syncTimestamp;
		this.portalAddress = options.portalAddress;
		this.connectionName = Loc.getMessage('CALENDAR_TITLE_MAC');

		this.setConnections();
	}

	getPortalAddress()
	{
		return this.portalAddress;
	}
}