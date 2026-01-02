import { MarketExpiredPopup } from './market-expired-popup';
import PopupCategory from '../type/popup-category';
import { MarketTrialPopup } from './market-trial-popup';
import { MarketSubscriptionPopup } from './market-subscription-popup';
import { Analytic } from '../analytic';
import type { DiscountEar } from '../component/discount-ear';
import { DiscountEarSubscription } from '../component/discount-ear-subscription';
import { DiscountEarTransition } from '../component/discount-ear-transition';
import { ajax, Loc } from 'main.core';
import type { PopupConfig } from '../type/popup-config';
import { MarketTransitionPopup } from './market-transition-popup';
import { MarketItem } from '../component/market-item';
import { MarketList } from '../component/market-list';

export class PopupFactory
{
	config: PopupConfig;

	constructor(config: PopupConfig)
	{
		this.config = config;
	}

	async createPopup(): ?MarketExpiredPopup
	{
		let popup = null;
		const analytic = this.#getAnalytic();
		const listItemCount = this.#getListItemCount();
		const { appList, integrationList } = await this.#getMarketList(listItemCount, analytic);

		if (appList || integrationList)
		{
			const discountEar = this.#getDiscountEar();
			const PopupClass = this.#getPopupClass();

			popup = new PopupClass({
				appList,
				integrationList,
				analytic,
				discountEar,
				expireDate: this.config.expireDate,
				marketSubscriptionUrl: this.config.marketSubscriptionUrl,
				withDemo: this.config.withDemo,
				olWidgetCode: this.config.olWidgetCode,
				type: this.config.type,
				expireDays: this.config.expireDays,
			});
		}

		return popup;
	}

	#getPopupClass(): string
	{
		switch (this.config.category)
		{
			case PopupCategory.TRIAL:
				return MarketTrialPopup;
			case PopupCategory.SUBSCRIPTION:
				return MarketSubscriptionPopup;
			case PopupCategory.TRANSITION:
			default:
				return MarketTransitionPopup;
		}
	}

	#getAnalytic(): Analytic
	{
		return new Analytic({
			withDiscount: this.config.discount?.isAvailable ?? false,
			popupType: this.config.type,
			popupCategory: this.config.category,
		});
	}

	#getListItemCount(): number
	{
		switch (this.config.category)
		{
			case PopupCategory.TRIAL:
			case PopupCategory.SUBSCRIPTION:
				return 2;

			case PopupCategory.TRANSITION:
			default:
				return 3;
		}
	}

	#getDiscountEar(): ?DiscountEar
	{
		const discountConfig = this.config.discount;

		if (!discountConfig?.isAvailable)
		{
			return null;
		}

		switch (this.config.category)
		{
			case PopupCategory.TRIAL:
			case PopupCategory.SUBSCRIPTION:
				return new DiscountEarSubscription({
					discountPercentage: discountConfig.percentage,
					termsUrl: discountConfig.termsUrl,
					marketLabel: this.config.isRenamedMarket ? '' : '_MARKET_PLUS',
				});

			case PopupCategory.TRANSITION:
			default:
				return new DiscountEarTransition();
		}
	}

	async #getMarketList(limit: number, analytic: Analytic)
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

		let appList = null;
		let integrationList = null;

		await Promise.all([
			ajax.runAction('rest.integration.getApplicationList', { data: { limit } }),
			ajax.runAction('rest.integration.getIntegrationList', { data: { limit } }),
		]).then(([appsResponse, integrationsResponse]) => {
			appList = getMarketListFromResponse(
				appsResponse,
				'/market/installed/',
				Loc.getMessage('REST_MARKET_EXPIRED_POPUP_MARKET_LIST_TITLE_APPS'),
				() => {
					analytic.sendClickButton('view_all_apps');
				},
			);
			integrationList = getMarketListFromResponse(
				integrationsResponse,
				'/devops/list/',
				Loc.getMessage('REST_MARKET_EXPIRED_POPUP_MARKET_LIST_TITLE_INTEGRATIONS'),
				() => {
					analytic.sendClickButton('view_all_integrations');
				},
			);
		}).catch((error) => {
			console.log(error);
		});

		return { appList, integrationList };
	}
}
