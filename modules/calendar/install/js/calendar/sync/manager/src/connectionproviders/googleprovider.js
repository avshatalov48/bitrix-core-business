import {ConnectionProvider} from "./connectionprovider";
import {Loc} from "main.core";

export class GoogleProvider extends ConnectionProvider
{
	constructor(options)
	{
		super({
			status: options.syncInfo.status || false,
			connected: options.syncInfo.connected || false,
			gridTitle: Loc.getMessage('CALENDAR_TITLE_GOOGLE'),
			gridColor: '#387ced',
			gridIcon: '/bitrix/images/calendar/sync/google.svg',
			type: 'google',
			interfaceClassName: '',
			viewClassification: 'web',
			templateClass: 'BX.Calendar.Sync.Interface.GoogleTemplate',
		});
		this.syncTimestamp = options.syncInfo.syncTimestamp;
		this.connectionName = options.syncInfo.userName
			? options.syncInfo.userName
			: Loc.getMessage('CALENDAR_TITLE_GOOGLE')
		;
		this.id = options.syncInfo.id;

		this.isSetSyncCaldavSettings = options.isSetSyncCaldavSettings;

		this.syncLink = options.syncLink;
		this.sections = options.sections;

		this.setConnections();
	}

	getSyncLink()
	{
		return this.syncLink;
	}

	hasSetSyncCaldavSettings()
	{
		return this.isSetSyncCaldavSettings;
	}
}