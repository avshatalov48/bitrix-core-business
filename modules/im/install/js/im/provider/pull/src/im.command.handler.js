/**
 * Bitrix Messenger
 * Im pull commands (Pull Command Handler)
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

import {PullClient} from "pull.client";
import {VuexBuilderModel} from 'ui.vue.vuex';

class ImPullCommandHandler
{
	static create(params = {})
	{
		return new this(params);
	}

	constructor(params = {})
	{
		if (typeof params.controller === 'object' && params.controller)
		{
			this.controller = params.controller;
		}
		if (typeof params.store === 'object' && params.store)
		{
			this.store = params.store;
		}
	}

	getModuleId()
	{
		return 'im';
	}

	getSubscriptionType()
	{
		return PullClient.SubscriptionType.Server;
	}

	handleMessageChat(params)
	{
		if (params.chat && params.chat[params.chatId])
		{
			this.store.dispatch('dialogues/update', {
				dialogId: 'chat'+params.chatId,
				fields: params.chat[params.chatId]
			});
		}

		if (params.users)
		{
			this.store.dispatch('users/set', VuexBuilderModel.convertToArray(params.users));
		}

		if (params.files)
		{
			this.store.dispatch('files/set', this.controller.prepareFilesBeforeSave(
				 VuexBuilderModel.convertToArray(params.files)
			));
		}

		let collection = this.store.state.messages.collection[params.chatId];
		if (!collection)
		{
			collection = [];
		}

		let update = false;
		if (params.message.tempId && collection.length > 0)
		{
			for (let index = collection.length-1; index >= 0; index--)
			{
				if (collection[index].id == params.message.tempId)
				{
					update = true;
					break;
				}
			}
		}
		if (update)
		{
			this.store.dispatch('messages/update', {
				id: params.message.tempId,
				chatId: params.message.chatId,
				fields: params.message
			});
		}
		else if (this.controller.isUnreadMessagesLoaded())
		{
			let unreadCountInCollection = 0;
			if (collection.length > 0)
			{
				collection.forEach(element => element.unread? unreadCountInCollection++: 0);
			}

			if (unreadCountInCollection > 0)
			{
				this.store.commit('application/set', {dialog: {
					messageLimit: this.controller.getRequestMessageLimit() + unreadCountInCollection
				}});
			}
			else if (this.controller.getMessageLimit() != this.controller.getRequestMessageLimit())
			{
				this.store.commit('application/set', {dialog: {
					messageLimit: this.controller.getRequestMessageLimit()
				}});
			}

			this.store.dispatch('messages/set', {...params.message, unread: true});
		}

		this.controller.stopOpponentWriting({
			dialogId: 'chat'+params.message.chatId,
			userId: params.message.senderId
		});

		if (params.message.senderId == this.controller.getUserId())
		{
			this.store.dispatch('messages/readMessages', {
				chatId: params.message.chatId
			}).then(result => {
				this.store.dispatch('dialogues/update', {
					dialogId: 'chat'+params.message.chatId,
					fields: {
						counter: 0,
					}
				});
			});
		}
		else
		{
			this.store.dispatch('dialogues/increaseCounter', {
				dialogId: 'chat'+params.message.chatId,
				count: 1,
			});
		}
	}

	handleMessageUpdate(params, extra, command)
	{
		this.execMessageUpdateOrDelete(params, extra, command);
	}

	handleMessageDelete(params, extra, command)
	{
		this.execMessageUpdateOrDelete(params, extra, command);
	}

	handleMessageDeleteComplete(params)
	{
		this.store.dispatch('messages/delete', {
			id: params.id,
			chatId: params.chatId,
		});

		this.controller.stopOpponentWriting({
			dialogId: params.dialogId,
			userId: params.senderId,
			action: false
		});
	}

	handleMessageParamsUpdate(params)
	{
		this.store.dispatch('messages/update', {
			id: params.id,
			chatId: params.chatId,
			fields: {params: params.params}
		});
	}

	handleStartWriting(params)
	{
		this.controller.startOpponentWriting(params);
	}

	handleReadMessageChat(params)
	{
		this.store.dispatch('messages/readMessages', {
			chatId: params.chatId,
			readId: params.lastId
		}).then(result => {
			this.store.dispatch('dialogues/update', {
				dialogId: params.dialogId,
				fields: {
					counter: params.counter,
				}
			});
		});
	}

	execMessageUpdateOrDelete(params, extra, command)
	{
		this.store.dispatch('messages/update', {
			id: params.id,
			chatId: params.chatId,
			fields: {
				text: command == "messageUpdate"? params.text: '',
				textOriginal: command == "messageUpdate"? params.textOriginal: '',
				params: params.params,
				blink: true
			}
		});

		this.controller.stopOpponentWriting({
			dialogId: params.dialogId,
			userId: params.senderId
		});
	}
}

export {ImPullCommandHandler};