import { ajax as Ajax, Dom, Type, Tag, Loc } from 'main.core';
import { Popup } from 'main.popup';
import { Loader } from 'main.loader';
import { PULL } from 'pull.client';
import 'main.qrcode';

export class QrAuthorization
{
	title;
	content;

	constructor(options = {})
	{
		this.title = options.title || null;
		this.content = options.content || null;
		this.helpLink = options.helpLink || null;
		this.popup = null;
		this.loader = null;

		this.qrNode = null;
		this.successNode = null;
		this.loadingNode = null;

		this.isSubscribe = false;
	}

	createQrCodeImage()
	{
		Dom.clean(this.getQrNode())
		this.loading();

		Ajax.runAction(
			'mobile.deeplink.get', {
				data: {
					intent: 'calendar'
				}
			}).then((response)=> {
				let link = response.data?.link;
				if(link)
				{
					this.clean();
					new QRCode(this.getQrNode(), {
						text: link,
						width: 180,
						height: 180
					});

					if(!this.isSubscribe)
					{
						this.isSubscribe = true;
						this.subscribe();
					}
				}
			}).catch(()=> {});
	}

	subscribe()
	{
		if(PULL)
		{
			PULL.subscribe({
				type: 'BX.PullClient.SubscriptionType.Server',
				moduleId: 'mobile',
				command: 'onDeeplinkShouldRefresh',
				callback: (params) => {
					this.success();
				}
			});
		}
	}

	getQrNode(): HTMLElement
	{
		if(!this.qrNode)
		{
			this.qrNode = Tag.render`
				<div class="ui-qr-authorization__popup-qr"></div>
			`;
		}

		return this.qrNode;
	}

	getPopup(): Popup
	{
		if(!this.popup)
		{
			let container = Tag.render`
				<div class="ui-qr-authorization__popup-wrapper">
					<div class="ui-qr-authorization__popup-top">
						<div class="ui-qr-authorization__popup-left ${!this.title ? '--flex' : ''}"">
							${this.title
								? '<div class="ui-qr-authorization__popup-title">' + this.title + '</div>'
								: ''}
							${this.content
								? '<div class="ui-qr-authorization__popup-text">' + this.content + '</div>'
								: ''}
						</div>
						<div class="ui-qr-authorization__popup-right ${!this.title ? '--no-margin' : ''}">
							${this.getQrNode()}
						</div>
					</div>
					<div class="ui-qr-authorization__popup-bottom">
						<div class="ui-qr-authorization__popup-bottom--title">${Loc.getMessage('UI_QR_AUTHORIZE_TAKE_CODE')}</div>
						${this.helpLink
							? '<a href="' + this.helpLink + '" class="ui-qr-authorization__popup-bottom--link">' + Loc.getMessage('UI_QR_AUTHORIZE_HELP') + '</a>'
							: ''}
					</div>
				</div>
			`;

			this.popup = new Popup({
				className: 'ui-qr-authorization__popup ui-qr-authorization__popup-scope',
				width: this.title && this.content ? 710 : null,
				content: container,
				closeByEsc: true,
				closeIcon: {
					top: 14,
					right: 15
				},
				padding: 0,
				animation: 'fading-slide'
			});
		}

		return this.popup;
	}

	success()
	{
		this.clean();
		this.getQrNode().classList.add('--success');
		this.getQrNode().appendChild(this.getSuccessNode());
	}

	getSuccessNode(): HTMLElement
	{
		if (!this.successNode)
		{
			this.successNode = Tag.render`
				<div class="ui-qr-authorization__popup-qr-success"></div>
			`;
		}

		return this.successNode;
	}

	loading()
	{
		this.clean();
		this.getQrNode().classList.add('--loading');
		this.getQrNode().appendChild(this.getLoadingNode());
		this.showLoader();
	}

	getLoadingNode(): HTMLElement
	{
		if (!this.loadingNode)
		{
			this.loadingNode = Tag.render`
				<div class="ui-qr-authorization__popup-qr-loading"></div>
			`;
		}

		return this.loadingNode;
	}

	getLoader(): Loader
	{
		if (!this.loader)
		{
			this.loader = new Loader({
				target: this.getLoadingNode(),
				size: 150,
			});
		}

		return this.loader;
	}

	showLoader(): void
	{
		void this.getLoader().show();
	}

	hideLoader(): void
	{
		void this.getLoader().hide();
	}

	clean()
	{
		this.getQrNode().classList.remove('--loading');
		this.getQrNode().classList.remove('--success');
		Dom.remove(this.getLoadingNode());
		Dom.remove(this.getSuccessNode());
		this.hideLoader();
	}

	show()
	{
		if (!this.getPopup().isShown())
		{
			this.createQrCodeImage();
			this.loading();
			this.getPopup().show();
		}
	}

	close()
	{
		if (this.getPopup().isShown())
		{
			this.clean();
			this.getPopup().close();
		}
	}
}