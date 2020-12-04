// @flow
'use strict';

import {Loc} from "main.core";
import {InterfaceTemplate} from "./interfacetemplate";

export default class OutlookTemplate extends InterfaceTemplate
{
	constructor(provider, connection = null)
	{
		super({
			title: Loc.getMessage("CALENDAR_TITLE_MAC"),
			helpDeskCode: '5684075',
			titleInfoHeader: Loc.getMessage('CAL_CONNECT_MAC_CALENDAR_TITLE'),
			descriptionInfoHeader: Loc.getMessage('CAL_MAC_CONNECT_DESCRIPTION'),
			titleActiveHeader: Loc.getMessage('CAL_MAC_CALENDAR_IS_CONNECT_TITLE'),
			descriptionActiveHeader: Loc.getMessage('CAL_MAC_SELECTED_DESCRIPTION'),
			sliderIconClass: 'calendar-sync-slider-header-icon-mac',
			iconPath: '/bitrix/images/calendar/sync/mac.svg',
			color: '#ff5752',
			provider: provider,
			connection: connection,
			popupWithUpdateButton: false,
		});
	}
}