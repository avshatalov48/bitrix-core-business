// @flow

import SyncButton from './controls/syncbutton';
import {EventEmitter} from "main.core.events";
import {GoogleProvider} from "./connectionproviders/googleprovider";
import {Office365Provider} from "./connectionproviders/office365provider";
import {ICloudProvider} from "./connectionproviders/icloudprovider";
import {AndroidProvider} from "./connectionproviders/androidprovider";
import {CaldavConnection} from "./connectionproviders/caldavconnection";
import {CaldavProvider} from "./connectionproviders/caldavprovider";
import {ExchangeProvider} from "./connectionproviders/exchangeprovider";
import {IphoneProvider} from "./connectionproviders/iphoneprovider";
import {MacProvider} from "./connectionproviders/macprovider";
import {OutlookProvider} from "./connectionproviders/outlookprovider";
import {YandexProvider} from "./connectionproviders/yandexprovider";
import SyncStatusPopup from "./controls/syncstatuspopup";
import {Util} from "calendar.util";
import { Runtime } from 'main.core';

type ManagerOptions = {
	calendar: any,
	wrapper: string,
	syncInfo: any,
	userId: number,
	syncLinks: any,
	sections: any,
	portalAddress: string,
	isRuZone: boolean,
	calendarInstance: window.BXEventCalendar.Core,
	isSetSyncGoogleSettings: boolean,
	isSetSyncOffice365Settings: boolean
};

export default class Manager extends EventEmitter
{
	status = 'not_connected';
	STATUS_SUCCESS = 'success';
	STATUS_FAILED = 'failed';
	STATUS_REFUSED = 'refused';
	STATUS_NOT_CONNECTED = 'not_connected';
	WIZARD_SYNC_MODE = 'wizard_sync_mode';
	STATUS_SYNCHRONIZING = 'synchronizing';
	WAITING_MODE_PERIODIC_TIMEOUT = 5000;
	REFRESH_DELAY = 300;
	REFRESH_CONTENT_DELAY = 300;
	WIZARD_SLIDER_PREFIX = 'calendar:sync-wizard';

	constructor(options: ManagerOptions)
	{
		super();
		this.setEventNamespace('BX.Calendar.Sync.Manager.Manager');

		this.isGoogleApplicationRefused = options.calendar.util.config.isGoogleApplicationRefused === 'Y';
		this.showGoogleApplicationRefused = options.calendar.util.config.showGoogleApplicationRefused === 'Y';
		this.wrapper = options.wrapper;
		this.setSyncInfo(options.syncInfo);
		this.userId = options.userId;
		this.syncLinks = options.syncLinks;
		this.sections = options.sections;
		this.portalAddress = options.portalAddress;
		this.isRuZone = options.isRuZone;
		this.calendarInstance = options.calendar;
		this.isSetSyncGoogleSettings = options.isSetSyncGoogleSettings;
		this.isSetSyncOffice365Settings = options.isSetSyncOffice365Settings;
		this.refreshDebounce = Runtime.debounce(this.refresh, this.REFRESH_DELAY, this);
		this.refreshContentDebounce = Runtime.debounce(this.refreshContent, this.REFRESH_CONTENT_DELAY, this);

		this.init();
		this.subscribeOnEvent();
	}

	subscribeOnEvent()
	{
		EventEmitter.subscribe('BX.Calendar.Sync.Interface.SyncStatusPopup:onRefresh', event => {
			this.refreshDebounce(event);
		});

		EventEmitter.subscribe('BX.Calendar.Sync.Interface.InterfaceTemplate:reDrawCalendarGrid', event => {
			this.reDrawCalendarGrid();
		});

		window.addEventListener('message', (event) => {
			if (event.data.title === 'googleOAuthSuccess')
			{
				window.location.reload()
			}
		});
	}

	showSyncButton()
	{
		this.syncButton = new SyncButton({
			status: this.status,
			wrapper: this.wrapper,
			connectionsProviders: this.connectionsProviders,
			userId: this.userId,
			isGoogleApplicationRefused: this.isGoogleApplicationRefused,
		});
		this.syncButton.show();

		if (this.needToShowGoogleRefusedPopup())
		{
			this.syncButton.showGoogleApplicationRefusedPopup();
			this.showGoogleApplicationRefused = false;
		}
	}

