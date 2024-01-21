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

	saveConnection()
	{
		return new Promise((resolve) => {
			BX.ajax.runAction('calendar.api.syncajax.createOffice365Connection')
				.then(
					(response) => {
						if (response?.data?.status === this.provider.ERROR_CODE)
						{
							this.setStatus(this.provider.STATUS_FAILED);
							this.setWizardState(
								{
									status: this.provider.ERROR_CODE,
									vendorName: this.provider.type,
								}
							);
						}
						else if (response?.data?.connectionId)
						{
							this.setStatus(this.provider.STATUS_SUCCESS);
							this.getConnection().setId(response.data.connectionId);
							this.getConnection().setStatus(true);
							this.getConnection().setConnected(true);
							this.getConnection().setSyncDate(new Date());
						}
						resolve(response.data);
					},
					(response) => {
						this.setStatus(this.provider.STATUS_FAILED);
						this.setWizardState(
							{
								status: this.provider.ERROR_CODE,
								vendorName: this.provider.type,
							}
						);
						resolve(response.errors);
					}
				);
		})
	}
}
