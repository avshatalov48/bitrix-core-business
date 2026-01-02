import { AnalyticsSection } from '../const';

export class SliderInvite
{
	getEmptyStateContext(): string
	{
		return AnalyticsSection.chatEmptyState;
	}

	getRecentCreateMenuContext(): string
	{
		return AnalyticsSection.chatCreateMenu;
	}
}
