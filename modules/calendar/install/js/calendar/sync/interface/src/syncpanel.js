import {Loc, Tag} from "main.core";
import StatusBlock from "./controls/statusblock";
import {ConnectionItem} from "./connectionproviders/connectionitem";

export default class SyncPanel
{
	constructor(options)
	{
		this.status = options.status;
		this.connectionsProviders = options.connectionsProviders;
		this.userId = options.userId;

		this.statusBlockEnterTimeout = null;
		this.statusBlockLeaveTimeout = null;
	}

	showContent()
	{
		const mainHeader = Tag.render`
			<span class="calendar-sync-header-text">${Loc.getMessage('SYNC_CALENDAR_HEADER')}</span>
		`;

		const connections = this.getConnections();

		this.blockStatus = StatusBlock.createInstance({
			status: this.status,
			connections: connections,
			withStatus: true,
			popupWithUpdateButton: true,
			popupId: 'calendar-syncPanel-status',
		}).getContentStatusBlock();

		const webHeader = Tag.render `
			<div class="calendar-sync-title">${Loc.getMessage('SYNC_WEB_HEADER')}</div>
		`;

		const mobileHeader = Tag.render `
			<div class="calendar-sync-title">${Loc.getMessage('SYNC_MOBILE_HEADER')}</div>
		`;

		const webContentBlock = Tag.render `
			<div id="calendar-sync-web" class="calendar-sync-web"></div>
		`;

		const mobileContentBlock = Tag.render `
			<div id="calendar-sync-mobile" class="calendar-sync-mobile"></div>
		`;

		return Tag.render`
			<div class="calendar-sync-wrap">
				<div class="calendar-sync-header">
					${mainHeader}
					${this.blockStatus}
				</div>
				${mobileHeader}
				${mobileContentBlock}
				${webHeader}
				${webContentBlock}
			</div>
		`;
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
				itemConnections.forEach(connection =>
					{
						if (connection instanceof ConnectionItem)
						{
							if (connection.getConnectStatus() === true)
							{
								connections.push(connection);
							}
						}
					}
				)
			}
		});

		return connections;
	}

	setGridContent()
	{
		const items = Object.values(this.connectionsProviders);
		const mobileItems = items.filter(item => {
			return item.getViewClassification() === 'mobile';
		});
		const webItems = items.filter(item => {
			return item.getViewClassification() === 'web';
		});

		this.showWebGridContent(webItems);
		this.showMobileGridContent(mobileItems);
	}

	showWebGridContent(items)
	{
		const grid = new BX.TileGrid.Grid({
			id: 'calendar_sync',
			items: items,
			container: document.getElementById('calendar-sync-web'),
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
		const grid = new BX.TileGrid.Grid({
			id: 'calendar_sync',
			items:  items,
			container:  document.getElementById('calendar-sync-mobile'),
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
	}
}