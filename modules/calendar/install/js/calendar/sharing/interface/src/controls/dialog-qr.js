import {Tag, Loc, Runtime} from 'main.core';
import { Popup } from 'main.popup';
import { Loader } from 'main.loader';

type DialogQrOptions = {
	sharingUrl: string,
	context: 'calendar' | 'crm',
}
export default class DialogQr
{
	QRCODE_SIZE = 114;
	QRCODE_COLOR_LIGHT = '#fff';
	QRCODE_COLOR_DARK = '#000';
	#popup;
	#loader;
	#layout;
	#qrCode;
	#context;

	constructor(options: DialogQrOptions)
	{
		this.#popup = null;
		this.#loader = null;
		this.#layout = {
			qr: null,
		};
		this.#qrCode = null;
		this.#context = options.context;

		this.sharingUrl = options.sharingUrl;
	}

	/**
	 *
	 * @returns {Popup}
	 */
	getPopup()
	{
		if (!this.#popup)
		{
			this.#popup = new Popup({
				className: 'calendar-sharing__qr',
				width: 315,
				padding: 0,
				content: this.getContent(),
				closeIcon: true,
				closeByEsc: true,
				autoHide: true,
				overlay: true,
				animation: 'fading-slide',
			});
		}

		return this.#popup;
	}

	/**
	 *
	 * @returns {Loader}
	 */
	getLoader()
	{
		if (!this.#loader)
		{
			this.#loader = new Loader({ size: 95 });
		}

		return this.#loader;
	}

	/**
	 *
	 * @returns {HTMLElement}
	 */
	getNodeQr()
	{
		if (!this.#layout.qr)
		{
			this.#layout.qr = Tag.render`
				<div class="calendar-sharing__qr-block"></div>
			`;

			// qr emulation
			this.getLoader().show(this.#layout.qr);

			this.showQr();
		}

		return this.#layout.qr;
	}

	async showQr()
	{
		await this.initQrCode();
		this.QRCode = new QRCode(this.#layout.qr, {
			text: this.sharingUrl,
			width: this.QRCODE_SIZE,
			height: this.QRCODE_SIZE,
			colorDark : this.QRCODE_COLOR_DARK,
			colorLight : this.QRCODE_COLOR_LIGHT,
			correctLevel : QRCode.CorrectLevel.H
		});
		await this.getLoader().hide();
	}

	async initQrCode()
	{
		await Runtime.loadExtension(['main.qrcode']);
	}

	/**
	 *
	 * @returns {HTMLElement}
	 */
	getContent()
	{
		return Tag.render`
			<div class="calendar-sharing__qr-content">
				<div class="calendar-sharing__qr-title">${this.getPhraseDependsOnContext('SHARING_INFO_POPUP_QR_TITLE')}</div>
				${this.getNodeQr()}
				<div class="calendar-sharing__qr-info">${Loc.getMessage('SHARING_INFO_POPUP_QR_INFO')}</div>
				<a class="calendar-sharing__dialog-link" href="${this.sharingUrl}" target="_blank">${Loc.getMessage('SHARING_INFO_POPUP_QR_OPEN_LINK')}</a>
			</div>
		`;
	}

	isShown()
	{
		return this.getPopup().isShown();
	}
	close()
	{
		this.getPopup().close();
	}

	show()
	{
		this.getPopup().show();
	}

	destroy()
	{
		this.getPopup().destroy();
	}

	getPhraseDependsOnContext(code: string)
	{
		return Loc.getMessage(code + '_' + this.#context.toUpperCase())
	}
}