import { Dom, Tag, Type, Loc, Event } from 'main.core';
import { Popup } from 'main.popup';
import { sendData } from 'ui.analytics';
import { Lottie } from 'ui.lottie';
import 'main.qrcode';
import './style.css';

export class MobilePromoter
{
	constructor(options)
	{
		this.target = Type.isElementNode(options.target) ? options.target : window;
		this.qrContent = Type.isString(options.qrContent) ? options.qrContent : '';
		this.position = Type.isObject(options.position) ? options.position : null;
		this.className = Type.isString(options.className) ? options.className : null;
		this.node = {
			content: null,
			phoneBg: null,
			qr: null,
			qrOverlay: null,
		};
		this.title = Type.isString(options.title) ? options.title : Loc.getMessage('UI_MOBILE_PROMOTER_TITLE');
		this.content = Type.isElementNode(options.content) ? options.content : null;
		this.analytics = Type.isObject(options.analytics) ? options.analytics : null;
		this.analyticsParameters = {
			tool: 'intranet',
			category: 'activation',
		};

		this.init();
	}

	getPopup(): Popup
	{
		if (!this.popup)
		{
			this.popup = new Popup({
				bindElement: this.target,
				borderRadius: '32px 32px 32px 32px',
				padding: 0,
				contentPadding: 0,
				closeIcon: {
					top: '19px',
					right: '19px',
				},
				content: this.getContent(),
				className: `ui-mobile-promoter__popup ${this.className} --qr-hide`,
				animation: 'fading-slide',
				maxWidth: 550,
				minHeight: 330,
				autoHide: true,
				overlay: {
					backgroundColor: 'black',
					opacity: 15,
				},
				events: {
					onPopupShow: () => {
						// this.getAnimation().play();

					},
					onAfterShow: () => {
						this.setAnalyticParameters({
							event: 'qrcode_view',
						});
						this.sendAnalytics();
					},
					onPopupClose: () => {
						// this.getAnimation().stop();
						Dom.removeClass(this.getQrBg(), '--qr-animation');
					},
					onAfterClose: () => {
						this.setAnalyticParameters({
							event: 'qrcode_close',
						});
						this.sendAnalytics();
					},
				},
			});
		}

		return this.popup;
	}

	getAnimation(): Lottie
	{
		if (!this.lottieAnim)
		{
			this.lottieAnim = Lottie.loadAnimation({
				autoplay: false,
				container: this.getPhoneBg(),
				renderer: 'svg',
				path: '/bitrix/js/ui/mobile-promoter/animation/invitation.json',
				loop: false,
				name: 'invitation-anim',
			});
		}

		return this.lottieAnim;
	}

	getQrBg(): HTMLElement
	{
		if (!this.node.qr)
		{
			this.node.qr = Tag.render`
				<div class="ui-mobile-promoter__popup-qr"></div>
			`;
		}

		return this.node.qr;
	}

	getPhoneBg(): HTMLElement
	{
		if (!this.node.phoneBg)
		{
			this.node.phoneBg = Tag.render`
				<div class="ui-mobile-promoter__popup-phone"></div>
			`;
		}

		return this.node.phoneBg;
	}

	getDefaultContent(): HTMLElement
	{
		return Tag.render`
			<ul class="ui-mobile-promoter__popup-list">
				<li class="ui-mobile-promoter__popup-list-item">${Loc.getMessage('UI_MOBILE_PROMOTER_LIST_ITEM_1')}</li>
				<li class="ui-mobile-promoter__popup-list-item">${Loc.getMessage('UI_MOBILE_PROMOTER_LIST_ITEM_2')}</li>
				<li class="ui-mobile-promoter__popup-list-item">${Loc.getMessage('UI_MOBILE_PROMOTER_LIST_ITEM_3')}</li>
			</ul>
			<div class="ui-mobile-promoter__popup-list-afterlist">
				<div class="ui-mobile-promoter__popup-desc">${Loc.getMessage('UI_MOBILE_PROMOTER_DESC')}</div>
				<div class="ui-mobile-promoter__popup-info">${Loc.getMessage('UI_MOBILE_PROMOTER_INFO')}</div>
			</div>
		`;
	}

	showQrCode()
	{
		Dom.removeClass(this.getContent(), '--qr-hide');
		Dom.addClass(this.getQrOverlay(), '--hide');
		Dom.addClass(this.getQrBg(), '--qr-animation');
	}

	getQrOverlay(): HTMLElement
	{
		if (!this.node.qrOverlay)
		{
			const qrShowButton = Tag.render`
				<span class="ui-btn --air ui-btn-no-caps --style-tinted ui-btn-xs">
					<span class="ui-btn-text">
						<span class="ui-btn-text-inner">${Loc.getMessage('UI_MOBILE_PROMOTER_SHOW_QR')}</span>
					</span>
				</span>
			`;

			Event.bind(qrShowButton, 'click', () => this.showQrCode());

			this.node.qrOverlay = Tag.render`
				<div class="ui-mobile-promoter__popup-qr-overlay">
					<div class="ui-mobile-promoter__popup-qr-overlay-text">
						${qrShowButton}
					</div>
				</div>
			`;

			Event.bind(this.node.qrOverlay, 'transitionend', () => {
				Dom.remove(this.node.qrOverlay);
			});
		}

		return this.node.qrOverlay;
	}

	getContent(): HTMLElement
	{
		if (!this.node.content)
		{
			this.node.content = Tag.render`
				<div class="ui-mobile-promoter__popup-wrap --qr-hide">
					<div class="ui-mobile-promoter__popup-phone-box">
						${this.getPhoneBg()}
						${this.getQrOverlay()}
						${this.getQrBg()}
					</div>
					<div class="ui-mobile-promoter__popup-content">
						<div class="ui-mobile-promoter__popup-title">${this.title}</div>
						<div class="ui-mobile-promoter__popup-content">
							${this.content ?? this.getDefaultContent()}
						</div>
					</div>
				</div>
			`;
		}

		return this.node.content;
	}

	init(): void
	{
		// eslint-disable-next-line no-undef
		new QRCode(this.getQrBg(), {
			text: this.qrContent,
			width: 123,
			height: 123,
			colorDark: '#000000',
			colorLight: '#ffffff',
		});

		if (this.analytics)
		{
			this.setAnalyticParameters(this.analytics);
		}

		if (this.position)
		{
			this.getPopup().setFixed(true);
			Dom.addClass(this.getPopup().getPopupContainer(), '--right-bottom');
			Object.entries(this.position).forEach(([key, value]) => {
				Dom.style(this.getPopup().getPopupContainer(), key, `${value}px`);
			});
		}
	}

	show(): void
	{
		this.getPopup().show();
	}

	close(): void
	{
		this.getPopup().close();
	}

	setAnalyticParameters(parameters: {[key: string]: string})
	{
		this.analyticsParameters = {
			...this.analyticsParameters,
			...parameters,
		};
	}

	sendAnalytics(): void
	{
		sendData(this.analyticsParameters);
	}
}
