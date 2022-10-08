// @flow
'use strict';

import {Loc} from "main.core";
import {CaldavInterfaceTemplate} from "./caldavinterfacetemplate";

export default class YandexTemplate extends CaldavInterfaceTemplate
{
	constructor(provider, connection = null)
	{
		super({
			title: Loc.getMessage("CALENDAR_TITLE_YANDEX"),
			helpDeskCode: '10930170',
			titleInfoHeader: Loc.getMessage('CAL_CONNECT_YANDEX_CALENDAR'),
			descriptionInfoHeader: Loc.getMessage('CAL_YANDEX_CONNECT_DESCRIPTION'),
			titleActiveHeader: Loc.getMessage('CAL_YANDEX_CALENDAR_IS_CONNECT'),
			descriptionActiveHeader: Loc.getMessage('CAL_YANDEX_SELECTED_DESCRIPTION'),
			sliderIconClass: 'calendar-sync-slider-header-icon-yandex',
			iconPath: '/bitrix/images/calendar/sync/yandex.svg',
			iconLogoClass: '--yandex',
			color: '#f9c500',
			provider: provider,
			connection: connection,
			popupWithUpdateButton: true,
		});
	}
}