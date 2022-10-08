// @flow
'use strict';

import {Loc} from "main.core";
import MobileInterfaceTemplate from "./mobileinterfacetemplate";
import { Util } from 'calendar.util';

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

		this.warningText = Loc.getMessage('CAL_SYNC_WARNING_ANDROID');
		this.warningButtonText = Loc.getMessage('CALENDAR_CONNECT_GOOGLE');
	}

	handleWarningButtonClick()
	{
		BX.SidePanel.Instance.getOpenSliders().forEach(slider =>
		{
			if (['calendar:auxiliary-sync-slider', 'calendar:item-sync-connect-android'].includes(slider.getUrl()))
			{
				slider.close();
			}
		});

		const calendarContext = Util.getCalendarContext();
		if (calendarContext)
		{
			calendarContext
				.syncInterface
				.getGoogleProvider()
				.getInterfaceUnit()
				.getConnectionTemplate()
				.handleConnectButton();
		}
	}
}