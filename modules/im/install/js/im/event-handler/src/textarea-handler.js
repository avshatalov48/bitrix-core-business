import { EventEmitter } from "main.core.events";
import { EventType, RestMethod } from "im.const";
import { Timer } from "im.lib.timer";
import { Utils } from "im.lib.utils";

export class TextareaHandler
{
	store: Object = null;
	restClient: Object = null;
	timer: Timer = null;

	constructor($Bitrix)
	{
		this.store = $Bitrix.Data.get('controller').store;
		this.restClient = $Bitrix.RestClient.get();
		this.timer = new Timer();

		this.subscribeToEvents();
	}

	// region events
	subscribeToEvents()
	{
		this.onStartWritingHandler = this.onStartWriting.bind(this);
		this.onStopWritingHandler = this.onStopWriting.bind(this);
		this.onAppButtonClickHandler = this.onAppButtonClick.bind(this);
		this.onFocusHandler = this.onFocus.bind(this);
		this.onBlurHandler = this.onBlur.bind(this);
		this.onKeyUpHandler = this.onKeyUp.bind(this);
		this.onEditHandler = this.onEdit.bind(this);

		EventEmitter.subscribe(EventType.textarea.startWriting, this.onStartWritingHandler);
		EventEmitter.subscribe(EventType.textarea.stopWriting, this.onStopWritingHandler);
		EventEmitter.subscribe(EventType.textarea.appButtonClick, this.onAppButtonClickHandler);
		EventEmitter.subscribe(EventType.textarea.focus, this.onFocusHandler);
		EventEmitter.subscribe(EventType.textarea.blur, this.onBlurHandler);
		EventEmitter.subscribe(EventType.textarea.keyUp, this.onKeyUpHandler);
		EventEmitter.subscribe(EventType.textarea.edit, this.onEditHandler);
	}

	onStartWriting()
	{
		this.startWriting();
	}

	onStopWriting()
	{
		this.stopWriting();
	}

	onAppButtonClick()
	{
		//
	}

	onFocus()
	{
		//
	}

	onBlur()
	{
		//
	}

	onKeyUp()
	{
		//
	}

	onEdit()
	{
		//
	}
	//endregion events

	// region writing
	startWriting(dialogId = this.getDialogId())
	{
		if (Utils.dialog.isEmptyDialogId(dialogId) || this.timer.has('writes', dialogId))
		{
			return false;
		}

		this.timer.start('writes', dialogId, 28);
		this.timer.start('writesSend', dialogId, 5, () => {
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
	// endregion writing

	// region helpers
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

	getDiskFolderId()
	{
		return this.store.state.application.dialog.diskFolderId;
	}
	// endregion helpers

	destroy()
	{
		EventEmitter.unsubscribe(EventType.textarea.startWriting, this.onStartWritingHandler);
		EventEmitter.unsubscribe(EventType.textarea.stopWriting, this.onStopWritingHandler);
		EventEmitter.unsubscribe(EventType.textarea.appButtonClick, this.onAppButtonClickHandler);
		EventEmitter.unsubscribe(EventType.textarea.focus, this.onFocusHandler);
		EventEmitter.unsubscribe(EventType.textarea.blur, this.onBlurHandler);
		EventEmitter.unsubscribe(EventType.textarea.keyUp, this.onKeyUpHandler);
		EventEmitter.unsubscribe(EventType.textarea.edit, this.onEditHandler);
	}
}