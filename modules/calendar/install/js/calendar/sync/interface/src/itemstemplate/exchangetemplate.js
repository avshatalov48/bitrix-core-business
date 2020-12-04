// @flow
'use strict';

import {Loc} from "main.core";
import {InterfaceTemplate} from "./interfacetemplate";

export default class ExchangeTemplate extends InterfaceTemplate
{
	constructor(provider, connection = null)
	{
		super({
			title: Loc.getMessage("CALENDAR_TITLE_EXCHANGE"),
			helpDeskCode: '11864622',
			titleInfoHeader: Loc.getMessage('CAL_CONNECT_EXCHANGE_CALENDAR_TITLE'),
			descriptionInfoHeader: Loc.getMessage('CAL_EXCHANGE_CONNECT_DESCRIPTION'),
			titleActiveHeader: Loc.getMessage('CAL_SYNC_CONNECTED_EXCHANGE_TITLE'),
			descriptionActiveHeader: Loc.getMessage('CAL_EXCHANGE_SELECTED_DESCRIPTION'),
			sliderIconClass: 'calendar-sync-slider-header-icon-office',
			iconPath: '/bitrix/images/calendar/sync/exchange.svg',
			color: '#54d0df',
			provider: provider,
			connection: connection,
			popupWithUpdateButton: true,
		});
	}
}