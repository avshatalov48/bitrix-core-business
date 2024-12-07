import { Dom, Tag } from 'main.core';
import { EventEmitter } from 'main.core.events';

import { ParserRecursionPrevention } from '../utils/recursion-prevention';
import { ParserUtils } from '../utils/utils';
import { getUtils, getConst } from '../utils/core-proxy';
import { ParserIcon } from './icon';

const { EventType } = getConst();

const QUOTE_SIGN = '&gt;&gt;';
const NO_CONTEXT_TAG = 'none';

export const ParserQuote = {

	decodeArrowQuote(text: string): string
	{
		if (!text.includes(QUOTE_SIGN))
		{
			return text;
		}

		let isProcessed = false;

		const textLines = text.split('<br />');
		for (let i = 0; i < textLines.length; i++)
		{
			if (!textLines[i].startsWith(QUOTE_SIGN))
			{
				continue;
			}

			const quoteStartIndex = i;

			const outerContainerStart = `<div data-context="${NO_CONTEXT_TAG}" class="bx-im-message-quote --inline">`;
			const innerContainerStart = '<div class="bx-im-message-quote__wrap">';
			const containerEnd = '</div>';
			textLines[quoteStartIndex] = textLines[quoteStartIndex].replace(QUOTE_SIGN, `${outerContainerStart}${innerContainerStart}`);
			// remove >> from all next lines
			while (++i < textLines.length && textLines[i].startsWith(QUOTE_SIGN))
			{
				textLines[i] = textLines[i].replace(QUOTE_SIGN, '');
			}
			const quoteEndIndex = i - 1;
			textLines[quoteEndIndex] += `${containerEnd}${containerEnd}`;
			isProcessed = true;
		}

		if (!isProcessed)
		{
			return text;
		}

		return textLines.join('<br />');
	},

	purifyArrowQuote(text, spaceLetter = ' '): string
	{
		text = text.replace(
			new RegExp(`^(${QUOTE_SIGN}(.*))`, 'gim'),
			ParserIcon.getQuoteBlock() + spaceLetter
		);

		return text;
	},

	decodeQuote(text, { contextDialogId = '' } = {}): string
	{
		const sanitizedText = ParserRecursionPrevention.cutTags(text);

		const decodedText = sanitizedText.replaceAll(
			/-{54}(<br \/>(.*?)\[(.*?)]( #(?:chat\d+|\d+:\d+)\/\d+)?)?<br \/>(.*?)-{54}(<br \/>)?/gs,
			(whole, userBlock, userName, timeTag, contextTag, quoteText): string => {
				const preparedQuoteText = getQuoteText(userName, timeTag, quoteText);
				const userContainer = getUserBlock(userName, timeTag);
				const finalContextTag = getFinalContextTag(contextTag, contextDialogId);

				const layout = Tag.render`
					<div class='bx-im-message-quote' data-context='${finalContextTag}'>
						<div class='bx-im-message-quote__wrap'>
							${userContainer}
							<div class='bx-im-message-quote__text'>${preparedQuoteText}</div>
						</div>
					</div>
				`;

				return layout.outerHTML;
			},
		);

		return ParserRecursionPrevention.recoverTags(decodedText);
	},

	purifyQuote(text: string, spaceLetter: string = ' '): string
	{
		return text.replace(/-{54}(.*?)-{54}/gims, ParserIcon.getQuoteBlock() + spaceLetter);
	},

	decodeCode(text: string): string
	{
		return text.replace(/\[code](<br \/>)?([\0-\uFFFF]*?)\[\/code](<br \/>)?/gis, (whole, br, code) => {
			return Dom.create({
				tag: 'div',
				attrs: {className: 'bx-im-message-content-code'},
				html: code
			}).outerHTML;
		});
	},

	purifyCode(text: string, spaceLetter: string = ' '): string
	{
		return text.replace(/\[code](<br \/>)?([\0-\uFFFF]*?)\[\/code]/gis, ParserIcon.getCodeBlock() + spaceLetter);
	},

	executeClickEvent(event: PointerEvent)
	{
		if (
			!event.target.className.startsWith('bx-im-message-quote')
			&& !(
				event.target.parentNode
				&& event.target.parentNode.className.startsWith('bx-im-message-quote')
			)
		)
		{
			return;
		}

		const target = getUtils().dom.recursiveBackwardNodeSearch(event.target, 'bx-im-message-quote');
		if (!target || target.dataset.context === NO_CONTEXT_TAG)
		{
			return;
		}

		const [dialogId, messageId] = target.dataset.context.split('/');
		EventEmitter.emit(EventType.dialog.goToMessageContext, {
			messageId: Number.parseInt(messageId, 10),
			dialogId: dialogId.toString(),
		});
	},
};

const getQuoteText = (userName, timeTag, text): string => {
	const hasUserBlock = userName && timeTag;
	if (!hasUserBlock && !text)
	{
		// the case, when inside the quote we have only some string in square brackets
		return String(timeTag);
	}

	const BR_HTML_TAG = '<br />';

	if (text.endsWith(BR_HTML_TAG))
	{
		return text.slice(0, -BR_HTML_TAG.length);
	}

	return text;
};

const getUserBlock = (userName: string, timeTag: string): HTMLElement | '' => {
	const hasDataForUserBlock = userName && timeTag;
	if (!hasDataForUserBlock)
	{
		return '';
	}

	return Tag.render`
		<div class='bx-im-message-quote__name'>
			<div class="bx-im-message-quote__name-text">${userName.trim()}</div>
			<div class="bx-im-message-quote__name-time">${timeTag.trim()}</div>
		</div>
	`;
};

const getFinalContextTag = (contextTag: string, contextDialogId: string): string => {
	if (!contextTag)
	{
		return NO_CONTEXT_TAG;
	}

	const tagWithoutHashSign = contextTag.trim().slice(1);
	const finalContextTag = ParserUtils.getFinalContextTag(tagWithoutHashSign);
	if (!isQuoteFromTheSameChat(finalContextTag, contextDialogId))
	{
		return NO_CONTEXT_TAG;
	}

	return finalContextTag;
};

const isQuoteFromTheSameChat = (finalContextTag: string, dialogId: string): boolean => {
	const contextDialogId = ParserUtils.getDialogIdFromFinalContextTag(finalContextTag);

	return contextDialogId === dialogId;
};
