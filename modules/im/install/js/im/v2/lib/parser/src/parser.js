import {Type, Text, Loc} from 'main.core';

import {ParserSlashCommand} from './functions/slash-command';
import {ParserQuote} from './functions/quote';
import {ParserImage} from './functions/image';
import {ParserSmile} from './functions/smile';
import {ParserUrl} from './functions/url';
import {ParserFont} from './functions/font';
import {ParserLines} from './functions/lines';
import {ParserAction} from './functions/action';
import {ParserCall} from './functions/call';
import {ParserMention} from './functions/mention';
import {ParserCommon} from './functions/common';
import {ParserIcon} from './functions/icon';
import {ParserRecursionPrevention} from './utils/recursion-prevention';
import {ParserReplace} from './functions/replace';
import {ParserDisk} from './functions/disk';

import {getCore, getLogger} from './utils/core-proxy';

import './parser.css';

import type {ImModelMessage, ImModelNotification, ImModelRecentItem} from 'im.v2.model';
import type {ParserConfig} from './types/parser-config';

export const Parser = {
	decodeMessage(message: ImModelMessage): string
	{
		const messageFiles = getCore().store.getters['messages/getMessageFiles'](message.id);

		return this.decode({
			text: message.text,
			attach: message.attach,
			files: messageFiles,
			replaces: message.replaces,
			showIconIfEmptyText: false
		});
	},

	decodeNotification(notification: ImModelNotification): string
	{
		return this.decode({
			text: notification.text,
			attach: notification.params.ATTACH ?? false,
			replaces: notification.replaces,
			showIconIfEmptyText: false,
			showImageFromLink: false,
			urlTarget: '_self',
		});
	},

	decodeText(text: string): string
	{
		return this.decode({text});
	},

	decodeHtml(text: string): string
	{
		return this.decode({text});
	},

	decodeSmileForLegacyCore(text: string, options: {})
	{
		options.ratioConfig = Object.freeze({
			Default: 1,
			Big: 1.6,
		});
		return ParserSmile.decodeSmile(text, options);
	},

	decode(config: ParserConfig): string
	{
		if (!Type.isPlainObject(config))
		{
			getLogger().error('Parser.decode: the first parameter must be object', config);
			return '<b style="color:red">Parser.decode: the first parameter must be a parameter object</b';
		}

		let {text} = config;
		const {
			attach = false,
			files = false,
			replaces = [],
			removeLinks = false,
			showIconIfEmptyText = true,
			showImageFromLink = true,
			urlTarget = '_blank',
		} = config;

		if (!Type.isString(text))
		{
			if (Type.isNumber(text))
			{
				return text.toString();
			}

			return '';
		}
		if (!text)
		{
			if (showIconIfEmptyText)
			{
				text = ParserIcon.addIconToShortText({text, attach, files});
			}
			return text.trim();
		}

		text = ParserReplace.decode(text, replaces);
		text = Text.encode(text.trim());

		text = ParserCommon.decodeNewLine(text);
		text = ParserCommon.decodeTabulation(text);

		text = ParserRecursionPrevention.cutPutTag(text);
		text = ParserRecursionPrevention.cutSendTag(text);
		text = ParserRecursionPrevention.cutCodeTag(text);

		text = ParserSmile.decodeSmile(text);
		text = ParserSlashCommand.decode(text);
		text = ParserQuote.decodeArrowQuote(text);
		text = ParserQuote.decodeQuote(text);
		text = ParserUrl.decode(text, {urlTarget, removeLinks});
		text = ParserFont.decode(text);
		text = ParserLines.decode(text);
		text = ParserMention.decode(text);
		text = ParserCall.decode(text);
		text = ParserImage.decodeIcon(text);
		if (showImageFromLink)
		{
			text = ParserImage.decodeLink(text);
		}
		text = ParserDisk.decode(text);
		text = ParserAction.decodeDate(text);
		text = ParserRecursionPrevention.cutStartTag(text);

		text = ParserRecursionPrevention.recoverStartTag(text);
		text = ParserRecursionPrevention.recoverSendTag(text);
		text = ParserAction.decodeSend(text);

		text = ParserRecursionPrevention.recoverPutTag(text);
		text = ParserAction.decodePut(text);

		text = ParserRecursionPrevention.recoverCodeTag(text);
		text = ParserQuote.decodeCode(text);

		text = ParserRecursionPrevention.recoverRecursionTag(text);

		text = ParserCommon.removeDuplicateTags(text);

		ParserRecursionPrevention.clean();

		return text;
	},

	purifyMessage(message: ImModelMessage): string
	{
		const messageFiles = getCore().store.getters['messages/getMessageFiles'](message.id);

		return this.purify({
			text: message.text,
			attach: message.attach,
			files: messageFiles
		});
	},

	purifyNotification(notification: ImModelNotification): string
	{
		const messageFiles = getCore().store.getters['messages/getMessageFiles'](notification.id);

		return this.purify({
			text: notification.text,
			attach: notification.params.ATTACH ?? false,
			files: messageFiles
		});
	},

	purifyRecent(recentMessage: ImModelRecentItem): string
	{
		const {files, attach} = this.prepareConfigForRecent(recentMessage);

		return this.purify({
			text: recentMessage.message.text,
			attach,
			files,
			showPhraseMessageWasDeleted: recentMessage.message.id !== 0
		});
	},

	purifyText(text: string): string
	{
		return this.purify({text});
	},

	purify(config: ParserConfig): string
	{
		if (!Type.isPlainObject(config))
		{
			getLogger().error('Parser.purify: the first parameter must be a object', config);
			return 'Parser.purify: the first parameter must be a parameter object';
		}

		let {text} = config;
		const {
			attach = false,
			files = false,
			replaces = [],
			showIconIfEmptyText = true,
			showPhraseMessageWasDeleted = true,
		} = config;

		if (!Type.isString(text))
		{
			text = Type.isNumber(text) ? text.toString() : '';
		}

		if (!text)
		{
			text = ParserIcon.addIconToShortText({text, attach, files});
			return text.trim();
		}

		text = Text.encode(text.trim());

		text = ParserCommon.purifyNewLine(text, '\n');
		text = ParserSlashCommand.purify(text);
		text = ParserQuote.purifyArrowQuote(text);
		text = ParserQuote.purifyQuote(text);
		text = ParserQuote.purifyCode(text);
		text = ParserAction.purifyPut(text);
		text = ParserAction.purifySend(text);
		text = ParserMention.purify(text);
		text = ParserFont.purify(text);
		text = ParserLines.purify(text);
		text = ParserCall.purify(text);
		text = ParserImage.purifyLink(text);
		text = ParserImage.purifyIcon(text);
		text = ParserUrl.purify(text);
		text = ParserDisk.purify(text);
		text = ParserCommon.purifyNewLine(text);
		text = ParserIcon.addIconToShortText({text, attach, files});

		if (text.length > 0)
		{
			text = Text.decode(text);
		}
		else if (showPhraseMessageWasDeleted)
		{
			text = Loc.getMessage('IM_PARSER_MESSAGE_DELETED');
		}

		return text.trim();
	},

	prepareQuote(message: ImModelMessage): string
	{
		const {id, attach} = message;
		let {text} = message;

		const files = getCore().store.getters['messages/getMessageFiles'](id);

		text = Text.encode(text.trim());

		text = ParserMention.purify(text);
		text = ParserCall.purify(text);
		text = ParserLines.purify(text);
		text = ParserCommon.purifyBreakLine(text, '\n');
		text = ParserCommon.purifyNbsp(text);
		text = ParserUrl.removeSimpleUrlTag(text);
		text = ParserQuote.purifyCode(text, ' ');
		text = ParserQuote.purifyQuote(text, ' ');
		text = ParserQuote.purifyArrowQuote(text, ' ');
		text = ParserIcon.addIconToShortText({text, attach, files});

		if (text.length > 0)
		{
			text = Text.decode(text);
		}
		else
		{
			text = Loc.getMessage('IM_PARSER_MESSAGE_DELETED');
		}

		return text.trim();
	},

	prepareEdit(message: ImModelMessage): string
	{
		let {text} = message;

		text = ParserUrl.removeSimpleUrlTag(text);

		return text.trim();
	},

	prepareCopy(message: ImModelMessage): string
	{
		const {id} = message;
		let {text} = message;

		const files = getCore().store.getters['messages/getMessageFiles'](id).map(file => {
			return `[DISK=${file.id}]\n`;
		});

		text = files.join('\n') + text;

		return text.trim();
	},

	prepareConfigForRecent(recentMessage: ImModelRecentItem): {files: boolean | Object[], attach: boolean | string | Object[]}
	{
		let files = false;
		const fileField = recentMessage.message.params.withFile;
		if (Type.isBoolean(fileField))
		{
			files = fileField;
		}
		else if (Type.isPlainObject(fileField))
		{
			files = [fileField];
		}

		let attach = false;
		const attachField = recentMessage.message.params.withAttach;
		if (
			Type.isBoolean(attachField)
			|| Type.isStringFilled(attachField)
			|| Type.isArray(attachField)
		)
		{
			attach = attachField;
		}
		else if (Type.isPlainObject(attachField))
		{
			attach = [attachField];
		}

		return {files, attach};
	},

	executeClickEvent(event: PointerEvent)
	{
		ParserMention.executeClickEvent(event);
		ParserQuote.executeClickEvent(event);
	},
};