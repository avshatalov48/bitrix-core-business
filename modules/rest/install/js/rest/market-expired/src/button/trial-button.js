import { Button } from 'ui.buttons';
import { MarketPopupButton } from './market-popup-button';
import { ajax } from 'main.core';
import { UI } from 'ui.notification';
import { FeaturePromotersRegistry } from 'ui.info-helper';

export class TrialButton extends MarketPopupButton
{
	getButtonConfig(): Object
	{
		return {
			id: 'marketExpiredPopup_button_demo',
			size: Button.Size.MEDIUM,
			color: Button.Color.LIGHT_BORDER,
			noCaps: true,
			round: true,
		};
	}

	onClick(): void
	{
		this.getButton().unbindEvent('click');
		this.getButton().setState(Button.State.WAITING);
		this.analytic?.sendClickButton('demo');

		ajax({
			url: '/bitrix/tools/rest.php',
			method: 'POST',
			dataType: 'json',
			data: {
				sessid: BX.bitrix_sessid(),
				action: 'activate_demo',
			},
			onsuccess: (result) => {
				this.onSuccess();

				if (result.error)
				{
					UI.Notification.Center.notify({
						content: result.error,
						category: 'demo_subscribe_error',
						position: 'top-right',
					});
				}
				else
				{
					this.analytic?.sendDemoActivated();
					FeaturePromotersRegistry.getPromoter({ code: 'limit_market_trial_active' }).show();
				}
			},
		});
	}
}