	init()
	{
		this.connectionsProviders = {};
		const yandexConnections = [];
		const caldavConnections = [];

		const syncInfo = this.syncInfo;
		this.sectionsByType = this.sortSections();

		for (let key in syncInfo)
		{
			if (syncInfo.hasOwnProperty(key))
			{
				switch (syncInfo[key].type)
				{
					case 'yandex':
						yandexConnections.push({
							syncInfo: syncInfo[key],
							sections: this.sectionsByType.caldav['caldav' + syncInfo[key].id],
							isRuZone: this.isRuZone,
						});
						break;
					case 'caldav':
						caldavConnections.push({
							syncInfo: syncInfo[key],
							sections: this.sectionsByType.caldav['caldav' + syncInfo[key].id],
						});
						break;
				}
			}
		}

		this.connectionsProviders = {
			google: this.getGoogleProvider(),
			icloud: this.getIcloudProvider(),
			office365: this.getOffice365Provider(),
			caldav: this.getCaldavProvider(caldavConnections),
			iphone: this.getIphoneProvider(),
			android: this.getAndroidProvider(),
			mac: this.getMacProvider(),
		};

		if (this.isRuZone)
		{
			this.connectionsProviders.yandex = this.getYandexProvider(yandexConnections);
		}

		if (!BX.browser.IsMac() && syncInfo.hasOwnProperty('outlook'))
		{
			this.connectionsProviders.outlook = this.getOutlookProvider();
		}

		if (syncInfo.hasOwnProperty('exchange'))
		{
			this.connectionsProviders.exchange = this.getExchangeProvider();
		}

		this.status = this.getSummarySyncStatus();
		this.subscribeEventHandlers();
	}

	setSyncMode(value)
	{
		this.syncMode = value;
	}

	getSyncMode()
	{
		return this.syncMode;
	}

	isWizardSyncMode()
	{
		for (let providerName in this.connectionsProviders)
		{
			if (this.connectionsProviders.hasOwnProperty(providerName)
				&& this.connectionsProviders[providerName].getWizardSyncMode())
			{
				return true;
			}
		}
		return false;
	}

	isSyncInProcess()
	{
		for (let providerName in this.connectionsProviders)
		{
			if (
				this.connectionsProviders.hasOwnProperty(providerName)
				&& this.connectionsProviders[providerName].getSyncStatus() === this.STATUS_SYNCHRONIZING
			)
			{
				return true;
			}
		}
		return false;
	}

	sortSections()
	{
		const sections = this.sections;
		const exchangeSections = [];
		const googleSections = [];
		const icloudSections = [];
		const sectionsByType = {};
		const outlookSections = [];
		const office365Sections = [];
		sectionsByType.caldav = {};

		sections.forEach(section => {
			if (
				section.belongsToView()
				&& section.data.OUTLOOK_JS
				&& section.data['EXTERNAL_TYPE'] === 'local'
			)
			{
				outlookSections.push({
					id: section.id,
					connectURL: section.data.OUTLOOK_JS,
					text: section.name,
				});
			}

			if (section.data['IS_EXCHANGE'] === true)
			{
				exchangeSections.push(section.data);
			}
			else if (
				section.data['GAPI_CALENDAR_ID']
				&& section.data['CAL_DAV_CON']
				&& section.data['EXTERNAL_TYPE'] !== 'local'
			)
			{
				googleSections.push(section.data);
			}
			else if (section.data['EXTERNAL_TYPE'] === 'icloud')
			{
				icloudSections.push(section.data);
			}
			else if (section.data['EXTERNAL_TYPE'] === 'office365')
			{
				office365Sections.push(section.data);
			}
			else if (section.data['CAL_DAV_CON'] && section.data['CAL_DAV_CAL'])
			{
				sectionsByType.caldav['caldav' + section.data['CAL_DAV_CON']] = section.data;
			}
		});

		sectionsByType.google = googleSections;
		sectionsByType.icloud = icloudSections;
		sectionsByType.office365 = office365Sections;
		sectionsByType.exchange = exchangeSections;
		sectionsByType.outlook = outlookSections;

		return sectionsByType;
	}

	refresh(event)
	{
		return new Promise((resolve) => {
			BX.ajax.runAction('calendar.api.syncajax.updateConnection', {
				data: {
					type: 'user',
					requestUid: Util.registerRequestId(),
				}
			}).then((response) => {
				this.setSyncInfo(response.data);
				this.status = this.getSummarySyncStatus();

				if (this.needToShowGoogleRefusedPopup())
				{
					this.syncButton.showGoogleApplicationRefusedPopup();
					this.showGoogleApplicationRefused = false;
				}

				const activePopup = (event && event.getTarget) ? event.getTarget() : null;
				this.refreshContent(activePopup);
				resolve();
			});
		});
	}

