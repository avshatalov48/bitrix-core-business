import {Tag, Text, Event, Loc, Dom} from 'main.core';
import {Menu} from 'main.popup';
import {EventEmitter} from 'main.core.events';
import {MessageBox} from 'ui.dialogs.messagebox';

import EditableTitle from './editableTitle';
import LeaderShip from './leadership';
import PopupHelper from './popupHelper';

export default class Item
{
	constructor(options)
	{
		this.id = options.id;
		this.grid = options.grid;
		this.title = options.title;
		this.url = options.url;
		this.fullUrl = options.fullUrl;
		this.domainProvider = options.domainProvider;
		this.pagesUrl = options.pagesUrl;
		this.ordersUrl = options.ordersUrl;
		this.domainUrl = options.domainUrl;
		this.contactsUrl = options.contactsUrl;
		this.indexEditUrl = options.indexEditUrl;
		this.ordersCount = options.ordersCount;
		this.phone = options.phone;
		this.preview = options.preview;
		this.cloudPreview = options.cloudPreview;
		this.published = options.published;
		this.deleted = options.deleted;
		this.domainStatus = options.domainStatus;
		this.domainStatusMessage = options.domainStatusMessage;
		this.menuItems = options.menuItems || [];
		this.menuBottomItems = options.menuBottomItems || [];
		this.notPublishedText = options.notPublishedText || null;
		this.access = options.access || {};
		this.articles = options.articles || [];
		this.editableTitle = null;
		this.editableUrl = null;
		this.leadership = null;
		this.popupHelper = null;
		this.popupStatus = null;
		this.popupConfig = null;
		this.loader = null;

		this.$container = null;
		this.$containerWrapper = null;
		this.$containerPreviewImage = null;
		this.$containerPreviewStatus = null;
		this.$containerPreviewShowPages = null;
		this.$containerPreviewInstruction = null;
		this.$containerInfo = null;
		this.$containerPhone = null;
		this.$containerTitle = null;
		this.$containerDomain = null;
		this.$containerDomainLink = null;
		this.$containerDomainStatus = null;
		this.$containerDomainStatusIcon = null;
		this.$containerDomainStatusTitle = null;
		this.$containerDomainStatusMessage = null;
		this.$containerSiteStatus = null;
		this.$containerSiteStatusRound = null;
		this.$containerSiteStatusTitle = null;
		this.$containerSiteMore = null;
		this.$containerLinks = null;

		this.bindEvents();

		this.lazyLoadCloudPreview = this.lazyLoadCloudPreview.bind(this);
	}

	bindEvents()
	{
		EventEmitter.subscribe('BX.Landing.SiteTile:showLeadership', options => {
			if (this === options.data)
			{
				this.active();
				this.setContainerPosition();
			}

			if (this !== options.data)
			{
				this.fade();
			}
		});

		EventEmitter.subscribe('BX.Landing.SiteTile:hideLeadership', options => {
			if (this === options.data)
			{
				this.unActive();
				this.unSetContainerPosition();
			}

			this.unFade();
		});

		EventEmitter.subscribe(this.getPopupHelper(), 'BX.Landing.SiteTile.Popup:onShow', () => {
			this.getContainerWrapper().classList.add('--fade');
		});

		EventEmitter.subscribe(this.getPopupHelper(), 'BX.Landing.SiteTile.Popup:onHide', () => {
			this.getContainerWrapper().classList.remove('--fade');
		});
	}

	setContainerPosition()
	{
		let offsetRight = window.innerWidth - this.getContainer().getBoundingClientRect().right;
		let leaderShipWidth = this.getLeadership().getContainer().offsetWidth;
		let previousItem = this.getContainer().previousSibling;
		if (offsetRight > leaderShipWidth)
		{
			return;
		}

		this.getContainer().style.transform = 'translateX(-' + (leaderShipWidth + 40 - offsetRight) + 'px)';

		if (previousItem && (previousItem.offsetTop === this.getContainer().offsetTop))
		{
			previousItem.style.transform = 'translateX(-10px)';
		}
	}

