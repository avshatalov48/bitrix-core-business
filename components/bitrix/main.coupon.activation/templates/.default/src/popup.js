import {Popup} from "main.popup";
import {ExpiredLicense} from "./content/expired-license";
import BaseContent from "./content/base-content";
import {EventEmitter} from "main.core.events";
import {Type} from "main.core";
import {Partner} from "./content/partner";
import {Activate} from './content/activate';

import 'ui.fonts.opensans';
import 'ui.design-tokens';
import 'ui.fonts.montserrat';
import './style.css';
import {Request} from "./request";
import {Success} from "./content/success";

export class LicensePopup
{
	#popup: ?Popup;

	#currentContent: ?BaseContent
	#history: Array = [];

	constructor(popupContent: ?BaseContent)
	{
		if (popupContent instanceof BaseContent)
		{
			this.#currentContent = popupContent;
		}
		else
		{
			this.#currentContent = new ExpiredLicense();
		}

		EventEmitter.subscribe(
			EventEmitter.GLOBAL_TARGET,
			'MainCouponActivation:changeContent',
			this.changeHandler.bind(this)
		);

		EventEmitter.subscribe(
			EventEmitter.GLOBAL_TARGET,
			'MainCouponActivation:back',
			this.backHandler.bind(this)
		);

		new Request('activate', 'POST');
	}

	static createExpiredLicensePopup(parameters: Object)
	{
		const partnerId = Type.isNil(parameters.PARTNER_ID) ? 0 : parameters.PARTNER_ID;
		const buyId = Type.isString(parameters.BUY_LINK) ?  parameters.BUY_LINK : '';

		return new LicensePopup(new ExpiredLicense(buyId, partnerId, parameters));
	}

	getPopup(): Popup
	{
		if (this.#popup)
		{
			return this.#popup
		}

		this.#popup = new Popup({
			className: 'license-intranet-popup',
			padding: 34,
			width: 700,
			closeIcon: false,
			borderRadius: '20px',
		});

		return this.#popup;
	}

	addHistory(content: BaseContent): void
	{
		this.#history.push(content);
	}

	back(): void
	{
		const content = this.#history.pop();
		if (content instanceof BaseContent)
		{
			this.#currentContent = content;
		}
	}

	init()
	{
		this.changeContent();
		this.getPopup().show();
	}

	changeContent()
	{
		this.#currentContent.init(this.getPopup());
		EventEmitter.emit(EventEmitter.GLOBAL_TARGET, 'MainCouponActivation:onAfterChangeContent', {
			target: this.#currentContent.getContent()
		});
	}

	changeHandler(event)
	{
		if (event.data.target instanceof BaseContent)
		{
			this.addHistory(this.#currentContent);
			this.#currentContent = event.data.target;
		}
		this.changeContent();
	}
	backHandler(event)
	{
		if (event.data.source instanceof BaseContent)
		{
			this.back();
		}
		this.changeContent();
	}
}

