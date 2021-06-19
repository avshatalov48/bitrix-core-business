/**
 * Bitrix Messenger
 * Application controller
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2020 Bitrix
 */

import {Timer} from 'im.lib.timer';
import {DialogCrmType, DialogType, RestMethod} from "im.const";
import {Utils} from "im.lib.utils";

export class ApplicationController
{
	constructor()
	{
		this.store = null;
		this.restClient = null;
		this.templateEngine = null;

		this.timer = new Timer();

		this._prepareFilesBeforeSave = params => { return params };

		this.defaultMessageLimit = 20;
		this.requestMessageLimit = this.getDefaultMessageLimit();

		this.messageLastReadId = {};
		this.messageReadQueue = {};
	}

	getDialogData(dialogId = this.getDialogId())
	{
		if (this.store.state.dialogues.collection[dialogId])
		{
			return this.store.state.dialogues.collection[dialogId];
		}

		return this.store.getters['dialogues/getBlank']();
	}

	getDialogCrmData(dialogId = this.getDialogId())
	{
		let result = {
			enabled: false,
			entityType: DialogCrmType.none,
			entityId: 0
		};

		let dialogData = this.getDialogData(dialogId);
		if (dialogData.type === DialogType.call)
		{
			if (dialogData.entityData1 && typeof dialogData.entityData1 === 'string')
			{
				let [enabled, entityType, entityId] = dialogData.entityData1.split('|');
				if (enabled)
				{
					entityType = entityType? entityType.toString().toLowerCase(): DialogCrmType.none;
					result = {enabled, entityType, entityId};
				}
			}
		}
		else if (dialogData.type === DialogType.crm)
		{
			let [entityType, entityId] = dialogData.entityId.split('|');
			entityType = entityType? entityType.toString().toLowerCase(): DialogCrmType.none;
			result = {enabled: true, entityType, entityId};
		}

		return result;
	}

	getDialogIdByChatId(chatId)
	{
		if (this.getDialogId() === 'chat'+chatId)
		{
			return this.getDialogId();
		}

		let dialog = this.store.getters['dialogues/getByChatId'](chatId);
		if (!dialog)
		{
			return 0;
		}

		return dialog.dialogId;
	}

	getDefaultMessageLimit()
	{
		return this.defaultMessageLimit;
	}

	getRequestMessageLimit()
	{
		return this.requestMessageLimit;
	}

	emit(eventName, params = {})
	{
		this.templateEngine.$emit(eventName, params);

		return true;
	}

	listen(eventName, callback)
	{
		if (typeof callback !== 'function')
		{
			return false;
		}

		this.templateEngine.$on(eventName, callback);

		return true;
	}

	muteDialog(action = null, dialogId = this.getDialogId())
	{
		if (Utils.dialog.isEmptyDialogId(dialogId))
		{
			return false;
		}

		if (action === null)
		{
			action = !this.isDialogMuted();
		}

		this.timer.start('muteDialog', dialogId, .3, (id) => {
			this.restClient.callMethod(RestMethod.imChatMute, {
				'DIALOG_ID': dialogId,
				'ACTION': action? 'Y': 'N'
			})
		});

		let muteList = [];
		if (action)
		{
			muteList = this.getDialogData().muteList;
			muteList.push(this.getUserId());
		}
		else
		{
			muteList = this.getDialogData().muteList.filter(userId => userId !== this.getUserId());
		}

		this.store.dispatch('dialogues/update', {
			dialogId,
			fields: {muteList},
		});

		return true;
	}

	isDialogMuted(dialogId = this.getDialogId())
	{
		return this.getDialogData().muteList.includes(this.getUserId());
	}

