// @flow
'use strict';

import {Loc} from "main.core";
import MobileInterfaceTemplate from "./mobileinterfacetemplate";

export default class IphoneTemplate extends MobileInterfaceTemplate
{
	constructor(provider, connection = null)
	{
		super({
			title: Loc.getMessage("CALENDAR_TITLE_IPHONE"),
			helpDeskCode: '5686207',
			titleInfoHeader: Loc.getMessage('CAL_CONNECT_IPHONE_CALENDAR_TITLE'),
			descriptionInfoHeader: Loc.getMessage('CAL_IPHONE_CONNECT_DESCRIPTION'),
			titleActiveHeader: Loc.getMessage('CAL_SYNC_CONNECTED_IPHONE_TITLE'),
			descriptionActiveHeader: Loc.getMessage('CAL_IPHONE_SELECTED_DESCRIPTION'),
			sliderIconClass: 'calendar-sync-slider-header-icon-iphone',
			iconPath: '/bitrix/images/calendar/sync/iphone.svg',
			color: '#2fc6f6',
			provider: provider,
			connection: connection,
			popupWithUpdateButton: false,
		});
	}
}