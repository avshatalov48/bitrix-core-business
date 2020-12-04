// @flow
'use strict';

import {Loc, Tag} from "main.core";
import SyncStatusPopup from "./controls/syncstatuspopup";
import {EventEmitter} from "main.core.events";

export default class SliderContent
{
	constructor(options)
	{
		// this.setEventNamespace('BX.Calendar.Sync.Interface.SliderContent');
		this.options = options;
		this.webItems = [];
		this.mobileItems = [];
	}

	getContent()
	{
		const mainHeader = Tag.render`
			<span class="calendar-sync-header-text">${Loc.getMessage('SYNC_CALENDAR_HEADER')}</span>
		`;

		this.blockStatus = this.getBlockStatus();

		const webHeader = Tag.render `
			<div class="calendar-sync-title">${Loc.getMessage('SYNC_WEB_HEADER')}</div>
		`;

		const mobileHeader = Tag.render `
			<div class="calendar-sync-title">${Loc.getMessage('SYNC_MOBILE_HEADER')}</div>
		`;

		this.webContentBlock = Tag.render `
			<div id="calendar-sync-web" class="calendar-sync-web"></div>
		`;

		this.mobileContentBlock = Tag.render `
			<div id="calendar-sync-mobile" class="calendar-sync-mobile"></div>
		`;

		return Tag.render`
			<div class="calendar-sync-wrap">
				<div class="calendar-sync-header">
					${mainHeader}
					${this.blockStatus}
				</div>
				${mobileHeader}
				${this.mobileContentBlock}
				${webHeader}
				${this.webContentBlock}
			</div>
		`;

	}

	getGridContent()
	{
		this.setConnections();
		this.getWebGridContent();
		this.getMobileGridContent();
	}

	getWebGridContent()
	{
		const params = {};
		const items = this.webItems;
		params.id = 'calendar_sync';
		params.items = items;
		params.container = document.getElementById('calendar-sync-web');
		params.sizeRatio = "55%";
		params.itemMinWidth = 180;
		params.tileMargin = 7;
		params.itemType = 'BX.Calendar.Sync.Interface.SyncPanelItem';
		params.userId = this.options.userId;
		const grid = new BX.TileGrid.Grid(params);

		grid.draw();
	}

	getMobileGridContent()
	{
		const params = {};
		const items = this.mobileItems;
		params.id = 'calendar_sync';
		params.items = items;
		params.container = document.getElementById('calendar-sync-mobile');
		params.sizeRatio = "55%";
		params.itemMinWidth = 180;
		params.tileMargin = 7;
		params.itemType = 'BX.Calendar.Sync.Interface.SyncPanelItem';
		const grid = new BX.TileGrid.Grid(params);

		grid.draw();
	}

	getBlockStatus()
	{
		let statusInfoBlock;
		if (this.options.status === 'success')
		{
			statusInfoBlock = Tag.render `
				<div id="status-info-block" class="ui-alert ui-alert-success calendar-sync-status-info">
					<span class="ui-alert-message">${Loc.getMessage('SYNC_STATUS_SUCCESS')}</span>
				</div>
			`;
		}
		else if (this.options.status === 'failed')
		{
			statusInfoBlock = Tag.render `
				<div id="status-info-block" class="ui-alert ui-alert-danger calendar-sync-status-info">
					<span class="ui-alert-message">${Loc.getMessage('SYNC_STATUS_ALERT')}</span>
				</div>
			`;
		}
		else
		{
			statusInfoBlock = Tag.render `
				<div id="status-info-block" class="ui-alert ui-alert-primary calendar-sync-status-info">
					<span class="ui-alert-message">${Loc.getMessage('SYNC_STATUS_NOT_CONNECTED')}</span>
				</div>
			`;
		}

		statusInfoBlock.addEventListener('mouseenter', (event) => {
			this.blockEnterTimeout = setTimeout(() =>
				{
					this.blockEnterTimeout = null;
					this.showPopup(statusInfoBlock, event);
				}, 150
			);
		}, true);

		statusInfoBlock.addEventListener('mouseleave', event => {
			this.blockLeaveTimeout = setTimeout(() =>
				{
					this.hidePopup();
				}, 150
			);
		});

		return Tag.render `
			<div class="calendar-sync-status-block" id="calendar-sync-status-block">
				<div class="calendar-sync-status-subtitle">
					<span data-hint=""></span>
					<span class="calendar-sync-status-text">${Loc.getMessage('LABEL_STATUS_INFO')}:</span>
				</div>
				${statusInfoBlock}
			</div>
		`;
	}

