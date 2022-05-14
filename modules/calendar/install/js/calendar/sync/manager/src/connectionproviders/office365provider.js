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
			gridIcon: '',
			type: 'office365',
			interfaceClassName: '',
			viewClassification: 'web',
			templateClass: 'BX.Calendar.Sync.Interface.Office365template',
			mainPanel: true,
			pendingStatus: true
		});
		this.connectionName = 'Office365';
		this.syncLink = options.syncLink || '';
		this.id = options.syncInfo.id;
		this.setConnections();
	}

	getSyncLink()
	{
		return this.syncLink;
	}
}