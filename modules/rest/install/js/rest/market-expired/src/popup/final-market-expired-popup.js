import { Loc, Tag, Type } from 'main.core';
import { MarketExpiredPopup } from './market-expired-popup';

export class FinalMarketExpiredPopup extends MarketExpiredPopup
{
	getType(): string
	{
		return 'final';
	}

	getTitle(): string
	{
		return Loc.getMessage('REST_MARKET_EXPIRED_POPUP_TITLE_FINAL');
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
					${Loc.getMessage('REST_MARKET_EXPIRED_POPUP_DESCRIPTION_FINAL')}
				</p>
				<p class="rest-market-expired-popup__description-text">
					${Loc.getMessage(`REST_MARKET_EXPIRED_POPUP_FINAL_DESCRIPTION${this.withDemo ? '_DEMO' : ''}`)}
				</p>
			</div>
		`;
	}

	renderButtons(): ?HTMLElement
	{
		if (this.withDemo)
		{
			return Tag.render`
				<div class="rest-market-expired-popup__buttons-wrapper">
					${this.getSubscribeButton().render()}
					<div class="rest-market-expired-popup__button-container">
						${this.getDemoButton().render()}
						${this.getHideButton().render()}
					</div>
				</div>
			`;
		}

		return Tag.render`
			<div class="rest-market-expired-popup__button-container">
				${this.getSubscribeButton().render()}
				${this.getHideButton().render()}
			</div>
		`;
	}
}
