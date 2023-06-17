import {Dom, Text} from 'main.core';

import {getUtils} from '../utils/core-proxy';

const atomRegExpPart = '\\d{4}-\\d{2}-\\d{2}T[0-2]\\d:[0-5]\\d:[0-5]\\d[+-][0-2]\\d:[0-5]\\d';

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
	}
}

