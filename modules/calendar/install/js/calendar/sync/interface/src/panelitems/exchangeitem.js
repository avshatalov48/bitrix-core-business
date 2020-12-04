import SyncItemTemplate from "./syncitemtemplate";
import {Loc} from "main.core";

export default class ExchangeItem extends SyncItemTemplate
{
	constructor(options)
	{
		super(options);
		this.helpdeskCode = '11864622';
		this.sliderTitle = Loc.getMessage('CALENDAR_TITLE_EXCHANGE');
		this.titleInfoHeader = Loc.getMessage('CAL_CONNECT_EXCHANGE_CALENDAR');
		this.descriptionInfoHeader = this.selected ? Loc.getMessage('CAL_EXCHANGE_SELECTED_DESCRIPTION') : Loc.getMessage('CAL_EXCHANGE_CONNECT_DESCRIPTION');
		this.sliderIconClass = 'calendar-sync-slider-header-icon-office';
		this.image = '/bitrix/images/calendar/sync/exchange.svg';
		this.color = '#54d0df';
		this.title = Loc.getMessage('CALENDAR_TITLE_EXCHANGE');
		this.titleActiveHeader = Loc.getMessage('CAL_EXCHANGE_CALENDAR_IS_CONNECT')
	}

	getConnectSliderContent()
	{
		return this.getSliderContentInfoBlock();
	}

	getSelectedSliderContent()
	{
		return this.getConnectSliderContent();
	}
}