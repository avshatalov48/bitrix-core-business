/**
 * Bitrix Messenger
 * Im base pull commands (Pull Command Handler)
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2020 Bitrix
 */

import {PullClient} from "pull.client";
import {VuexBuilderModel} from 'ui.vue.vuex';
import {EventType} from "im.const";
import {Logger} from "im.lib.logger";
import {EventEmitter} from 'main.core.events';

export class ImBasePullHandler
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

		this.option = typeof params.store === 'object' && params.store? params.store: {};

		if (
			!(
				typeof this.option.handlingDialog === 'object'
				&& this.option.handlingDialog
				&& this.option.handlingDialog.chatId
				&& this.option.handlingDialog.dialogId
			)
		)
		{
			this.option.handlingDialog = false;
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

	skipExecute(params, extra = {})
	{
		if (!extra.optionImportant)
		{
			if (this.option.skip)
			{
				Logger.info('Pull: command skipped while loading messages', params);
				return true;
			}

			if (!this.option.handlingDialog)
			{
				return false;
			}
		}

		if (typeof params.chatId !== 'undefined' || typeof params.dialogId !== 'undefined' )
		{
			if (
				typeof params.chatId !== 'undefined'
				&& parseInt(params.chatId) === parseInt(this.option.handlingDialog.chatId)
			)
			{
				return false;
			}

			if (
				typeof params.dialogId !== 'undefined'
				&& params.dialogId.toString() === this.option.handlingDialog.dialogId.toString()
			)
			{
				return false;
			}

			return true;
		}

		return false;
	}

	handleMessage(params, extra)
	{
		this.handleMessageAdd(params, extra);
	}

	handleMessageChat(params, extra)
	{
		this.handleMessageAdd(params, extra);
	}

	handleMessageAdd(params, extra)
	{
		Logger.warn('handleMessageAdd', params);
		if (this.skipExecute(params, extra))
		{
			return false;
		}

		let collection = this.store.state.messages.collection[params.chatId];
		if (!collection)
		{
			collection = [];
		}

		//search for message with message id from params
		const message = collection.find(element => {
			if (params.message.templateId && element.id === params.message.templateId)
			{
				return true;
			}

			return element.id === params.message.id;
		});

		//stop if it's message with 'push' (pseudo push message in mobile)
		if (message && params.message.push)
		{
			return false;
		}

		if (params.chat && params.chat[params.chatId])
		{
			const existingChat = this.store.getters['dialogues/getByChatId'](params.chatId);
			//add new chat if there is no one
			if (!existingChat)
			{
				const chatToAdd = Object.assign(
					{},
					params.chat[params.chatId],
					{dialogId: params.dialogId}
				);
				this.store.dispatch('dialogues/set', chatToAdd);
			}
			//otherwise - update it
			else
			{
				this.store.dispatch('dialogues/update', {
					dialogId: params.dialogId,
					fields: params.chat[params.chatId]
				});
			}
		}

		//set users
		if (params.users)
		{
			this.store.dispatch('users/set', VuexBuilderModel.convertToArray(params.users));
		}

		//set files
		if (params.files)
		{
			let files = this.controller.application.prepareFilesBeforeSave(
				VuexBuilderModel.convertToArray(params.files)
			);
			files.forEach(file =>
			{
				if (
					files.length === 1
					&& params.message.templateFileId
					&& this.store.state.files.index[params.chatId]
					&& this.store.state.files.index[params.chatId][params.message.templateFileId]
				)
				{
					this.store.dispatch('files/update', {
						id: params.message.templateFileId,
						chatId: params.chatId,
						fields: file
					}).then(() => {
						EventEmitter.emit(EventType.dialog.scrollToBottom, {chatId: params.chatId, cancelIfScrollChange: true});
					});
				}
				else
				{
					this.store.dispatch('files/set', file);
				}
			});
		}

		//if we already have message - update it and scrollToBottom
		if (message)
		{
			Logger.warn('New message pull handler: we already have this message', params.message);
			this.store.dispatch('messages/update', {
				id: message.id,
				chatId: message.chatId,
				fields: {
					...params.message,
					sending: false,
					error: false,
				}
			}).then(() => {
				if (!params.message.push)
				{
					EventEmitter.emit(EventType.dialog.scrollToBottom, {
						chatId: message.chatId,
						cancelIfScrollChange: params.message.senderId !== this.controller.application.getUserId()
					});
				}
			});
		}
		//if we dont have message and we have all pages - add new message and send newMessage event (handles scroll stuff)
		//we dont do anything if we dont have message and there are unloaded messages
		else if (this.controller.application.isUnreadMessagesLoaded())
		{
			Logger.warn('New message pull handler: we dont have this message', params.message);
			this.store.dispatch('messages/setAfter', {
				...params.message,
				unread: true
			}).then(() => {
				if (!params.message.push)
				{
					EventEmitter.emit(EventType.dialog.newMessage, {
						chatId: params.message.chatId,
						messageId: params.message.id
					});
				}
			});
		}

		//stop writing event
		this.controller.application.stopOpponentWriting({
			dialogId: params.dialogId,
			userId: params.message.senderId
		});

		// if we sent message - read all messages on server and client, set counter to 0
		if (params.message.senderId === this.controller.application.getUserId())
		{
			if (
				this.store.state.dialogues.collection[params.dialogId]
				&& this.store.state.dialogues.collection[params.dialogId].counter !== 0
			)
			{
				this.controller.restClient.callMethod('im.dialog.read', {
					dialog_id: params.dialogId
				}).then(() => {
					this.store.dispatch('messages/readMessages', {
						chatId: params.chatId
					}).then(result => {
						EventEmitter.emit(EventType.dialog.scrollToBottom, {chatId: params.chatId, cancelIfScrollChange: false});
						this.store.dispatch('dialogues/update', {
							dialogId: params.dialogId,
							fields: {
								counter: 0,
							}
						});
					});
				});
			}
		}
		//increase the counter if message is not ours
		else if (params.message.senderId !== this.controller.application.getUserId())
		{
			this.store.dispatch('dialogues/increaseCounter', {
				dialogId: params.dialogId,
				count: 1,
			});
		}

		//set new lastMessageId (used for pagination)
		this.store.dispatch('dialogues/update', {
			dialogId: params.dialogId,
			fields: {
				lastMessageId: params.message.id
			}
		});

		//increase total message count
		this.store.dispatch('dialogues/increaseMessageCounter', {
			dialogId: params.dialogId,
			count: 1,
		});
	}

	handleMessageUpdate(params, extra, command)
	{
		this.execMessageUpdateOrDelete(params, extra, command);
	}

	handleMessageDelete(params, extra, command)
	{
		this.execMessageUpdateOrDelete(params, extra, command);
	}

	execMessageUpdateOrDelete(params, extra, command)
	{
		if (this.skipExecute(params, extra))
		{
			return false;
		}

		this.controller.application.stopOpponentWriting({
			dialogId: params.dialogId,
			userId: params.senderId
		});

		let fields = {
			params: params.params,
			blink: true
		};
		if (command === "messageUpdate")
		{
			if (typeof params.textLegacy !== 'undefined')
			{
				fields.textLegacy = params.textLegacy;
			}
			if (typeof params.textOriginal !== 'undefined')
			{
				fields.textOriginal = params.textOriginal;
			}
			if (typeof params.text !== 'undefined')
			{
				fields.text = params.text;
			}
		}

		this.store.dispatch('messages/update', {
			id: params.id,
			chatId: params.chatId,
			fields
		}).then(() => {
			EventEmitter.emit(EventType.dialog.scrollToBottom, {chatId: params.chatId, cancelIfScrollChange: true});
		});
	}

	handleMessageDeleteComplete(params, extra)
	{
		if (this.skipExecute(params, extra))
		{
			return false;
		}

		this.store.dispatch('messages/delete', {
			id: params.id,
			chatId: params.chatId,
		});

		this.controller.application.stopOpponentWriting({
			dialogId: params.dialogId,
			userId: params.senderId,
			action: false
		});
	}

	handleMessageLike(params, extra)
	{
		if (this.skipExecute(params, extra))
		{
			return false;
		}

		this.store.dispatch('messages/update', {
			id: params.id,
			chatId: params.chatId,
			fields: {params: {LIKE: params.users}}
		});
	}

	handleChatOwner(params, extra)
	{
		if (this.skipExecute(params, extra))
		{
			return false;
		}

		this.store.dispatch('dialogues/update', {
			dialogId: params.dialogId,
			fields: {
				ownerId: params.userId,
			}
		});
	}

	handleChatManagers(params, extra)
	{
		if (this.skipExecute(params, extra))
		{
			return false;
		}

		this.store.dispatch('dialogues/update', {
			dialogId: params.dialogId,
			fields: {
				managerList: params.list,
			}
		});
	}

	handleChatUpdateParams(params, extra)
	{
		if (this.skipExecute(params, extra))
		{
			return false;
		}

		this.store.dispatch('dialogues/update', {
			dialogId: params.dialogId,
			fields: params.params
		});
	}

	handleChatUserAdd(params, extra)
	{
		if (this.skipExecute(params, extra))
		{
			return false;
		}

		this.store.dispatch('dialogues/update', {
			dialogId: params.dialogId,
			fields: {userCounter: params.userCount}
		});
	}

	handleChatUserLeave(params, extra)
	{
		if (this.skipExecute(params, extra))
		{
			return false;
		}

		this.store.dispatch('dialogues/update', {
			dialogId: params.dialogId,
			fields: {userCounter: params.userCount}
		});
	}

	handleMessageParamsUpdate(params, extra)
	{
		if (this.skipExecute(params, extra))
		{
			return false;
		}

		this.store.dispatch('messages/update', {
			id: params.id,
			chatId: params.chatId,
			fields: {params: params.params}
		}).then(() => {
			EventEmitter.emit(EventType.dialog.scrollToBottom, {chatId: params.chatId, cancelIfScrollChange: true});
		});
	}

	handleStartWriting(params, extra)
	{
		if (this.skipExecute(params, extra))
		{
			return false;
		}

		this.controller.application.startOpponentWriting(params);
	}

	handleReadMessage(params, extra)
	{
		if (this.skipExecute(params, extra))
		{
			return false;
		}

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

	handleReadMessageChat(params, extra)
	{
		this.handleReadMessage(params, extra);
	}

	handleReadMessageOpponent(params, extra)
	{
		this.execReadMessageOpponent(params, extra);
	}

	handleReadMessageChatOpponent(params, extra)
	{
		this.execReadMessageOpponent(params, extra);
	}

	execReadMessageOpponent(params, extra)
	{
		if (this.skipExecute(params, extra))
		{
			return false;
		}

		this.store.dispatch('dialogues/updateReaded', {
			dialogId: params.dialogId,
			userId: params.userId,
			userName: params.userName,
			messageId: params.lastId,
			date: params.date,
			action: true
		});
	}

	handleUnreadMessageOpponent(params, extra)
	{
		this.execUnreadMessageOpponent(params, extra);
	}

	handleUnreadMessageChatOpponent(params, extra)
	{
		this.execUnreadMessageOpponent(params, extra);
	}

	execUnreadMessageOpponent(params, extra)
	{
		if (this.skipExecute(params, extra))
		{
			return false;
		}

		this.store.dispatch('dialogues/updateReaded', {
			dialogId: params.dialogId,
			userId: params.userId,
			action: false
		});
	}

	handleFileUpload(params, extra)
	{
		if (this.skipExecute(params, extra))
		{
			return false;
		}

		this.store.dispatch('files/set', this.controller.application.prepareFilesBeforeSave(
			 VuexBuilderModel.convertToArray({file: params.fileParams})
		)).then(() => {
			EventEmitter.emit(EventType.dialog.scrollToBottom, {cancelIfScrollChange: true});
		});
	}

	handleChatMuteNotify(params, extra)
	{
		const existingChat = this.store.getters['dialogues/get'](params.dialogId);
		if (!existingChat)
		{
			return false;
		}

		const existingMuteList = existingChat.muteList;
		let newMuteList = [];
		const currentUser = this.store.state.application.common.userId;
		if (params.mute)
		{
			newMuteList = [...existingMuteList, currentUser];
		}
		else
		{
			newMuteList = existingMuteList.filter(element => {
				return element !== currentUser;
			});
		}

		this.store.dispatch('dialogues/update', {
			dialogId: params.dialogId,
			fields: {
				muteList: newMuteList
			}
		});
	}

	handleUserInvite(params, extra)
	{
		if (!params.invited)
		{
			this.store.dispatch('users/update', {
				id: params.userId,
				fields: params.user
			});
		}
	}
}