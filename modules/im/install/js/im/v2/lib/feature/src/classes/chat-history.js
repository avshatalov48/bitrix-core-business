import { Loc } from 'main.core';
import { FeaturePromoter } from 'ui.info-helper';

import { SliderCode } from 'im.v2.const';
import { Core } from 'im.v2.application.core';

type TariffRestrictions = {
	fullChatHistory: {
		isAvailable: boolean,
		limitDays: number | null,
	}
};

export const ChatHistoryManager = {
	isAvailable(): boolean
	{
		const { fullChatHistory } = this.getTariffRestrictions();

		return fullChatHistory.isAvailable;
	},

	getDaysLimit(): number
	{
		const { fullChatHistory } = this.getTariffRestrictions();

		return fullChatHistory.limitDays;
	},

	openFeatureSlider(): void
	{
		const promoter = new FeaturePromoter({ code: SliderCode.historyLimited });
		promoter.show();
	},

	getLimitTitle(): string
	{
		return Loc.getMessage('IM_LIB_FEATURE_HISTORY_LIMIT_TITLE');
	},

	getLimitSubtitle(withEmphasis: boolean = false): string
	{
		if (withEmphasis)
		{
			return Loc.getMessagePlural(
				'IM_LIB_FEATURE_HISTORY_LIMIT_SUBTITLE',
				this.getDaysLimit(),
				{ '#DAY_LIMIT#': this.getDaysLimit() },
			);
		}

		return Loc.getMessagePlural(
			'IM_LIB_FEATURE_HISTORY_LIMIT_SUBTITLE',
			this.getDaysLimit(),
			{
				'#DAY_LIMIT#': this.getDaysLimit(),
				'[action_emphasis]': '',
				'[/action_emphasis]': '',
			},
		);
	},

	getLearnMoreText(): string
	{
		return Loc.getMessage('IM_LIB_FEATURE_HISTORY_LIMIT_LEARN_MORE');
	},

	getTooltipText(): string
	{
		return Loc.getMessage('IM_LIB_FEATURE_HISTORY_LIMIT_TOOLTIP');
	},

	getTariffRestrictions(): TariffRestrictions
	{
		return Core.getStore().getters['application/tariffRestrictions/get'];
	},
};
