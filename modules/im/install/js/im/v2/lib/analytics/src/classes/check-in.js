import { sendData } from 'ui.analytics';

import { AnalyticsCategory, AnalyticsEvent, AnalyticsSection, AnalyticsTool } from '../const';

export class CheckIn
{
	onOpenCheckInPopup()
	{
		sendData({
			event: AnalyticsEvent.popupOpen,
			tool: AnalyticsTool.checkin,
			category: AnalyticsCategory.shift,
			c_section: AnalyticsSection.chat,
		});
	}
}
