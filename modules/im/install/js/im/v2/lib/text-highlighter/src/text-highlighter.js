import { Utils } from 'im.v2.lib.utils';

export const highlightText = (text: string, textToHighlight: string = '') => {
	if (textToHighlight.length === 0)
	{
		return text;
	}

	const wordsToHighlight = getWordsToHighlight(textToHighlight);
	const pattern = createRegExPatternFromWords(wordsToHighlight);

	return text.replaceAll(new RegExp(pattern, 'ig'), wrapWithSpan);
};

const wrapWithSpan = (text: string): string => `<span class="--highlight">${text}</span>`;

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