	refreshContent(activePopup = {})
	{
		this.init();

		this.refreshCalendarGrid();

		if (this.syncButton)
		{
			this.syncButton.refresh(this.status);
			this.syncButton.setConnectionProviders(this.connectionsProviders);
		}

		if (activePopup)
		{
			this.refreshActivePopup(activePopup);
			this.refreshOpenSliders(activePopup);
		}
	}

	refreshCalendarGrid()
	{
		this.calendarInstance.reload();
	}

	refreshActivePopup(activePopup)
	{
		if (activePopup instanceof SyncStatusPopup && activePopup.getId() === 'calendar-syncPanel-status')
		{
			activePopup.refresh(this.getConnections());
		}
		else if (this.syncButton.popup instanceof SyncStatusPopup && this.syncButton.popup.getId() === 'calendar-sync-button-status')
		{
			this.syncButton.popup.refresh(this.getConnections());
		}
	}

	refreshOpenSliders(activePopup = {})
	{
		const openSliders = BX.SidePanel.Instance.getOpenSliders();
		if (openSliders.length > 0)
		{
			openSliders.forEach(slider => {
				if (slider.getUrl() === 'calendar:auxiliary-sync-slider')
				{
					this.refreshMainSlider(this.syncButton.getSyncPanel());
				}
				else if (slider.getUrl().indexOf('calendar:item-sync-') !== -1)
				{
					this.refreshConnectionSlider(slider, activePopup);
				}
			});
		}
	}

	refreshConnectionSlider(slider, activePopup)
	{
		let updatedConnection = undefined;
		const itemInterface = slider.getData().get('itemInterface');
		const connection = slider.getData().get('connection');
		if (connection)
		{
			updatedConnection = this.connectionsProviders[connection.getType()].getConnectionById(connection.getId());
		}

		if (activePopup instanceof SyncStatusPopup && updatedConnection)
		{
			activePopup.refresh([updatedConnection]);
		}

		if (itemInterface && updatedConnection)
		{
			itemInterface.refresh(updatedConnection);
		}

		slider.reload();
	}

	refreshMainSlider(syncPanel)
	{
		syncPanel.refresh(this.status, this.connectionsProviders);
	}

	getConnections()
	{
		const connections = [];
		const items = Object.values(this.connectionsProviders);

		items.forEach(item => {
			const itemConnections = item.getConnections();
			if (itemConnections.length > 0)
			{
				itemConnections.forEach(connection => {
						if (connection.getConnectStatus() === true)
						{
							connections.push(connection);
						}
					}
				)
			}
		});

		return connections;
	}

	reDrawCalendarGrid()
	{
		this.calendarInstance.reloadDebounce();
	}

	updateSyncStatus(params)
	{
		for (let connectionName in params.syncInfo)
		{
			if (
				params.syncInfo.hasOwnProperty(connectionName)
				&& this.syncInfo[connectionName]
			)
			{
				this.syncInfo[connectionName] = {
					...this.syncInfo[connectionName],
					...params.syncInfo[connectionName]
				};
			}
		}

		this.status = this.STATUS_SUCCESS;
		this.refreshContentDebounce();
	}

	addSyncConnection(params)
	{
		for (const connectionName in params.syncInfo)
		{
			if (['yandex', 'caldav'].includes(params.syncInfo[connectionName].type))
			{
				BX.reload();
			}

			if (BX.Calendar.Util.checkRequestId(params.requestUid))
			{
				if (this.syncInfo[connectionName])
				{
					this.syncInfo[connectionName] = {
						...this.syncInfo[connectionName],
						...params.syncInfo[connectionName]
					};
				}
			}
		}

		this.status = this.STATUS_SUCCESS;
		this.refreshContentDebounce();
	}

	deleteSyncConnection(params)
	{
		if (!BX.Calendar.Util.checkRequestId(params.requestUid))
		{
			return;
		}

		if (params.connectionId)
		{
			for (const connectionName in this.syncInfo)
			{
				if (this.syncInfo.hasOwnProperty(connectionName)
					&& this.syncInfo[connectionName]
					&& parseInt(this.syncInfo[connectionName].id) === parseInt(params.connectionId))
				{
					delete this.syncInfo[connectionName];
				}
			}
		}

		if (params.syncInfo)
		{
			for (const connectionName in params.syncInfo)
			{
				if (this.syncInfo[connectionName])
				{
					delete this.syncInfo[connectionName];
				}
			}
		}

		if (this.status !== this.STATUS_NOT_CONNECTED)
		{
			this.status = this.STATUS_SUCCESS;
		}
		this.refreshDebounce();
	}

