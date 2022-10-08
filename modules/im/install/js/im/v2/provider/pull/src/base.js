import {EventEmitter} from 'main.core.events';
import {PullClient} from 'pull.client';
import {BuilderModel} from 'ui.vue3.vuex';
import {ChatTypes, EventType} from 'im.v2.const';
import {Logger} from 'im.v2.lib.logger';

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

		if (typeof params.chatId !== 'undefined' || typeof params.dialogId !== 'undefined')
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

		if (params.lines)
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
			if (!existingChat)
			{
				const chatToAdd = {...params.chat[params.chatId], dialogId: params.dialogId};
				this.store.dispatch('dialogues/set', chatToAdd);
			}
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
			const {users, dialogues} = this.prepareUsersForModels(params.users);
			this.store.dispatch('dialogues/set', dialogues);
			this.store.dispatch('users/set', users);
		}

		//set files
		if (params.files)
		{
			const files = BuilderModel.convertToArray(params.files);
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
						cancelIfScrollChange: params.message.senderId !== this.controller.getUserId()
					});
				}
			});
		}
		//if we dont have message and we have all pages - add new message and send newMessage event (handles scroll stuff)
		//we dont do anything if we dont have message and there are unloaded messages
		else if (this.store.getters['dialogues/areUnreadMessagesLoaded'](params.dialogId))
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
		this.store.dispatch('dialogues/stopWriting', {
			dialogId: params.dialogId,
			userId: params.message.senderId
		});

		//if we sent message - read all messages on server and client, set counter to 0
		if (params.message.senderId === this.controller.getUserId())
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
		else if (params.message.senderId !== this.controller.getUserId())
		{
			this.store.dispatch('dialogues/update', {
				dialogId: params.dialogId,
				fields: {
					counter: params.counter,
				}
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

		this.store.dispatch('dialogues/stopWriting', {
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

		this.store.dispatch('dialogues/stopWriting', {
			dialogId: params.dialogId,
			userId: params.senderId
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

		if (params.users)
		{
			const {users, dialogues} = this.prepareUsersForModels(params.users);
			this.store.dispatch('dialogues/set', dialogues);
			this.store.dispatch('users/set', users);
		}

		this.store.dispatch('dialogues/update', {
			dialogId: params.dialogId,
			fields: {userCounter: params.userCount}
		});
	}

	handleChatUserLeave(params, extra)
	{
		this.handleChatUserAdd(params, extra);
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

		const {dialogId, userId, userName} = params;
		this.store.dispatch('dialogues/startWriting', {
			dialogId,
			userId,
			userName
		});
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
		}).then(() => {
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

		this.store.dispatch('dialogues/addToReadList', {
			dialogId: params.dialogId,
			userId: params.userId,
			userName: params.userName,
			messageId: params.lastId,
			date: params.date
		});
	}

	handleUnreadMessage(params, extra)
	{
		if (this.skipExecute(params, extra))
		{
			return false;
		}

		this.store.dispatch('dialogues/update', {
			dialogId: params.dialogId,
			fields: {
				counter: params.counter,
			}
		});
	}

	handleUnreadMessageChat(params, extra)
	{
		this.handleUnreadMessage(params, extra);
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

		this.store.dispatch('dialogues/removeFromReadList', {
			dialogId: params.dialogId,
			userId: params.userId
		});
	}

	handleFileUpload(params, extra)
	{
		if (this.skipExecute(params, extra))
		{
			return false;
		}

		this.store.dispatch('files/set', BuilderModel.convertToArray({file: params.fileParams})).then(() => {
			EventEmitter.emit(EventType.dialog.scrollToBottom, {cancelIfScrollChange: true});
		});
	}

	handleChatMuteNotify(params)
	{
		if (params.muted)
		{
			this.store.dispatch('dialogues/mute', {
				dialogId: params.dialogId
			});

			return true;
		}

		this.store.dispatch('dialogues/unmute', {
			dialogId: params.dialogId
		});
	}

	handleUserInvite(params)
	{
		if (!params.invited)
		{
			this.store.dispatch('users/update', {
				id: params.userId,
				fields: params.user
			});
		}
	}

	handleChatRename(params)
	{
		const dialog = this.store.getters['dialogues/getByChatId'](params.chatId);
		if (!dialog)
		{
			return false;
		}

		this.store.dispatch('dialogues/update', {
			dialogId: dialog.dialogId,
			fields: {
				name: params.name
			}
		});
	}

	handleChatAvatar(params)
	{
		const dialog = this.store.getters['dialogues/getByChatId'](params.chatId);
		if (!dialog)
		{
			return false;
		}

		this.store.dispatch('dialogues/update', {
			dialogId: dialog.dialogId,
			fields: {
				avatar: params.avatar
			}
		});
	}

	prepareUsersForModels(users)
	{
		const userDialogues = BuilderModel.convertToArray(users).map(user => {
			return {
				dialogId: user.id,
				avatar: user.avatar,
				color: user.color,
				name: user.name,
				type: ChatTypes.user
			};
		});

		return {users: BuilderModel.convertToArray(users), dialogues: userDialogues};
	}
}