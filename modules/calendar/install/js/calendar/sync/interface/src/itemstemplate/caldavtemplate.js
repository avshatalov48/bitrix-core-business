// @flow
'use strict';

import {Loc} from "main.core";
import {CaldavInterfaceTemplate} from "./caldavinterfacetemplate";

export default class CaldavTemplate extends CaldavInterfaceTemplate
{
	constructor(provider, connection = null)
	{
		super({
			title: Loc.getMessage("CALENDAR_TITLE_CALDAV"),
			helpDeskCode: '5697365',
			titleInfoHeader: Loc.getMessage('CAL_CONNECT_CALDAV_CALENDAR'),
			descriptionInfoHeader: Loc.getMessage('CAL_CALDAV_CONNECT_DESCRIPTION'),
			titleActiveHeader: Loc.getMessage('CAL_CALDAV_CALENDAR_IS_CONNECT'),
			descriptionActiveHeader: Loc.getMessage('CAL_CALDAV_SELECTED_DESCRIPTION'),
			sliderIconClass: 'calendar-sync-slider-header-icon-caldav',
			iconPath: '/bitrix/images/calendar/sync/caldav.svg',
			color: '#1eae43',
			provider: provider,
			connection: connection,
			popupWithUpdateButton: true,
		});
	}
}