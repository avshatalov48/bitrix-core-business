import {ConnectionProvider} from "./connectionprovider";
import {Loc} from "main.core";

export class Office365Provider extends ConnectionProvider
{
	constructor(options)
	{
		super({
			id: options.syncInfo.id || null,
			status: options.syncInfo.status || false,
			connected: options.syncInfo.connected || false,
			userName: options.syncInfo.userName || options.syncInfo.connectionName || '',
			gridTitle: Loc.getMessage('CALENDAR_TITLE_OFFICE365'),
			gridColor: '#fc1d1d',
			gridIcon: '/bitrix/images/calendar/sync/office365.svg',
			type: 'office365',
			interfaceClassName: '',
			viewClassification: 'web',
			templateClass: 'BX.Calendar.Sync.Interface.Office365template',
			mainPanel: true,
		});
		this.connectionName = Loc.getMessage('CALENDAR_TITLE_OFFICE365');
		this.syncLink = options.syncLink || '';
		this.isSetSyncOffice365Settings = options.isSetSyncOffice365Settings;

		this.setSyncDate(options.syncInfo.syncOffset);
		this.setSections(options.sections);
		this.setConnections();
	}

	getSyncLink()
	{
		return this.syncLink;
	}

	hasSetSyncOffice365Settings()
	{
		return this.isSetSyncOffice365Settings;
	}

	removeConnection(id)
	{
		BX.ajax.runAction('calendar.api.syncajax.deactivateConnection', {
			data: {
				connectionId: id
			}
		}).then(() => {
			BX.reload();
		});
	}
}
