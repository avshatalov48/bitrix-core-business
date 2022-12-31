// @flow
'use strict';

import {Dom, Loc, Tag, Cache} from "main.core";
import StatusBlock from "./controls/statusblock";
import {ConnectionItem} from "calendar.sync.manager";

export default class AuxiliarySyncPanel
{
	MAIN_SYNC_SLIDER_NAME = 'calendar:auxiliary-sync-slider';
	SLIDER_WIDTH = 684;
	LOADER_NAME = "calendar:loader";

	cache = new Cache.MemoryCache();

	constructor(options)
	{
		this.status = options.status;
		this.connectionsProviders = options.connectionsProviders;
		this.userId = options.userId;

		this.statusBlockEnterTimeout = null;
		this.statusBlockLeaveTimeout = null;
	}

	openSlider()
	{
		BX.SidePanel.Instance.open(this.MAIN_SYNC_SLIDER_NAME, {
			contentCallback: (slider) => {
				return new Promise((resolve, reject) => {
					resolve(this.getContent());
				});
			},
			allowChangeHistory:false,
			events: {
				onLoad: () => {
					this.setGridContent();
				},
				// onMessage: (event) => {
				// 	if (event.getEventId() === 'refreshSliderGrid')
				// 	{
				// 		this.refreshData();
				// 	}
				// },
				// onClose: (event) => {
				// 	BX.SidePanel.Instance.postMessageTop(window.top.BX.SidePanel.Instance.getTopSlider(), "refreshCalendarGrid", {});
				// },
			},
			cacheable: false,
			width: this.SLIDER_WIDTH,
			loader: this.LOADER_NAME,
		});
	}

	getContent()
	{
		return Tag.render`
			<div class="calendar-sync-wrap">
				${this.getHeader()}
				${this.getMobileHeader()}
				${this.getMobileContentWrapper()}
				${this.getWebHeader()}
				${this.getWebContentWrapper()}
			</div>
		`;
	}

	getHeader()
	{
		return Tag.render`
			<div class="calendar-sync-header">
				${this.getMainHeader()}
				${this.getStatusBlockContent(this.getConnections())}
			</div>
		`;
	}

	getMainHeader()
	{
		return this.cache.remember('calendar-syncPanel-mainHeader', () => {
			return Tag.render`
				<span class="calendar-sync-header-text">${Loc.getMessage('SYNC_CALENDAR_HEADER_NEW')}</span>
			`;
		});
	}

	getMobileContentWrapper()
	{
		return this.cache.remember('calendar-syncPanel-mobileContentWrapper', () => {
			return Tag.render`
			<div id="calendar-sync-mobile" class="calendar-sync-mobile"></div>
		`;
		});

	}

	getWebContentWrapper()
	{
		return this.cache.remember('calendar-syncPanel-webContentWrapper', () => {
			return Tag.render`
				<div id="calendar-sync-web" class="calendar-sync-web"></div>
			`;
		});
	}

	getMobileHeader()
	{
		return this.cache.remember('calendar-syncPanel-mobileHeader', () => {
			return Tag.render`
				<div class="calendar-sync-title">${Loc.getMessage('SYNC_MOBILE_HEADER')}</div>
			`;
		});
	}

	getWebHeader()
	{
		return this.cache.remember('calendar-syncPanel-webHeader', () => {
			return Tag.render`
				<div class="calendar-sync-title">${Loc.getMessage('SYNC_WEB_HEADER')}</div>
		`;
		});
	}

	getStatusBlockContent(connections)
	{
		this.statusBlock = StatusBlock.createInstance({
			status: this.status,
			connections: connections,
			withStatusLabel: true,
			popupWithUpdateButton: true,
			popupId: 'calendar-syncPanel-status',
		});

		this.statusBlockContent =  this.statusBlock.getContent()
		return this.statusBlockContent;
	}

	getConnections()
	{
		const connections = [];
		const items = Object.values(this.connectionsProviders);
		items.forEach(item =>
		{
			const itemConnections = item.getConnections();
			if (itemConnections.length > 0)
			{
				itemConnections.forEach(connection => {

					if (ConnectionItem.isConnectionItem(connection) && connection.getConnectStatus() === true)
					{
						connections.push(connection);
					}
				})
			}
		});

		return connections;
	}

	setGridContent()
	{
		const items = Object.values(this.connectionsProviders);

		this.showWebGridContent(items.filter(item => {
			return item.mainPanel === false && item.getViewClassification() === 'web';
		}));

		this.showMobileGridContent(items.filter(item => {
			return item.mainPanel === false && item.getViewClassification() === 'mobile';
		}));
	}

	showWebGridContent(items)
	{
		const wrapper = this.getWebContentWrapper();
		Dom.clean(wrapper);
		const grid = new BX.TileGrid.Grid({
			id: 'calendar_sync',
			items: items,
			container: wrapper,
			sizeRatio: "55%",
			itemMinWidth: 180,
			tileMargin: 7,
			itemType: 'BX.Calendar.Sync.Interface.GridUnit',
			userId: this.userId,
		});

		grid.draw();
	}

	showMobileGridContent(items)
	{
		const wrapper = this.getMobileContentWrapper();
		Dom.clean(wrapper);
		const grid = new BX.TileGrid.Grid({
			id: 'calendar_sync',
			items:  items,
			container: wrapper,
			sizeRatio:  "55%",
			itemMinWidth:  180,
			tileMargin:  7,
			itemType: 'BX.Calendar.Sync.Interface.GridUnit',
		});

		grid.draw();
	}

	refresh(status, connectionsProviders)
	{
		this.status = status;
		this.connectionsProviders = connectionsProviders;
		this.blockStatusContent = this.statusBlock.refresh(status, this.getConnections()).getContent();
		Dom.replace(document.querySelector('#calendar-sync-status-block'), this.blockStatusContent);
		this.setGridContent();
	}
}