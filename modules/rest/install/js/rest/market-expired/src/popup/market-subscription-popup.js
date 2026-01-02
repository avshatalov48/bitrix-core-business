import { Loc, Tag } from 'main.core';
import { MarketExpiredPopup } from './market-expired-popup';
import { SubscribeButton } from '../button/subscribe-button';
import { HideButton } from '../button/hide-button';
import PopupCategory from '../type/popup-category';
import PopupType from '../type/popup-type';

export class MarketSubscriptionPopup extends MarketExpiredPopup
{
	getCategory(): PopupCategory
	{
		return PopupCategory.SUBSCRIPTION;
	}

	getTitle(): string
	{
		const replacements = { '#DAYS#': this.expireDays };
		const marketLabel = this.type === PopupType.WARNING ? this.marketLabel : '';

		return Loc.getMessage(`REST_MARKET_EXPIRED_POPUP_TITLE_SUBSCRIPTION_${this.type}${marketLabel}`, replacements);
	}

	renderDescription(): HTMLElement
	{
		const replacements = { '#DATE#': this.expireDate };
		const marketLabel = this.type === PopupType.FINAL ? this.marketLabel : '';

		return Tag.render`
			<div class="rest-market-expired-popup__description">
				<p class="rest-market-expired-popup__description-text">
					${
						Loc.getMessage(`REST_MARKET_EXPIRED_POPUP_DESCRIPTION_SUBSCRIPTION_${this.type}${marketLabel}`, replacements)
					}
				</p>
			</div>
		`;
	}

	renderButtons(): HTMLElement
	{
		return Tag.render`
			<div class="rest-market-expired-popup__buttons-wrapper">
				<div class="rest-market-expired-popup__button-container">
					${this.#getSubscribeButton().render()}
					${this.#getHideButton().render()}
				</div>
			</div>
		`;
	}

	onOpen(): void
	{
		if (this.type === PopupType.FINAL)
		{
			BX.userOptions.save('rest', 'marketSubscriptionPopupDismiss', null, 'Y');
		}
		else
		{
			BX.userOptions.save('rest', 'marketSubscriptionPopupTs', null, Math.floor(Date.now() / 1000));
		}

		super.onOpen();
	}

	renderAboutLink(): HTMLElement
	{
		const onclick = () => {
			BX.Helper.show('redirect=detail&code=17451118');
			this.getAnalytic()?.sendClickButton('details');
		};

		return Tag.render`
			<span class="rest-market-expired-popup__details">
				<a
					class="ui-link rest-market-expired-popup__link"
					href="#"
					onclick="${onclick}"
				>
					${Loc.getMessage('REST_MARKET_EXPIRED_POPUP_DETAILS')}
				</a>
			</span>
		`;
	}

	#getSubscribeButton(): SubscribeButton
	{
		return new SubscribeButton({
			text: Loc.getMessage('REST_MARKET_EXPIRED_POPUP_BUTTON_RENEW_SUBSCRIPTION'),
			analytic: this.getAnalytic(),
		});
	}

	#getHideButton(): HideButton
	{
		return new HideButton({
			text: Loc.getMessage('REST_MARKET_EXPIRED_POPUP_BUTTON_NEVER_SHOW_AGAIN'),
			onSuccess: () => {
				BX.userOptions.save('rest', 'marketSubscriptionPopupDismiss', null, 'Y');
				this.close();
			},
			analytic: this.getAnalytic(),
		});
	}
}
