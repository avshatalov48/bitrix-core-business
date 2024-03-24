import {Type, Loc, ajax, Event, Dom, Tag} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {MenuManager} from 'main.popup';

import {PinnedPanel} from './pinned';
import {Post} from './post';
import {Informer} from './informer';
import {TaskCreator} from './taskcreator';
import {Loader} from './loader';
import {Forum} from './forum';
import {MoreButton} from './morebutton';
import {Filter} from './filter';
import {Page} from './page';
import {ContentView} from './contentview';
import {CommentForm} from './commentform';

import './css/feed.css';
import './css/gratitude.css';
import './css/important.css';
import './css/warning.css';
import './css/taskcreator.css';
import './css/task.css';
import './css/timeman.css';
import './css/calendar.css';

class Feed
{
	constructor()
	{
		this.entryData = {};
		this.feedInitialized = false;
		this.moreButtonDataList = new Map();
	}

	initOnce(params)
	{
		const loaderContainer = document.getElementById('feed-loader-container');
		if (!Type.isPlainObject(params))
		{
			params = {};
		}

		if (Type.isStringFilled(params.signedParameters))
		{
			PageInstance.setSignedParameters(params.signedParameters);
		}

		if (Type.isStringFilled(params.context))
		{
			PageInstance.setContext(params.context);
		}

		if (Type.isStringFilled(params.componentName))
		{
			PageInstance.setComponentName(params.componentName);
		}

		if (Type.isStringFilled(params.commentFormUID))
		{
			PageInstance.commentFormUID = params.commentFormUID;
		}

		if (loaderContainer)
		{
			Event.bind(loaderContainer, 'animationend', Loader.onAnimationEnd)
			Event.bind(loaderContainer, 'webkitAnimationEnd', Loader.onAnimationEnd)
			Event.bind(loaderContainer, 'oanimationend', Loader.onAnimationEnd)
			Event.bind(loaderContainer, 'MSAnimationEnd', Loader.onAnimationEnd)
		}

		EventEmitter.subscribe('BX.Forum.Spoiler:toggle', Forum.processSpoilerToggle);

		FilterInstance.init({
			filterId: params.filterId,
		});

		InformerInstance.init({
			isSpaceFeatureEnabled: params.isSpaceEnabled,
			userId: params.userId,
			spaceId: params.spaceId,
		});

		if (
			Type.isStringFilled(params.crmEntityTypeName)
			&& !Type.isUndefined(params.crmEntityId)
			&& parseInt(params.crmEntityId) > 0
		)
		{
			FilterInstance.initEventsCrm();
		}

		BX.UserContentView.init();

		document.getElementById('log_internal_container').addEventListener('click', (e) => {
			const tagValue = e.target.getAttribute('bx-tag-value');
			if (!Type.isStringFilled(tagValue))
			{
				return;
			}

			if (FilterInstance.clickTag(tagValue))
			{
				e.preventDefault();
				e.stopPropagation();
			}
		}, true);

		const noTasksNotificationCloseIcon = document.getElementById('feed-notification-notasks-close-btn');
		const noTasksNotificationReadButton = document.getElementById('feed-notification-notasks-read-btn');

		if (noTasksNotificationCloseIcon)
		{
			Event.bind(noTasksNotificationCloseIcon, 'click', this.setNoTasksNotificationRead.bind(this));
		}
		if (noTasksNotificationReadButton)
		{
			Event.bind(noTasksNotificationReadButton, 'click', this.setNoTasksNotificationRead.bind(this));
		}
	}

	init()
	{
		if (this.feedInitialized)
		{
			return;
		}

		PinnedPanelInstance.init();

		this.feedInitialized = true;
	}

	changeFollow(params)
	{
		const logId = (params.logId ? parseInt(params.logId) : 0);
		if (!logId)
		{
			return false;
		}

		const followNode = document.getElementById('log_entry_follow_' + logId);
		const valueOld = (followNode && followNode.getAttribute('data-follow') === 'Y' ? 'Y' : 'N');
		const valueNew = (valueOld === 'Y' ? 'N' : 'Y');

		this.renderFollow({
			logId: logId,
			value: valueNew
		});

		ajax.runAction('socialnetwork.api.livefeed.changeFollow', {
			data: {
				logId: logId,
				value: valueNew
			},
			analyticsLabel: {
				b24statAction: (valueNew === 'Y' ? 'setFollow' : 'setUnfollow')
			}
		}).then((response) => {
			if (!response.data.success)
			{
				this.renderFollow({
					logId: logId,
					value: valueOld
				});
			}
		}, () => {
			this.renderFollow({
				logId: logId,
				value: valueOld
			});
		});

		return false;
	}

