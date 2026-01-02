import { Button } from 'ui.buttons';
import { MarketPopupButton } from './market-popup-button';
import { Extension } from 'main.core';

export class SubscribeButton extends MarketPopupButton
{
	getButtonConfig(): Object
	{
		return {
			id: 'marketExpiredPopup_button_subscribe',
			size: Button.Size.MEDIUM,
			color: Button.Color.SUCCESS,
			noCaps: true,
			round: true,
			tag: Button.Tag.LINK,
			link: this.#getSubscribeLink(),
		};
	}

	onClick(): void
	{
		this.analytic?.sendClickButton('buy');
		super.onClick();
	}

	#getSubscribeLink(): string
	{
		return Extension.getSettings('rest.market-expired')?.marketSubscriptionUrl ?? '';
	}
}