	showPopup(elementNode, event)
	{
		if(!this.popup && this.options.status !== 'not_connect')
		{
			this.popup = SyncStatusPopup.getInstance(this.options);
			this.popup.createPopup(elementNode);
			this.popup.show();

			this.popup.getPopup().getPopupContainer().addEventListener('mouseenter', e => {
				clearTimeout(this.blockEnterTimeout);
				clearTimeout(this.blockLeaveTimeout);
			});
			this.popup.getPopup().getPopupContainer().addEventListener('mouseleave', () => {
				this.hidePopup();
			});
		}
		else if (this.popup)
		{
			this.popup.popup.setBindElement(event.target);
			this.popup.popup.show();
		}
	}

	hidePopup()
	{
		if (this.popup)
		{
			this.popup.hide();
		}
	}

	refreshSyncInfo(syncInfo)
	{
		this.options.syncInfo = syncInfo;
		this.clearDataSlider();
		this.getGridContent();

		if (this.popup)
		{
			this.popup.refresh(syncInfo);
		}

		let statusInfoBlock = document.getElementById('status-info-block');

		if (this.options.status === 'success')
		{
			statusInfoBlock.className = 'ui-alert ui-alert-success' +
				' calendar-sync-status-info';
			statusInfoBlock.innerHTML = '<span class="ui-alert-message">' + Loc.getMessage('SYNC_STATUS_SUCCESS') + '</span>';
		}
		else
		{
			statusInfoBlock.className = 'ui-alert ui-alert-danger' +
				' calendar-sync-status-info';
			statusInfoBlock.innerHTML = '<span class="ui-alert-message">' + Loc.getMessage('SYNC_STATUS_ALERT') + '</span>';
		}
	}

	setStatus(status)
	{
		this.options.status = status;
	}

	clearDataSlider()
	{
		this.webContentBlock.innerHTML = '';
		this.mobileContentBlock.innerHTML = '';
	}

