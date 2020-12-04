import SyncItemTemplate from "./syncitemtemplate";
import {Loc} from "main.core";

export default class YandexItem extends SyncItemTemplate
{
	constructor(options)
	{
		super(options);

		this.layout = {
			container: null,
			header: null,
			content: null
		};

		this.helpdeskCode = '10930170';
		this.title = Loc.getMessage('CALENDAR_TITLE_YANDEX');
		this.sliderTitle = Loc.getMessage('CALENDAR_TITLE_YANDEX');
		this.titleInfoHeader = Loc.getMessage('CAL_CONNECT_YANDEX_CALENDAR');
		this.titleActiveHeader = Loc.getMessage('CAL_YANDEX_CALENDAR_IS_CONNECT');
		this.descriptionInfoHeader = Loc.getMessage('CAL_YANDEX_CONNECT_DESCRIPTION');
		this.descriptionActiveHeader = Loc.getMessage('CAL_YANDEX_SELECTED_DESCRIPTION');
		this.sliderIconClass = 'calendar-sync-slider-header-icon-yandex';
		this.image = '/bitrix/images/calendar/sync/yandex.svg';
		this.color = '#f9c500';
	}
}
