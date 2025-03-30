import { Tag, Loc } from 'main.core';

export class DiscountEar
{
	render(): HTMLElement
	{
		return Tag.render`
			<aside class="rest-market-expired-popup__discount">
				<p class="rest-market-expired-popup__discount-description">
					${Loc.getMessage('REST_MARKET_EXPIRED_POPUP_DISCOUNT_DESCRIPTION', {
						'[white-span]': '<span class="rest-market-expired-popup__discount-description-white">',
						'[/white-span]': '</span>',
					})}
				</p>
			</aside>
		`;
	}
}
