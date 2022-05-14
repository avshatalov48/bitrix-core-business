import {ConnectionProvider} from "./connectionprovider";
import {Loc} from "main.core";

export class ICloudProvider extends ConnectionProvider
{
	constructor(options)
	{
		super({
			status: options.syncInfo.status || false,
			connected: options.syncInfo.connected || false,
			gridTitle: Loc.getMessage('CALENDAR_TITLE_ICLOUD'),
			gridColor: '#000',
			gridIcon: '',
			type: 'icloud',
			interfaceClassName: '',
			viewClassification: 'web',
			templateClass: 'BX.Calendar.Sync.Interface.IcloudTemplate',
			mainPanel: true,
			pendingStatus: true
		});
		this.connectionName = 'icloud';
		this.id = options.syncInfo.id;
		this.setConnections();
	}
}