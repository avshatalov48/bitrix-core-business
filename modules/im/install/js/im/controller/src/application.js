/**
 * Bitrix Messenger
 * Application controller
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

import {Timer} from 'im.tools.timer';
import {RestMethod} from "im.const";

class ApplicationController
{
	constructor()
	{
		this.store = null;
		this.restClient = null;

		this.timer = new Timer();

		this._prepareFilesBeforeSave = params => { return params };

		this.defaultMessageLimit = 20;
		this.requestMessageLimit = this.getDefaultMessageLimit();

		this.messageLastReadId = {};
		this.messageReadQueue = {};
	}

	setRestClient(client)
	{
		this.restClient = client;
	}

	setVuexStore(store)
	{
		this.store = store;
	}

	getSiteId()
	{
		return this.store.state.application.common.siteId;
	}

	getChatId()
	{
		return this.store.state.application.dialog.chatId;
	}

	getUserId()
	{
		return this.store.state.application.common.userId;
	}

	getDialogId()
	{
		return this.store.state.application.dialog.dialogId;
	}

	getDialogIdByChatId(chatId) // TODO error with work user dialog id (not chat)
	{
		return 'chat'+chatId;
	}

	getDiskFolderId()
	{
		return this.store.state.application.dialog.diskFolderId;
	}

	getMessageLimit()
	{
		return this.store.state.application.dialog.messageLimit;
	}

	getDefaultMessageLimit()
	{
		return this.defaultMessageLimit;
	}

	getRequestMessageLimit()
	{
		return this.requestMessageLimit;
	}

	isUnreadMessagesLoaded()
	{
		let dialog = this.store.state.dialogues.collection[this.getDialogId()];
		if (!dialog)
		{
			return true;
		}

		if (dialog.unreadLastId <= 0)
		{
			return true;
		}

		let collection = this.store.state.messages.collection[this.getChatId()];
		if (!collection || collection.length <= 0)
		{
			return true;
		}

		let lastElementId = 0;
		for (let index = collection.length-1; index >= 0; index--)
		{
			let lastElement = collection[index];
			if (typeof lastElement.id === "number")
			{
				lastElementId = lastElement.id;
				break;
			}
		}

		return lastElementId >= dialog.unreadLastId;
	}

	prepareFilesBeforeSave(files)
	{
		return this._prepareFilesBeforeSave(files);
	}

	setPrepareFilesBeforeSaveFunction(func)
	{
		this._prepareFilesBeforeSave = func.bind(this);
	}

	startOpponentWriting(params)
	{
		let {dialogId, userId, userName} = params;

		this.store.dispatch('dialogues/updateWriting', {
			dialogId,
			userId,
			userName,
			action : true
		});

		this.timer.start('writingEnd', dialogId+'|'+userId, 35, (id, params) => {
			let {dialogId, userId} = params;
			this.store.dispatch('dialogues/updateWriting', {
				dialogId,
				userId,
				action: false
			});
		}, {dialogId, userId});

		return true;
	}

	stopOpponentWriting(params = {})
	{
		let {dialogId, userId, userName} = params;

		this.timer.stop('writingStart', dialogId+'|'+userId, true);
		this.timer.stop('writingEnd', dialogId+'|'+userId);

		return true;
	}

	startWriting()
	{
		if (!this.getChatId() || this.timer.has('writes'))
		{
			return false;
		}

		this.timer.start('writes', null, 28);

		this.timer.start('writesSend', null, 5, (id) => {
			this.restClient.callMethod(RestMethod.imChatSendTyping, {
				'CHAT_ID': this.getChatId()
			}).catch(() => {
				this.timer.stop('writes', this.getChatId());
			});
		});
	}

	stopWriting()
	{
		this.timer.stop('writes');
		this.timer.stop('writesSend');
	}

	setSendingMessageFlag(messageId)
	{
		this.store.dispatch('messages/actionStart', {
			id: messageId,
			chatId: this.getChatId()
		});
	}

	readMessage(messageId = null)
	{
		let chatId = this.getChatId();

		if (typeof this.messageLastReadId[chatId] == 'undefined')
		{
			this.messageLastReadId[chatId] = null;
		}
		if (typeof this.messageReadQueue[chatId] == 'undefined')
		{
			this.messageReadQueue[chatId] = [];
		}

		if (messageId)
		{
			this.messageReadQueue[chatId].push(parseInt(messageId));
		}

		this.timer.start('readMessage', chatId, .1, (chatId, params) =>
		{
			this.messageReadQueue[chatId] = this.messageReadQueue[chatId].filter(elementId => {
				if (!this.messageLastReadId[chatId])
				{
					this.messageLastReadId[chatId] = elementId;
				}
				else if (this.messageLastReadId[chatId] < elementId)
				{
					this.messageLastReadId[chatId] = elementId;
				}
				return false;
			});

			if (this.messageLastReadId[chatId] <= 0)
			{
				return false
			}

			this.store.dispatch('messages/readMessages', {
				chatId: chatId,
				readId: this.messageLastReadId[chatId]
			}).then(result => {
				this.store.dispatch('dialogues/decreaseCounter', {
					dialogId: this.getDialogIdByChatId(chatId),
					count: result.count
				});
			});

			this.timer.start('readMessageServer', chatId, .5, (chatId, params) => {
				this.restClient.callMethod(RestMethod.imDialogRead, {
					'DIALOG_ID': this.getDialogIdByChatId(chatId),
					'MESSAGE_ID': this.messageLastReadId[chatId]
				})
				// TODO catch set message to unread status
			});
		});
	}
}

export {ApplicationController};