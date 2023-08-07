import {ConnectionProvider} from "./connectionprovider";
import { Event, Loc } from 'main.core';

export class GoogleProvider extends ConnectionProvider
{
	constructor(options)
	{
		super({
			id: options.syncInfo.id || null,
			status: options.syncInfo.status || false,
			connected: options.syncInfo.connected || false,
			userName: options.syncInfo.userName || '',
			gridTitle: Loc.getMessage('CALENDAR_TITLE_GOOGLE'),
			gridColor: '#387ced',
			gridIcon: '/bitrix/images/calendar/sync/google.svg',
			type: 'google',
			interfaceClassName: '',
			viewClassification: 'web',
			templateClass: 'BX.Calendar.Sync.Interface.GoogleTemplate',
			mainPanel: options.mainPanel,
		});
		this.connectionName = Loc.getMessage('CALENDAR_TITLE_GOOGLE');
		this.isSetSyncGoogleSettings = options.isSetSyncGoogleSettings;
		this.syncLink = options.syncLink;
		this.isGoogleApplicationRefused = options.isGoogleApplicationRefused;

		this.setSyncDate(options.syncInfo.syncOffset);
		this.setSections(options.sections);
		this.setConnections();
	}

	getSyncLink()
	{
		return this.syncLink;
	}

	hasSetSyncGoogleSettings()
	{
		return this.isSetSyncGoogleSettings;
	}

	saveConnection()
	{
		BX.ajax.runAction('calendar.api.syncajax.createGoogleConnection', {
			data: {}
		}).then(
			response => {
				if (response?.data?.status === this.ERROR_CODE)
				{
					if (this.isGoogleApplicationRefused)
					{
						this.setStatus(this.STATUS_REFUSED);
					}
					else
					{
						this.setStatus(this.STATUS_FAILED);
					}
					this.setWizardState(
						{
							status: this.ERROR_CODE,
							vendorName: this.type,
							accountName: response?.data?.googleApiStatus?.googleCalendarPrimaryId
						}
					);
				}
				else
				{
					this.setWizardState(
						{
							stage: 'connection_created',
							vendorName: this.type,
							accountName: response?.data?.googleApiStatus?.googleCalendarPrimaryId
						}
					);
				}

				this.emit(
					'onSyncInfoUpdated',
					new Event.BaseEvent({
					data: {
						syncInfo: response.data.syncInfo
					}
				}));
			},
			response => {
				if (this.isGoogleApplicationRefused)
				{
					this.setStatus(this.STATUS_REFUSED);
				}
				else
				{
					this.setStatus(this.STATUS_FAILED);
				}
				this.setWizardState(
					{
						status: this.ERROR_CODE,
						vendorName: this.type
					}
				);
			}
		);
	}
}