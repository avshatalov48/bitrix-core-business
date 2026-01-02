import { BannerDispatcher } from 'ui.banner-dispatcher';
import { NotificationPanel } from 'ui.notification-panel';
import { Icon, Main } from 'ui.icon-set.api.core';
import { sendData } from 'ui.analytics';
import 'ui.icon-set.main';
import CurtainPage from '../type/curtain-page';
import PopupType from '../type/popup-type';
import { Tag } from 'main.core';

export type MarketExpiredCurtainOptions = {
	marketSubscriptionUrl: string,
	type: PopupType,
	expireDays: string;
	curtainPage: CurtainPage;
};

export class MarketExpiredCurtain
{
	#panel: NotificationPanel = null;

	constructor(options: MarketExpiredCurtainOptions)
	{
		this.options = options;
	}

	#getPanel(onDone: function): NotificationPanel
	{
		this.#panel ??= new NotificationPanel({
			content: Tag.render`
				<span class="rest-market-expired-curtain">${this.getContent()}</span>
			`,
			backgroundColor: '#E89B06',
			crossColor: '#FFFFFF',
			leftIcon: new Icon({
				icon: Main.MARKET_1,
				color: '#FFFFFF',
			}),
			rightButtons: this.getRightButtons(),
			events: {
				onHide: () => {
					onDone();
					this.onHide();
				},
			},
			zIndex: 1001,
		});

		return this.#panel;
	}

	show(): void
	{
		BannerDispatcher.critical.toQueue((onDone) => {
			const panel = this.#getPanel(onDone);
			panel.show();
			this.#sendAnalytics('show_notification_panel');
		});
	}

	getRightButtons(): []
	{
		return [];
	}

	getContent(): string
	{
		throw new Error('Not Implemented');
	}

	onRightButtonClick(): void
	{
		this.#getPanel().hide();
		this.#sendAnalytics('click_button');
	}

	onHide(): void
	{}

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
