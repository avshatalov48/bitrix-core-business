// @flow
'use strict';

import {Loc} from "main.core";
import MobileInterfaceTemplate from "./mobileinterfacetemplate";

export default class AndroidTemplate extends MobileInterfaceTemplate
{
	constructor(provider, connection = null)
	{
		super({
			title: Loc.getMessage("CALENDAR_TITLE_ANDROID"),
			helpDeskCode: '5686179',
			titleInfoHeader: Loc.getMessage('CAL_CONNECT_ANDROID_CALENDAR_TITLE'),
			descriptionInfoHeader: Loc.getMessage('CAL_ANDROID_CONNECT_DESCRIPTION'),
			titleActiveHeader: Loc.getMessage('CAL_SYNC_CONNECTED_ANDROID_TITLE'),
			descriptionActiveHeader: Loc.getMessage('CAL_ANDROID_SELECTED_DESCRIPTION'),
			sliderIconClass: 'calendar-sync-slider-header-icon-android',
			iconPath: '/bitrix/images/calendar/sync/android.svg',
			color: '#9ece03',
			provider: provider,
			connection: connection,
			popupWithUpdateButton: false,
		});
	}
}