	isUnreadMessagesLoaded()
	{
		let dialog = this.store.state.dialogues.collection[this.getDialogId()];
		if (!dialog)
		{
			return true;
		}

		if (dialog.lastMessageId <= 0)
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

		return lastElementId >= dialog.lastMessageId;
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

	startWriting(dialogId = this.getDialogId())
	{
		if (Utils.dialog.isEmptyDialogId(dialogId) || this.timer.has('writes', dialogId))
		{
			return false;
		}

		this.timer.start('writes', dialogId, 28);
		this.timer.start('writesSend', dialogId, 5, (id) => {
			this.restClient.callMethod(RestMethod.imDialogWriting, {
				'DIALOG_ID': dialogId
			}).catch(() => {
				this.timer.stop('writes', dialogId);
			});
		});
	}

	stopWriting(dialogId = this.getDialogId())
	{
		this.timer.stop('writes', dialogId, true);
		this.timer.stop('writesSend', dialogId, true);
	}

	joinParentChat(messageId, dialogId)
	{
		return new Promise((resolve, reject) =>
		{
			if (!messageId || !dialogId)
			{
				return reject();
			}

			if (typeof this.tempJoinChat === 'undefined')
			{
				this.tempJoinChat = {};
			}
			else if (this.tempJoinChat['wait'])
			{
				return reject();
			}

			this.tempJoinChat['wait'] = true;

			this.restClient.callMethod(RestMethod.imChatParentJoin, {
				'DIALOG_ID': dialogId,
				'MESSAGE_ID': messageId
			}).then(() => {
				this.tempJoinChat['wait'] = false;
				this.tempJoinChat[dialogId] = true;
				return resolve(dialogId);
			}).catch(() => {
				this.tempJoinChat['wait'] = false;
				return reject();
			});
		});

	};

	setTextareaMessage(params)
	{
		let {
			message = '',
			dialogId = this.getDialogId()
		} = params;

		this.store.dispatch('dialogues/update', {
			dialogId,
			fields: {
				textareaMessage: message
			},
		});
	}

	setSendingMessageFlag(messageId)
	{
		this.store.dispatch('messages/actionStart', {
			id: messageId,
			chatId: this.getChatId()
		});
	}

	reactMessage(messageId, type = 'like', action = 'auto')
	{
		this.restClient.callMethod(RestMethod.imMessageLike, {
			'MESSAGE_ID': messageId,
			'ACTION': action === 'auto'? 'auto': (action === 'set'? 'plus': 'minus')
		});
	}

	readMessage(messageId = null, force = false, skipAjax = false)
	{
		let chatId = this.getChatId();

		if (typeof this.messageLastReadId[chatId] === 'undefined')
		{
			this.messageLastReadId[chatId] = null;
		}
		if (typeof this.messageReadQueue[chatId] === 'undefined')
		{
			this.messageReadQueue[chatId] = [];
		}

		if (messageId)
		{
			this.messageReadQueue[chatId].push(parseInt(messageId));
		}

		this.timer.stop('readMessage', chatId, true);
		this.timer.stop('readMessageServer', chatId, true);

		if (force)
		{
			return this.readMessageExecute(chatId, skipAjax);
		}

		return new Promise((resolve, reject) => {
			this.timer.start('readMessage', chatId, .1, (chatId, params) => this.readMessageExecute(chatId, skipAjax).then((result) => resolve(result)));
		});
	}

	readMessageExecute(chatId, skipAjax = false)
	{
		return new Promise((resolve, reject) =>
		{
			if (this.messageReadQueue[chatId])
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
				});
			}

			let dialogId = this.getDialogIdByChatId(chatId);
			let lastId = this.messageLastReadId[chatId] || 0;
			if (lastId <= 0)
			{
				resolve({dialogId, lastId: 0});
				return true;
			}

			this.store.dispatch('messages/readMessages', {
				chatId: chatId,
				readId: lastId
			}).then(result =>
			{
				this.store.dispatch('dialogues/decreaseCounter', {
					dialogId,
					count: result.count
				});

				if (this.getChatId() === chatId && this.store.getters['dialogues/canSaveChat'])
				{
					let dialog = this.store.getters['dialogues/get'](dialogId);
					if (dialog.counter <= 0)
					{
						this.store.commit('application/clearDialogExtraCount');
					}
				}

				if (skipAjax)
				{
					resolve({dialogId, lastId});
				}
				else
				{
					this.timer.start('readMessageServer', chatId, .5, () => {
						this.restClient.callMethod(RestMethod.imDialogRead, {
							'DIALOG_ID': dialogId,
							'MESSAGE_ID': lastId
						}).then(() => resolve({dialogId, lastId})).catch(() => resolve({dialogId, lastId}));
					});
				}

			}).catch(() => {
				resolve();
			});
		});
	}

	unreadMessage(messageId = null, skipAjax = false)
	{
		let chatId = this.getChatId();

		if (typeof this.messageLastReadId[chatId] === 'undefined')
		{
			this.messageLastReadId[chatId] = null;
		}
		if (typeof this.messageReadQueue[chatId] === 'undefined')
		{
			this.messageReadQueue[chatId] = [];
		}

		if (messageId)
		{
			this.messageReadQueue[chatId] = this.messageReadQueue[chatId].filter(id => id < messageId);
		}

		this.timer.stop('readMessage', chatId, true);
		this.timer.stop('readMessageServer', chatId, true);

		this.messageLastReadId[chatId] = messageId;

		this.store.dispatch('messages/unreadMessages', {
			chatId: chatId,
			unreadId: this.messageLastReadId[chatId]
		}).then(result => {

			let dialogId = this.getDialogIdByChatId(chatId);

			this.store.dispatch('dialogues/update', {
				dialogId,
				fields: {
					unreadId: messageId
				},
			});

			this.store.dispatch('dialogues/increaseCounter', {
				dialogId,
				count: result.count
			});

			if (!skipAjax)
			{
				this.restClient.callMethod(RestMethod.imDialogUnread, {
					'DIALOG_ID': dialogId,
					'MESSAGE_ID': this.messageLastReadId[chatId]
				});
			}

		}).catch(() => {});
	}

	shareMessage(messageId, type, date = null)
	{
		this.restClient.callMethod(RestMethod.imMessageShare, {
			'DIALOG_ID': this.getDialogId(),
			'MESSAGE_ID': messageId,
			'TYPE': type,
		});

		return true;
	}
}