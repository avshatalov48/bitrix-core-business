// @flow
'use strict';

import { Loc, Event, Runtime } from 'main.core';
import { InterfaceTemplate } from "./interfacetemplate";
import { MessageBox } from 'ui.dialogs.messagebox';
import { Util } from 'calendar.util';
import GoogleSyncWizard from "../syncwizard/googlesyncwizard"

export default class GoogleTemplate extends InterfaceTemplate
{
	HANDLE_CONNECTION_DELAY = 500;

	constructor(provider, connection = null)
	{
		super({
			title: Loc.getMessage("CALENDAR_TITLE_GOOGLE"),
			helpDeskCode: '6030429',
			titleInfoHeader: Loc.getMessage('CAL_CONNECT_GOOGLE_CALENDAR'),
			descriptionInfoHeader: Loc.getMessage('CAL_GOOGLE_CONNECT_DESCRIPTION'),
			titleActiveHeader: Loc.getMessage('CAL_GOOGLE_CALENDAR_IS_CONNECT'),
			descriptionActiveHeader: Loc.getMessage('CAL_GOOGLE_SELECTED_DESCRIPTION'),
			sliderIconClass: 'calendar-sync-slider-header-icon-google',
			iconPath: '/bitrix/images/calendar/sync/google.svg',
			iconLogoClass: '--google',
			color: '#387ced',
			provider: provider,
			connection: connection,
			popupWithUpdateButton: true,
		});

		this.sectionStatusObject = {};
		this.sectionList = [];

		this.handleSuccessConnectionDebounce = Runtime.debounce(this.handleSuccessConnection, this.HANDLE_CONNECTION_DELAY, this);
	}

	createConnection()
	{
		BX.ajax.runAction('calendar.api.calendarajax.analytical', {
			analyticsLabel: {
				calendarAction: 'createConnection',
				click_to_connection_button: 'Y',
				connection_type: 'google',
			}
		});

		BX.util.popup(this.provider.getSyncLink(), 500, 600);

		Event.bind(window, 'hashchange', this.handleSuccessConnectionDebounce);
		Event.bind(window, 'message', this.handleSuccessConnectionDebounce);
	}

	handleSuccessConnection(event)
	{
		if (
			(window.location.hash === '#googleAuthSuccess')
			|| (event.data.title === 'googleAuthSuccess')
		)
		{
			Util.removeHash();
			this.provider.setWizardSyncMode(true);

			this.provider.saveConnection();
			this.openSyncWizard();
			this.provider.setStatus(this.provider.STATUS_SYNCHRONIZING);
			this.provider.getInterfaceUnit().refreshButton();
		}
	}

	getSectionsForGoogle()
	{
		return new Promise((resolve) => {
			BX.ajax.runAction('calendar.api.syncajax.getAllSectionsForGoogle', {
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
			);
		})
	}

	onClickCheckSection(event)
	{
		this.sectionStatusObject[event.target.value] = event.target.checked;
		this.runUpdateInfo();
		this.showUpdateSectionListNotification();
	}

	showAlertPopup()
	{
		const messageBox = new MessageBox({
			className: this.id,
			message: Loc.getMessage('GOOGLE_IS_NOT_CALDAV_SETTINGS_WARNING_MESSAGE'),
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

	handleConnectButton()
	{
		if (this.provider.hasSetSyncGoogleSettings())
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
		if (!this.wizard)
		{
			this.wizard = new GoogleSyncWizard();
			this.wizard.openSlider();
			this.provider.setActiveWizard(this.wizard);
		}
	}

	sendRequestRemoveConnection(id)
	{
		this.deactivateConnection(id);
	}
}
