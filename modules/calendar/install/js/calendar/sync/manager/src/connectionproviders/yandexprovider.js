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

		this.connectionsSyncInfo = options.connections;

		this.setConnections(options);
	}
}