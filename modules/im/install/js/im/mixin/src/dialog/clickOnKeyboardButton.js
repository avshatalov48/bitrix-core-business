import { EventEmitter } from 'main.core.events';
import { EventType, RestMethod } from "im.const";
import { Clipboard } from "im.lib.clipboard";

/**
 * @notice needs TextareaCore mixin
 */
export const DialogClickOnKeyboardButton = {
	created()
	{
		EventEmitter.subscribe(EventType.dialog.clickOnKeyboardButton, this.onClickOnKeyboardButton);
	},
	beforeDestroy()
	{
		EventEmitter.unsubscribe(EventType.dialog.clickOnKeyboardButton, this.onClickOnKeyboardButton);
	},
	methods: {
		onClickOnKeyboardButton({data: event})
		{
			if (event.action === 'ACTION')
			{
				const {dialogId, messageId, botId, action, value} = event.params;

				if (action === 'SEND')
				{
					this.addMessageOnClient(value);
					setTimeout(
						() => {
							EventEmitter.emit(EventType.dialog.scrollToBottom, {chatId: this.chatId, duration: 300, cancelIfScrollChange: false});
						},
					300);
				}
				else if (action === 'PUT')
				{
					this.insertText({ text: value + ' ' });
				}
				else if (action === 'CALL')
				{
					//this.openPhoneMenu(value);
				}
				else if (action === 'COPY')
				{
					Clipboard.copy(value);

					BX.UI.Notification.Center.notify({
						content: this.localize['IM_DIALOG_CLIPBOARD_COPY_SUCCESS'],
						autoHideDelay: 4000
					});
				}
				else if (action === 'DIALOG')
				{
					//this.openDialog(value);
				}

				return true;
			}

			if (event.action === 'COMMAND')
			{
				const {dialogId, messageId, botId, command, params} = event.params;

				this.getRestClient().callMethod(RestMethod.imMessageCommand, {
					'MESSAGE_ID': messageId,
					'DIALOG_ID': dialogId,
					'BOT_ID': botId,
					'COMMAND': command,
					'COMMAND_PARAMS': params,
				});

				return true;
			}

			return false;
		},
	}
};