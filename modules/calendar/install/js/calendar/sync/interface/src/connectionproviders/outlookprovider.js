import {ConnectionProvider} from "./connectionprovider";
import {Loc} from "main.core";
import {Menu} from "main.popup";

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
		this.syncTimestamp = options.syncInfo.syncTimestamp;
		this.connectionName = Loc.getMessage('CALENDAR_TITLE_OUTLOOK');

		this.sections = options.sections;
		this.infoBySections = options.infoBySections;

		// this.setConnectStatus();
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
				this.menu.getPopupWindow().setBindElement(bindElement);
				this.menu.show();
			}
			else
			{
				const menuItems = this.getConnection().getSections();

				menuItems.forEach(item =>
				{
					if (this.infoBySections[item.id])
					{
						item.className = 'calendar-sync-outlook-popup-item';
					}

					item.onclick = () =>
					{
						if (item && item.connectURL)
						{
							try
							{
								eval(item.connectURL);
							} catch (e)
							{
							}
						}
					};
				});

				this.menu = new Menu({
					className: 'calendar-sync-popup-status',
					bindElement: bindElement,
					items: menuItems,
					width: this.MENU_WIDTH,
					padding: 7,
					autoHide: true,
					closeByEsc: true,
					zIndexAbsolute: 3020,
					id: this.getType() + '-menu',
				});

				this.menu.getMenuContainer().addEventListener('click', () =>
				{
					this.menu.close();
				});

				this.menu.show();
			}
		}
	}
}