	unSetContainerPosition()
	{
		this.getContainer().style.transform = null;

		let previousItem = this.getContainer().previousSibling;
		if (previousItem && (previousItem.offsetTop === this.getContainer().offsetTop))
		{
			previousItem.style.transform = null;
		}
	}

	updatePublishedStatus(status: boolean)
	{
		if (this.published === status)
		{
			return;
		}
		if (this.popupStatus)
		{
			this.popupStatus.destroy();
		}
		this.popupStatus = null;

		if (status)
		{
			this.published = true;
			this.getContainerSiteStatusRound().className = 'landing-sites__status-round --success';
			this.getContainerSiteStatusTitle().innerText = Loc.getMessage('LANDING_SITE_TILE_STATUS_PUBLISHED');
			this.getContainerPreviewImage().classList.remove('--not-published');
			this.getContainerPreviewStatus().classList.add('--hide');
			return;
		}

		this.published = false;
		this.getContainerSiteStatusRound().className = 'landing-sites__status-round --alert';
		this.getContainerSiteStatusTitle().innerText = Loc.getMessage('LANDING_SITE_TILE_STATUS_NOT_PUBLISHED');
		this.getContainerPreviewImage().classList.add('--not-published');
		this.getContainerPreviewStatus().classList.remove('--hide');
	}

	updateTitle(param: string)
	{
		if (param)
		{
			this.title = param;
		}
	}

	updateUrl(param: string)
	{
		if (param)
		{
			this.url = param;
		}
	}

	getContainerTitle()
	{
		if (!this.$containerTitle)
		{
			this.$containerTitle = Tag.render`
				<div class="landing-sites__title">
					<div class="landing-sites__title-text">${this.title}</div>
					<div class="landing-sites__title-edit"></div>
				</div>
			`;
		}

		return this.$containerTitle;
	}

	mergeMenuItems(items: Array<Object>): Array<Object>
	{
		const addMenu = [
			{
				text: this.deleted
					? Loc.getMessage('LANDING_SITE_TILE_RESTORE')
					: Loc.getMessage('LANDING_SITE_TILE_REMOVE'),
				access: 'delete',
				onclick: () => {
					if (!this.deleted)
					{
						const messageBox = new MessageBox({
							title: Loc.getMessage('LANDING_SITE_TILE_DELETE_ALERT_TITLE'),
							message: Loc.getMessage('LANDING_SITE_TILE_DELETE_ALERT_MESSAGE'),
							buttons: BX.UI.Dialogs.MessageBoxButtons.OK_CANCEL,
							onOk: () => {
								EventEmitter.emit('BX.Landing.SiteTile:remove', [this, messageBox]);
								messageBox.close();
							},
							popupOptions: {
								autoHide: true,
								closeByEsc: true,
								minHeight: false,
								minWidth: 260,
								maxWidth: 300,
								width: false,
								animation: 'fading-slide',
							},
						});
						messageBox.show();
					}
					else
					{
						EventEmitter.emit('BX.Landing.SiteTile:restore', this);
						this.getPopupConfig().close();
					}
				},
			},
		];

		let spliceStart = 0;
		items.map((item, i) => {
			if (item.delimiter === true)
			{
				spliceStart = i;
			}
			if (this.deleted)
			{
				item.disabled = true;
			}
		});
		addMenu.reverse().map(item => {
			items.push(item);
		});

		return items;
	}

	disableMenuItems(items: Array<Object>): Array<Object>
	{
		items = items.map(item => {
			if (item.access && this.access[item.access] !== true)
			{
				item.disabled = true;
			}
			return item;
		});

		return items;
	}

