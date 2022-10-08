// @flow
'use strict';

import {ConnectionProvider} from "./connectionprovider";
import {Loc} from "main.core";

export class OutlookProvider extends ConnectionProvider
{
	constructor(options)
	{
		super({
			status: options.syncInfo.status,
			connected: options.syncInfo.connected,
			gridTitle: Loc.getMessage('CALENDAR_TITLE_OUTLOOK'),
			gridColor: '#ffa900',
			gridIcon: '/bitrix/images/calendar/sync/outlook.svg',
			type: 'outlook',
			viewClassification: 'web',
			templateClass: 'BX.Calendar.Sync.Interface.OutlookTemplate',
		});
		this.setSyncDate(options.syncInfo.syncOffset);
		this.connectionName = Loc.getMessage('CALENDAR_TITLE_OUTLOOK');

		this.sections = options.sections;
		this.infoBySections = options.infoBySections;

		this.setConnections();
	}

	hasMenu()
	{
		return this.sections.length > 0;
	}

	showMenu(bindElement)
	{
		if (this.hasMenu())
		{
			if (this.menu)
			{
				this.menu.destroy();
			}
			const menuItems = this.getConnection().getSections();

			menuItems.forEach(item =>
			{
				if (this.infoBySections[item.id])
				{
					item.className = 'calendar-sync-outlook-popup-item';
				}
				
				item.onclick = () =>
				{
					this.connectToOutlook(item);
				};
			});

			this.menu = new (window.top.BX || window.BX).Main.Menu({
				className: 'calendar-sync-popup-status',
				bindElement: bindElement,
				items: menuItems,
				padding: 7,
				autoHide: true,
				closeByEsc: true,
				zIndexAbsolute: 3020,
				id: this.getType() + '-menu',
				offsetLeft: -40,
			});

			this.menu.getMenuContainer().addEventListener('click', () =>
			{
				this.menu.close();
			});

			this.menu.show();
		}
	}
	
	connectToOutlook(section)
	{
		if (section.id)
		{
			BX.ajax.runAction('calendar.api.syncajax.getOutlookLink', {
				data: {
					id: section.id
				}
			})
				.then(
					(response) => {
						const url = response.data.result;
						eval(url);
					},
				)
		}
	}
}