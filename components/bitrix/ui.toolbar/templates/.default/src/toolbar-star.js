import { ready, Event, Type, Uri, Dom, Loc, bind } from 'main.core';
import { PopupWindowManager } from 'main.popup';

export class ToolbarStar
{
	initialized: boolean;
	currentPageInMenu: false;
	starContNode: HTMLElement;

	constructor()
	{
		this.initialized = false;
		this.currentPageInMenu = false;
		this.starContNode = null;

		ready(() => this.init());

		Event.EventEmitter.subscribe('onFrameDataProcessed', () => {
			this.init();
		});
		// BX.addCustomEvent('onFrameDataProcessed', () => this.init());
	}

	init(): boolean
	{
		this.starContNode = document.getElementById('uiToolbarStar');
		if (!this.starContNode || this.initialized)
		{
			return false;
		}

		this.initialized = true;

		let currentFullPath = Dom.attr(this.starContNode, 'data-bx-url');
		if (!Type.isStringFilled(currentFullPath))
		{
			currentFullPath = document.location.pathname + document.location.search;
		}
		currentFullPath = Uri.removeParam(currentFullPath, ['IFRAME', 'IFRAME_TYPE']);

		top.BX.addCustomEvent('BX.Bitrix24.LeftMenuClass:onSendMenuItemData', (params) => {
			this.processMenuItemData(params);
		});
		top.BX.addCustomEvent('BX.Bitrix24.LeftMenuClass:onStandardItemChangedSuccess', (params) => {
			this.onStandardItemChangedSuccess(params);
		});

		top.BX.onCustomEvent('UI.Toolbar:onRequestMenuItemData', [
			{
				currentFullPath,
				context: window,
			},
		]);

		return true;
	}

	processMenuItemData(params): void | boolean
	{
		if (params.context && params.context !== window)
		{
			return;
		}

		this.currentPageInMenu = params.currentPageInMenu;

		if (Type.isObjectLike(params.currentPageInMenu))
		{
			Dom.addClass(this.starContNode, 'ui-toolbar-star-active');
		}

		this.starContNode.title = Loc.getMessage(
			Dom.hasClass(this.starContNode, 'ui-toolbar-star-active')
				? 'UI_TOOLBAR_DELETE_PAGE_FROM_LEFT_MENU'
				: 'UI_TOOLBAR_ADD_PAGE_TO_LEFT_MENU',
		);

		// default page
		if (Type.isDomNode(this.currentPageInMenu)
			&& Dom.attr(this.currentPageInMenu, 'data-type') !== 'standard'
		)
		{
			this.starContNode.title = Loc.getMessage('UI_TOOLBAR_STAR_TITLE_DEFAULT_PAGE');
			bind(this.starContNode, 'click', () => {
				this.showMessage(Loc.getMessage('UI_TOOLBAR_STAR_TITLE_DEFAULT_PAGE_DELETE_ERROR'));
			});

			return true;
		}

		// any page
		bind(this.starContNode, 'click', () => {
			let pageTitle = document.getElementById('pagetitle')?.innerText || '';
			const titleTemplate = this.starContNode.getAttribute('data-bx-title-template');
			if (Type.isStringFilled(titleTemplate))
			{
				pageTitle = titleTemplate.replace(/#page_title#/i, pageTitle);
			}

			let pageLink = this.starContNode.getAttribute('data-bx-url');
			if (!Type.isStringFilled(pageLink))
			{
				pageLink = document.location.pathname + document.location.search;
			}
			pageLink = Uri.removeParam(pageLink, ['IFRAME', 'IFRAME_TYPE']);

			top.BX.onCustomEvent('UI.Toolbar:onStarClick', [
				{
					isActive: Dom.hasClass(this.starContNode, 'ui-toolbar-star-active'),
					context: window,
					pageTitle,
					pageLink,
				},
			]);
		});
	}

	onStandardItemChangedSuccess(params): void
	{
		if (!Type.isBoolean(params.isActive) || !this.starContNode || (params.context && params.context !== window))
		{
			return;
		}

		if (params.isActive)
		{
			this.showMessage(Loc.getMessage('UI_TOOLBAR_ITEM_WAS_ADDED_TO_LEFT'));
			this.starContNode.title = Loc.getMessage('UI_TOOLBAR_DELETE_PAGE_FROM_LEFT_MENU');
			Dom.addClass(this.starContNode, 'ui-toolbar-star-active');
		}
		else
		{
			this.showMessage(Loc.getMessage('UI_TOOLBAR_ITEM_WAS_DELETED_FROM_LEFT'));
			this.starContNode.title = Loc.getMessage('UI_TOOLBAR_ADD_PAGE_TO_LEFT_MENU');
			Dom.removeClass(this.starContNode, 'ui-toolbar-star-active');
		}
	}

	showMessage(message) {
		let popup = PopupWindowManager.create('left-menu-message', this.starContNode, {
			content: message,
			darkMode: true,
			offsetTop: 2,
			offsetLeft: 0,
			angle: true,
			autoHide: true,
			events: {
				onPopupClose: () => {
					if (popup)
					{
						popup.destroy();
						popup = null;
					}
				},
			},
		});

		popup.show();

		setTimeout(() => {
			if (popup)
			{
				popup.destroy();
				popup = null;
			}
		}, 3000);
	}
}