	renderFollow(params)
	{
		const logId = (params.logId ? parseInt(params.logId) : 0);
		if (!logId)
		{
			return;
		}
		const followNode = document.getElementById('log_entry_follow_' + logId);
		const value = (params.value && params.value === 'Y' ? 'Y' : 'N');

		if (followNode)
		{
			followNode.setAttribute('data-follow', value);
		}

		const textNode = (followNode ? followNode.querySelector('a') : null);
		if (textNode)
		{
			textNode.innerHTML = Loc.getMessage('SONET_EXT_LIVEFEED_FOLLOW_TITLE_' + value);
		}

		const postNode = (followNode ? followNode.closest('.feed-post-block') : null);
		if (postNode)
		{
			if (value === 'N')
			{
				postNode.classList.add('feed-post-block-unfollowed');
			}
			else if (value === 'Y')
			{
				postNode.classList.remove('feed-post-block-unfollowed');
			}
		}
	}

	changeFavorites(params)
	{
		const logId = (params.logId ? parseInt(params.logId) : 0);
		const event = (params.event ? params.event : null);

		let node = (params.node ? params.node : null);
		let newState = (params.newState ? params.newState : null);

		if (Type.isStringFilled(node))
		{
			node = document.getElementById(node);
		}

		if (!logId)
		{
			return;
		}

		let menuItem = null;

		if (event)
		{
			menuItem = event.target;
			if (!menuItem.classList.contains('menu-popup-item-text'))
			{
				menuItem = menuItem.querySelector('.menu-popup-item-text');
			}
		}

		let nodeToAdjust = null;

		if (Type.isDomNode(node))
		{
			nodeToAdjust = (
				node.classList.contains('feed-post-important-switch')
					? node
					: node.querySelector('.feed-post-important-switch')
			);
		}

		if (typeof this.entryData[logId] == 'undefined')
		{
			this.entryData[logId] = {};
		}

		if (typeof this.entryData[logId].favorites != 'undefined')
		{
			newState = (this.entryData[logId].favorites ? 'N' : 'Y');
			this.entryData[logId].favorites = !this.entryData[logId].favorites;
		}
		else if (nodeToAdjust)
		{
			newState = (
				nodeToAdjust.classList.contains('feed-post-important-switch-active')
					? 'N'
					: 'Y'
			);
			this.entryData[logId].favorites = (newState == 'Y');
		}

		if (!newState)
		{
			return;
		}

		this.adjustFavoritesControlItem(nodeToAdjust, newState);
		this.adjustFavoritesMenuItem(menuItem, newState);

		ajax.runAction('socialnetwork.api.livefeed.changeFavorites', {
			data: {
				logId: logId,
				value: newState,
			},
			analyticsLabel: {
				b24statAction: (newState == 'Y' ? 'addFavorites' : 'removeFavorites')
			}
		}).then(response =>
		{
			if (
				Type.isStringFilled(response.data.newValue)
				&& ['Y', 'N'].includes(response.data.newValue)
			)
			{
				this.entryData[logId].favorites = (response.data.newValue == 'Y');
			}

			this.adjustFavoritesControlItem(nodeToAdjust, response.data.newValue);
			this.adjustFavoritesMenuItem(menuItem, response.data.newValue);

		}, () => {
			this.entryData[logId].favorites = !this.entryData[logId].favorites;
		});
	}

	adjustFavoritesMenuItem(menuItemNode, state)
	{
		if (
			!Type.isDomNode(menuItemNode)
			|| !['Y', 'N'].includes(state)
		)
		{
			return;
		}

		menuItemNode.innerHTML = this.getMenuTitle(state === 'Y');
	}

	adjustFavoritesControlItem(node, state)
	{
		if (
			!Type.isDomNode(node)
			|| !['Y', 'N'].includes(state)
		)
		{
			return;
		}

		node.title = this.getMenuTitle(state === 'Y');
		if (state == 'Y')
		{
			node.classList.add('feed-post-important-switch-active');
		}
		else
		{
			node.classList.remove('feed-post-important-switch-active');
		}
	}

