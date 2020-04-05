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
import {EventType} from "im.const";

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
				console.info('Pull: command skipped while loading messages', params);
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
		if (this.skipExecute(params, extra))
		{
			return false;
		}

		if (params.chat && params.chat[params.chatId])
		{
			this.store.dispatch('dialogues/update', {
				dialogId: params.dialogId,
				fields: params.chat[params.chatId]
			});
		}

		if (params.users)
		{
			this.store.dispatch('users/set', VuexBuilderModel.convertToArray(params.users));
		}

		if (params.files)
		{
			let files = VuexBuilderModel.convertToArray(params.files);
			files.forEach(file =>
			{
				file = this.controller.prepareFilesBeforeSave(file);
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
						this.controller.emit(EventType.dialog.scrollToBottom, {cancelIfScrollChange: true});
					});
				}
				else
				{
					this.store.dispatch('files/set', file);
				}
			});
		}

		let collection = this.store.state.messages.collection[params.chatId];
		if (!collection)
		{
			collection = [];
		}

		let update = false;
		if (params.message.templateId && collection.length > 0)
		{
			for (let index = collection.length-1; index >= 0; index--)
			{
				if (collection[index].id === params.message.templateId)
				{
					update = true;
					break;
				}
			}
		}

		if (update)
		{
			this.store.dispatch('messages/update', {
				id: params.message.templateId,
				chatId: params.chatId,
				fields: {
					push: false,
					...params.message,
					sending: false,
					error: false,
				}
			}).then(() => {
				this.controller.emit(EventType.dialog.scrollToBottom, {cancelIfScrollChange: params.message.senderId !== this.controller.getUserId()});
			});
		}
		else if (this.controller.isUnreadMessagesLoaded())
		{
			if (this.controller.getChatId() === params.chatId)
			{
				this.store.commit('application/increaseDialogExtraCount');
			}

			this.store.dispatch('messages/setAfter', {
				push: false,
				...params.message,
				unread: true
			});
		}

		this.controller.stopOpponentWriting({
			dialogId: params.dialogId,
			userId: params.message.senderId
		});

		if (params.message.senderId === this.controller.getUserId())
		{
			this.store.dispatch('messages/readMessages', {
				chatId: params.chatId
			}).then(result => {
				this.store.dispatch('dialogues/update', {
					dialogId: params.dialogId,
					fields: {
						counter: 0,
					}
				});
			});
		}
		else
		{
			this.store.dispatch('dialogues/increaseCounter', {
				dialogId: params.dialogId,
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

	execMessageUpdateOrDelete(params, extra, command)
	{
		if (this.skipExecute(params, extra))
		{
			return false;
		}

		this.controller.stopOpponentWriting({
			dialogId: params.dialogId,
			userId: params.senderId
		});

		this.store.dispatch('messages/update', {
			id: params.id,
			chatId: params.chatId,
			fields: {
				text: command === "messageUpdate"? params.text: '',
				textOriginal: command === "messageUpdate"? params.textOriginal: '',
				params: params.params,
				blink: true
			}
		}).then(() => {
			this.controller.emit(EventType.dialog.scrollToBottom, {cancelIfScrollChange: true});
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

		this.controller.stopOpponentWriting({
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
			this.controller.emit(EventType.dialog.scrollToBottom, {cancelIfScrollChange: true});
		});
	}

	handleStartWriting(params, extra)
	{
		if (this.skipExecute(params, extra))
		{
			return false;
		}

		this.controller.startOpponentWriting(params);
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

		this.store.dispatch('files/set', this.controller.prepareFilesBeforeSave(
			 VuexBuilderModel.convertToArray({file: params.fileParams})
		)).then(() => {
			this.controller.emit(EventType.dialog.scrollToBottom, {cancelIfScrollChange: true});
		});
	}
}

export {ImPullCommandHandler};