	setConnections()
	{
		this.webItems = [];
		this.mobileItems = [];
		const syncInfo = this.options.syncInfo;
		let googleItem = {};
		let yandexItem = {};
		let googleData = [];
		let yandexData = [];
		let caldavData = [];
		let caldavItem = {};
		let exchangeItem = {};
		let outlookItem = {};
		let macosItem = {};
		let iphoneItem = {};
		let androidItem = {};
		let icalItems = [];

		const sectionsByType = this.sortSections();

		for (let key in syncInfo)
		{
			switch (syncInfo[key].type)
			{
				case 'mac':
					macosItem = this.getSyncItem({
						connected: syncInfo[key].connected,
						className: 'BX.Calendar.Sync.Interface.MacItem',
						status: syncInfo[key].status,
						id: 'mac',
						syncDate: syncInfo[key].syncDate,
					},
		{
						portalAddress: this.options.portalAddress,
					});
					break;
				case 'outlook':
					outlookItem = this.getSyncItem({
						connected: syncInfo[key].connected,
						className: 'BX.Calendar.Sync.Interface.OutlookItem',
						status: syncInfo[key].status,
						id: 'outlook',
						syncDate: null,
					},
					{
						sections: sectionsByType.outlook,
						infoBySections: syncInfo[key].infoBySections || {},
					});
					break;
				case 'exchange':
					exchangeItem = this.getSyncItem({
						connected: syncInfo[key].connected,
						className: 'BX.Calendar.Sync.Interface.ExchangeItem',
						status: syncInfo[key].status,
						id: 'exchange',
						sections: sectionsByType['exchange'],
						syncDate: syncInfo[key].syncDate,
					});
					break;
				case 'iphone':
					iphoneItem = this.getSyncItem({
						connected: syncInfo[key].connected,
						className: 'BX.Calendar.Sync.Interface.MobileItem',
						status: syncInfo[key].status,
						id: 'iphone',
						syncDate: syncInfo[key].syncDate,
					});
					break;
				case 'android':
					androidItem = this.getSyncItem({
						connected: syncInfo[key].connected,
						className: 'BX.Calendar.Sync.Interface.MobileItem',
						status: syncInfo[key].status,
						id: 'android',
						syncDate: syncInfo[key].syncDate,
					});
					break;
				case 'google':
					googleData.push({
						text: syncInfo[key].userName,
						id: syncInfo[key].id,
						status: syncInfo[key].status,
						syncDate: syncInfo[key].syncDate,
					});
					break;
				case 'yandex':
					yandexData.push({
						text: syncInfo[key].connectionName,
						id: syncInfo[key].id,
						status: syncInfo[key].status,
						syncDate: syncInfo[key].syncDate,
						server: syncInfo[key].server,
						connectionName: syncInfo[key].connectionName,
						userName: syncInfo[key].userName,
					});
					break;
				case 'caldav':
					caldavData.push({
						text: syncInfo[key].connectionName,
						id: syncInfo[key].id,
						status: syncInfo[key].status,
						syncDate: syncInfo[key].syncDate,
						server: syncInfo[key].server,
						connectionName: syncInfo[key].connectionName,
						userName: syncInfo[key].userName,
					});
					break;
			}
		}

		const googleOptions = {
			id: 'google',
			className: 'BX.Calendar.Sync.Interface.GoogleItem',
			connections: googleData,
			sections: sectionsByType.google,
		};

		const googleAddParams = {
			authLink: this.options.syncLinks.google,
		};

		const yandexOptions = {
			id: 'yandex',
			className: 'BX.Calendar.Sync.Interface.YandexItem',
			connections: yandexData,
			sections: sectionsByType.caldav,
		};

		const caldavOptions = {
			id: 'caldav',
			className: 'BX.Calendar.Sync.Interface.CaldavItem',
			connections: caldavData,
			sections: sectionsByType.caldav,
		};

		googleItem = this.getPermanentItem(googleOptions, googleAddParams);
		caldavItem = this.getPermanentItem(caldavOptions);

		const has = Object.prototype.hasOwnProperty;
		if (has.call(syncInfo, `exchange`))
		{
			this.webItems.push(exchangeItem);
		}

		if (this.options.isRuZone)
		{
			this.webItems.push(this.getPermanentItem(yandexOptions));
		}



		this.webItems = [googleItem, ...this.webItems, macosItem, caldavItem];
		this.mobileItems = [iphoneItem, androidItem];
	}

	getPermanentItem(data, addParams = {})
	{
		return {
			id: data.id,
			className: data.className,
			status: data.connections ? this.getStatusItem(data.connections) : false,
			itemSelected: (data.connections.length > 0),
			syncDate: data.syncDate,
			data: {
				hasMenu: (data.connections.length > 0),
				menu: data.connections,
				sections: data.sections,
				userId: this.options.userId,
				connectionName: data.connectionName,
				text: data.text,
				...addParams,
			}
		};
	}

	getStatusItem(items)
	{
		let status = true;
		items.forEach(item => {status = item.status && status;});

		return status;
	}

	getSyncItem(options, additionalData = {})
	{
		return {
			id: options.id,
			className: options.className,
			title: options.title,
			status: options.status,
			color: options.color,
			itemSelected: options.connected,
			data: {
				hasMenu: false,
				userId: this.options.userId,
				syncDate: options.syncDate || null,
				...additionalData,
			}
		}
	}

	sortSections()
	{
		const sections = this.options.sections;
		let exchangeSections = [];
		let googleSections = [];
		let sectionsByType = {};
		let outlookSections = [];
		sectionsByType.caldav = {};

		sections.forEach(section => {
			if (section.belongsToView() && section.data.OUTLOOK_JS)
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
			else if (section.data['GAPI_CALENDAR_ID'] && section.data['CAL_DAV_CON'])
			{
				googleSections.push(section.data);
			}
			else if (section.data['CAL_DAV_CON'] && section.data['CAL_DAV_CAL'])
			{
				sectionsByType.caldav['caldav' + section.data['CAL_DAV_CON']] = section.data;
			}
		});

		sectionsByType.google = googleSections;
		sectionsByType.exchange = exchangeSections;
		sectionsByType.outlook = outlookSections;

		return sectionsByType;
	}
}