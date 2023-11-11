import { Dom, Text } from 'main.core';
import { getConst, getUtils } from '../utils/core-proxy';

const { MessageMentionType } = getConst();
export const ParserCall = {

	decode(text): string
	{
		let result = text;

		result = result.replaceAll(/\[call(?:=([\d #()+./-]+))?](.+?)\[\/call]/gi, (whole, number, text) => {
			if (!text)
			{
				return whole;
			}

			let destination = '';

			if (number)
			{
				destination = number;
			}
			else if (getUtils.call.isNumber(text))
			{
				destination = text;
			}
			else
			{
				return whole;
			}

			return Dom.create({
				tag: 'span',
				attrs: {
					className: 'bx-im-mention',
					'data-type': MessageMentionType.call,
					'data-destination': destination,
				},
				text: Text.decode(text),
			}).outerHTML;
		});

		result = result.replaceAll(/\[pch=(\d+)](.*?)\[\/pch]/gi, (whole, historyId, text) => '');

		return result;
	},

	purify(text): string
	{
		let result = text;

		result = result.replaceAll(
			/\[call(?:=([\d #()+./-]+))?](.+?)\[\/call]/gi,
			(whole, number, text) => {
				return text || number;
			},
		);

		result = result.replaceAll(
			/\[pch=(\d+)](.*?)\[\/pch]/gi,
			(whole, historyId, text) => text,
		);

		return result;
	},
};
