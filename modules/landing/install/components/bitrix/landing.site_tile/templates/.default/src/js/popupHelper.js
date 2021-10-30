import { Tag, Event, Loc } from 'main.core';
import {EventEmitter} from "main.core.events";
import 'ui.notification';

export default class PopupHelper {
	constructor(options)
	{
		this.id = options.id;
		this.url = options.url;
		this.fullUrl = options.fullUrl;
		this.ordersUrl = options.ordersUrl;
		this.qr = null;

		this.$container = null;
		this.$containerClose = null;
		this.$containerFirstStep = null;
		this.$containerSecondStep = null;
		this.$containerQr = null;
		this.$containerQrimage = null;
		this.$containerInputUrl = null;
		this.$containerCopyLink = null;
		this.$containerTestOrder = null;

		this.adjustCloseEditByClick = this.adjustCloseEditByClick.bind(this);
		this.adjustCloseEditByKeyDown = this.adjustCloseEditByKeyDown.bind(this);
	}

	hide()
	{
		this.getContainer().classList.remove('--show');
		Event.unbind(document.body, 'click', this.adjustCloseEditByClick);
		Event.unbind(document.body, 'keydown', this.adjustCloseEditByKeyDown);
		EventEmitter.emit(this, 'BX.Landing.SiteTile.Popup:onHide', this);
	}

	show(param: string)
	{
		this.getContainer().classList.add('--show');
		if(param === 'link')
		{
			this.getContainerFirstStep().style.display = 'none';
			this.getContainerSecondStep().style.display = 'none';
		}
		else
		{
			this.getContainerFirstStep().style.display = null;
			this.getContainerSecondStep().style.display = null;
		}

		Event.bind(document.body, 'click', this.adjustCloseEditByClick);
		Event.bind(document.body, 'keydown', this.adjustCloseEditByKeyDown);
		EventEmitter.emit(this, 'BX.Landing.SiteTile.Popup:onShow', this);
	}

	adjustCloseEditByClick(ev)
	{
		if(	ev.type !== 'click')
		{
			return;
		}

		if(	ev.target.closest('.landing-sites__popup')
			|| ev.target.closest('.landing-sites__container-link-' + this.id)
			|| ev.target.closest('.landing-sites__status-' + this.id)
		)
		{
			return
		}

		this.hide();
	}

	adjustCloseEditByKeyDown(ev)
	{
		if(ev.type !== 'keydown')
		{
			return;
		}

		if(ev.keyCode === 27) // close by Escape
		{
			this.hide();
		}
	}

	showSecondStep()
	{
		this.getContainerFirstStep().classList.add('--hide-right');
		this.getContainerSecondStep().classList.remove('--hide-left');
	}

	showFirstStep()
	{
		this.getContainerFirstStep().classList.remove('--hide-right');
		this.getContainerSecondStep().classList.add('--hide-left');
	}

	getContainerInputUrl()
	{
		if(!this.$containerInputUrl)
		{
			this.$containerInputUrl = Tag.render`
				<input 
					type="text" 
					style="position: absolute; opacity: 0; pointer-events: none"
					value="${this.fullUrl}">
			`;
		}

		return this.$containerInputUrl;
	}

	getContainerCopyLink()
	{
		if(!this.$containerCopyLink)
		{
			this.$containerCopyLink = Tag.render`
				<div class="landing-sites__popup-copy">
					${Loc.getMessage('LANDING_SITE_TILE_POPUP_COPY_LINK')}
				</div>
			`;

			Event.bind(this.$containerCopyLink, 'click', ()=> {
				this.getContainerInputUrl().select();
				document.execCommand('copy');
				BX.UI.Notification.Center.notify({
					content: Loc.getMessage('LANDING_SITE_TILE_POPUP_COPY_LINK_COMPLETE'),
					autoHideDelay: 2000,
				});
			});
		}

		return this.$containerCopyLink;
	}

	getContainerQrImage()
	{
		let node = Tag.render`
			<div class="landing-sites__popup-image"></div>
		`;

		new QRCode(node, {
			text: this.fullUrl,
			width: 250,
			height: 250
		});

		return node;
	}

