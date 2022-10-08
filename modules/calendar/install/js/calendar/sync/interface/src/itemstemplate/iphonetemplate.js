// @flow
'use strict';

import { Loc, Tag } from "main.core";
import MobileInterfaceTemplate from "./mobileinterfacetemplate";
import { Util } from 'calendar.util';

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

		this.warningText = Loc.getMessage('CAL_SYNC_WARNING_IPHONE_AND_MAC');
		this.warningButtonText = Loc.getMessage('CALENDAR_CONNECT_ICLOUD');
	}

	handleWarningButtonClick()
	{
		BX.SidePanel.Instance.getOpenSliders().forEach(slider =>
		{
			if (['calendar:auxiliary-sync-slider', 'calendar:item-sync-connect-iphone'].includes(slider.getUrl()))
			{
				slider.close();
			}
		});

		const calendarContext = Util.getCalendarContext();
		if (calendarContext)
		{
			calendarContext
				.syncInterface
				.getIcloudProvider()
				.getInterfaceUnit()
				.getConnectionTemplate()
				.handleConnectButton();
		}
	}
}