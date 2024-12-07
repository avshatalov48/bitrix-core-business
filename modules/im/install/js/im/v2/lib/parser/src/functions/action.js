import { Messenger } from 'im.public';
import { Dom, Text } from 'main.core';
import { EventEmitter } from 'main.core.events';

import { getConst, getUtils, getCore } from '../utils/core-proxy';

const { EventType } = getConst();

const atomRegExpPart = '\\d{4}-\\d{2}-\\d{2}T[0-2]\\d:[0-5]\\d:[0-5]\\d[+-][0-2]\\d:[0-5]\\d';

const ActionType = {
	put: 'put',
	send: 'send',
};

export const ParserAction = {

	decodePut(text): string
	{
		text = text.replace(/\[PUT(?:=(?:.+?))?](?:.+?)?\[\/PUT]/gi, (match) =>
		{
			return match.replace(/\[PUT(?:=(.+))?](.+?)?\[\/PUT]/gi, (whole, command, text) =>
			{
				text = text? text: command;
				command = command? command: text;

				text = Text.decode(text);
				command = Text.decode(command).replace('<br />', '\n');

				if (!text.trim())
				{
					return '';
				}

				text = text.replace(/<(\w+)[^>]*>(.*?)<\/\1>/i, "$2", text);
				text = text.replace(/\[(\w+)[^\]]*](.*?)\[\/\1]/i, "$2", text);

				return this._getHtmlForAction('put', text, command);
			});
		});

		return text;
	},

	purifyPut(text): string
	{
		text = text.replace(/\[PUT(?:=(?:.+?))?](?:.+?)?\[\/PUT]/gi, (match) =>
		{
			return match.replace(/\[PUT(?:=(.+))?](.+?)?\[\/PUT]/gi, (whole, command, text) => {
				return text? text: command;
			});
		});

		return text;
	},

	decodeSend(text): string
	{
		text = text.replace(/\[SEND(?:=(?:.+?))?](?:.+?)?\[\/SEND]/gi, (match) =>
		{
			return match.replace(/\[SEND(?:=(.+))?](.+?)?\[\/SEND]/gi, (whole, command, text) =>
			{
				text = text? text: command;
				command = command? command: text;

				text = Text.decode(text);
				command = Text.decode(command).replace('<br />', '\n');

				if (!text.trim())
				{
					return '';
				}

				text = text.replace(/<(\w+)[^>]*>(.*?)<\\1>/i, "$2", text);
				text = text.replace(/\[(\w+)[^\]]*](.*?)\[\/\1]/i, "$2", text);

				command = command.split('####REPLACEMENT_PUT_').join('####REPLACEMENT_SP_');

				return this._getHtmlForAction('send', text, command);
			});
		});

		return text;
	},

	purifySend(text): string
	{
		text = text.replace(/\[SEND(?:=(?:.+?))?](?:.+?)?\[\/SEND]/gi, (match) =>
		{
			return match.replace(/\[SEND(?:=(.+))?](.+?)?\[\/SEND]/gi, (whole, command, text) => {
				return text? text: command;
			});
		});

		return text;
	},

	decodeDate(text): string
	{
		text = text.replace(RegExp('\\[DATE=('+atomRegExpPart+')](.+?)\\[\\/DATE]', 'ig'), (whole, date, text) => {
			text = text.replace(/<(\w+)[^>]*>(.*?)<\\1>/i, "$2", text);
			text = text.replace(/\[(\w+)[^\]]*](.*?)\[\/\1]/i, "$2", text);

			return this._getHtmlForAction('date', text, date);
		});

		return text;
	},

	purifyDate(text): string
	{
		const atomRegexp = getUtils().date.atomRegexpString;
		text = text.replace(RegExp('\[DATE=('+atomRegexp+')](.+?)\[\/DATE]', 'ig'), (whole, date, text) => {
			return text;
		});

		return text;
	},

	_getHtmlForAction(method, text, data)
	{
		return Dom.create({
			tag: 'span',
			attrs: { className: 'bx-im-message-command-wrap' },
			children: [
				Dom.create({
					tag: 'span',
					attrs: {
						className: 'bx-im-message-command',
						'data-entity': method,
					},
					text
				}),
				Dom.create({
					tag: 'span',
					attrs: { className: 'bx-im-message-command-data' },
					text: data
				}),
			]
		}).outerHTML;
	},

	executeClickEvent(event: PointerEvent)
	{
		if (!Dom.hasClass(event.target, 'bx-im-message-command'))
		{
			return;
		}

		const element: HTMLSpanElement = event.target;
		const messageId = getMessageIdForClickElement(element);
		const dialogId = getDialogIdByMessageId(messageId) ?? '';
		if (element.dataset.entity === ActionType.put)
		{
			const { innerText: textToInsert = '' } = element.parentElement.querySelector('.bx-im-message-command-data');
			if (!textToInsert)
			{
				return;
			}

			EventEmitter.emit(EventType.textarea.insertText, {
				text: textToInsert,
				dialogId,
			});
		}
		else if (element.dataset.entity === ActionType.send)
		{
			const { innerText: textToSend = '' } = element.parentElement.querySelector('.bx-im-message-command-data');
			if (!textToSend)
			{
				return;
			}

			EventEmitter.emit(EventType.textarea.sendMessage, {
				text: textToSend,
				dialogId,
			});
		}
	},
};

const getMessageIdForClickElement = (element: HTMLSpanElement): number | null => {
	const messageElement = element.closest('.bx-im-message-base__wrap');
	if (!messageElement || !messageElement.dataset.id)
	{
		return null;
	}

	return messageElement.dataset.id;
};

const getDialogIdByMessageId = (messageId: number): string | null => {
	const message = getCore().getStore().getters['messages/getById'](messageId);
	if (!message)
	{
		return null;
	}
	const dialog = getCore().getStore().getters['chats/getByChatId'](message.chatId);
	if (!dialog)
	{
		return null;
	}

	return dialog.dialogId;
};
