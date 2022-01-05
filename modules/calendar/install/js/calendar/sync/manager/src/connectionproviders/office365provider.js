import {ConnectionProvider} from "./connectionprovider";
import {Loc} from "main.core";

export class Office365Provider extends ConnectionProvider
{
	constructor(options)
	{
		super({
			status: options.syncInfo.status || false,
			connected: options.syncInfo.connected || false,
			gridTitle: Loc.getMessage('CALENDAR_TITLE_OFFICE365'),
			gridColor: '#000',
			gridIcon: '/bitrix/images/calendar/sync/google.svg',
			type: 'office365',
			interfaceClassName: '',
			viewClassification: 'web',
			templateClass: 'BX.Calendar.Sync.Interface.GoogleTemplate',
			mainPanel: true,
			pendingStatus: true
		});
		this.connectionName = 'Office365';
		this.id = options.syncInfo.id;
		this.setConnections();
	}
}