	getProviderById(id): Array
	{
		let connection;
		for (let providerName in this.connectionsProviders)
		{
			if (
				this.connectionsProviders.hasOwnProperty(providerName)
				&& this.connectionsProviders[providerName].connected
				&& ['google', 'caldav', 'yandex', 'icloud', 'office365'].includes(providerName)
			)
			{
				connection = this.connectionsProviders[providerName].getConnectionById(id);
				if (connection)
				{
					return [this.connectionsProviders[providerName], connection];
				}
			}
		}

		return [undefined, undefined];
	}

	processSyncConnection(params)
	{
		for (let providerName in this.connectionsProviders)
		{
			if (
				this.connectionsProviders.hasOwnProperty(providerName)
				&& this.connectionsProviders[providerName].getWizardSyncMode()
				&& providerName === params?.vendorName
			)
			{
				if (params.accountName)
				{
					this.connectionsProviders[providerName].setUserName(params.accountName);
				}
				this.connectionsProviders[providerName].setWizardState(params);
				break;
			}
		}
	}

	handlePullEvent(params)
	{
		let wizardSyncMode = this.isWizardSyncMode();
		switch (params.command)
		{
			case 'refresh_sync_status':
				if (!wizardSyncMode)
				{
					this.updateSyncStatus(params);
				}
				break;
			case 'add_sync_connection':
				if (!wizardSyncMode)
				{
					this.addSyncConnection(params);
				}
				break;
			case 'delete_sync_connection':
				if (!wizardSyncMode)
				{
					this.deleteSyncConnection(params);
				}
				break;
			case 'process_sync_connection':
				if (wizardSyncMode)
				{
					this.processSyncConnection(params);
				}
				break;
		}
	}

	setSyncInfo(syncInfo)
	{
		this.syncInfo = syncInfo;
	}

	subscribeEventHandlers()
	{
		for (let providerName in this.connectionsProviders)
		{
			if (this.connectionsProviders.hasOwnProperty(providerName))
			{
				this.connectionsProviders[providerName].unsubscribeAll('onStartWaitingMode');
				this.connectionsProviders[providerName].unsubscribeAll('onEndWaitingMode');
				this.connectionsProviders[providerName].unsubscribeAll('onCloseSyncWizard');

				this.connectionsProviders[providerName].subscribe(
					'onStartWaitingMode',
					this.handleStartWaitingMode.bind(this)
				);
				this.connectionsProviders[providerName].subscribe(
					'onEndWaitingMode',
					this.handleEndWaitingMode.bind(this)
				);

				this.connectionsProviders[providerName].subscribe(
					'onCloseSyncWizard',
					this.handleCloseSyncWizard.bind(this)
				);
			}
		}
	}

	handleCloseSyncWizard()
	{
		if (this.isSyncInProcess())
		{
			if (this.syncButton)
			{
				this.syncButton.refresh(this.STATUS_SYNCHRONIZING);
			}
		}
		else
		{
			this.refreshContentDebounce();
		}
	}

	handleStartWaitingMode()
	{
		this.doPeriodicRefresh();
	}

	handleEndWaitingMode()
	{
		this.stopPeriodicRefresh();
	}

	doPeriodicRefresh()
	{
		if (!this.hasOpenedWizard())
		{
			return;
		}

		if (Util.documentIsDisplayingNow())
		{
			this.refresh()
				.then(() => {
					this.refreshTimeout = setTimeout(
						this.doPeriodicRefresh.bind(this),
						this.WAITING_MODE_PERIODIC_TIMEOUT
					);
				});
		}
		else
		{
			this.refreshTimeout = setTimeout(
				this.doPeriodicRefresh.bind(this),
				this.WAITING_MODE_PERIODIC_TIMEOUT
			);
		}
	}

	stopPeriodicRefresh()
	{
		if (this.refreshTimeout)
		{
			clearInterval(this.refreshTimeout);
			this.refreshTimeout = null;
		}
	}

	openSyncPanel()
	{
		this.syncButton.handleClick();
	}

