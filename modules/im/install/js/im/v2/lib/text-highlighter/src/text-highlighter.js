import { Text } from 'main.core';

import { Utils } from 'im.v2.lib.utils';

export const highlightText = (text: string, textToHighlight: string = '') => {
	if (textToHighlight.length === 0)
	{
		return text;
	}

	const wordsToHighlight = getWordsToHighlight(textToHighlight);
	const pattern = createRegExPatternFromWords(wordsToHighlight);

	return wrapWithSpan(text, pattern);
};

const wrapWithSpan = (text: string, pattern: string): string => {
	const decodedText = Text.decode(text);

	const textWithPlaceholders = decodedText.replaceAll(new RegExp(pattern, 'ig'), wrapWithSpanPlaceholder);

	return replacePlaceholders(textWithPlaceholders);
};

const wrapWithSpanPlaceholder = (text: string): string => `#SPAN_START#${text}#SPAN_END#`;

const replacePlaceholders = (textWithPlaceholders: string): string => {
	const encodedText = Text.encode(textWithPlaceholders);

	return encodedText.replaceAll('#SPAN_START#', '<span class="--highlight">').replaceAll('#SPAN_END#', '</span>');
};

const getWordsToHighlight = (textToHighlight: string): string[] => {
	const wordsToHighlight = Utils.text.getWordsFromString(textToHighlight);
	const result = new Set(wordsToHighlight);

	return [...result];
};

const createRegExPatternFromWords = (words: string[]): string => {
	return words.map((word) => {
		return BX.util.escapeRegExp(word);
	}).join('|');
};
