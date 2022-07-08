import { EventEmitter } from "main.core.events";
import { EventType, RestMethod } from "im.const";
import { Logger } from "im.lib.logger";

export class DialogActionHandler
{
	restClient: Object = null;

	constructor($Bitrix)
	{
		this.restClient = $Bitrix.RestClient.get();

		this.subscribeToEvents();
	}

	subscribeToEvents()
	{
		this.clickOnMentionHandler = this.onClickOnMention.bind(this);
		this.clickOnUserNameHandler = this.onClickOnUserName.bind(this);
		this.clickOnMessageMenuHandler = this.onClickOnMessageMenu.bind(this);
		this.clickOnReadListHandler = this.onClickOnReadList.bind(this);
		this.clickOnChatTeaserHandler = this.onClickOnChatTeaser.bind(this);
		this.clickOnDialogHandler = this.onClickOnDialog.bind(this);
		EventEmitter.subscribe(EventType.dialog.clickOnMention, this.clickOnMentionHandler);
		EventEmitter.subscribe(EventType.dialog.clickOnUserName, this.clickOnUserNameHandler);
		EventEmitter.subscribe(EventType.dialog.clickOnMessageMenu, this.clickOnMessageMenuHandler);
		EventEmitter.subscribe(EventType.dialog.clickOnReadList, this.clickOnReadListHandler);
		EventEmitter.subscribe(EventType.dialog.clickOnChatTeaser, this.clickOnChatTeaserHandler);
		EventEmitter.subscribe(EventType.dialog.clickOnDialog, this.clickOnDialogHandler);
	}

	onClickOnMention({data: event})
	{
		if (event.type === 'USER')
		{
			Logger.warn('DialogActionHandler: open user profile', event);
		}
		else if (event.type === 'CHAT')
		{
			Logger.warn('DialogActionHandler: open dialog from mention click', event);
		}
		else if (event.type === 'CALL')
		{
			Logger.warn('DialogActionHandler: open phone menu', event);
		}
	}

	onClickOnUserName({data: event})
	{
		EventEmitter.emit(EventType.textarea.insertText, {
			text: `${event.user.name}, `
		});
	}

	onClickOnMessageMenu({data: event})
	{
		Logger.warn('DialogActionHandler: open message menu', event);
	}

	onClickOnReadList({data: event})
	{
		Logger.warn('DialogActionHandler: open read list', event);
	}

	onClickOnChatTeaser({data: event})
	{
		this.joinParentChat(event.message.id, `chat${event.message.params.CHAT_ID}`)
			.then((dialogId) => {
				Logger.warn('DialogActionHandler: open dialog from teaser click', dialogId);
			})
			.catch((error) => {
				console.error('DialogActionHandler: error joining parent chat', error);
			});
	}

	onClickOnDialog()
	{
		Logger.warn('DialogActionHandler: click on dialog');
	}

	joinParentChat(messageId, dialogId)
	{
		return new Promise((resolve, reject) =>
		{
			if (!messageId || !dialogId)
			{
				return reject();
			}

			// TODO: what is this for
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
	}

	unsubscribeEvents()
	{
		EventEmitter.unsubscribe(EventType.dialog.clickOnMention, this.clickOnMentionHandler);
		EventEmitter.unsubscribe(EventType.dialog.clickOnUserName, this.clickOnUserNameHandler);
		EventEmitter.unsubscribe(EventType.dialog.clickOnMessageMenu, this.clickOnMessageMenuHandler);
		EventEmitter.unsubscribe(EventType.dialog.clickOnReadList, this.clickOnReadListHandler);
		EventEmitter.unsubscribe(EventType.dialog.clickOnChatTeaser, this.clickOnChatTeaserHandler);
		EventEmitter.unsubscribe(EventType.dialog.clickOnDialog, this.clickOnDialogHandler);
	}

	destroy()
	{
		this.unsubscribeEvents();
	}
}