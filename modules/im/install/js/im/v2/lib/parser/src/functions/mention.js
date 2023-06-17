import {Loc, Dom, Text, Type} from 'main.core';
import {EventEmitter} from 'main.core.events';

import {Parser} from '../parser';
import {ParserUtils} from '../utils/utils';
import {getCore, getConst} from '../utils/core-proxy';

const {EventType, MessageMentionType} = getConst();

export const ParserMention = {

	decode(text): string
	{
		text = text.replace(/\[USER=([0-9]+)( REPLACE)?](.*?)\[\/USER]/gi, (whole, userId, replace, userName) => {
			userId = Number.parseInt(userId, 10);

			if (!Type.isNumber(userId) || userId === 0)
			{
				return userName;
			}

			if (replace || !userName)
			{
				const user = getCore().getStore().getters['users/get'](userId);
				if (user)
				{
					userName = user.name;
				}
			}
			else
			{
				userName = Text.decode(userName);
			}

			if (!userName)
			{
				userName = `User ${userId}`;
			}

			return Dom.create({
				tag: 'span',
				attrs: {
					className: 'bx-im-mention',
					'data-type': 'USER',
					'data-value': userId,
				},
				text: userName
			}).outerHTML;
		});

		text = text.replace(/\[chat=(imol\|)?(\d+)](.*?)\[\/chat]/gi, (whole, openlines, chatId, chatName) => {
			chatId = Number.parseInt(chatId, 10);

			if (!Type.isNumber(chatId) || chatId === 0 || openlines)
			{
				return chatName;
			}

			if (chatName)
			{
				chatName = Text.decode(chatName);
			}
			else
			{
				const dialog = getCore().store.getters['dialogues/get']('chat'+chatId);
				chatName = dialog? dialog.name: 'Chat '+chatId;
			}

			return Dom.create({
				tag: 'span',
				attrs: {
					className: 'bx-im-mention',
					'data-type': 'CHAT',
					'data-value': 'chat' + chatId,
				},
				text: chatName
			}).outerHTML;
		});

		text = text.replace(/\[context=((?:chat\d+|\d+:\d+)\/(\d+))](.*?)\[\/context]/gis, (whole, contextTag, messageId, text) =>
		{
			if (!text)
			{
				return '';
			}

			text = Text.decode(text);
			contextTag = ParserUtils.getFinalContextTag(contextTag);
			if (!contextTag)
			{
				return text;
			}
			const dialogId = contextTag.split('/')[0];

			let title = '';
			messageId = Number.parseInt(messageId, 10);
			if (Type.isNumber(messageId) && messageId > 0)
			{
				const message = getCore().store.getters['messages/getById'](messageId);
				if (message)
				{
					title = Parser.purifyMessage(message);
					const user = getCore().store.getters['users/get'](message.authorId);
					if (user)
					{
						title = `${user.name}: ${title}`;
					}
				}
			}
			if (!Type.isStringFilled(title))
			{
				title = Loc.getMessage('IM_PARSER_MENTION_DIALOG');
			}

			return Dom.create({
				tag: 'span',
				attrs: {
					className: 'bx-im-mention',
					'data-type': 'CONTEXT',
					'data-dialog-id': dialogId,
					'data-message-id': messageId,
					title,
				},
				text
			}).outerHTML;
		});

		return text;
	},

	purify(text): string
	{
		text = text.replace(/\[USER=([0-9]+)( REPLACE)?](.*?)\[\/USER]/gi, (whole, userId, replace, userName) => {
			userId = Number.parseInt(userId, 10);

			if (!Type.isNumber(userId) || userId === 0)
			{
				return userName;
			}

			if (replace || !userName)
			{
				const user = getCore().getStore().getters['users/get'](userId);
				if (user)
				{
					userName = user.name;
				}
			}
			else
			{
				userName = Text.decode(userName);
			}

			if (!userName)
			{
				userName = `User ${userId}`;
			}

			return userName;
		});

		text = text.replace(/\[CHAT=(imol\|)?(\d+)](.*?)\[\/CHAT]/gi, (whole, openlines, chatId, chatName) => {
			chatId = Number.parseInt(chatId, 10);

			if (!chatName)
			{
				const dialog = getCore().store.getters['dialogues/get']('chat'+chatId);
				chatName = dialog? dialog.name: 'Chat '+chatId;
			}

			return chatName;
		});

		text = text.replace(/\[context=(chat\d+|\d+:\d+)\/(\d+)](.*?)\[\/context]/gis, (whole, dialogId, messageId, text) => {
			if (!text)
			{
				const dialog = getCore().store.getters['dialogues/get'](dialogId);
				text = dialog? dialog.name: 'Dialog '+dialogId;
			}

			return text;
		});

		return text;
	},

	executeClickEvent(event: PointerEvent)
	{
		if (!Dom.hasClass(event.target, 'bx-im-mention'))
		{
			return;
		}

		if (
			event.target.dataset.type === MessageMentionType.user
			|| event.target.dataset.type === MessageMentionType.chat
		)
		{
			EventEmitter.emit(EventType.mention.openChatInfo, {
				event,
				dialogId: event.target.dataset.value
			});
		}
		else if (event.target.dataset.type === MessageMentionType.context)
		{
			EventEmitter.emit(EventType.dialog.goToMessageContext, {
				messageId: Number.parseInt(event.target.dataset.messageId, 10),
				dialogId: event.target.dataset.dialogId.toString(),
			});
		}
	},
}

