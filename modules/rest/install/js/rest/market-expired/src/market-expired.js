import { MarketItem } from './component/market-item';
import { MarketList } from './component/market-list';
import { MarketExpiredPopup } from './popup/market-expired-popup';
import { ajax, Extension, Loc } from 'main.core';
import './style.css';
import { FinalMarketExpiredPopup } from './popup/final-market-expired-popup';
import { WarningMarketExpiredPopup } from './popup/warning-market-expired-popup';
import { Analytic } from './analytic';

export {
	MarketItem,
	MarketList,
	WarningMarketExpiredPopup,
	FinalMarketExpiredPopup,
};

const POPUPS = {
	WARNING: WarningMarketExpiredPopup,
	FINAL: FinalMarketExpiredPopup,
};

export class MarketExpired
{
	static async getPopup(): ?MarketExpiredPopup
	{
		const getMarketListFromResponse = (response, moreLink, title, onClick): ?MarketList => {
			if (!response || !response.data)
			{
				return null;
			}

			const { items, count } = response.data;
			const marketList = [];

			if (items.length === 0 || count < 1)
			{
				return null;
			}

			Object.values(items).forEach((item) => {
				marketList.push(new MarketItem({
					name: item.name,
					icon: item.icon,
				}));
			});

			return new MarketList({
				title,
				count,
				items: marketList,
				link: moreLink,
				onClick,
			});
		};

		const options = Extension.getSettings('rest.market-expired');
		let popup = null;

		await Promise.all([
			ajax.runAction('rest.integration.getApplicationList', { data: { limit: 3 } }),
			ajax.runAction('rest.integration.getIntegrationList', { data: { limit: 3 } }),
		]).then(([appsResponse, integrationsResponse]) => {
			const analytic = new Analytic({
				withDiscount: options.withDiscount,
				popupType: options.type,
			});

			const appList = getMarketListFromResponse(
				appsResponse,
				'/market/installed/',
				Loc.getMessage('REST_MARKET_EXPIRED_POPUP_MARKET_LIST_TITLE_APPS'),
				() => {
					analytic.sendClickButton('view_all_apps');
				},
			);
			const integrationList = getMarketListFromResponse(
				integrationsResponse,
				'/devops/list/',
				Loc.getMessage('REST_MARKET_EXPIRED_POPUP_MARKET_LIST_TITLE_INTEGRATIONS'),
				() => {
					analytic.sendClickButton('view_all_integrations');
				},
			);

			if (appList || integrationList)
			{
				const PopupClass = POPUPS[options.type];

				if (PopupClass)
				{
					popup = new PopupClass({
						transitionPeriodEndDate: options.transitionPeriodEndDate,
						appList,
						integrationList,
						marketSubscriptionUrl: options.marketSubscriptionUrl,
						withDiscount: options.withDiscount,
						withDemo: options.withDemo,
						olWidgetCode: options.olWidgetCode,
						analytic,
					});
				}
			}
		}).catch((error) => {
			console.log(error);
		});

		return popup;
	}
}
