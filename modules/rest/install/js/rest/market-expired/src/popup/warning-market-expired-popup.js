import { Loc, Tag } from 'main.core';
import { MarketExpiredPopup } from './market-expired-popup';

export class WarningMarketExpiredPopup extends MarketExpiredPopup
{
	getType(): string
	{
		return 'warning';
	}

	renderDescription(): HTMLElement
	{
		return Tag.render`
			<div class="rest-market-expired-popup__description">
				<p class="rest-market-expired-popup__description-text">
					${Loc.getMessage('REST_MARKET_EXPIRED_POPUP_DESCRIPTION_1')}
				</p>
				<p class="rest-market-expired-popup__description-text">
					${Loc.getMessage('REST_MARKET_EXPIRED_POPUP_DESCRIPTION_2')}
				</p>
				<p class="rest-market-expired-popup__description-text">
					${Loc.getMessage('REST_MARKET_EXPIRED_POPUP_DESCRIPTION_3')}
				</p>
				<p class="rest-market-expired-popup__description-text">
					${Loc.getMessage(`REST_MARKET_EXPIRED_POPUP_WARNING_DESCRIPTION${this.withDemo ? '_DEMO' : ''}`, {
						'#DATE#': this.transitionPeriodEndDate,
					})}
				</p>
			</div>
		`;
	}

	getTitle(): string
	{
		return Loc.getMessage('REST_MARKET_EXPIRED_POPUP_TITLE_WARNING');
	}

	renderButtons(): ?HTMLElement
	{
		return Tag.render`
			<div class="rest-market-expired-popup__button-container">
				${this.getSubscribeButton().render()}
				${this.withDemo ? this.getDemoButton().render() : ''}
			</div>
		`;
	}
}
