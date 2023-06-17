import {Dom, Text} from 'main.core';

export const ParserCall = {

	decode(text): string
	{
		text = text.replace(/\[CALL(?:=([-+\d()./# ]+))?](.+?)\[\/CALL]/gi, (whole, number, text) => {

			if (!text)
			{
				return whole;
			}

			if (!number)
			{
				if (text.match(/^([-+\d()./# ]+)$/))
				{
					number = text;
				}
				else
				{
					return whole;
				}
			}

			text = Text.decode(text);

			return Dom.create({
				tag: 'span',
				attrs: {
					className: 'bx-im-mention',
					'data-type': 'CALL',
					'data-value': number,
				},
				text
			}).outerHTML;
		});

		text = text.replace(
			/\[PCH=([0-9]+)](.*?)\[\/PCH]/gi, (whole, historyId, text) => text
		);

		return text;
	},

	purify(text): string
	{
		text = text.replace(
			/\[CALL(?:=([-+\d()./# ]+))?](.+?)\[\/CALL]/gi,
			(whole, number, text) => text? text: number
		);

		text = text.replace(
			/\[PCH=([0-9]+)](.*?)\[\/PCH]/gi,
			(whole, historyId, text) => text
		);

		return text;
	}
}