	getPopupConfig()
	{
		if (!this.popupConfig)
		{
			this.popupConfig = new Menu({
				className: 'landing-sites__status-popup',
				bindElement: this.getContainerSiteMore(),
				offsetLeft: -61,
				minWidth: 220,
				closeByEsc: true,
				autoHide: true,
				angle: {
					offset: 97,
				},
				items: this.disableMenuItems(this.mergeMenuItems(this.menuItems)),
				events: {
					onPopupClose: () => {
						this.getContainerSiteMore().classList.remove('--hover');
					},
					onPopupShow: () => {
						this.getContainerSiteMore().classList.add('--hover');
					},
				},
				animation: 'fading-slide',
			});
		}

		return this.popupConfig;
	}

	getPopupStatus(): Menu
	{
		if (!this.popupStatus)
		{
			this.popupStatus = new Menu({
				className: 'landing-sites__status-popup',
				bindElement: this.getContainerSiteStatus(),
				minWidth: 220,
				closeByEsc: true,
				autoHide: true,
				angle: {
					offset: 97,
				},
				items: [
					{
						text: this.published
							? Loc.getMessage('LANDING_SITE_TILE_UNPUBLISH')
							: Loc.getMessage('LANDING_SITE_TILE_PUBLISH'),
						onclick: () => {
							this.popupStatus.close();
							this.published
								? EventEmitter.emit('BX.Landing.SiteTile:unPublish', this)
								: EventEmitter.emit('BX.Landing.SiteTile:publish', this);
						},
					},
				],
				events: {
					onPopupClose: () => {
						this.getContainerSiteStatus().classList.remove('--hover');
					},
					onPopupShow: () => {
						this.getContainerSiteStatus().classList.add('--hover');
					},
				},
				animation: 'fading-slide',
			});
		}

		return this.popupStatus;
	}

	getContainerSiteStatus()
	{
		if (!this.$containerSiteStatus)
		{
			this.$containerSiteStatus = Tag.render`
				<div class="${this.access.publication ? 'landing-sites__status' : 'landing-sites__status_disabled'}">
					${this.getContainerSiteStatusRound()}
					${this.getContainerSiteStatusTitle()}
					${this.access.publication ? Tag.render`<div class="landing-sites__status-arrow"></div>` : ''}
				</div>
			`;

			if (this.access.publication)
			{
				Event.bind(this.$containerSiteStatus, 'click', ev => {
					this.getPopupStatus().layout.menuContainer.style.left =
						this.$containerSiteStatus.getBoundingClientRect().left + 'px';
					this.getPopupStatus().show();
					ev.stopPropagation();
				});
			}
		}

		return this.$containerSiteStatus;
	}

	getContainerSiteMore()
	{
		if (!this.$containerSiteMore)
		{
			this.$containerSiteMore = Tag.render`<div class="landing-sites__more"></div>`;

			Event.bind(this.$containerSiteMore, 'click', ev => {
				this.getPopupConfig().show();
				ev.stopPropagation();
			});
		}

		return this.$containerSiteMore;
	}

	getContainerSiteStatusRound()
	{
		if (!this.$containerSiteStatusRound)
		{
			let status = this.published
				? '--success'
				: '--alert';

			this.$containerSiteStatusRound = Tag.render`<div class="landing-sites__status-round ${status}"></div>`;
		}

		return this.$containerSiteStatusRound;
	}

	getContainerSiteStatusTitle()
	{
		if (!this.$containerSiteStatusTitle)
		{
			let title = this.published
				? Loc.getMessage('LANDING_SITE_TILE_STATUS_PUBLISHED')
				: Loc.getMessage('LANDING_SITE_TILE_STATUS_NOT_PUBLISHED');

			this.$containerSiteStatusTitle = Tag.render`<div class="landing-sites__status-title">${title}</div>`;
		}

		return this.$containerSiteStatusTitle;
	}

