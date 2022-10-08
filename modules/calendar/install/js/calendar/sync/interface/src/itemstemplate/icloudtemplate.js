// @flow
'use strict';

import { Loc } from 'main.core';
import { Util } from 'calendar.util';
import { InterfaceTemplate } from './interfacetemplate';
import IcloudAuthDialog from '../controls/icloudauthdialog';
import IcloudSyncWizard from '../syncwizard/icloudsyncwizard';
import { EventEmitter } from 'main.core.events';
import WarnSyncIcloudDialog from '../controls/warnsynciclouddialog';

export default class IcloudTemplate extends InterfaceTemplate
{
	constructor(provider, connection = null)
	{
		super({
			title: Loc.getMessage("CALENDAR_TITLE_ICLOUD"),
			helpDeskCode: '6030429',
			titleInfoHeader: Loc.getMessage('CAL_CONNECT_ICLOUD_CALENDAR'),
			descriptionInfoHeader: Loc.getMessage('CAL_ICLOUD_CONNECT_DESCRIPTION'),
			titleActiveHeader: Loc.getMessage('CAL_CALENDAR_IS_CONNECT'),
			descriptionActiveHeader: Loc.getMessage('CAL_ICLOUD_SELECTED_DESCRIPTION'),
			sliderIconClass: 'calendar-sync-slider-header-icon-icloud',
			iconPath: '/bitrix/images/calendar/sync/icloud.svg',
			iconLogoClass: '--icloud',
			color: '#95a0af',
			provider: provider,
			connection: connection,
			popupWithUpdateButton: true,
		});

		this.sectionStatusObject = {};
		this.sectionList = [];
	}

	createConnection(data)
	{
		BX.ajax.runAction('calendar.api.syncajax.createIcloudConnection', {
			data: {
				appleId: data.appleId,
				appPassword: data.appPassword,
			},
		}).then(
			(response) => {
				const result = response.data;
				
				if (result.status === 'success' && result.connectionId)
				{
					this.openSyncWizard(data.appleId);
					this.syncCalendarsWithIcloud(result.connectionId);
				}
			},
			(response) => {
				const result = response.data;
				if (result.status === 'incorrect_app_pass')
				{
					BX.ajax.runAction('calendar.api.calendarajax.analytical', {
						analyticsLabel: {
							calendarAction: 'createConnection',
							wrong_app_pass: 'Y',
							connection_type: 'icloud'
						}
					});
					
				}
				this.authDialog.showErrorAuthorizationAlert();
			}
		);
	}

	syncCalendarsWithIcloud(connectionId)
	{
		this.authDialog.close();
		
		return new Promise((resolve) => {
			BX.ajax.runAction('calendar.api.syncajax.syncIcloudConnection', {
				data: {
					connectionId: connectionId,
				}
			}).then(
				(response) => {
					this.provider.setStatus(this.provider.STATUS_SUCCESS);
					if (connectionId)
					{
						this.provider.getConnection().setId(connectionId);
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
				});
		})
	}

	getSectionsForIcloud()
	{
		return new Promise((resolve) => {
			BX.ajax.runAction('calendar.api.syncajax.getAllSectionsForIcloud', {
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

	onClickCheckSection(event)
	{
		this.sectionStatusObject[event.target.value] = event.target.checked;
		this.runUpdateInfo();
		this.showUpdateSectionListNotification();
	}

	handleConnectButton()
	{
		BX.ajax.runAction('calendar.api.calendarajax.analytical', {
			analyticsLabel: {
				calendarAction: 'createConnection',
				click_to_connection_button: 'Y',
				connection_type: 'icloud',
			}
		});
		
		this.initPopup();
		if (Util.isIphoneConnected() || Util.isMacConnected())
		{
			this.alertSyncPopup.show();
		}
		else
		{
			this.authDialog.show();
		}
	}
	
	initPopup()
	{
		if (!this.authDialog)
		{
			this.authDialog = new IcloudAuthDialog();
			
			EventEmitter.unsubscribeAll('BX.Calendar.Sync.Icloud:onSubmit');
			EventEmitter.subscribe('BX.Calendar.Sync.Icloud:onSubmit', (e) => {
				this.createConnection(e.data);
			})
		}
		
		if (!this.alertSyncPopup)
		{
			this.alertSyncPopup = new WarnSyncIcloudDialog({
				authDialog: this.authDialog
			})
		}
	}

	openSyncWizard(appleId)
	{
		this.provider.setWizardSyncMode(true);
		this.wizard = new IcloudSyncWizard();
		this.wizard.openSlider();
		this.provider.setActiveWizard(this.wizard);

		EventEmitter.subscribeOnce('BX.Calendar.Sync.Interface.SyncStageUnit:onRenderDone', () => {
			this.wizard.updateState({
				stage: 'connection_created',
				vendorName: 'icloud',
				accountName: appleId,
			})
		})
	}
	
	sendRequestRemoveConnection(id)
	{
		this.deactivateConnection(id);
	}
}
