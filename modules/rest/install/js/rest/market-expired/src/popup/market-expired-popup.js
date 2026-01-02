import { Tag, Dom } from 'main.core';
import { PopupWindowManager } from 'main.popup';
import { MarketList } from '../component/market-list';
import { EventEmitter } from 'main.core.events';
import { DiscountEar } from '../component/discount-ear';
import 'main.polyfill.intersectionobserver';
import { Analytic } from '../analytic';
import PopupType from '../type/popup-type';
import PopupCategory from '../type/popup-category';

export type MarketExpiredPopupOptions = {
	expireDate: string,
	appList: ?MarketList,
	integrationList: ?MarketList,
	marketSubscriptionUrl: string,
	withDemo: boolean,
	olWidgetCode: string,
	analytic: Analytic,
	type: PopupType,
	discountEar: ?DiscountEar;
	expireDays: string;
	isRenamedMarket: boolean;
};

export class MarketExpiredPopup extends EventEmitter
{
	expireDate: string;
	expireDays: string;
	#popup: ?Popup = null;
	#container: ?HTMLElement = null;
	#appList: ?MarketList;
	#integrationList: ?MarketList;
	withDemo: boolean;
	olWidgetCode: string;
	#analytic: Analytic;
	discountEar: ?DiscountEar;
	marketLabel: boolean;

	constructor(options: MarketExpiredPopupOptions)
	{
		super();
		this.setEventNamespace('Rest.MarketExpired:Popup');
		this.expireDate = options.expireDate;
		this.#appList = options.appList;
		this.#integrationList = options.integrationList;
		this.withDemo = options.withDemo;
		this.olWidgetCode = options.olWidgetCode;
		this.#analytic = options.analytic;
		this.type = options.type;
		this.expireDays = options.expireDays;
		this.discountEar = options.discountEar;
		this.marketLabel = options.isRenamedMarket ? '' : '_MARKET_PLUS';
	}

	getTitle(): string
	{
		throw new Error('Not implemented');
	}

	getCategory(): PopupCategory
	{
		throw new Error('Not implemented');
	}

	show(): void
	{
		this.#popup ??= PopupWindowManager.create(
			`marketExpiredPopup_${this.getCategory()}_${this.type}`,
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
				closeByEsc: true,
				events: {
					onClose: this.onClose.bind(this),
					onShow: this.onOpen.bind(this),
				},
			},
		);

		const observerCallback = (entries, observer) => {
			entries.forEach((entry) => {
				if (!entry.isIntersecting)
				{
					this.#popup.setDisableScroll(false);
					observer.unobserve(entry.target);
				}
			});
		};
		const observer = new IntersectionObserver(observerCallback, {
			root: null,
			rootMargin: '0px',
			threshold: [0, 1],
		});
		observer.observe(this.#popup.getContentContainer().querySelector('.rest-market-expired-popup__close-icon'));
		observer.observe(this.#popup.getContentContainer().querySelector('.rest-market-expired-popup__button-container'));

		this.#popup?.show();
		this.#analytic?.sendShow();

		// hack for blur
		if (this.discountEar)
		{
			if (this.#popup.getContentContainer().querySelector('.rest-market-expired-popup__content-wrapper').offsetHeight < window.innerHeight)
			{
				Dom.style(
					this.#getContainer(),
					{
						maxHeight: `${this.discountEar.getContainer().offsetHeight}px`,
					},
				);
			}
			else
			{
				Dom.style(
					this.#popup.getContentContainer().parentNode,
					{
						'backdrop-filter': 'none',
						'-webkit-backdrop-filter': 'none',
					},
				);
			}

			this.#popup.adjustPosition();
		}
	}

	close(): void
	{
		this.#popup.close();
	}

	onClose(): void
	{
		this.emit('onClose');
	}

	onOpen(): void
	{
		this.emit('onOpen');
	}

	renderDescription(): HTMLElement
	{
		return null;
	}

	renderButtons(): HTMLElement
	{
		return null;
	}

	renderAboutLink(): HTMLElement
	{
		return '';
	}

	getAnalytic(): ?Analytic
	{
		return this.#analytic;
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
				${this.discountEar?.getContainer()}
				<div class="rest-market-expired-popup__content-wrapper">
					<div class="rest-market-expired-popup__content">
						<span class="rest-market-expired-popup__title">${this.getTitle()}</span>
						${this.renderDescription()}
						${this.renderAboutLink()}
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

	#renderMarketList(): HTMLElement
	{
		return Tag.render`
			<aside class="rest-market-expired-popup__aside">
				${this.#appList?.render()}
				${this.#integrationList?.render()}
			</aside>
		`;
	}
}