	publush()
	{
		this.published = true;
		this.getContainerSiteStatusRound().className = 'landing-sites__status-round --success';
		this.getContainerSiteStatusTitle().innerText = Loc.getMessage('LANDING_SITE_TILE_STATUS_PUBLISHED');
		this.getContainerPreviewStatus().classList.add('--hide');
	}

	unPublish()
	{
		this.published = false;
		this.getContainerSiteStatusRound().className = 'landing-sites__status-round --alert';
		this.getContainerSiteStatusTitle().innerText = Loc.getMessage('LANDING_SITE_TILE_STATUS_NOT_PUBLISHED');
		this.getContainerPreviewStatus().classList.remove('--hide');
	}

	getEditableTitle()
	{
		if (!this.editableTitle)
		{
			this.editableTitle = new EditableTitle({
				phone: this.phone,
				type: 'title',
				item: this,
				url: this.contactsUrl,
				disabled: !this.access.settings,
			});
		}

		return this.editableTitle;
	}

	getContainerInfo()
	{
		if (!this.$containerInfo)
		{
			this.$containerInfo = Tag.render`
				<div class="landing-sites__container --white-bg">
					<div class="landing-sites__container-left">
						<div class="landing-sites__title">
							<div class="landing-sites__title-text" title="${Text.encode(this.title)}">${Text.encode(this.title)}</div>
						</div>
						${this.phone ? this.getEditableTitle().getContainer() : ''}
					</div>
					<div class="landing-sites__container-right">
						${this.getContainerSiteStatus()}
						${this.getContainerSiteMore()}
					</div>
				</div>
			`;
		}

		return this.$containerInfo;
	}

	updateDomainStatus(status: string, statusText: string)
	{
		// success
		// alert
		// danger
		// clock
		!status ? status = '' : null;
		this.getContainerDomainStatus().className = 'landing-sites__container-status --' + status;

		!statusText ? statusText = '' : null;
		this.updateDomainStatusMessage(statusText);
	}

	getContainerDomainStatus()
	{
		if (!this.$containerDomainStatus)
		{
			this.$containerDomainStatus = Tag.render`
				<div class="landing-sites__container-status --${this.domainStatus}"></div>
			`;
		}

		return this.$containerDomainStatus;
	}

	getEditableUrl()
	{
		if (!this.editableUrl)
		{
			this.editableUrl = new EditableTitle({
				title: this.url,
				type: 'url',
				item: this,
				url: this.domainUrl,
				disabled: !this.access.settings,
			});
		}

		return this.editableUrl;
	}

	getContainerDomainStatusIcon()
	{
		if (!this.$containerDomainStatusIcon)
		{
			this.$containerDomainStatusIcon = Tag.render`
				<div class="landing-sites__status-icon --${this.domainStatus}"></div>
			`;
		}

		return this.$containerDomainStatusIcon;
	}

	getContainerDomainStatusTitle()
	{
		if (!this.$containerDomainStatusTitle)
		{
			let title = Loc.getMessage('LANDING_SITE_TILE_OPEN');

			this.$containerDomainStatusTitle = Tag.render`
				<div class="landing-sites__status-title">
					${title}
				</div>`;
		}

		return this.$containerDomainStatusTitle;
	}

	updateDomainStatusMessage(text: string)
	{
		!text ? text = '' : null;

		this.getContainerDomainStatusMessage().innerText = text;
		this.domainStatusMessage = text;
	}

	getContainerDomainStatusMessage()
	{
		if (!this.$containerDomainStatusMessage)
		{
			!this.domainStatusMessage ? this.domainStatusMessage = '' : null;
			this.$containerDomainStatusMessage = Tag.render`
				<div class="landing-sites__sub-title">${this.domainStatusMessage}</div>
			`;
		}

		return this.$containerDomainStatusMessage;
	}