	getContainerQr()
	{
		if(!this.$containerQr)
		{
			let closeIcon = Tag.render`<div class="landing-sites__popup-close"></div>`;
			Event.bind(closeIcon, 'click', this.hide.bind(this));

			this.$containerQr = Tag.render`
				<div class="landing-sites__popup-container --qr">
					${closeIcon}
					<div class="landing-sites__popup-wrapper">
						<div class="landing-sites__popup-content">
							<div class="landing-sites__popup-text">${Loc.getMessage('LANDING_SITE_TILE_POPUP_TEST_ORDER_ACTION_3')}</div>
							${this.getContainerQrImage()}
							<div class="landing-sites__popup-buttons">
								<a href="${this.fullUrl}" target="_blank" class="ui-btn ui-btn-light-border ui-btn-round">${Loc.getMessage('LANDING_SITE_TILE_POPUP_OPEN_SITE')}</a>
							</div>
						</div>
						<div class="landing-sites__popup-bottom">
							<a href="${this.fullUrl}" target="_blank" class="landing-sites__popup-url">
								${this.url}
								${this.getContainerInputUrl()}
							</a>
							${this.getContainerCopyLink()}
						</div>
					</div>
				</div>
			`;
		}

		return this.$containerQr;
	}

	getContainerTestOrder()
	{
		if(!this.$containerTestOrder)
		{
			this.$containerTestOrder = Tag.render`
				<span class="ui-btn ui-btn-success ui-btn-round">
					${Loc.getMessage('LANDING_SITE_TILE_POPUP_CREATE_TEST_ORDER')}
				</span>	
			`;

			Event.bind(this.$containerTestOrder, 'click', this.showSecondStep.bind(this))
		}

		return this.$containerTestOrder;
	}

	getContainerFirstStep()
	{
		if(!this.$containerFirstStep)
		{
			let closeIcon = Tag.render`<div class="landing-sites__popup-close"></div>`;
			Event.bind(closeIcon, 'click', this.hide.bind(this));

			this.$containerFirstStep = Tag.render`
				<div class="landing-sites__popup-container">
					${closeIcon}
					<div class="landing-sites__popup-wrapper">
						<div class="landing-sites__popup-title">
							<span class="landing-sites__popup-title-text">${Loc.getMessage('LANDING_SITE_TILE_POPUP_CREATE_TEST_ORDER')}</span>
						</div>
						<div class="landing-sites__popup-content">
							<div class="landing-sites__popup-text">${Loc.getMessage('LANDING_SITE_TILE_POPUP_TEST_ORDER_TEXT')}</div>
							<div class="landing-sites__popup-image --first-order"></div>
							<div class="landing-sites__popup-buttons">
								${this.getContainerTestOrder()}
								<a href="${this.ordersUrl}" class="ui-btn ui-btn-light-border ui-btn-round">${Loc.getMessage('LANDING_SITE_TILE_POPUP_OPEN_CRM')}</a>
							</div>
						</div>
					</div>
				</div>
			`;
		}

		return this.$containerFirstStep;
	}

	getContainerSecondStep()
	{
		if(!this.$containerSecondStep)
		{
			let closeIcon = Tag.render`<div class="landing-sites__popup-close"></div>`;
			let prevIcon = Tag.render`<div class="landing-sites__popup-prev"></div>`;

			Event.bind(closeIcon, 'click', this.hide.bind(this));
			Event.bind(prevIcon, 'click', this.showFirstStep.bind(this));

			this.$containerSecondStep = Tag.render`
				<div class="landing-sites__popup-container --hide-left">
					${closeIcon}
					${prevIcon}
					<div class="landing-sites__popup-wrapper">
						<div class="landing-sites__popup-title">
							<span class="landing-sites__popup-title-text">${Loc.getMessage('LANDING_SITE_TILE_POPUP_TEST_ORDER')}</span>
						</div>
						<div class="landing-sites__popup-content">
							<div class="landing-sites__popup-text">
								<div class="landing-sites__popup-text --list"><span>1</span> ${Loc.getMessage('LANDING_SITE_TILE_POPUP_TEST_ORDER_ACTION_1')}</div>
								<div class="landing-sites__popup-text --list"><span>2</span> ${Loc.getMessage('LANDING_SITE_TILE_POPUP_TEST_ORDER_ACTION_2')}</div>
							</div>
							${this.getContainerQrImage()}
							<div class="landing-sites__popup-buttons">
								<a href="${this.ordersUrl}" class="ui-btn ui-btn-success ui-btn-round">${Loc.getMessage('LANDING_SITE_TILE_POPUP_OPEN_CRM')}</a>
							</div>
						</div>
					</div>
				</div>
			`;
		}

		return this.$containerSecondStep;
	}

	getContainer()
	{
		if(!this.$container)
		{
			this.$container = Tag.render`
				<div class="landing-sites__popup">
					${this.getContainerFirstStep()}
					${this.getContainerSecondStep()}
					${this.getContainerQr()}
				</div>
			`;
		}

		return this.$container;
	}
}