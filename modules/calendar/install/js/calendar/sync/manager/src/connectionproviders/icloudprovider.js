import {ConnectionProvider} from "./connectionprovider";
import {Loc} from "main.core";

export class ICloudProvider extends ConnectionProvider
{
	constructor(options)
	{
		super({
			id: options.syncInfo.id || null,
			status: options.syncInfo.status || false,
			connected: options.syncInfo.connected || false,
			userName: options.syncInfo.userName || '',
			gridTitle: Loc.getMessage('CALENDAR_TITLE_ICLOUD'),
			gridColor: '#948f8f',
			gridIcon: '/bitrix/images/calendar/sync/icloud.svg',
			type: 'icloud',
			interfaceClassName: '',
			viewClassification: 'web',
			templateClass: 'BX.Calendar.Sync.Interface.IcloudTemplate',
			mainPanel: true,
		});
		this.connectionName = Loc.getMessage('CALENDAR_TITLE_ICLOUD');

		this.setSyncDate(options.syncInfo.syncOffset);
		this.setSections(options.sections);
		this.setConnections();
	}
}