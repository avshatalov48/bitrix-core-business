import { MarketExpiredCurtain } from './market-expired-curtain';
import { Button } from 'ui.buttons';
import { Loc } from 'main.core';

export class MarketTransitionCurtain extends MarketExpiredCurtain
{
	getRightButtons(): []
	{
		return [
			new Button({
				text: Loc.getMessage('REST_MARKET_EXPIRED_CURTAIN_TRANSITION_BUTTON'),
				size: Button.Size.EXTRA_SMALL,
				color: Button.Color.CURTAIN_WARNING,
				tag: Button.Tag.LINK,
				noCaps: true,
				round: true,
				props: {
					href: 'FEATURE_PROMOTER=limit_v2_nosubscription_marketplace_withapplications_off',
				},
				onclick: () => super.onRightButtonClick.bind(this),
			}),
		];
	}

	getContent(): string
	{
		return Loc.getMessage('REST_MARKET_EXPIRED_CURTAIN_TRANSITION_TEXT');
	}

	onHide()
	{
		BX.userOptions.save('rest', `marketTransitionCurtain${this.options.curtainPage}Ts`, null, Math.floor(Date.now() / 1000));
	}
}
