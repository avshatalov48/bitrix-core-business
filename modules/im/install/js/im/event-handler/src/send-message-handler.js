import { EventEmitter } from 'main.core.events';
import { EventType, RestMethod, RestMethod as ImRestMethod, RestMethodHandler } from 'im.const';
import { Logger } from 'im.lib.logger';
import { Utils } from 'im.lib.utils';
import { Clipboard } from "im.lib.clipboard";

export class SendMessageHandler
{
	messagesToSend: Array<Object> = [];
	store: Object = null;
	restClient: Object = null;
	loc: Object = null;

	constructor($Bitrix)
	{
		this.controller = $Bitrix.Data.get('controller');
		this.store = this.controller.store;
		this.restClient = $Bitrix.RestClient.get();
		this.loc = $Bitrix.Loc.messages;

		this.onSendMessageHandler = this.onSendMessage.bind(this);
		this.onClickOnMessageRetryHandler = this.onClickOnMessageRetry.bind(this);
		this.onClickOnCommandHandler = this.onClickOnCommand.bind(this);
		this.onClickOnKeyboardHandler = this.onClickOnKeyboard.bind(this);

		EventEmitter.subscribe(EventType.textarea.sendMessage, this.onSendMessageHandler);
		EventEmitter.subscribe(EventType.dialog.clickOnMessageRetry, this.onClickOnMessageRetryHandler);
		EventEmitter.subscribe(EventType.dialog.clickOnCommand, this.onClickOnCommandHandler);
		EventEmitter.subscribe(EventType.dialog.clickOnKeyboardButton, this.onClickOnKeyboardHandler);
	}

	onSendMessage({data})
	{
		if (!data.text && !data.file)
		{
			return false;
		}

		this.sendMessage(data.text, data.file);
	}
	//endregion events

	// entry point for sending message
	sendMessage(text = '', file = null)
	{
		if (!text && !file)
		{
			return false;
		}

		// quote handling
		const quoteId = this.store.getters['dialogues/getQuoteId'](this.getDialogId());
		if (quoteId)
		{
			const quoteMessage = this.store.getters['messages/getMessage'](this.getChatId(), quoteId);
			if (quoteMessage)
			{
				text = this.getMessageTextWithQuote(quoteMessage, text);
				EventEmitter.emit(EventType.dialog.quotePanelClose);
			}
		}

		if (!this.controller.application.isUnreadMessagesLoaded())
		{
			// not all messages are loaded, adding message only on server
			this.sendMessageToServer({
				id: 0,
				chatId: this.getChatId(),
				dialogId: this.getDialogId(),
				text,
				file
			});

			this.processQueue();

			return true;
		}

		const params = {};
		if (file)
		{
			params.FILE_ID = [file.id];
		}

		this.addMessageToModel({
			text,
			params,
			sending: !file
		}).then(messageId => {
			EventEmitter.emit(EventType.dialog.scrollToBottom, {
				chatId: this.getChatId(),
				cancelIfScrollChange: true
			});

			this.addMessageToQueue({messageId, text, file});
			this.processQueue();
		});
	}

	/**
	 * Goes through messages queue:
	 * - For messages with file sends event to uploader
	 * - For common messages sends them to server
	 */
	processQueue()
	{
		this.messagesToSend.filter(element => !element.sending).forEach(element => {
			this.deleteFromQueue(element.id);
			element.sending = true;
			if (element.file)
			{
				EventEmitter.emit(EventType.textarea.stopWriting);
				EventEmitter.emit(EventType.uploader.addMessageWithFile, element);
			}
			else
			{
				this.sendMessageToServer(element);
			}
		});
	}

	addMessageToModel({text, params, sending}): Promise
	{
		return this.store.dispatch('messages/add', {
			chatId: this.getChatId(),
			authorId: this.getUserId(),
			text,
			params,
			sending
		});
	}

	addMessageToQueue({messageId, text, file})
	{
		this.messagesToSend.push({
			id: messageId,
			chatId: this.getChatId(),
			dialogId: this.getDialogId(),
			text,
			file,
			sending: false
		});
	}

	sendMessageToServer(element)
	{
		EventEmitter.emit(EventType.textarea.stopWriting);

		this.restClient.callMethod(ImRestMethod.imMessageAdd, {
			'TEMPLATE_ID': element.id,
			'DIALOG_ID': element.dialogId,
			'MESSAGE': element.text
		}, null, null).then(response => {
			this.controller.executeRestAnswer(RestMethodHandler.imMessageAdd, response, element);
		}).catch(error => {
			this.controller.executeRestAnswer(RestMethodHandler.imMessageAdd, error, element);
			Logger.warn('SendMessageHandler: error during adding message', error);
		});
	}

