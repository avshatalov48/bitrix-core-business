import {ConnectionProvider} from "./connectionprovider";
import {Loc} from "main.core";

export class AndroidProvider extends ConnectionProvider
{
	constructor(options)
	{
		super({
			status: options.syncInfo.status,
			connected: options.syncInfo.connected,
			gridTitle: Loc.getMessage('CALENDAR_TITLE_ANDROID'),
			gridColor: '#9ece03',
			gridIcon: '/bitrix/images/calendar/sync/android.svg',
			type: 'android',
			viewClassification: 'mobile',
			templateClass: 'BX.Calendar.Sync.Interface.AndroidTemplate',
		});
		this.connectionName = Loc.getMessage('CALENDAR_TITLE_ANDROID');

		this.setSyncDate(options.syncInfo.syncOffset);
		this.setConnections();
	}
}