	getContainerDomainLink()
	{
		if (!this.$containerDomainLink)
		{
			this.$containerDomainLink = Tag.render`
				<div class="landing-sites__status landing-sites__status-${this.id}">
					${this.getContainerDomainStatusIcon()}
					${this.getContainerDomainStatusTitle()}
				</div>
			`;

			Event.bind(this.$containerDomainLink, 'click', () => {
				this.getPopupHelper().show(this.published ? 'link' : 'notPublished');
			});
		}

		return this.$containerDomainLink;
	}

	getContainerDomain()
	{
		if (!this.$containerDomain)
		{
			this.$containerDomain = Tag.render`
				<div class="landing-sites__container --white-bg --white-bg--alpha --domain">
					${this.getContainerDomainStatus()}
					<div class="landing-sites__container-left">
						${this.getEditableUrl().getContainer()}
						${this.getContainerDomainStatusMessage()}
					</div>
					<div class="landing-sites__container-right">
						${this.getContainerDomainLink()}
					</div>
				</div>
			`;
		}

		return this.$containerDomain;
	}

	getContainerPreviewImage()
	{
		if (!this.$containerPreviewImage)
		{
			this.$containerPreviewImage = Tag.render`<div class="landing-sites__preview-image ${this.published ? '' : '--not-published'}"></div>`;

			this.$containerPreviewImage.style.backgroundImage = 'url(' + this.preview + ')';
			this.$containerPreviewImage.style.backgroundSize = 'cover';
			if (this.published && this.cloudPreview && (this.cloudPreview !== this.preview))
			{
				this.lazyLoadCloudPreview();
			}
		}

		return this.$containerPreviewImage;
	}

	lazyLoadCloudPreview()
	{
		const previewUrl =
			this.cloudPreview
			+ ((this.cloudPreview.indexOf('?') > 0) ? '&' : '?')
			+ 'refreshed' + (Date.now()/86400000|0)
		;
		const xhr = new XMLHttpRequest();
		xhr.open("HEAD", previewUrl);
		xhr.onload = () => {
			const expires = xhr.getResponseHeader("expires");
			if (
				expires
				&& (new Date(expires)) <= (new Date())
			)
			{
				setTimeout(this.lazyLoadCloudPreview, 3000);
			}
			else
			{
				this.$containerPreviewImage.style.backgroundImage = 'url(' + previewUrl + ')';
			}
		};
		xhr.send();
	}

	getContainerPreviewStatus()
	{
		if (!this.$containerPreviewStatus)
		{
			this.$containerPreviewStatus = Tag.render`
				<div class="landing-sites__preview-status --not-published ${this.published ? '--hide' : ''}">
					<div class="landing-sites__preview-status-wrapper">
						<div class="landing-sites__preview-status-icon"></div>
						<div class="landing-sites__preview-status-text">
							${Loc.getMessage('LANDING_SITE_TILE_STATUS_NOT_PUBLISHED')}
						</div>
					</div>
				</div>
			`;

			Event.bind(this.$containerPreviewStatus, 'mouseenter', () => {
				this.$containerPreviewStatus.style.width = this.$containerPreviewStatus.firstElementChild.offsetWidth + 'px';
			});

			Event.bind(this.$containerPreviewStatus, 'mouseleave', () => {
				this.$containerPreviewStatus.style.width = null;
			});
		}

		return this.$containerPreviewStatus;
	}

	getContainerPreviewShowPages()
	{
		if (!this.$containerPreviewShowPages)
		{
			this.$containerPreviewShowPages = Tag.render`
				<div class="landing-sites__preview-show">
					${Loc.getMessage('LANDING_SITE_TILE_SHOW_PAGES')}
				</div>
			`;
		}

		return this.$containerPreviewShowPages;
	}

	getContainerPreviewInstruction()
	{
		if (!this.$containerPreviewInstruction)
		{
			this.$containerPreviewInstruction = Tag.render`
				<div class="landing-sites__preview-leadership">
					<div class="landing-sites__preview-leadership-text">
						${Loc.getMessage('LANDING_SITE_TILE_INSTRUCTION')}
					</div>
				</div>
			`;

			Event.bind(this.$containerPreviewInstruction, 'click', () => {
				this.getLeadership().show();
			});
		}

		return this.$containerPreviewInstruction;
	}

