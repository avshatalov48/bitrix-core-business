// @flow
'use strict';

import {Dom, Loc, Tag, Cache, Event} from "main.core";
import {ConnectionItem} from "calendar.sync.manager";
import AuxiliarySyncPanel from "./auxiliarysyncpanel";
import SyncPanelUnit from './syncpanelunit';

export default class SyncPanel
{
	MAIN_SYNC_SLIDER_NAME = 'calendar:sync-slider';
	HELPDESK_CODE = 11828176;
	SLIDER_WIDTH = 770;
	LOADER_NAME = "calendar:loader";
	cache = new Cache.MemoryCache();

	constructor(options)
	{
		this.status = options.status;
		this.connectionsProviders = options.connectionsProviders;
		this.userId = options.userId;
		this.BX = window.top.BX || window.BX;
	}

	openSlider()
	{
		BX.SidePanel.Instance.open(this.MAIN_SYNC_SLIDER_NAME, {
			contentCallback: (slider) => {
				return new Promise((resolve, reject) => {
					resolve(this.getContent());
				});
			},
			allowChangeHistory: false,
			events: {
				onLoad: () => {
					this.displayConnectionUnits();
				}
			},
			cacheable: false,
			width: this.SLIDER_WIDTH,
			loader: this.LOADER_NAME,
		});
	}

	getContent()
	{
		return Tag.render`
			<div class="calendar-sync__wrapper calendar-sync__scope">
				${this.getHeaderWrapper()}
				<div class="calendar-sync__content">
				${this.getUnitsContentWrapper()}
				${this.getFooterWrapper()}
				</div>
			</div>
		`;
	}

	getHeaderWrapper()
	{
		return Tag.render`
			<div class="calendar-sync__header">
				<div class="calendar-sync__header-logo"></div>
				<div class="calendar-sync__header-container">
					<div class="calendar-sync__header-title">${Loc.getMessage('CAL_SYNC_TITLE_NEW')}</div>
					<div class="calendar-sync__header-sub-title">${Loc.getMessage('CAL_SYNC_SUB_TITLE')}</div>
				</div>
			</div>
		`;
	}

	getUnitsContentWrapper()
	{
		this.unitsContentWrapper = Tag.render`
			<div class="calendar-sync__calendar-list">
			</div>
		`;

		return this.unitsContentWrapper;
	}

	getFooterWrapper()
	{
		return Tag.render`
			<div class="calendar-sync__content-block --space-bottom --space-left">
				${this.getExtraInfoWithCheckIcon()}
			</div>
			<div class="calendar-sync__content-block --space-bottom --space-left--double">
				${this.getOpenAuxiliaryPanelLink()}
			</div>
			<div class="calendar-sync__content-block --space-left--double">
				${this.getOpenHelpLink()}
			</div>
		`;
	}

	getExtraInfoWithCheckIcon()
	{
		const alreadyConnected = Object.values(this.connectionsProviders).filter(item => {
			return item.mainPanel && item.status;
		}).length > 0;

		return Tag.render`
			<div class="calendar-sync__content-text --icon-check${(alreadyConnected ? ' --disabled' : '')}">
				${Loc.getMessage('CAL_SYNC_INFO_PROMO')}
			</div>
		`;
	}

	getOpenAuxiliaryPanelLink()
	{
		const link = Tag.render`
			<div class="calendar-sync__content-link">
				${Loc.getMessage('CAL_OPEN_AUXILIARY_PANEL')}
			</div>
		`;
		Event.bind(link, 'click', () => {
				this.auxiliarySyncPanel = new AuxiliarySyncPanel({
					connectionsProviders: this.connectionsProviders,
					userId: this.userId,
					status: this.status,
				});
				this.auxiliarySyncPanel.openSlider();
		});

		return link;
	}

	getOpenHelpLink()
	{
		const link = Tag.render`
			<div class="calendar-sync__content-link">${Loc.getMessage('CAL_SHOW_SYNC_HELP')}</divclass>
		`;
		Event.bind(link, 'click', () => {
			if(this.BX.Helper)
			{
				this.BX.Helper.show("redirect=detail&code=" + this.HELPDESK_CODE);
			}
		});

		return link;
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

	displayConnectionUnits()
	{
		const items = Object.values(this.connectionsProviders).filter(item => {
			return item.mainPanel || item.connected;
		});

		this.renderConnectionUnits(items);
	}

	renderConnectionUnits(providers)
	{
		Dom.clean(this.unitsContentWrapper);
		providers.forEach((provider) => {
			const interfaceUnit = new SyncPanelUnit({connectionProvider: provider});
			provider.setInterfaceUnit(interfaceUnit);
			interfaceUnit.renderTo(this.unitsContentWrapper);
			interfaceUnit.setSyncStatus(provider.getStatus());
		});
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

	refresh(status, connectionsProviders)
	{
		this.status = status;
		this.connectionsProviders = connectionsProviders;
		Dom.replace(document.querySelector('#calendar-sync-status-block'), this.blockStatusContent);
		this.displayConnectionUnits();
		this.auxiliarySyncPanel.refresh(status, connectionsProviders);
	}
}
