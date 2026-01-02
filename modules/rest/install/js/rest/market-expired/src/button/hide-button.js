import { Button } from 'ui.buttons';
import { MarketPopupButton } from './market-popup-button';

export class HideButton extends MarketPopupButton
{
	getButtonConfig(): Object
	{
		return {
			id: 'marketExpiredPopup_button_hide',
			size: Button.Size.EXTRA_SMALL,
			color: Button.Color.LINK,
			noCaps: true,
		};
	}

	onClick(): void
	{
		this.analytic?.sendClickButton('ok');
		super.onClick();
	}
}
