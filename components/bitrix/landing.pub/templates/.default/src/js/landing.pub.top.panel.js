import {Event, Cache, Dom, Loc, Type} from 'main.core';
import {SliderHacks} from 'landing.sliderhacks';

const onEditButtonClick = Symbol('onEditButtonClick');
const onBackButtonClick = Symbol('onBackButtonClick');
const onForwardButtonClick = Symbol('onForwardButtonClick');
const onCopyLinkButtonClick = Symbol('onCopyLinkButtonClick');
const onUniqueViewIconClick = Symbol('onUniqueViewIconClick');

export class TopPanel
{
	static cache = new Cache.MemoryCache();

	constructor(data)
	{
		this.userData = data.userData;

		Event.bind(TopPanel.getEditButton(), 'click', this[onEditButtonClick]);
		Event.bind(TopPanel.getBackButton(), 'click', this[onBackButtonClick]);
		Event.bind(TopPanel.getForwardButton(), 'click', this[onForwardButtonClick]);
		Event.bind(TopPanel.getCopyLinkButton(), 'click', this[onCopyLinkButtonClick]);
		Event.bind(TopPanel.getUniqueViewIcon(), 'click', this[onUniqueViewIconClick]);

		TopPanel.pushHistory(window.location.toString());
		TopPanel.checkNavButtonsActivity();
		TopPanel.checkHints();
		TopPanel.initUniqueViewPopup(this.userData);
	}

	static getLayout(): HTMLDivElement
	{
		return TopPanel.cache.remember('layout', () => {
			return document.querySelector('.landing-pub-top-panel');
		});
	}

	static getEditButton(): HTMLAnchorElement
	{
		return TopPanel.cache.remember('editButton', () => {
			return TopPanel.getLayout().querySelector('.landing-pub-top-panel-edit-button');
		});
	}

	[onEditButtonClick](event)
	{
		event.preventDefault();

		const href = Dom.attr(event.currentTarget, 'href');
		const landingId = Dom.attr(event.currentTarget, 'data-landingId');

		if (Type.isString(href) && href !== '')
		{
			TopPanel.openSlider(href, landingId);
		}
	}

	static openSlider(url, landingId)
	{
		BX.SidePanel.Instance.open(url, {
			cacheable: false,
			customLeftBoundary: 60,
			allowChangeHistory: false,
			events: {
				onClose() {
					void SliderHacks.reloadSlider(
						window.location.toString().split('#')[0] + '#landingId' + landingId
					);
				},
			},
		});
	}

	// HISTORY save
	static history = [];
	static historyState;

	static pushHistory(url)
	{
		if (!Type.isNumber(TopPanel.historyState))
		{
			TopPanel.historyState = -1; // will increase later
		}

		if (TopPanel.historyState < TopPanel.history.length - 1)
		{
			TopPanel.history.splice(TopPanel.historyState + 1);
		}

		TopPanel.history.push(url);
		TopPanel.historyState++;
	}

	static checkNavButtonsActivity()
	{
		Dom.removeClass(TopPanel.getForwardButton(), 'ui-btn-disabled');
		Dom.removeClass(TopPanel.getBackButton(), 'ui-btn-disabled');

		if (
			!Type.isArrayFilled(TopPanel.history)
			|| !Type.isNumber(TopPanel.historyState)
			|| TopPanel.history.length === 1
		)
		{
			Dom.addClass(TopPanel.getForwardButton(), 'ui-btn-disabled');
			Dom.addClass(TopPanel.getBackButton(), 'ui-btn-disabled');
			return;
		}

		if (TopPanel.historyState === 0)
		{
			Dom.addClass(TopPanel.getBackButton(), 'ui-btn-disabled');
		}

		if (TopPanel.historyState >= TopPanel.history.length - 1)
		{
			Dom.addClass(TopPanel.getForwardButton(), 'ui-btn-disabled');
		}
	}

	static getBackButton(): HTMLAnchorElement
	{
		return TopPanel.cache.remember('backButton', () => {
			const layout = TopPanel.getLayout();
			return layout ? layout.querySelector('.landing-pub-top-panel-back') : null;
		});
	}

	static getForwardButton(): HTMLAnchorElement
	{
		return TopPanel.cache.remember('forwardButton', () => {
			const layout = TopPanel.getLayout();
			return layout ? layout.querySelector('.landing-pub-top-panel-forward') : null;
		});
	}

	static getCopyLinkButton(): HTMLAnchorElement
	{
		return TopPanel.cache.remember('copyLinkButton', () => {
			const layout = TopPanel.getLayout();
			return layout ? layout.querySelector('.landing-page-link-btn') : null;
		});
	}

