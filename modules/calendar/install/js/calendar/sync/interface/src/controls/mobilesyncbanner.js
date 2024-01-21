// @flow
'use strict';
import { Type, Tag, Loc, Runtime, Dom } from 'main.core';
import { Util } from "calendar.util";
import { Popup } from 'main.popup';

export default class MobileSyncBanner
{
	zIndex = 3100;
	DOM = {};
	QRCODE_SIZE = 186;
	QRCODE_COLOR_LIGHT = '#ffffff';
	QRCODE_COLOR_DARK = '#000000';
	QRCODE_WRAP_CLASS = 'calendar-sync-slider-qr-container';
	QRC = null;

	constructor(options = {})
	{
		this.type = options.type;
		this.helpDeskCode = options.helpDeskCode || '11828176';
		this.alreadyConnectedToNew = this.type === 'android'
		 	? Util.isGoogleConnected()
			: Util.isIcloudConnected()
		;
	}

	show()
	{
	}

	showInPopup()
	{
		this.popup = new Popup({
			className: 'calendar-sync-qr-popup',
			draggable: true,
			content: this.getContainer(),
			width: 580,
			zIndexAbsolute: this.zIndex,
			cacheable: false,
			closeByEsc: true,
			closeIcon: true,
		});
		this.popup.show();

		this.initQrCode().then(this.drawQRCode.bind(this));
	}

	close()
	{
		this.popup.close();
	}

	getContainer()
	{
		this.DOM.container = Tag.render `
			<div class="calendar-sync-qr-popup-content">
				<div class="calendar-sync-qr-popup-title">
					${this.getTitle()}
				</div>
				<div class="calendar-sync-slider-content">
					<img class="calendar-sync-slider-phone-img" src="/bitrix/images/calendar/sync/qr-background.svg" alt="">
					${this.getQrContainer()}
					${this.getInstructionContainer()}
				</div>
			</div>
		`;

		Util.initHintNode(this.DOM.container.querySelector('.calendar-notice-mobile-banner'));

		return this.DOM.container;
	}

	getQrContainer()
	{
		if (!this.DOM.qrContainer)
		{
			this.DOM.qrContainer = Tag.render`
				<div class="calendar-sync-slider-qr">
					<div class="${this.QRCODE_WRAP_CLASS}">${Util.getLoader(this.QRCODE_SIZE)}</div>
					<span class="calendar-sync-slider-logo"></span>
				</div>
			`;
		}

		return this.DOM.qrContainer;
	}

	getInstructionContainer()
	{
		if (!this.DOM.instructionContainer)
		{
			this.DOM.instructionContainer = Tag.render`
				<div class="calendar-sync-slider-instruction">
					<!--<div class="calendar-sync-slider-instruction-subtitle"></div>-->
					${this.getInstructionTextContainer()}
					<div class="calendar-sync-slider-instruction-notice">${Loc.getMessage('SYNC_MOBILE_NOTICE')}</div>
					<a href="javascript:void(0);" 
							onclick="BX.Helper.show('redirect=detail&code=' + ${this.getHelpdeskCode()},{zIndex:3100,}); event.preventDefault();" 
							class="ui-btn ui-btn-success ui-btn-round">
						${Loc.getMessage('SYNC_MOBILE_ABOUT_BTN')}
					</a>
				</div>
			`;
		}

		return this.DOM.instructionContainer;
	}

	getInstructionTextContainer()
	{
		if (!this.DOM.instructionTextContainer)
		{
			this.DOM.instructionTextContainer = Tag.render`
				<div class="calendar-sync-slider-instruction-title">
					${Loc.getMessage('SYNC_MOBILE_NOTICE_HOW_TO') + ' '} 
					${this.type !== 'iphone' ? this.getAndroidHintIcon() : ''}
				</div>
			`;
		}

		return this.DOM.instructionTextContainer;
	}

	getAndroidHintIcon()
	{
		if (!this.DOM.androidHintIcon)
		{
			this.DOM.androidHintIcon = Tag.render`
			<span 
				class="calendar-notice-mobile-banner" 
				data-hint="${Loc.getMessage('CAL_ANDROID_QR_CODE_HINT')}" 
				data-hint-no-icon="Y">
			</span>`;
		}

		return this.DOM.androidHintIcon;
	}

	getInnerContainer()
	{
		return this.DOM.container.querySelector('.' + this.QRCODE_WRAP_CLASS);
	}

	initQrCode()
	{
		return new Promise((resolve) => {
			Runtime.loadExtension(['main.qrcode']).then(() => {
				if (typeof QRCode !== 'undefined')
				{
					resolve();
				}
			});
		});
	}

	drawQRCode(wrap)
	{
		if (!Type.isDomNode(wrap))
		{
			wrap = this.getInnerContainer();
		}

		this.getMobileSyncUrl().then((link) => {
			Dom.clean(wrap);
			this.QRC = new QRCode(wrap, {
				text: link,
				width: this.getSize(),
				height: this.getSize(),
				colorDark : this.QRCODE_COLOR_DARK,
				colorLight : this.QRCODE_COLOR_LIGHT,
				correctLevel : QRCode.CorrectLevel.H
			});
		});
	}

	getTitle()
	{
		return Loc.getMessage('SYNC_BANNER_MOBILE_TITLE');
	}

	getMobileSyncUrl()
	{
		return new Promise((resolve, reject) => {
			BX.ajax.runAction('calendar.api.syncajax.getAuthLink', {
				data: {
					type: this.type ? 'slider' : 'banner',
				}
			})
			.then(
				(response) => {
					resolve(response.data.link);
				},
				reject
			);
		});
	}

	getSize()
	{
		return this.QRCODE_SIZE;
	}

	getDetailHelpUrl()
	{
		return 'https://helpdesk.bitrix24.ru/open/' + this.getHelpdeskCode();
	}

	getHelpdeskCode()
	{
		return this.helpDeskCode;
	}
}