	getMenuTitle(state: boolean): string
	{
		return Loc.getMessage(`SONET_EXT_LIVEFEED_MENU_TITLE_FAVORITES_${state ? 'Y' : 'N'}`);
	}

	delete(params)
	{
		const logId = (params.logId ? parseInt(params.logId) : 0);
		const node = (Type.isStringFilled(params.nodeId) ? document.getElementById(params.nodeId) : null);
		const ind = (params.ind ? params.ind : '');

		if (
			logId <= 0
			|| !node
		)
		{
			return;
		}

		ajax.runAction('socialnetwork.api.livefeed.deleteEntry', {
			data: {
				logId: logId,
			},
			analyticsLabel: {
				b24statAction: 'deleteLogEntry',
			}
		}).then((response) => {
			if (response.data.success)
			{
				if (!Type.isUndefined(ind))
				{
					MenuManager.destroy(Post.getMenuId(ind));
				}
				this.deleteSuccess(node);
			}
			else
			{
				this.deleteFailure(node);
			}
		}, () => {
			this.deleteFailure(node);
		});
	}

	deleteSuccess(node)
	{
		if (!Type.isDomNode(node))
		{
			return;
		}

		(new BX.fx({
			time: 0.5,
			step: 0.05,
			type: 'linear',
			start: node.offsetHeight,
			finish: 56,
			callback: (height) => {
				node.style.height = `${height}px`;
			},
			callback_start: () => {
				node.style.overflow = 'hidden';
				node.style.minHeight = 0;
			},
			callback_complete: () => {
				node.style.marginBottom = 0;
				Dom.clean(node);
				node.classList.add('feed-post-block-deleted');
				node.appendChild(Tag.render`<div class="feed-add-successfully"><span class="feed-add-info-text"><span class="feed-add-info-icon"></span><span>${Loc.getMessage('SONET_EXT_LIVEFEED_DELETE_SUCCESS')}</span></span></span></div>`);
			}
		})).start();
	}

	deleteFailure(node)
	{
		if (!Type.isDomNode(node))
		{
			return;
		}

		node.insertBefore(Tag.render`<div class="feed-add-error" style="margin: 18px 37px 4px 84px;"><span class="feed-add-info-text"><span class="feed-add-info-icon"></span><span>${Loc.getMessage('sonetLMenuDeleteFailure')}</span></span></div>`, node.firstChild);
	}

	setMoreButtons(value)
	{
		this.moreButtonDataList = value;
	}

	getMoreButtons()
	{
		return this.moreButtonDataList;
	}

	clearMoreButtons()
	{
		for (const buttonData of this.moreButtonDataList)
		{
			const moreButton = document.getElementById(buttonData.outerBlockID)?.querySelector(`.${MoreButton.cssClass.more}`);
			if (!moreButton?.hasClickListener)
			{
				return;
			}
		}

		this.moreButtonDataList.clear();
	}

	addMoreButton(key, data)
	{
		this.moreButtonDataList.set(key, data);
	}

	setNoTasksNotificationRead(event)
	{
		const notificationNode = event.currentTarget.closest('.feed-notification-container');
		if (!notificationNode)
		{
			return;
		}

		ajax.runAction('socialnetwork.api.livefeed.readNoTasksNotification', {
			data: {}
		}).then((response) => {
			if (!response.data.success)
			{
				return;
			}

			notificationNode.style.height = notificationNode.offsetHeight + 'px';

			setTimeout(() => {
				notificationNode.classList.add('feed-notification-container-collapsed');
			}, 10);
			setTimeout(() => {
				notificationNode.parentNode.removeChild(notificationNode);
			}, 250);

		}, () => {});
	}

}

const FeedInstance = new Feed();
const PinnedPanelInstance = new PinnedPanel();
const InformerInstance = new Informer();
const FilterInstance = new Filter();
const PageInstance = new Page();
const MoreButtonInstance = new MoreButton();
new TaskCreator();

export {
	FeedInstance,
	PinnedPanelInstance,
	InformerInstance,
	FilterInstance,
	PageInstance,
	MoreButtonInstance,
	Post,
	TaskCreator,
	Loader,
	MoreButton,
	ContentView,
	CommentForm,
};