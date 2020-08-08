/**
 * Bitrix Messenger
 * Recent list controller
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2020 Bitrix
 */

import { RestMethod, RestMethodHandler } from "im.const";

export class RecentController
{
	constructor()
	{
		this.paginationCount = 50;
		this.loadingMore = false;
		this.hasMoreToLoad = true;
		this.placeholderCount = 0;
		this.lastMessageDate = null;
	}

	setCoreController(controller)
	{
		this.controller = controller;
	}

	drawPlaceholders()
	{
		let placeholders = this.generatePlaceholders(this.paginationCount);
		this.controller.getStore().dispatch('recent/set', { general: placeholders })
	}

	getRecentData()
	{
		let queryParams = {
			'SKIP_OPENLINES': 'Y',
			'LIMIT': this.paginationCount
		};

		this.controller.restClient.callMethod(RestMethod.imRecentList, queryParams, null, null).then(result => {
			this.lastMessageDate = this.getLastMessageDate(result.data().items);

			if (result.data().items.length !== this.paginationCount)
			{
				this.hasMoreToLoad = false;
			}

			this.controller.getStore().dispatch('recent/clearPlaceholders');
			this.controller.executeRestAnswer(RestMethodHandler.imRecentList, result);
		})
	}

	loadMore()
	{
		if (
			!this.loadingMore &&
			this.hasMoreToLoad
		)
		{
			this.loadingMore = true;
			let firstMessageToUpdate = this.placeholderCount;
			let placeholders = this.generatePlaceholders(this.paginationCount);

			this.controller.getStore().dispatch('recent/set', { general: placeholders })
				.then(() => {
					let queryParams = {
						'SKIP_OPENLINES': 'Y',
						'LIMIT': this.paginationCount,
						'LAST_MESSAGE_UPDATE': this.lastMessageDate
					};

					this.controller.restClient.callMethod(RestMethod.imRecentList, queryParams, null, null)
						.then(result => {
							let items = result.data().items;

							if (!items || items.length === 0)
							{
								this.controller.getStore().dispatch('recent/clearPlaceholders');
								return false;
							}

							if (items.length !== this.paginationCount)
							{
								this.hasMoreToLoad = false;
							}

							this.lastMessageDate = this.getLastMessageDate(items);
							let data = this.prepareDataForModels(items);

							this.controller.getStore().dispatch('users/set', data.users)
								.then(() => {
									this.controller.getStore().dispatch('dialogues/set', data.dialogues)
										.then(() => {
											this.controller.getStore().dispatch('recent/updatePlaceholders',
												{
													items: data.recent,
													firstMessage: firstMessageToUpdate
												})
												.then(() => {
													this.loadingMore = false;

													if (!this.hasMoreToLoad)
													{
														this.controller.getStore().dispatch('recent/clearPlaceholders');
													}
												});
										});
								});
						});
				});
		}
	}

	generatePlaceholders(amount)
	{
		let placeholders = [];

		for (let i = 0; i < amount; i++)
		{
			placeholders.push({
				id: 'placeholder' + this.placeholderCount,
				templateId: 'placeholder' + this.placeholderCount,
				template: 'placeholder',
				sectionCode: 'general'
			});
			this.placeholderCount++;
		}

		return placeholders;
	}

	getLastMessageDate(collection)
	{
		return collection.slice(-1)[0].date_update;
	}

	openOldDialog(event)
	{
		if (event.id !== 'notify')
		{
			BXIM.openMessenger(event.id);
		}
		else
		{
			BXIM.openNotify();
		}
	}

	openOldContextMenu(event)
	{
		event.$event.preventDefault();
		let recentItem = this.controller.getStore().getters['recent/get'](event.id);

		let params = {
			userId: event.id,
			userIsChat: typeof event.id === 'string',
			dialogIsPinned: recentItem.element.pinned
		};
		BXIM.messenger.openPopupMenu(event.$event.target, 'contactList', undefined, params);
	}

	prepareDataForModels(items)
	{
		let result = {
			users: [],
			dialogues: [],
			recent: []
		};

		items.forEach(item => {
			let userId = 0;
			let chatId = 0;

			if (item.user && item.user.id > 0)
			{
				userId = item.user.id;
				result.users.push(item.user);
			}
			if (item.chat)
			{
				chatId = item.chat.id;
				result.dialogues.push(Object.assign(item.chat, {dialogId: item.id}));
			}
			else
			{
				result.dialogues.push(Object.assign({}, {dialogId: item.id}));
			}
			result.recent.push({
				...item,
				avatar: item.avatar.url,
				color: item.avatar.color,
				userId: userId,
				chatId: chatId
			});
		});

		return result;
	}
}