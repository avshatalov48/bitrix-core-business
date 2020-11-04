import {Type, Loc, ajax, Event} from "main.core";
import {PinnedPanel} from "./pinned";

class Feed
{
	constructor()
	{
		this.init();
		this.entryData = {};
	}

	init()
	{

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

Event.ready(() =>
{
	FeedInstance = new Feed();
	PinnedPanelInstance = new PinnedPanel();
});

export {
	FeedInstance,
	PinnedPanelInstance
};