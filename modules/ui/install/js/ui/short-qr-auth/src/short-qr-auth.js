import { ajax, Cache, Dom, Tag, Type, Loc } from 'main.core';
import 'main.qrcode';
import { AirButtonStyle, Button } from 'ui.buttons';
import { PULL } from 'pull.client';
import { Loader } from 'main.loader';
import './style.css';

export type ShortQrAuthOptions = {
	intent?: string;
	ttl?: number;
	small?: boolean;
	stub?: boolean;
};

export class ShortQrAuth
{
	#cache = new Cache.MemoryCache();
	#intent: string;
	#ttl: number;
	#ttlInterval: number;
	#small: boolean;
	#stub: boolean;

	constructor(options: ShortQrAuthOptions = {})
	{
		this.#intent = Type.isString(options.intent) ? options.intent : 'default';
		this.#small = Type.isBoolean(options.small) ? options.small : false;
		this.#ttl = Type.isNumber(options.ttl) ? options.ttl : 60;
		this.#stub = Type.isBoolean(options.stub) ? options.stub : true;
	}

	render(): HTMLElement
	{
		return this.#getContainer();
	}

	#getContainer(): HTMLElement
	{
		return this.#cache.remember('container', () => {
			const qrNode = this.#getQrNode();

			if (!this.#stub)
			{
				this.#addQrCodeImage();
			}

			return Tag.render`
				<div class="ui-short-qr-auth__container ${this.#small ? '--small' : ''}">
					<div class="ui-short-qr-auth__corner --top-left"></div>
					<div class="ui-short-qr-auth__corner --top-right"></div>
					<div class="ui-short-qr-auth__corner --bottom-left"></div>
					<div class="ui-short-qr-auth__corner --bottom-right"></div>
					${qrNode}
				</div>
			`;
		});
	}

	#getQrNode(): HTMLElement
	{
		return this.#cache.remember('qrNode', () => {
			return Tag.render`
				<div class="ui-short-qr-auth__qr">
					${this.#stub ? this.#getQrStub() : null}
				</div>
			`;
		});
	}

	#getQrStub(): HTMLElement
	{
		return this.#cache.remember('qrStub', () => {
			return Tag.render`
				<div class="ui-short-qr-auth__qr-stub">
					<i class="ui-icon-set --o-qr-code ui-short-qr-auth__qr-stub-icon"></i>
					${this.#getShowQrButton().render()}
				</div>
			`;
		});
	}

	#getShowQrButton(): Button
	{
		return this.#cache.remember('showQrButton', () => {
			return new Button({
				size: Button.Size.EXTRA_SMALL,
				text: Loc.getMessage('UI_SHORT_QR_AUTH_BUTTON_TITLE'),
				useAirDesign: true,
				maxWidth: 117,
				style: AirButtonStyle.TINTED,
				noCaps: true,
				wide: true,
				onclick: () => {
					this.#addQrCodeImage();
				},
			});
		});
	}

	#addQrCodeImage(): void
	{
		this.#createQrCodeImage();

		if (!this.#ttlInterval)
		{
			this.#ttlInterval = setInterval(() => {
				this.#createQrCodeImage();
			}, this.#ttl * 1000);
		}
	}

	#clean(): void
	{
		Dom.clean(this.#getQrNode());
	}

	#loading(): void
	{
		this.#clean();
		(new Loader({ size: 60 })).show(this.#getQrNode());
	}

	#createQrCodeImage(): void
	{
		this.#loading();
		ajax.runAction('mobile.deeplink.get', {
			data: {
				intent: this.#intent,
				ttl: this.#ttl,
			},
		}).then((response) => {
			const link = response.data?.link;
			if (link)
			{
				this.#clean();
				// eslint-disable-next-line no-undef
				new QRCode(this.#getQrNode(), {
					text: link,
					width: this.#getQrSize(),
					height: this.#getQrSize(),
				});

				if (!this.isSubscribe)
				{
					this.isSubscribe = true;
					this.#subscribeEventRefresh();
				}
			}
		}).catch(() => {});
	}

	#getQrSize(): number
	{
		return this.#small ? 98 : 151;
	}

	#subscribeEventRefresh()
	{
		if (PULL)
		{
			PULL.subscribe({
				type: 'BX.PullClient.SubscriptionType.Server',
				moduleId: 'mobile',
				command: 'onDeeplinkShouldRefresh',
				callback: () => {
					this.#createQrCodeImage();
				},
			});
		}
	}
}
