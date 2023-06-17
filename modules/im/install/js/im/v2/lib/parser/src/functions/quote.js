import {Dom, Tag} from 'main.core';
import {EventEmitter} from 'main.core.events';

import {ParserUtils} from '../utils/utils';
import {getUtils, getConst} from '../utils/core-proxy';
import {ParserIcon} from './icon';

const {EventType} = getConst();

const QUOTE_SIGN = '&gt;&gt;';

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

			const outerContainerStart = '<div class="bx-im-message-quote --inline">';
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

	decodeQuote(text): string
	{
		text = text.replace(
			/-{54}(<br \/>(.*?)\[(.*?)]( #(?:chat\d+|\d+:\d+)\/\d+)?)?<br \/>(.*?)-{54}(<br \/>)?/gs,
			(whole, userBlock, userName, timeTag, contextTag, text): string => {
				const skipUserBlock = !userName || !timeTag;
				if (skipUserBlock && !text) // greedy date detector :(
				{
					text = `${timeTag}`;
				}

				let userContainer = '';
				if (!skipUserBlock)
				{
					userContainer = Tag.render`
						<div class='bx-im-message-quote__name'>
							<div class="bx-im-message-quote__name-text">${userName}</div>
							<div class="bx-im-message-quote__name-time">${timeTag}</div>
						</div>
					`;
				}

				let quoteBaseClass = 'bx-im-message-quote';
				if (contextTag)
				{
					contextTag = contextTag.trim().slice(1);
					contextTag = ParserUtils.getFinalContextTag(contextTag);
				}
				if (contextTag)
				{
					quoteBaseClass += ' --with-context';
				}
				else
				{
					contextTag = 'none';
				}

				const layout = Tag.render`
					<div class='${quoteBaseClass}' data-context='${contextTag}'>
						<div class='bx-im-message-quote__wrap'>
							${userContainer}
							<div class='bx-im-message-quote__text'>${text}</div>
						</div>
					</div>
				`;

				return layout.outerHTML;
			}
		);

		return text;
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

		const target = getUtils().dom.recursiveBackwardNodeSearch(event.target, '--with-context');
		if (!target || target.dataset.context === 'none')
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