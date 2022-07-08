import {Timer} from "im.lib.timer";
import {Logger} from "im.lib.logger";
import {EventType, RestMethod} from "im.const";
import {EventEmitter} from "main.core.events";

export class ReadingHandler
{
	messagesToRead: Object = {}; // {<chatId>: [<messageId>]}
	timer: Timer = null;
	store: Object = null;
	restClient: Object = null;

	constructor($Bitrix)
	{
		this.store = $Bitrix.Data.get('controller').store;
		this.restClient = $Bitrix.RestClient.get();
		this.timer = new Timer();

		this.onReadMessageHandler = this.onReadMessage.bind(this);
		EventEmitter.subscribe(EventType.dialog.readMessage, this.onReadMessageHandler);
	}

	onReadMessage({data: {id = null, skipTimer = false, skipAjax = false}}): Promise
	{
		return this.readMessage(id, skipTimer, skipAjax);
	}

	readMessage(messageId, skipTimer = false, skipAjax = false): Promise
	{
		const chatId = this.getChatId();
		if (messageId)
		{
			if (!this.messagesToRead[chatId])
			{
				this.messagesToRead[chatId] = [];
			}
			this.messagesToRead[chatId].push(Number.parseInt(messageId, 10));
		}

		this.timer.stop('readMessage', chatId, true);
		this.timer.stop('readMessageServer', chatId, true);

		if (skipTimer)
		{
			return this.processMessagesToRead(chatId, skipAjax);
		}

		return new Promise((resolve, reject) => {
			this.timer.start('readMessage', chatId, 0.1, () => {
				this.processMessagesToRead(chatId, skipAjax).then(result => resolve(result)).catch(reject);
			});
		});
	}

	processMessagesToRead(chatId, skipAjax = false): Promise
	{
		const lastMessageToRead = this.getMaxMessageIdFromQueue(chatId);
		delete this.messagesToRead[chatId];
		if (lastMessageToRead <= 0)
		{
			return Promise.resolve();
		}

		return new Promise((resolve, reject) => {
			this.readMessageOnClient(chatId, lastMessageToRead).then(readResult => {
				return this.decreaseChatCounter(chatId, readResult.count);
			}).then(() => {
				if (skipAjax)
				{
					return resolve({chatId, lastId: lastMessageToRead});
				}

				this.timer.start('readMessageServer', chatId, 0.5, () => {
					this.readMessageOnServer(chatId, lastMessageToRead).then(() => {
						resolve({chatId, lastId: lastMessageToRead});
					}).catch(reject);
				});
			}).catch(error => {
				Logger.error('Reading messages error', error);
				reject();
			});
		});
	}

	getMaxMessageIdFromQueue(chatId): number
	{
		let maxMessageId = 0;
		if (!this.messagesToRead[chatId])
		{
			return maxMessageId;
		}

		this.messagesToRead[chatId].forEach(messageId => {
			if (maxMessageId < messageId)
			{
				maxMessageId = messageId;
			}
		});

		return maxMessageId;
	}

	readMessageOnClient(chatId, lastMessageToRead): Promise
	{
		return this.store.dispatch('messages/readMessages', {
			chatId: chatId,
			readId: lastMessageToRead
		});
	}

	readMessageOnServer(chatId, lastMessageToRead): Promise
	{
		return this.restClient.callMethod(RestMethod.imDialogRead, {
			'DIALOG_ID': this.getDialogIdByChatId(chatId),
			'MESSAGE_ID': lastMessageToRead
		});
	}

	decreaseChatCounter(chatId, counter)
	{
		return this.store.dispatch('dialogues/decreaseCounter', {
			dialogId: this.getDialogIdByChatId(chatId),
			count: counter
		});
	}

	getChatId(): number
	{
		return this.store.state.application.dialog.chatId;
	}

	getDialogIdByChatId(chatId): number
	{
		const dialog = this.store.getters['dialogues/getByChatId'](chatId);
		if (!dialog)
		{
			return 0;
		}

		return dialog.dialogId;
	}

	getDialogId(): number | string
	{
		return this.store.state.application.dialog.dialogId;
	}

	destroy()
	{
		EventEmitter.unsubscribe(EventType.dialog.readMessage, this.onReadMessageHandler);
	}
}