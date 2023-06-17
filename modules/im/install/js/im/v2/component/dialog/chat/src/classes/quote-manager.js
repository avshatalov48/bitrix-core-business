import {Loc} from 'main.core';
import {EventEmitter} from 'main.core.events';

import {Core} from 'im.v2.application.core';
import {EventType, DialogType} from 'im.v2.const';
import {DateFormatter, DateTemplate} from 'im.v2.lib.date-formatter';
import {Parser} from 'im.v2.lib.parser';

import type {ImModelMessage, ImModelUser, ImModelDialog} from 'im.v2.model';

const QUOTE_DELIMITER = '-'.repeat(54);

export const QuoteManager = {
	sendQuoteEvent(message: ImModelMessage)
	{
		EventEmitter.emit(EventType.textarea.insertText, {
			text: this.prepareQuoteText(message),
			withNewLine: true
		});
	},
	prepareQuoteText(message: ImModelMessage): string
	{
		let quoteTitle = Loc.getMessage('IM_DIALOG_CHAT_QUOTE_DEFAULT_TITLE');
		if (message.authorId)
		{
			const user: ImModelUser = Core.getStore().getters['users/get'](message.authorId);
			quoteTitle = user.name;
		}

		const quoteDate = DateFormatter.formatByTemplate(message.date, DateTemplate.notification);
		const quoteText = Parser.prepareQuote(message);

		let quoteContext = '';
		const dialog: ImModelDialog = Core.getStore().getters['dialogues/getByChatId'](message.chatId);
		if (dialog && dialog.type === DialogType.user)
		{
			quoteContext = `#${dialog.dialogId}:${Core.getUserId()}/${message.id}`;
		}
		else
		{
			quoteContext = `#${dialog.dialogId}/${message.id}`;
		}

		return `${QUOTE_DELIMITER}\n`
			+ `${quoteTitle} [${quoteDate}] ${quoteContext}\n`
			+ `${quoteText}\n`
			+ `${QUOTE_DELIMITER}\n`
		;
	}
};