import { Tag, Loc } from 'main.core';
import { DiscountEar } from './discount-ear';

export class DiscountEarSubscription extends DiscountEar
{
	constructor(props)
	{
		super(props);

		this.discountPercentage = props?.discountPercentage ?? null;
		this.termsUrl = props?.termsUrl ?? null;
		this.marketLabel = props?.marketLabel ?? '';
	}

	getContainer(): HTMLElement
	{
		this.container ??= Tag.render`
			<aside class="rest-market-expired-popup__discount rest-market-expired-popup__discount--subscription">
				${this.#renderDiscountPercent()}
				<p class="rest-market-expired-popup__discount-description">
					${Loc.getMessage(`REST_MARKET_EXPIRED_POPUP_DISCOUNT_SUBSCRIPTION_DESCRIPTION${this.marketLabel}`)}
				</p>
				${this.#renderTermsOfPromotion()}
			</aside>
		`;

		return this.container;
	}

	#renderDiscountPercent(): HTMLElement
	{
		if (this.discountPercentage)
		{
			return Tag.render`
				<p class="rest-market-expired-popup__discount-percentage">
					- ${this.discountPercentage}%
				</p>
			`;
		}

		return '';
	}

	#renderTermsOfPromotion(): HTMLElement
	{
		if (this.termsUrl)
		{
			return Tag.render`
				<a href="${this.termsUrl}" target="_blank" class="ui-link rest-market-expired-popup__discount-terms">
					${Loc.getMessage('REST_MARKET_EXPIRED_POPUP_TERMS_OF_PROMOTION')}
				</a>
			`;
		}

		return '';
	}
}
