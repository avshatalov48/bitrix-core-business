import {ConnectionProvider} from "./connectionprovider";
import {Loc} from "main.core";

export class IphoneProvider extends ConnectionProvider
{
	constructor(options)
	{
		super({
			status: options.syncInfo.status,
			connected: options.syncInfo.connected,
			gridTitle: Loc.getMessage('CALENDAR_TITLE_IPHONE'),
			gridColor: '#2fc6f6',
			gridIcon: '/bitrix/images/calendar/sync/iphone.svg',
			type: 'iphone',
			viewClassification: 'mobile',
			templateClass: 'BX.Calendar.Sync.Interface.IphoneTemplate',
		});
		this.syncTimestamp = options.syncInfo.syncTimestamp;
		this.connectionName = Loc.getMessage('CALENDAR_TITLE_IPHONE');

		this.setConnections();
	}
}