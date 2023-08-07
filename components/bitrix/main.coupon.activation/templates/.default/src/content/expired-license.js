import BaseContent from "./base-content";
import {Loc, Tag, Type} from "main.core";
import {Button} from "ui.buttons";
import {EventEmitter} from "main.core.events";
import {Popup} from "main.popup";
import {Activate} from "./activate";
import {Request} from "../request";
import {Loading} from "./loader";
import {Partner} from "./partner";

export class ExpiredLicense extends BaseContent
{
	static stateTypes = {
		LICENSE_EXPIRED: 'license_expired',
		LICENSE_ACTIVATED: 'license_activated',
		UPDATE_SERVER_IS_UNAVAILABLE: 'update_server_is_unavailable',
	};

	#buyLink: string;

	#partnerId: number;

	#state: ?string = null;

	#parameters: Object = [];

	constructor(buyLink: string, partnerId: number = 0, parameters: Object = [])
	{
		super();
		this.#partnerId = partnerId;
		this.#buyLink = buyLink?.length > 0 ? buyLink : 'https://www.1c-bitrix.ru/personal/order/basket.php';
		this.#parameters = parameters;
	}

	getContent(): HTMLElement
	{
		return Tag.render`
			<div class="license-intranet-popup__content --access-closed">
				<div class="license-intranet-popup__block">
					<div class="license-intranet-popup__title">${Loc.getMessage('MAIN_COUPON_ACTIVATION_LICENSE_OVER_TITLE')}</div>
					<div class="license-intranet-popup__content-area">
						<p class="license-intranet-popup__text ui-typography-text-lg">${Loc.getMessage('MAIN_COUPON_ACTIVATION_LICENSE_OVER_DESCRIPTION')}</p>
						<div class="license-intranet-popup__buttons">
							<div class="license-intranet-popup__button --renew-license">
								${this.#createBuyBtn().render()}
							</div>
							<div class="license-intranet-popup__button">
								${this.#createPartnerBtn().render()}
							</div>
						</div>
						<a class="license-intranet-popup__help-link"
							target="_blank"
							href="${this.#parameters.DOC_LINK}"
						>
				${Loc.getMessage('MAIN_COUPON_ACTIVATION_BUTTON_NEED_HELP')}
			</a>
					</div>
				</div>
			</div>
		`;
	}

	getButtonCollection(): Array
	{
		return [];
	}

	#createPartnerBtn(): Button
	{
		return new Button({
			text: Loc.getMessage('MAIN_COUPON_ACTIVATION_BUTTON_PARTNER'),
			noCaps: false,
			round: true,
			size: BX.UI.Button.Size.LARGE,
			color: BX.UI.Button.Color.LIGHT_BORDER,
			tag: BX.UI.Button.Tag.BUTTON,
			onclick: () => {
				EventEmitter.emit(EventEmitter.GLOBAL_TARGET, 'MainCouponActivation:changeContent', {
					source: this,
					target: new Partner(this.#parameters)
				});
			},
		});
	}

	#createBuyBtn(): Button
	{
		return new Button({
			text: Loc.getMessage('MAIN_COUPON_ACTIVATION_BUTTON_RENEW'),
			noCaps: false,
			round: true,
			link: this.#buyLink,
			size: BX.UI.Button.Size.LARGE,
			color: BX.UI.Button.Color.SUCCESS,
			tag: BX.UI.Button.Tag.LINK,
			props: {
				target: '_blank'
			}
		});
	}

	#checkRequest(): void
	{
		const request = new Request('check');
		request.send().then(this.successHandler.bind(this), this.failureHandler.bind(this));

		EventEmitter.emit(EventEmitter.GLOBAL_TARGET, 'MainCouponActivation:changeContent', {
			source: this,
			target: new Loading()
		});
	}

	init(popup: Popup): void
	{
		if (Type.isNil(this.#state))
		{
			this.#checkRequest();
		}
		else if (this.#state === ExpiredLicense.stateTypes.LICENSE_ACTIVATED)
		{
			document.location.href = '/';
		}
		else
		{
			popup.setContent(this.getContent());
		}
	}

	successHandler(response): void
	{
		const expireDate = new Date(response.data.DATE_TO_SOURCE);
		if (!Type.isNil(response.data.DATE_TO_SOURCE) && expireDate.getTime() > (new Date).getTime())
		{
			this.#state = ExpiredLicense.stateTypes.LICENSE_ACTIVATED;
		}
		else
		{
			this.#state = ExpiredLicense.stateTypes.LICENSE_EXPIRED;
		}

		EventEmitter.emit(EventEmitter.GLOBAL_TARGET, 'MainCouponActivation:back', {
			source: this,
		});
	}

	failureHandler(response): void
	{
		this.#state = ExpiredLicense.stateTypes.UPDATE_SERVER_IS_UNAVAILABLE;

		let errors = Type.isArray(response.errors) ? response.errors : [];
		// let errors = [];
		EventEmitter.emit(EventEmitter.GLOBAL_TARGET, 'MainCouponActivation:changeContent', {
			source: this,
			target: new Activate(this.#parameters.SUPPORT_LINK, this.#parameters.DOC_LINK, errors)
		});
	}
}