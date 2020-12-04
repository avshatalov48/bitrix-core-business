import SyncItemTemplate from "./syncitemtemplate";
import {Loc} from "main.core";

export default class CaldavItem extends SyncItemTemplate
{
	constructor(options)
	{
		super(options);

		this.layout = {
			container: null,
			header: null,
			content: null
		};

		this.helpdeskCode = '5697365';
		this.title = Loc.getMessage('CALENDAR_TITLE_CALDAV');
		this.sliderTitle = Loc.getMessage('CALENDAR_TITLE_CALDAV');
		this.titleInfoHeader = Loc.getMessage('CAL_CONNECT_CALDAV_CALENDAR');
		this.titleActiveHeader = Loc.getMessage('CAL_CALDAV_CALENDAR_IS_CONNECT');
		this.descriptionInfoHeader = Loc.getMessage('CAL_CALDAV_CONNECT_DESCRIPTION');
		this.descriptionActiveHeader = Loc.getMessage('CAL_CALDAV_SELECTED_DESCRIPTION');
		this.sliderIconClass = 'calendar-sync-slider-header-icon-caldav';
		this.image = '/bitrix/images/calendar/sync/caldav.svg';
		this.color = '#1eae43';
	}
}