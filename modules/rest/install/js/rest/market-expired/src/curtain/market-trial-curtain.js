import { MarketExpiredCurtain } from './market-expired-curtain';
import { Button } from 'ui.buttons';
import { Loc } from 'main.core';
import PopupType from '../type/popup-type';

export class MarketTrialCurtain extends MarketExpiredCurtain
{
	getRightButtons(): []
	{
		return [
			new Button({
				text: Loc.getMessage('REST_MARKET_EXPIRED_POPUP_BUTTON_SUBSCRIBE'),
				size: Button.Size.EXTRA_SMALL,
				color: Button.Color.CURTAIN_WARNING,
				tag: Button.Tag.LINK,
				noCaps: true,
				round: true,
				props: {
					href: this.options.marketSubscriptionUrl,
				},
				onclick: () => super.onRightButtonClick.bind(this),
			}),
		];
	}

	getContent(): string
	{
		return this.options.type === PopupType.FINAL
			? Loc.getMessage(`REST_MARKET_EXPIRED_CURTAIN_TRIAL_FINAL_TEXT${this.marketLabel}`)
			: Loc.getMessage(`REST_MARKET_EXPIRED_CURTAIN_TRIAL_WARNING_TEXT${this.marketLabel}`, {
				'#DAYS#': this.options.expireDays,
			});
	}

	onHide()
	{
		if (this.options.type === PopupType.FINAL)
		{
			BX.userOptions.save('rest', `marketSubscriptionCurtain${this.options.curtainPage}Dismiss`, null, 'Y');
		}
		else
		{
			BX.userOptions.save('rest', `marketSubscriptionCurtain${this.options.curtainPage}Ts`, null, Math.floor(Date.now() / 1000));
		}
	}
}