	static getUniqueViewIcon(): HTMLAnchorElement
	{
		return TopPanel.cache.remember('uniqueViewIcon', () => {
			const layout = TopPanel.getLayout();
			return layout ? layout.querySelector('.landing-pub-top-panel-unique-view') : null;
		});
	}

	static checkHints()
	{
		const linkPage = document.querySelector('.landing-pub-top-panel-chain-link-page');
		if (linkPage)
		{
			if (parseInt(window.getComputedStyle(linkPage).width) < 200)
			{
				Dom.style(linkPage, 'pointer-events', 'none');
			}
			else
			{
				BX.UI.Hint.init(BX('landing-pub-top-panel-chain-link-page'));
			}
		}
	}

	static initUniqueViewPopup(userData)
	{
		const setUserId = userData.id;
		const setUserName = userData.name;
		const avatar = userData.avatar;

		if (setUserId.length === setUserName.length){
			for (let i = 0; i < setUserId.length; i++)
			{
				this.createUserItem(setUserId[i], setUserName[i], avatar[i]);
			}
		}
	}

	static createUserItem(id, name, avatar)
	{
		const itemContainer = document.querySelector('.landing-pub-top-panel-unique-view-popup-item-container');
		const userUrl = window.location.origin + '/company/personal/user/' + id + '/';
		const userItem = BX.Dom.create({
			tag: 'div',
			props: {
				classList: 'landing-pub-top-panel-unique-view-popup-item',
			},
		});
		let userItemAvatar;
		if (avatar && avatar !== '')
		{
			userItemAvatar = BX.Dom.create({
				tag: 'div',
				props: {
					classList: 'landing-pub-top-panel-unique-view-popup-item-avatar',
				},
			});
			avatar = "url('" + avatar + "')";
			Dom.style(userItemAvatar, 'background-image', avatar);
		}
		else
		{
			userItemAvatar = BX.Dom.create({
				tag: 'div',
				props: {
					classList: 'landing-pub-top-panel-unique-view-popup-item-avatar landing-pub-top-panel-unique-view-popup-item-avatar-empty',
				},
			});
		}
		const userItemLink = BX.Dom.create({
			tag: 'a',
			props: {
				classList: 'landing-pub-top-panel-unique-view-popup-item-link',
			},
			text: name,
		});
		Dom.attr(userItemLink, 'href', userUrl);
		Dom.attr(userItemLink, 'target', '_blank');
		Dom.append(userItemAvatar, userItem);
		Dom.append(userItemLink, userItem);
		Dom.append(userItem, itemContainer);
	}

	[onCopyLinkButtonClick](event)
	{
		event.preventDefault();
		const link = BX.util.remove_url_param(window.location.href, ["IFRAME", "IFRAME_TYPE"]);
		const node = event.target;
		if (BX.clipboard.isCopySupported())
		{
			BX.clipboard.copy(link);
			this.timeoutIds = this.timeoutIds || [];
			const popupParams = {
				content: Loc.getMessage('LANDING_TPL_PUB_COPIED_LINK'),
				darkMode: true,
				autoHide: true,
				zIndex: 1000,
				angle: true,
				offsetLeft: 20,
				bindOptions: {
					position: 'top'
				}
			};
			const popup = new BX.PopupWindow(
				'landing_clipboard_copy',
				node,
				popupParams
			);
			popup.show();

			let timeoutId;
			while (timeoutId = this.timeoutIds.pop())
			{
				clearTimeout(timeoutId);
			}
			timeoutId = setTimeout(function(){
				popup.close();
			}, 2000);
			this.timeoutIds.push(timeoutId);
		}
	}

	[onUniqueViewIconClick](event)
	{
		const popup = document.querySelector('.landing-pub-top-panel-unique-view-popup');
		if (Dom.hasClass(popup, 'hide'))
		{
			Dom.removeClass(popup, 'hide');
			setTimeout(function(){
				Dom.addClass(popup, 'hide');
			}, 2000);
		}
		else
		{
			Dom.addClass(popup, 'hide');
		}
	}

	[onBackButtonClick](event)
	{
		event.preventDefault();
		if (
			Type.isArrayFilled(TopPanel.history)
			&& Type.isNumber(TopPanel.historyState)
			&& TopPanel.historyState > 0
		)
		{
			void SliderHacks.reloadSlider(TopPanel.history[--TopPanel.historyState]);
			TopPanel.checkNavButtonsActivity();
		}
	}

	[onForwardButtonClick](event)
	{
		event.preventDefault();

		if (
			Type.isArrayFilled(TopPanel.history)
			&& Type.isNumber(TopPanel.historyState)
			&& (TopPanel.historyState < TopPanel.history.length - 1)
		)
		{
			void SliderHacks.reloadSlider(TopPanel.history[++TopPanel.historyState]);
			TopPanel.checkNavButtonsActivity();
		}
	}
}