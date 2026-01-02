import { Loc, Tag, Type, Dom } from 'main.core';
import { MarketExpiredPopup } from './market-expired-popup';
import { TrialButton } from '../button/trial-button';
import { SubscribeButton } from '../button/subscribe-button';
import { HideButton } from '../button/hide-button';
import PopupType from '../type/popup-type';
import PopupCategory from '../type/popup-category';

export class MarketTransitionPopup extends MarketExpiredPopup
{
	getCategory(): PopupCategory
	{
		return PopupCategory.TRANSITION;
	}

	getTitle(): string
	{
		return Loc.getMessage(`REST_MARKET_EXPIRED_POPUP_TITLE_${this.type}`);
	}

	renderDescription(): HTMLElement
	{
		const descriptionContainer = Tag.render`
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
			</div>
		`;

		if (this.type === 'FINAL')
		{
			Dom.append(
				Tag.render`
					<p class="rest-market-expired-popup__description-text">
						${Loc.getMessage(`REST_MARKET_EXPIRED_POPUP_DESCRIPTION_FINAL${this.marketLabel}`)}
					</p>
				`,
				descriptionContainer,
			);
		}

		Dom.append(
			Tag.render`
				<p class="rest-market-expired-popup__description-text">
					${Loc.getMessage(
						`REST_MARKET_EXPIRED_POPUP_${this.type}_DESCRIPTION${this.withDemo ? '_DEMO' : ''}`,
						{
							'#DATE#': this.expireDate,
						},
					)}
				</p>
			`,
			descriptionContainer,
		);

		return descriptionContainer;
	}

	renderButtons(): HTMLElement
	{
		if (this.type === PopupType.WARNING)
		{
			return this.#renderButtonsForWarning();
		}

		return this.#renderButtonsForFinal();
	}

	show(): void
	{
		super.show();

		if (
			Type.isStringFilled(this.olWidgetCode)
			&& (!this.withDemo || this.type === 'FINAL')
		)
		{
			this.#showOpenLinesWidget(window, document, `https://bitrix24.team/upload/crm/site_button/loader_${this.olWidgetCode}.js`);
		}
	}

	onClose(): void
	{
		BX.SiteButton?.hide();
		BX.userOptions.save('rest', 'marketTransitionPopupTs', null, Math.floor(Date.now() / 1000));
		super.onClose();
	}

	renderAboutLink(): HTMLElement
	{
		const onclick = () => {
			this.getAnalytic()?.sendClickButton('details');
		};

		return Tag.render`
			<span class="rest-market-expired-popup__details">
				<a
					class="ui-link rest-market-expired-popup__link"
					href="FEATURE_PROMOTER=${this.#getFeatureCode()}"
					onclick="${onclick}"
				>
					${Loc.getMessage('REST_MARKET_EXPIRED_POPUP_DETAILS')}
				</a>
			</span>
		`;
	}

	#getDemoButton(): TrialButton
	{
		return new TrialButton({
			text: Loc.getMessage('REST_MARKET_EXPIRED_POPUP_BUTTON_DEMO'),
			onSuccess: this.close.bind(this),
			analytic: this.getAnalytic(),
		});
	}

	#getSubscribeButton(): SubscribeButton
	{
		return new SubscribeButton({
			text: Loc.getMessage('REST_MARKET_EXPIRED_POPUP_BUTTON_SUBSCRIBE'),
			analytic: this.getAnalytic(),
		});
	}

	#getHideButton(): HideButton
	{
		return new HideButton({
			text: Loc.getMessage('REST_MARKET_EXPIRED_POPUP_BUTTON_HIDE'),
			onSuccess: () => {
				BX.userOptions.save('rest', 'marketTransitionPopupDismiss', null, 'Y');
				this.close();
			},
			analytic: this.getAnalytic(),
		});
	}

	#renderButtonsForWarning(): HTMLElement
	{
		return Tag.render`
			<div class="rest-market-expired-popup__buttons-wrapper">
				<div class="rest-market-expired-popup__button-container">
					${this.#getSubscribeButton().render()}
					${this.withDemo ? this.#getDemoButton().render() : ''}
				</div>
			</div>
		`;
	}

	#renderButtonsForFinal(): HTMLElement
	{
		if (this.withDemo)
		{
			return Tag.render`
				<div class="rest-market-expired-popup__buttons-wrapper">
					${this.#getSubscribeButton().render()}
					<div class="rest-market-expired-popup__button-container">
						${this.#getDemoButton().render()}
						${this.#getHideButton().render()}
					</div>
				</div>
			`;
		}

		return Tag.render`
			<div class="rest-market-expired-popup__buttons-wrapper">
				<div class="rest-market-expired-popup__button-container">
					${this.#getSubscribeButton().render()}
					${this.#getHideButton().render()}
				</div>
			</div>
		`;
	}

	/**
	 * limit_v2_nosubscription_marketplace_withapplications_off
	 * limit_v2_nosubscription_marketplace_withapplications_off_no_demo
	 * limit_v2_nosubscription_marketplace_withapplications_nodiscount_off
	 * limit_v2_nosubscription_marketplace_withapplications_nodiscount_off_no_demo
	 */
	#getFeatureCode(): string
	{
		return `
			limit_v2_nosubscription_marketplace_withapplications
			${this.withDiscount ? '' : '_nodiscount'}
			_off
			${this.withDemo ? '' : '_no_demo'}
		`;
	}

	#showOpenLinesWidget(w, d, u): void
	{
		// eslint-disable-next-line unicorn/prefer-math-trunc
		const s = d.createElement('script'); s.async = true; s.src = `${u}?${Date.now() / 60000 | 0}`;
		// eslint-disable-next-line @bitrix24/bitrix24-rules/no-native-dom-methods
		const h = d.getElementsByTagName('script')[0]; h.parentNode.insertBefore(s, h);
	}
}
