import { Loc, Tag, ajax, Dom, Type } from 'main.core';
import { PopupWindowManager } from 'main.popup';
import { Button } from 'ui.buttons';
import { MarketList } from '../component/market-list';
import { EventEmitter } from 'main.core.events';
import { DiscountEar } from '../component/discount-ear';
import { FeaturePromotersRegistry } from 'ui.info-helper';
import { UI } from 'ui.notification';
import { Analytic } from '../analytic';

export type MarketExpiredPopupOptions = {
	transitionPeriodEndDate: string,
	appList: ?MarketList,
	integrationList: ?MarketList,
	marketSubscriptionUrl: string,
	withDiscount: boolean,
	withDemo: boolean,
	olWidgetCode: string,
	analytic: Analytic,
};

export class MarketExpiredPopup extends EventEmitter
{
	transitionPeriodEndDate: string;
	#popup: ?Popup = null;
	#container: ?HTMLElement = null;
	#appList: ?MarketList;
	#integrationList: ?MarketList;
	#marketSubscriptionUrl: string;
	#withDiscount: boolean;
	withDemo: boolean;
	olWidgetCode: string;
	#discountEarContainer: ?HTMLElement = null;
	#analytic: Analytic;

	constructor(options: MarketExpiredPopupOptions)
	{
		super();
		this.setEventNamespace('Rest.MarketExpired:Popup');
		this.transitionPeriodEndDate = options.transitionPeriodEndDate;
		this.#appList = options.appList;
		this.#integrationList = options.integrationList;
		this.#marketSubscriptionUrl = options.marketSubscriptionUrl;
		this.#withDiscount = options.withDiscount;
		this.withDemo = options.withDemo;
		this.olWidgetCode = options.olWidgetCode;
		this.#analytic = options.analytic;
	}

	getType(): string
	{
		return '';
	}

	getTitle(): string
	{
		return '';
	}

	show(): void
	{
		this.#popup ??= PopupWindowManager.create(
			`marketExpiredPopup_${this.getType()}`,
			null,
			{
				animation: {
					showClassName: 'rest-market-expired-popup__show',
					closeAnimationType: 'animation',
				},
				overlay: true,
				content: this.#getContent(),
				disableScroll: true,
				padding: 0,
				className: 'rest-market-expired-popup-wrapper',
				events: {
					onClose: this.onClose.bind(this),
				},
			},
		);

		this.#popup?.show();
		this.#analytic?.sendShow();

		// hack for blur
		if (this.#withDiscount)
		{
			Dom.style(
				this.#getContainer(),
				{
					maxHeight: `${this.#getDiscountEarContainer().offsetHeight}px`,
				},
			);
			this.#popup.adjustPosition();
		}

		if (
			Type.isStringFilled(this.olWidgetCode)
			&& (!this.withDemo || this.getType() === 'FINAL')
		)
		{
			this.#showOlWidget(window, document, `https://bitrix24.team/upload/crm/site_button/loader_${this.olWidgetCode}.js`);
		}
	}

