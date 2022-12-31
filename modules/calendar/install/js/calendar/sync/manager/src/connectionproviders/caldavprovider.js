import {CaldavConnection} from "./caldavconnection";
import {Loc} from "main.core";

export class CaldavProvider extends CaldavConnection
{
	constructor(options)
	{
		super({
			status: options.status,
			connected: options.connected,
			gridTitle: Loc.getMessage('CALENDAR_TITLE_CALDAV'),
			gridColor: '#1eae43',
			gridIcon: '/bitrix/images/calendar/sync/caldav.svg',
			type: 'caldav',
			viewClassification: 'web',
			templateClass: 'BX.Calendar.Sync.Interface.CaldavTemplate',
		});

		this.connectionsSyncInfo = options.connections;

		if (options.connections && options.connections[0] && options.connections[0].syncInfo)
		{
			this.setSyncDate(options.connections[0].syncInfo.syncOffset);
		}
		this.setConnections();
	}
}