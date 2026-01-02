import type { PopupConfig } from '../type/popup-config';
import type { MarketExpiredCurtain } from './market-expired-curtain';
import PopupCategory from '../type/popup-category';
import { MarketTrialCurtain } from './market-trial-curtain';
import { MarketSubscriptionCurtain } from './market-subscription-curtain';
import { MarketTransitionCurtain } from './market-transition-curtain';
import CurtainPage from '../type/curtain-page';

export class CurtainFactory
{
	config: PopupConfig;

	constructor(config: PopupConfig)
	{
		this.config = config;
	}

	createCurtain(curtainPage: CurtainPage): MarketExpiredCurtain
	{
		const CurtainClass = this.#getCurtainClass();

		return new CurtainClass({
			marketSubscriptionUrl: this.config.marketSubscriptionUrl,
			type: this.config.type,
			expireDays: this.config.expireDays,
			curtainPage,
		});
	}

	#getCurtainClass(): string
	{
		switch (this.config.category)
		{
			case PopupCategory.TRIAL:
				return MarketTrialCurtain;
			case PopupCategory.SUBSCRIPTION:
				return MarketSubscriptionCurtain;
			case PopupCategory.TRANSITION:
			default:
				return MarketTransitionCurtain;
		}
	}
}
