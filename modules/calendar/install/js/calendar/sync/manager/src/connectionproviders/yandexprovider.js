import {CaldavConnection} from "./caldavconnection";
import {Loc} from "main.core";

export class YandexProvider extends CaldavConnection
{
	constructor(options)
	{
		super({
			status: options.status,
			connected: options.connected,
			gridTitle: Loc.getMessage('CALENDAR_TITLE_YANDEX'),
			gridColor: '#f9c500',
			gridIcon: '/bitrix/images/calendar/sync/yandex.svg',
			type: 'yandex',
			viewClassification: 'web',
			templateClass: 'BX.Calendar.Sync.Interface.YandexTemplate',
		});

		this.connectionName = Loc.getMessage('CALENDAR_TITLE_YANDEX');
		this.connectionsSyncInfo = options.connections;

		if (options.connections && options.connections[0] && options.connections[0].syncInfo)
		{
			this.setSyncDate(options.connections[0].syncInfo.syncOffset);
		}
		this.setConnections();
	}
}