	getSummarySyncStatus()
	{
		let status = this.STATUS_NOT_CONNECTED;
		for (let providerName in this.connectionsProviders)
		{
			if (this.connectionsProviders.hasOwnProperty(providerName))
			{
				if ([this.STATUS_SUCCESS, this.STATUS_FAILED]
					.includes(this.connectionsProviders[providerName].getStatus()))
				{
					status = this.connectionsProviders[providerName].getStatus();
					break;
				}
			}
		}

		if (status === this.STATUS_NOT_CONNECTED && this.hasRefusedStatus())
		{
			status = this.STATUS_REFUSED;
		}

		return status;
	}

	needToShowGoogleRefusedPopup()
	{
		return this.syncButton && this.isGoogleApplicationRefused && this.showGoogleApplicationRefused && this.hasRefusedStatus();
	}

	hasRefusedStatus()
	{
		for (const providerName in this.connectionsProviders)
		{
			if (this.connectionsProviders.hasOwnProperty(providerName))
			{
				if (this.connectionsProviders[providerName].getStatus() === this.STATUS_REFUSED)
				{
					return true;
				}
			}
		}

		return false;
	}

	getGoogleProvider()
	{
		if (!this.googleProvider)
		{
			this.googleProvider = GoogleProvider.createInstance({
				syncInfo: this.syncInfo.google || {},
				sections: this.sectionsByType.google || {},
				syncLink: this.syncLinks.google || null,
				isSetSyncGoogleSettings: this.isSetSyncGoogleSettings,
				mainPanel: true,
				isGoogleApplicationRefused: this.isGoogleApplicationRefused,
			});
		}
		else
		{
			this.googleProvider.refresh({
				syncInfo: this.syncInfo.google || {},
				sections: this.sectionsByType.google || {},
				syncLink: this.syncLinks.google || null,
			});
		}

		return this.googleProvider;
	}

	getOffice365Provider()
	{
		if (!this.office365Provider)
		{
			this.office365Provider = Office365Provider.createInstance({
				syncInfo: this.syncInfo.office365 || {},
				sections: this.sectionsByType.office365 || {},
				syncLink: this.syncLinks.office365 || null,
				isSetSyncOffice365Settings: this.isSetSyncOffice365Settings,
				mainPanel: true,
			});
		}
		else
		{
			this.office365Provider.refresh({
				syncInfo: this.syncInfo.office365 || {},
				sections: this.sectionsByType.office365 || {},
				syncLink: this.syncLinks.office365 || null,
			});
		}

		return this.office365Provider;
	}

	getIcloudProvider()
	{
		if (!this.icloudProvider)
		{
			this.icloudProvider = ICloudProvider.createInstance({
				syncInfo: this.syncInfo.icloud || {},
				sections: this.sectionsByType.icloud || {},
				mainPanel: true,
			});
		}
		else
		{
			this.icloudProvider.refresh({
				syncInfo: this.syncInfo.icloud || {},
				sections: this.sectionsByType.icloud || {},
			})
		}

		return this.icloudProvider;
	}

	getCaldavProvider(caldavConnections)
	{
		return CaldavProvider.createInstance({
			status: CaldavConnection.calculateStatus(caldavConnections),
			connected: (caldavConnections.length > 0),
			connections: caldavConnections,
		});
	}

	getIphoneProvider()
	{
		return IphoneProvider.createInstance({
			syncInfo: this.syncInfo.iphone,
		});
	}

	getAndroidProvider()
	{
		return AndroidProvider.createInstance({
			syncInfo: this.syncInfo.android,
		});
	}

	getMacProvider()
	{
		return MacProvider.createInstance({
			syncInfo: this.syncInfo.mac,
			portalAddress: this.portalAddress,
		});
	}

	getYandexProvider(yandexConnections)
	{
		return YandexProvider.createInstance({
			status: CaldavConnection.calculateStatus(yandexConnections),
			connected: (yandexConnections.length > 0),
			connections: yandexConnections,
		});
	}

	getOutlookProvider()
	{
		return OutlookProvider.createInstance({
			syncInfo: this.syncInfo.outlook,
			sections: this.sectionsByType.outlook,
			infoBySections: this.syncInfo.outlook.infoBySections || {},
		});
	}

	getExchangeProvider()
	{
		return ExchangeProvider.createInstance({
			syncInfo: this.syncInfo.exchange,
			sections: this.sectionsByType.exchange
		})
	}

	hasOpenedWizard()
	{
		const sliderList = BX.SidePanel.Instance.getOpenSliders();
		for (let i in sliderList)
		{
			if (
				sliderList.hasOwnProperty(i)
				&& sliderList[i].getUrl().indexOf(this.WIZARD_SLIDER_PREFIX) !== -1
			)
			{
				return true;
			}
		}
		return false;
	}
}