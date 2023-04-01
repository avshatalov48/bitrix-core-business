// @flow
'use strict';

import { Loc, Event } from "main.core";
import {InterfaceTemplate} from "./interfacetemplate";
import Office365SyncWizard from '../syncwizard/office365syncwizard';
import { Util } from 'calendar.util';
import { MessageBox } from 'ui.dialogs.messagebox';

export default class Office365template extends InterfaceTemplate
{
	constructor(provider, connection = null)
	{
		super({
			title: Loc.getMessage("CALENDAR_TITLE_OFFICE365"),
			helpDeskCode: '6030429',
			titleInfoHeader: Loc.getMessage('CAL_CONNECT_OFFICE365_CALENDAR'),
			descriptionInfoHeader: Loc.getMessage('CAL_OFFICE365_CONNECT_DESCRIPTION'),
			titleActiveHeader: Loc.getMessage('CAL_OFFICE365_CALENDAR_IS_CONNECT'),
			descriptionActiveHeader: Loc.getMessage('CAL_OFFICE365_SELECTED_DESCRIPTION'),
			sliderIconClass: 'calendar-sync-slider-header-icon-office365',
			iconPath: '/bitrix/images/calendar/sync/office365.svg',
			iconLogoClass: '--office365',
			color: '#fc1d1d',
			provider: provider,
			connection: connection,
			popupWithUpdateButton: true,
		});

		this.sectionStatusObject = {};
		this.sectionList = [];
	}

	createConnection()
	{
		BX.ajax.runAction('calendar.api.calendarajax.analytical', {
			analyticsLabel: {
				calendarAction: 'createConnection',
				click_to_connection_button: 'Y',
				connection_type: 'office365',
			}
		});

		BX.util.popup(this.provider.getSyncLink(), 500, 600);

		Event.bind(window, 'hashchange', (event) => {
			if (window.location.hash === '#office365AuthSuccess')
			{
				Util.removeHash();
				this.provider.setWizardSyncMode(true);
				this.saveConnection();
				this.openSyncWizard();
				this.provider.setStatus(this.provider.STATUS_SYNCHRONIZING);
				this.provider.getInterfaceUnit().refreshButton();
			}
		});
	}

	saveConnection()
	{
		return new Promise((resolve) => {
			BX.ajax.runAction('calendar.api.syncajax.createOffice365Connection')
				.then(
					(response) => {
						if (response?.data?.status === this.provider.ERROR_CODE)
						{
							this.provider.setStatus(this.provider.STATUS_FAILED);
							this.provider.setWizardState(
								{
									status: this.provider.ERROR_CODE,
									vendorName: this.provider.type,
								}
							);
						}
						else if (response?.data?.connectionId)
						{
							this.provider.setStatus(this.provider.STATUS_SUCCESS);
							this.provider.getConnection().setId(response.data.connectionId);
							this.provider.getConnection().setStatus(true);
							this.provider.getConnection().setConnected(true);
							this.provider.getConnection().setSyncDate(new Date());
						}
						resolve(response.data);
					},
					(response) => {
						this.provider.setStatus(this.provider.STATUS_FAILED);
						this.provider.setWizardState(
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

	onClickCheckSection(event)
	{
		this.sectionStatusObject[event.target.value] = event.target.checked;
		this.runUpdateInfo();
		this.showUpdateSectionListNotification();
	}

	handleConnectButton()
	{
		if (this.provider.hasSetSyncOffice365Settings())
		{
			this.createConnection();
		}
		else
		{
			this.showAlertPopup();
		}
	}

	openSyncWizard()
	{
		this.wizard = new Office365SyncWizard();
		this.wizard.openSlider();
		this.provider.setActiveWizard(this.wizard);
	}

	getSectionsForOffice365()
	{
		return new Promise((resolve) => {
			BX.ajax.runAction('calendar.api.syncajax.getAllSectionsForOffice365', {
				data: {
					connectionId: this.connection.addParams.id
				}
			})
				.then(
					(response) => {
						this.sectionList = response.data;
						resolve(response.data);
					},
					(response) => {
						resolve(response.errors);
					}
				)
		})
	}

	sendRequestRemoveConnection(id)
	{
		this.deactivateConnection(id);
	}

	showAlertPopup()
	{
		const messageBox = new MessageBox({
			className: this.id,
			message: Loc.getMessage('OFFICE365_IS_NOT_CALDAV_SETTINGS_WARNING_MESSAGE'),
			width: 500,
			offsetLeft: 60,
			offsetTop: 5,
			padding: 7,
			onOk: () => {
				messageBox.close();
			},
			okCaption: 'OK',
			buttons: BX.UI.Dialogs.MessageBoxButtons.OK,
			popupOptions: {
				zIndexAbsolute: 4020,
				autoHide: true,
				animation: 'fading-slide',
			},
		});
		messageBox.show();
	}
}
