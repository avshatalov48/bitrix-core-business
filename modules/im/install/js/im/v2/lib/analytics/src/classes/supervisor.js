import { sendData } from 'ui.analytics';

import { AnalyticsCategory, AnalyticsEvent, AnalyticsTool, AnalyticsSection } from '../const';

export class Supervisor
{
	onOpenPriceTable(featureId: string): void
	{
		sendData({
			tool: AnalyticsTool.infoHelper,
			category: AnalyticsCategory.limit,
			event: AnalyticsEvent.openPrices,
			type: featureId,
			c_section: AnalyticsSection.chat,
		});
	}

	onOpenToolsSettings(toolId: string): void
	{
		sendData({
			tool: AnalyticsTool.infoHelper,
			category: AnalyticsCategory.toolOff,
			event: AnalyticsEvent.openSettings,
			type: toolId,
			c_section: AnalyticsSection.chat,
		});
	}
}
