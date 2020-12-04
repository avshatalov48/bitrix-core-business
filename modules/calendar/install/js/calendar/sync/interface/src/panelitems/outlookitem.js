import SyncItemTemplate from "./syncitemtemplate";
import {Menu} from "main.popup";
import {Loc, Tag} from "main.core";

export default class OutlookItem extends SyncItemTemplate

{
	constructor(options)
	{
		super(options);
		this.sliderIconClass = 'calendar-sync-slider-header-icon-ms';

		this.image = '/bitrix/images/calendar/sync/outlook.svg';
		this.color = '#ffa900';
		this.title = Loc.getMessage('CALENDAR_TITLE_OUTLOOK');
		this.data.hasMenu = true;
		this.infoBySections = options.data.infoBySections;
	}

	syncSectionWithOutlook (section)
	{

	}

	getMenuItems(): *
	{
		return this.data.sections;
	}

	showMenu()
	{
		if (!this.menu)
		{

		}
		else
		{
			this.menu.show();
		}
	}
}