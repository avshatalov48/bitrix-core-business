import BaseContent from "./base-content";
import {Loc, Tag, Type} from "main.core";
import {Button} from "ui.buttons";
import {EventEmitter} from "main.core.events";
import {Popup} from "main.popup";
import {Loading} from "./loader";
import {Request} from "../request";
import {ErrorCollection} from "../error-collection";
export class Activate extends BaseContent
{
	#formData: FormData;
	#errors: ErrorCollection;
	#supportLink: string;
	#docLink: string;

	constructor(supportLink: ?string, docLink: ?string) {
		super();
		this.#errors = new ErrorCollection();
		this.#formData = new FormData();
		this.#supportLink = !Type.isNil(supportLink) ? supportLink : '';
		this.#docLink = !Type.isNil(docLink) ? docLink : '';
	}

	getContent(): HTMLElement
	{
		return Tag.render`
			<form id="intranet-license-activate-key-form">
			<div class="license-intranet-popup__content --key-activate">
				<div class="license-intranet-popup__block --center">
					<div class="license-intranet-popup__title">${Loc.getMessage('MAIN_COUPON_ACTIVATION_TITLE_ACTIVATE_KEY')}</div>
					<div class="license-intranet-popup__text">${Loc.getMessage('MAIN_COUPON_ACTIVATION_SUBTITLE_ACTIVATE_KEY', {'#SUPPORT_LINK#': this.#supportLink})}</div>
				</div>
				
				<div class="license-intranet-popup__buttons">
					${this.renderRefreshPageBtn().render()}
				</div>

				<div class="license-intranet-popup__block --input-area">
					<div class="ui-form licence-key-form">
						<div class="ui-form-row ui-form-row-inline">
								<div class="ui-form-label">
									<div class="ui-ctl-label-text">${Loc.getMessage('MAIN_COUPON_ACTIVATION_LICENSE_KEY_FIELD')}</div>
								</div>
								<div class="ui-form-content">
									<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
										<input
											type="text"
											name="key"
											value="${this.#formData.get('key') ?? ''}"
											class="ui-ctl-element licence-key-form__input"
											placeholder="XXXX-XXXX-XXXX-XXXX-XX"
										>
									</div>
									${this.getSendBtn().render()}
								</div>
							</div>
					</div>
				</div>
				
				<a class="license-intranet-popup__help-link"
				target="_blank"
				href="${this.#docLink}"
			>
				${Loc.getMessage('MAIN_COUPON_ACTIVATION_BUTTON_NEED_HELP')}
			</a>
			</div>
			</form>
		`;
	}

	getSendBtn(): Button
	{
		return new Button({
			text: '',
			noCaps: false,
			round: true,
			className: 'ui-btn-icon-add licence-key-form__submit-btn',
			size: BX.UI.Button.Size.MEDIUM,
			color: BX.UI.Button.Color.LIGHT_BORDER,
			tag: BX.UI.Button.Tag.BUTTON,
			onclick: () => {
				const formNode = document.querySelector('#intranet-license-activate-key-form');
				const formData = new FormData(formNode);
				this.#errors.hideErrors();
				this.#errors.cleanErrors();

				EventEmitter.emit(EventEmitter.GLOBAL_TARGET, 'MainCouponActivation:changeContent', {
					source: this,
					target: new Loading()
				});

				this.#formData = formData;
				const request = new Request('activate', 'POST', 'class');
				request.send(formData).then(this.successHandler.bind(this), this.failureHandler.bind(this))
			},
		});
	}

	init(popup: Popup): void
	{
		popup.setContent(this.getContent());
		this.#errors.show();
	}

	successHandler(): void
	{
		EventEmitter.emit(EventEmitter.GLOBAL_TARGET, 'MainCouponActivation:back', {
			source: this,
		});
		document.location.href = '/';
	}

	failureHandler(event): void
	{
		this.#errors = new ErrorCollection(event.errors);
		EventEmitter.emit(EventEmitter.GLOBAL_TARGET, 'MainCouponActivation:back', {
			source: this,
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

	renderRefreshPageBtn(): Button
	{
		return  new Button({
			text: Loc.getMessage('MAIN_COUPON_ACTIVATION_BUTTON_REFRESH_PAGE'),
			noCaps: false,
			round: true,
			size: BX.UI.Button.Size.LARGE,
			color: BX.UI.Button.Color.SUCCESS,
			tag: BX.UI.Button.Tag.BUTTON,
			onclick: () => {
				this.#checkRequest();
			},
		});
	}
}