	onClickOnMessageRetry({data: event})
	{
		this.retrySendMessage(event.message);
	}

	retrySendMessage(message)
	{
		this.addMessageToQueue({messageId: message.id, text: message.text, file: null});
		this.setSendingMessageFlag(message.id);
		this.processQueue();
	}

	setSendingMessageFlag(messageId)
	{
		this.store.dispatch('messages/actionStart', {
			id: messageId,
			chatId: this.getChatId()
		});
	}

	deleteFromQueue(messageId)
	{
		this.messagesToSend = this.messagesToSend.filter(element => element.id !== messageId);
	}

	onClickOnCommand({data: event})
	{
		if (event.type === 'put')
		{
			this.handlePutAction(event.value);
		}
		else if (event.type === 'send')
		{
			this.handleSendAction(event.value);
		}
		else
		{
			Logger.warn('SendMessageHandler: Unprocessed command', event);
		}
	}

	onClickOnKeyboard({data: event})
	{
		if (event.action === 'ACTION')
		{
			const {action, value} = event.params;
			this.handleKeyboardAction(action, value);
		}

		if (event.action === 'COMMAND')
		{
			const {dialogId, messageId, botId, command, params} = event.params;

			this.restClient.callMethod(RestMethod.imMessageCommand, {
				'MESSAGE_ID': messageId,
				'DIALOG_ID': dialogId,
				'BOT_ID': botId,
				'COMMAND': command,
				'COMMAND_PARAMS': params,
			}).catch(error => console.error('SendMessageHandler: command processing error', error));
		}
	}

	handleKeyboardAction(action, value)
	{
		switch (action)
		{
			case 'SEND':
			{
				this.handleSendAction(value);
				break;
			}
			case 'PUT':
			{
				this.handlePutAction(value);
				break;
			}
			case 'CALL':
			{
				//this.openPhoneMenu(value);
				break;
			}
			case 'COPY':
			{
				Clipboard.copy(value);
				BX.UI.Notification.Center.notify({
					content: this.loc['IM_DIALOG_CLIPBOARD_COPY_SUCCESS'],
					autoHideDelay: 4000
				});
				break;
			}
			case 'DIALOG':
			{
				//this.openDialog(value);
				break;
			}
			default:
			{
				console.error('SendMessageHandler: unknown keyboard action');
			}
		}
	}

	handlePutAction(text)
	{
		EventEmitter.emit(EventType.textarea.insertText, { text: `${text} ` });
	}

	handleSendAction(text)
	{
		this.sendMessage(text);
		setTimeout(
			() => {
				EventEmitter.emit(EventType.dialog.scrollToBottom, {
					chatId: this.getChatId(),
					duration: 300,
					cancelIfScrollChange: false
				});
			},
			300);
	}

	// region helpers
	getMessageTextWithQuote(quoteMessage, text): string
	{
		let user = null;
		if (quoteMessage.authorId)
		{
			user = this.store.getters['users/get'](quoteMessage.authorId);
		}

		const files = this.store.getters['files/getList'](this.getChatId());

		const quoteDelimiter = '-'.repeat(54);
		const quoteTitle = (user && user.name) ? user.name: this.loc['IM_QUOTE_PANEL_DEFAULT_TITLE'];
		const quoteDate = Utils.date.format(quoteMessage.date, null, this.loc);
		const quoteContent = Utils.text.quote(quoteMessage.text, quoteMessage.params, files, this.loc);

		const message = [];
		message.push(quoteDelimiter);
		message.push(`${quoteTitle} [${quoteDate}]`);
		message.push(quoteContent);
		message.push(quoteDelimiter);
		message.push(text);

		return message.join("\n");
	}

	getChatId(): number
	{
		return this.store.state.application.dialog.chatId;
	}

	getDialogId(): number | string
	{
		return this.store.state.application.dialog.dialogId;
	}

	getUserId(): number
	{
		return this.store.state.application.common.userId;
	}
	// endregion helpers

	destroy()
	{
		EventEmitter.unsubscribe(EventType.textarea.sendMessage, this.onSendMessageHandler);
		EventEmitter.unsubscribe(EventType.dialog.clickOnMessageRetry, this.onClickOnMessageRetryHandler);
		EventEmitter.unsubscribe(EventType.dialog.clickOnCommand, this.onClickOnCommandHandler);
		EventEmitter.unsubscribe(EventType.dialog.clickOnKeyboardButton, this.onClickOnKeyboardHandler);
	}
}