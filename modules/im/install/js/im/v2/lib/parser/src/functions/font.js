import {Dom} from 'main.core';

import {ParserUtils} from '../utils/utils';

export const ParserFont = {

	decode(text): string
	{
		text = ParserUtils.recursiveReplace(text, /\[b]([^[]*(?:\[(?!b]|\/b])[^[]*)*)\[\/b]/gi, (whole, text) => '<b>'+text+'</b>');
		text = ParserUtils.recursiveReplace(text, /\[u]([^[]*(?:\[(?!u]|\/u])[^[]*)*)\[\/u]/gi, (whole, text) => '<u>'+text+'</u>');
		text = ParserUtils.recursiveReplace(text, /\[i]([^[]*(?:\[(?!i]|\/i])[^[]*)*)\[\/i]/gi, (whole, text) => '<i>'+text+'</i>');
		text = ParserUtils.recursiveReplace(text, /\[s]([^[]*(?:\[(?!s]|\/s])[^[]*)*)\[\/s]/gi, (whole, text) => '<s>'+text+'</s>');

		text = ParserUtils.recursiveReplace(text, /\[size=(\d+)(?:pt|px)?](.*?)\[\/size]/gis, (whole, number, text) => {
			number = Number.parseInt(number, 10);
			if (number <= 8)
			{
				number = 8;
			}
			else if (number >= 30)
			{
				number = 30;
			}

			return Dom.create({
				tag: 'span',
				style: {fontSize: `${number}px`},
				html: text
			}).outerHTML;
		});

		text = ParserUtils.recursiveReplace(text, /\[color=#([0-9a-f]{3}|[0-9a-f]{6})](.*?)\[\/color]/gis, (whole, hex, text) => {
			return Dom.create({
				tag: 'span',
				style: { color: '#'+ hex },
				html: text
			}).outerHTML;
		});

		return text;
	},

	purify(text, removeStrike = true): string
	{
		if (removeStrike)
		{
			text = ParserUtils.recursiveReplace(text, /\[s]([^[]*(?:\[(?!s]|\/s])[^[]*)*)\[\/s]/gi, () => ' ');
		}

		text = ParserUtils.recursiveReplace(text, /\[b]([^[]*(?:\[(?!b]|\/b])[^[]*)*)\[\/b]/gi, (whole, text) => text);
		text = ParserUtils.recursiveReplace(text, /\[u]([^[]*(?:\[(?!u]|\/u])[^[]*)*)\[\/u]/gi, (whole, text) => text);
		text = ParserUtils.recursiveReplace(text, /\[i]([^[]*(?:\[(?!i]|\/i])[^[]*)*)\[\/i]/gi, (whole, text) => text);
		text = ParserUtils.recursiveReplace(text, /\[s]([^[]*(?:\[(?!s]|\/s])[^[]*)*)\[\/s]/gi, (whole, text) => text);
		text = ParserUtils.recursiveReplace(text, /\[size=(\d+)(?:pt|px)?](.*?)\[\/size]/gis, (whole, number, text) => text);
		text = ParserUtils.recursiveReplace(text, /\[color=#([0-9a-f]{3}|[0-9a-f]{6})](.*?)\[\/color]/gis, (whole, hex, text) => text);

		return text;
	}
}

