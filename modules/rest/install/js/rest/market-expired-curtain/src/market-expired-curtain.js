import { Loc } from 'main.core';
import { BannerDispatcher } from 'ui.banner-dispatcher';
import { NotificationPanel } from 'ui.notification-panel';
import { Icon, Main } from 'ui.icon-set.api.core';
import { Button } from 'ui.buttons';
import { sendData } from 'ui.analytics';
import 'ui.icon-set.main';

export class MarketExpiredCurtain
{
	constructor(id: string)
	{
		this.id = id;
	}

	#getPanel(onDone: function): NotificationPanel
	{
		const panel = new NotificationPanel({
			content: Loc.getMessage('REST_SIDEPANEL_WRAPPER_MARKET_EXPIRED_NOTIFICATION_TEXT'),
			backgroundColor: '#E89B06',
			textColor: '#FFFFFF',
			crossColor: '#FFFFFF',
			leftIcon: new Icon({
				icon: Main.MARKET_1,
				color: '#FFFFFF',
			}),
			rightButtons: [
				new Button({
					text: Loc.getMessage('REST_SIDEPANEL_WRAPPER_MARKET_EXPIRED_NOTIFICATION_BUTTON_TEXT'),
					size: Button.Size.EXTRA_SMALL,
					color: Button.Color.CURTAIN_WARNING,
					tag: Button.Tag.LINK,
					noCaps: true,
					round: true,
					props: {
						href: 'FEATURE_PROMOTER=limit_v2_nosubscription_marketplace_withapplications_off',
					},
					onclick: () => {
						panel.hide();
						this.#sendAnalytics('click_button');
					},
				}),
			],
			events: {
				onHide: () => {
					onDone();
					BX.userOptions.save('rest', `marketTransitionCurtain${this.id}Ts`, null, Math.floor(Date.now() / 1000));
				},
			},
			zIndex: 1001,
		});

		return panel;
	}

	show(): void
	{
		BannerDispatcher.critical.toQueue((onDone) => {
			const panel = this.#getPanel(onDone);
			panel.show();
			this.#sendAnalytics('show_notification_panel');
		});
	}

	#sendAnalytics(event: string): void
	{
		const params = {
			tool: 'infohelper',
			category: 'market',
			event,
			type: 'notification_panel',
		};

		sendData(params);
	}
}
