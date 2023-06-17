import BaseContent from "./base-content";
import {Loc, Tag, Type} from "main.core";
import {Button} from "ui.buttons";
import {EventEmitter} from "main.core.events";
import {Popup} from "main.popup";
import { Alert, AlertColor, AlertIcon, AlertSize } from 'ui.alerts';
import {Loading} from "./loader";
import {Request} from "../request";
import {Success} from "./success";
import {ErrorCollection} from "../error-collection";
export class Partner extends BaseContent
{
	#formData: FormData;
	#errors: ErrorCollection;

	constructor(parameters: Object) {
		super();
		this.#errors = new ErrorCollection();
		this.#formData = new FormData();
		this.#formData.set('name', Type.isString(parameters?.NAME) ? parameters.NAME : '');
		this.#formData.set('phone', Type.isString(parameters?.PHONE) ? parameters.PHONE : '');
		this.#formData.set('email', Type.isString(parameters?.EMAIL) ? parameters.EMAIL : '');

	}
	getAlert(text: string): Alert
	{
		const alert = new Alert({
			color: AlertColor.DANGER,
			icon: AlertIcon.DANGER,
			size: AlertSize.SMALL
		});

		if (text)
		{
			alert.setText(text);
		}

		return alert.getContainer();
	}

	getContent(): HTMLElement
	{
		return Tag.render`
			<form id="main-coupon-activate-partner-form">
			<div id="intranet-license-partner-form">
				<div class="license-intranet-popup__content --partner">
					<div class="license-intranet-popup__block --center">
						<div class="license-intranet-popup__title">${Loc.getMessage('MAIN_COUPON_ACTIVATION_TITLE_PARTNER')}</div>
						<div class="license-intranet-popup__text ui-typography-text-lg">${Loc.getMessage('MAIN_COUPON_ACTIVATION_SUBTITLE_PARTNER')}</div>
					</div>
					
					<div class="license-intranet-popup__partner-form">
						<div class="license-intranet-popup__block --input-area">
							<div class="license-intranet-popup__input-label">${Loc.getMessage('MAIN_COUPON_ACTIVATION_NAME_FIELD')}</div>
							<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
								<input type="text" name="name" value="${this.#formData.get('name') ?? ''}" class="ui-ctl-element">
						   </div>
						</div>
						<div class="license-intranet-popup__block --input-area">
							<div class="license-intranet-popup__input-label">${Loc.getMessage('MAIN_COUPON_ACTIVATION_PHONE_FIELD')}</div>
							<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
								<input type="text" name="phone" value="${this.#formData.get('phone') ?? ''}" class="ui-ctl-element">
							</div>
						</div>
						<div class="license-intranet-popup__block --input-area">
							<div class="license-intranet-popup__input-label">${Loc.getMessage('MAIN_COUPON_ACTIVATION_EMAIL_FIELD')}</div>
							<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
								<input type="text" name="email" value="${this.#formData.get('email') ?? ''}" class="ui-ctl-element">
							</div>
						</div>
					</div>
					
				</div>
			</div>
			</form>
		`;
	}

	getSuccessContent(): HTMLElement
	{
		return Tag.render`
			<div id="intranet-license-partner-form">
				<div class="license-intranet-popup__content --partner-success">
					<div class="intranet-license-partner-form__success-icon"></div>
					<div class="license-intranet-popup__title">${Loc.getMessage('MAIN_COUPON_ACTIVATION_TITLE_PARTNER_SUCCESS')}</div>
				</div>
			</div>
		`
	}

	getButtonCollection(): Array
	{
		const sendBtn = new Button({
			text: Loc.getMessage('MAIN_COUPON_ACTIVATION_BUTTON_SEND'),
			noCaps: false,
			round: true,
			size: BX.UI.Button.Size.LARGE,
			color: BX.UI.Button.Color.SUCCESS,
			tag: BX.UI.Button.Tag.BUTTON,
			onclick: () => {
				const formNode = document.querySelector('#main-coupon-activate-partner-form');
				const formData = new FormData(formNode);
				this.#errors.hideErrors();
				this.#errors.cleanErrors();

				EventEmitter.emit(EventEmitter.GLOBAL_TARGET, 'MainCouponActivation:changeContent', {
					source: this,
					target: new Loading()
				});

				this.#formData = formData;
				const request = new Request('queryPartner', 'POST', 'class');
				request.send(formData).then(this.successHandler.bind(this), this.failureHandler.bind(this))
			},
		});

		return [sendBtn];
	}

	init(popup: Popup): void
	{
		popup.setContent(this.getContent());
		popup.setButtons(this.getButtonCollection());
		this.#errors.show();
	}

	successHandler(): void
	{
		EventEmitter.emit(EventEmitter.GLOBAL_TARGET, 'MainCouponActivation:changeContent', {
			source: this,
			target: new Success()
		});
	}

	failureHandler(event): void
	{
		this.#errors = new ErrorCollection(event.errors);

		EventEmitter.emit(EventEmitter.GLOBAL_TARGET, 'MainCouponActivation:back', {
			source: this,
		});
	}


}