	onClose(): void
	{
		BX.SiteButton?.hide();
		this.emit('onClose');
		BX.userOptions.save('rest', 'marketTransitionPopupTs', null, Math.floor(Date.now() / 1000));
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
			${this.#withDiscount ? '' : '_nodiscount'}
			_off
			${this.withDemo ? '' : '_no_demo'}
		`;
	}

	renderDescription(): ?HTMLElement
	{
		return null;
	}

	renderButtons(): ?HTMLElement
	{
		return null;
	}

	#getContent(): HTMLElement
	{
		return Tag.render`
			<div class="rest-market-expired-popup">
				${this.#getContainer()}
			</div>
		`;
	}

	#getContainer(): HTMLElement
	{
		this.#container ??= Tag.render`
			<div class="rest-market-expired-popup__container">
				${this.#withDiscount ? this.#getDiscountEarContainer() : ''}
				<div class="rest-market-expired-popup__content-wrapper">
					<div class="rest-market-expired-popup__content">
						<span class="rest-market-expired-popup__title">${this.getTitle()}</span>
						${this.renderDescription()}
						${this.#renderAboutLink()}
						${this.renderButtons()}
					</div>
					${this.#renderMarketList()}
					${this.#renderCloseIcon()}
				</div>
			</div>
		`;

		return this.#container;
	}

	#renderCloseIcon(): HTMLElement
	{
		const onClick = () => {
			this.#popup.close();
			this.#analytic?.sendClickButton('cancel');
		};

		return Tag.render`
			<div class="rest-market-expired-popup__close-icon ui-icon-set --cross-30" onclick="${onClick}"></div>
		`;
	}

	#getDiscountEarContainer(): HTMLElement
	{
		this.#discountEarContainer ??= (new DiscountEar(this.withDemo)).render();

		return this.#discountEarContainer;
	}

	#renderMarketList(): HTMLElement
	{
		return Tag.render`
			<aside class="rest-market-expired-popup__aside">
				${this.#appList?.render()}
				${this.#integrationList?.render()}
			</aside>
		`;
	}

	#renderAboutLink(): HTMLElement
	{
		const onclick = () => {
			this.#analytic?.sendClickButton('details');
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

	getSubscribeButton(): Button
	{
		return new Button({
			text: Loc.getMessage('REST_MARKET_EXPIRED_POPUP_BUTTON_SUBSCRIBE'),
			className: 'rest-market-expired-popup__button',
			id: 'marketExpiredPopup_button_subscribe',
			size: Button.Size.MEDIUM,
			color: Button.Color.SUCCESS,
			noCaps: true,
			round: true,
			tag: Button.Tag.LINK,
			link: this.#marketSubscriptionUrl,
			onclick: () => {
				this.#analytic?.sendClickButton('buy');
			},
		});
	}

	getDemoButton(): Button
	{
		const demoButton = new Button({
			text: Loc.getMessage('REST_MARKET_EXPIRED_POPUP_BUTTON_DEMO'),
			className: 'rest-market-expired-popup__button',
			id: 'marketExpiredPopup_button_demo',
			size: Button.Size.MEDIUM,
			color: Button.Color.LIGHT_BORDER,
			noCaps: true,
			round: true,
			onclick: () => {
				demoButton.unbindEvent('click');
				demoButton.setState(Button.State.WAITING);
				this.#analytic?.sendClickButton('demo');
				ajax({
					url: '/bitrix/tools/rest.php',
					method: 'POST',
					dataType: 'json',
					data: {
						sessid: BX.bitrix_sessid(),
						action: 'activate_demo',
					},
					onsuccess: (result) => {
						this.#popup.close();

						if (result.error)
						{
							UI.Notification.Center.notify({
								content: result.error,
								category: 'demo_subscribe_error',
								position: 'top-right',
							});
						}
						else
						{
							this.#analytic?.sendDemoActivated();
							FeaturePromotersRegistry.getPromoter({ code: 'limit_market_trial_active' }).show();
						}
					},
				});
			},
		});

		return demoButton;
	}

	getHideButton(): Button
	{
		return new Button({
			text: Loc.getMessage('REST_MARKET_EXPIRED_POPUP_BUTTON_HIDE'),
			className: 'rest-market-expired-popup__button rest-market-expired-popup__button--link',
			id: 'marketExpiredPopup_button_hide',
			size: Button.Size.EXTRA_SMALL,
			color: Button.Color.LINK,
			noCaps: true,
			onclick: () => {
				this.#popup?.close();
				this.#analytic?.sendClickButton('ok');
				BX.userOptions.save('rest', 'marketTransitionPopupDismiss', null, 'Y');
			},
		});
	}

	#showOlWidget(w, d, u): void
	{
		const s = d.createElement('script'); s.async = true; s.src = `${u}?${Date.now() / 60000 | 0}`;
		const h = d.getElementsByTagName('script')[0]; h.parentNode.insertBefore(s, h);
	}
}