	getContainerLinks()
	{
		if (!this.$containerLinks)
		{
			this.$containerLinks = Tag.render`<div class="landing-sites__container --without-bg --auto-height --flex"></div>`;

			this.menuBottomItems.map(menuItem => {
				this.$containerLinks.appendChild(this.getContainerLinksItem(menuItem.code, menuItem.href, menuItem.text));
			});
		}

		return this.$containerLinks;
	}

	getContainerLinksItem(type: string, link: string, title: string)
	{
		const container = Tag.render`
			<a href="${link}" class="landing-sites__container-link landing-sites__container-link-${this.id} --white-bg--alpha">
				<div class="landing-sites__container-link-icon --${type}"></div>
				<div class="landing-sites__container-link-text">${title}</div>
			</a>
		`;

		Event.bind(container, 'click', event => {
			EventEmitter.emit('BX.Landing.SiteTile:onBottomMenuClick', [type, event, this]);
		});

		return container;
	}

	getLeadership()
	{
		if (!this.leadership)
		{
			this.leadership = new LeaderShip({
				id: this.id,
				item: this,
				articles: this.articles,
			});
		}
		return this.leadership;
	}

	remove()
	{
		this.getContainer().classList.add('--remove');
		Event.bind(this.getContainer(), 'transitionend', () => {
			let items = this.grid.getItems();
			items.splice(items.indexOf(items), 1);
			Dom.remove(this.getContainer());
		});
	}

	lock()
	{
		this.getContainer().classList.add('--lock');
		if (!this.loader)
		{
			this.loader = new BX.Loader({
				target: this.getContainer(),
				size: 100,
			});
		}

		this.loader.show();
	}

	unLock()
	{
		this.getContainer().classList.remove('--lock');
		if (this.loader)
		{
			this.loader.hide();
		}
	}

	fade()
	{
		this.getContainer().classList.add('--fade');
	}

	unFade()
	{
		this.getContainer().classList.remove('--fade');
	}

	active()
	{
		this.getContainer().classList.add('--active');
	}

	unActive()
	{
		this.getContainer().classList.remove('--active');
	}

	getPopupHelper(): PopupHelper
	{
		if (!this.popupHelper)
		{
			this.popupHelper = new PopupHelper({
				id: this.id,
				url: this.url,
				itemObj: this,
				fullUrl: this.fullUrl,
				ordersUrl: this.ordersUrl,
				indexEditUrl: this.indexEditUrl,
				notPublishedText: this.notPublishedText,
			});
		}

		return this.popupHelper;
	}

	getContainerWrapper()
	{
		if (!this.$containerWrapper)
		{
			this.$containerWrapper = Tag.render`
				<div class="landing-sites__item-container">
					<a href="${this.pagesUrl}" class="landing-sites__preview">
						${this.getContainerPreviewImage()}
						${this.getContainerPreviewStatus()}
						${this.getContainerPreviewShowPages()}
						${this.articles.length > 0 ? this.getContainerPreviewInstruction() : ''}
					</a>
					${this.getContainerInfo()}
					${this.getContainerDomain()}
					${this.getContainerLinks()}
				</div>
			`;
		}

		return this.$containerWrapper;
	}

	getContainer()
	{
		if (!this.$container)
		{
			this.$container = Tag.render`
				<div class="landing-sites__grid-item ${this.deleted ? '--deleted' : ''}">
					<div class="landing-sites__item" id="landing-sites__grid-item--${this.id}">
						${this.getLeadership().getContainer()}
						${this.getContainerWrapper()}
						${this.getPopupHelper().getContainer()}
					</div>
				</div>
			`;
		}

		return this.$container;
	}
}
