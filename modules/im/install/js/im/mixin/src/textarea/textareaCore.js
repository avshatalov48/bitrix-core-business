import { EventEmitter } from 'main.core.events';
import {
	EventType,
	RestMethod,
	RestMethod as ImRestMethod,
	RestMethodHandler as ImRestMethodHandler
} from "im.const";
import { Logger } from "im.lib.logger";
import { Utils } from "im.lib.utils";

/**
 * @notice needs DialogCore mixin
 */
export const TextareaCore = {
	data()
	{
		return {
			messagesToSend: []
		}
	},
	created()
	{
		EventEmitter.subscribe(EventType.textarea.sendMessage, this.onSendMessage);
		EventEmitter.subscribe(EventType.textarea.startWriting, this.onTextareaStartWriting);
		EventEmitter.subscribe(EventType.textarea.appButtonClick, this.onTextareaAppButtonClick);
		EventEmitter.subscribe(EventType.textarea.focus, this.onTextareaFocus);
		EventEmitter.subscribe(EventType.textarea.blur, this.onTextareaBlur);
		EventEmitter.subscribe(EventType.textarea.keyUp, this.onTextareaKeyUp);
		EventEmitter.subscribe(EventType.textarea.edit, this.onTextareaEdit);
	},
	beforeDestroy()
	{
		EventEmitter.unsubscribe(EventType.textarea.sendMessage, this.onSendMessage);
		EventEmitter.unsubscribe(EventType.textarea.startWriting, this.onTextareaStartWriting);
		EventEmitter.unsubscribe(EventType.textarea.appButtonClick, this.onTextareaAppButtonClick);
		EventEmitter.unsubscribe(EventType.textarea.focus, this.onTextareaFocus);
		EventEmitter.unsubscribe(EventType.textarea.blur, this.onTextareaBlur);
		EventEmitter.unsubscribe(EventType.textarea.keyUp, this.onTextareaKeyUp);
		EventEmitter.unsubscribe(EventType.textarea.edit, this.onTextareaEdit);
	},
	methods: {
		//handlers
		onSendMessage({data: event})
		{
			if (!event.text)
			{
				return false;
			}

			this.addMessageOnClient(event.text);
		},
		onTextareaStartWriting({data: event})
		{
			this.startWriting();
		},
		onTextareaAppButtonClick({data: event})
		{
			//TODO
		},
		onTextareaFocus({data: event})
		{
			//TODO
		},
		onTextareaBlur({data: event})
		{
			//TODO
		},
		onTextareaKeyUp({ data: event })
		{
			//TODO
		},
		onTextareaEdit({ data: event })
		{
			//TODO
		},

		//actions
		addMessageOnClient(text = '', file = null)
		{
			if (!text && !file)
			{
				return false;
			}

			const quoteId = this.$store.getters['dialogues/getQuoteId'](this.dialogId);
			if (quoteId)
			{
				const quoteMessage = this.$store.getters['messages/getMessage'](this.chatId, quoteId);
				if (quoteMessage)
				{
					let user = null;
					if (quoteMessage.authorId)
					{
						user = this.$store.getters['users/get'](quoteMessage.authorId);
					}

					const files = this.$store.getters['files/getList'](this.chatId);

					const message = [];
					message.push('-'.repeat(54));
					message.push((user && user.name? user.name: this.localize['IM_QUOTE_PANEL_DEFAULT_TITLE'])+' ['+Utils.date.format(quoteMessage.date, null, this.localize)+']');
					message.push(Utils.text.quote(quoteMessage.text, quoteMessage.params, files, this.localize));
					message.push('-'.repeat(54));
					message.push(text);
					text = message.join("\n");

					this.quoteMessageClear();
				}
			}

			if (!this.isUnreadMessagesLoaded())
			{
				this.addMessageOnServer({ id: 0, chatId: this.chatId, dialogId: this.dialogId, text, file });
				this.processMessagesToSendQueue();

				return true;
			}

			const params = {};
			if (file)
			{
				params.FILE_ID = [file.id];
			}

			this.$store.dispatch('messages/add', {
				chatId: this.chatId,
				authorId: this.userId,
				text,
				params,
				sending: !file,
			}).then(messageId => {
				EventEmitter.emit(EventType.dialog.scrollToBottom, {chatId: this.chatId, cancelIfScrollChange: true});

				this.messagesToSend.push({
					id: messageId,
					chatId: this.chatId,
					dialogId: this.dialogId,
					text,
					file,
					sending: false
				});

				this.processMessagesToSendQueue();
			});

			return true;
		},
		processMessagesToSendQueue()
		{
			if (!this.diskFolderId)
			{
				this.requestDiskFolderId().then(() => {
					this.processMessagesToSendQueue();
				}).catch((error) => {
					Logger.warn('processMessagesToSendQueue error', error);
					return false;
				});

				return false;
			}

			this.messagesToSend.filter(element => !element.sending).forEach(element => {
				element.sending = true;
				if (element.file)
				{
					this.addMessageWithFile(element);
				}
				else
				{
					this.addMessageOnServer(element);
				}
			});

			return true;
		},
		addMessageOnServer(element)
		{
			this.stopWriting();

			const quoteId = this.$store.getters['dialogues/getQuoteId'](this.dialogId);
			if (quoteId)
			{
				const quoteMessage = this.$store.getters['messages/getMessage'](this.chatId, quoteId);
				if (quoteMessage)
				{
					const user = this.$store.getters['users/get'](quoteMessage.authorId);

					const newMessage = [];
					newMessage.push("------------------------------------------------------");
					newMessage.push((user.name? user.name: this.localize['IM_QUOTE_PANEL_DEFAULT_TITLE']));
					newMessage.push(quoteMessage.text);
					newMessage.push('------------------------------------------------------');
					newMessage.push(element.text);
					element.text = newMessage.join("\n");

					this.quoteMessageClear();
				}
			}

			this.getRestClient().callMethod(ImRestMethod.imMessageAdd, {
				'TEMPLATE_ID': element.id,
				'DIALOG_ID': element.dialogId,
				'MESSAGE': element.text
			}, null, null)
			.then(response => {
				this.$store.dispatch('messages/update', {
					id: element.id,
					chatId: element.chatId,
					fields: {
						id: response.data(),
						sending: false,
						error: false,
					}
				}).then(() => {
					this.$store.dispatch('messages/actionFinish', {
						id: response.data(),
						chatId: element.chatId
					});
				});
			}).catch(error => {
				Logger.warn('Error during adding message');
			});

			return true;
		},

		//writing
		stopWriting(dialogId = this.dialogId)
		{
			this.timer.stop('writes', dialogId, true);
			this.timer.stop('writesSend', dialogId, true);
		},
		startWriting(dialogId = this.dialogId)
		{
			if (Utils.dialog.isEmptyDialogId(dialogId) || this.timer.has('writes', dialogId))
			{
				return false;
			}

			this.timer.start('writes', dialogId, 28);
			this.timer.start('writesSend', dialogId, 5, () => {
				this.getRestClient().callMethod(RestMethod.imDialogWriting, {
					'DIALOG_ID': dialogId
				}).catch(() => {
					this.timer.stop('writes', dialogId);
				});
			});
		},

		insertText(event)
		{
			EventEmitter.emit(EventType.textarea.insertText, event);
		},

		requestDiskFolderId()
		{
			if (this.requestDiskFolderPromise)
			{
				return this.requestDiskFolderPromise;
			}

			this.requestDiskFolderPromise = new Promise((resolve, reject) =>
			{
				if (
					this.flagRequestDiskFolderIdSended
					|| this.diskFolderId
				)
				{
					this.flagRequestDiskFolderIdSended = false;
					resolve();

					return true;
				}

				this.flagRequestDiskFolderIdSended = true;

				this.getRestClient().callMethod(ImRestMethod.imDiskFolderGet, {chat_id: this.chatId})
					.then(response => {
						this.flagRequestDiskFolderIdSended = false;
						this.executeRestAnswer(ImRestMethodHandler.imDiskFolderGet, response);
						resolve();
					}).catch(error => {
					this.flagRequestDiskFolderIdSended = false;
					this.executeRestAnswer(ImRestMethodHandler.imDiskFolderGet, error);
					reject();
				});
			});

			return this.requestDiskFolderPromise;
		}
	}
};