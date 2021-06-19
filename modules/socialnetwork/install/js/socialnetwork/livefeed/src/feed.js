import {Type, Loc, ajax, Event} from "main.core";
import {PinnedPanel} from "./pinned";
import {Post} from "./post";
import {Informer} from "./informer";

import './css/feed.css';
import './css/gratitude.css';
import './css/important.css';
import './css/warning.css';

class Feed
{
	constructor()
	{
		this.entryData = {};
		this.feedInitialized = false;
	}

	init()
	{
		if (this.feedInitialized)
		{
			return;
		}

		PinnedPanelInstance.init();
		InformerInstance.init();

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
				value: newState
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

		}, response => {
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
}

let FeedInstance = null;
let PinnedPanelInstance = null;
let PostInstance = null;
let InformerInstance = null;

Event.ready(() =>
{
	FeedInstance = new Feed();
	PinnedPanelInstance = new PinnedPanel();
	PostInstance = new Post();
	InformerInstance = new Informer();
});

export {
	FeedInstance,
	PinnedPanelInstance,
	PostInstance,
	InformerInstance,
};