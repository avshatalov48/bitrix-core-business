export { ResizeManager } from './classes/resize-manager';

type InsertTextConfig = {
	text: string,
	withNewLine?: boolean,
	replace?: boolean
};

type InsertMentionConfig = {
	textToInsert: string,
	textToReplace?: string
};

const TAB = '\t';
const NEW_LINE = '\n';
const LETTER_CODE_PREFIX = 'Key';

/* eslint-disable no-param-reassign */
export const Textarea = {
	addTab(textarea: HTMLTextAreaElement): string
	{
		const newSelectionPosition = textarea.selectionStart + 1;

		const textBefore = textarea.value.slice(0, textarea.selectionStart);
		const textAfter = textarea.value.slice(textarea.selectionEnd);
		const textWithTab = `${textBefore}${TAB}${textAfter}`;

		textarea.value = textWithTab;
		textarea.selectionStart = newSelectionPosition;
		textarea.selectionEnd = newSelectionPosition;

		return textWithTab;
	},
	removeTab(textarea: HTMLTextAreaElement): string
	{
		const previousSymbol = textarea.value.slice(textarea.selectionStart - 1, textarea.selectionStart);
		if (previousSymbol !== TAB)
		{
			return textarea.value;
		}

		const newSelectionPosition = textarea.selectionStart - 1;

		const textBefore = textarea.value.slice(0, textarea.selectionStart - 1);
		const textAfter = textarea.value.slice(textarea.selectionEnd);
		const textWithoutTab = `${textBefore}${textAfter}`;

		textarea.value = textWithoutTab;
		textarea.selectionStart = newSelectionPosition;
		textarea.selectionEnd = newSelectionPosition;

		return textWithoutTab;
	},
	handleDecorationTag(textarea: HTMLTextAreaElement, decorationKey: 'KeyB' | 'KeyI' | 'KeyU' | 'KeyS'): string
	{
		decorationKey = decorationKey.replace(LETTER_CODE_PREFIX, '').toLowerCase();
		const LEFT_TAG = `[${decorationKey}]`;
		const RIGHT_TAG = `[/${decorationKey}]`;

		const selectedText = textarea.value.slice(textarea.selectionStart, textarea.selectionEnd);
		if (!selectedText)
		{
			return textarea.value;
		}

		const hasDecorationTag = (
			selectedText.toLowerCase().startsWith(LEFT_TAG)
			&& selectedText.toLowerCase().endsWith(RIGHT_TAG)
		);
		if (hasDecorationTag)
		{
			return this.removeDecorationTag(textarea, decorationKey);
		}
		else
		{
			return this.addDecorationTag(textarea, decorationKey);
		}
	},
	addDecorationTag(textarea: HTMLTextAreaElement, decorationKey: 'b' | 'i' | 'u' | 's'): string
	{
		const LEFT_TAG = `[${decorationKey}]`;
		const RIGHT_TAG = `[/${decorationKey}]`;

		const decorationTagLength = LEFT_TAG.length + RIGHT_TAG.length;
		const newSelectionStart = textarea.selectionStart;
		const newSelectionEnd = textarea.selectionEnd + decorationTagLength;

		const textBefore = textarea.value.slice(0, textarea.selectionStart);
		const selectedText = textarea.value.slice(textarea.selectionStart, textarea.selectionEnd);
		const textAfter = textarea.value.slice(textarea.selectionEnd);
		const textWithTag = `${textBefore}${LEFT_TAG}${selectedText}${RIGHT_TAG}${textAfter}`;

		textarea.value = textWithTag;
		textarea.selectionStart = newSelectionStart;
		textarea.selectionEnd = newSelectionEnd;

		return textWithTag;
	},
	removeDecorationTag(textarea: HTMLTextAreaElement, decorationKey: 'b' | 'i' | 'u' | 's'): string
	{
		const LEFT_TAG = `[${decorationKey}]`;
		const RIGHT_TAG = `[/${decorationKey}]`;

		const decorationTagLength = LEFT_TAG.length + RIGHT_TAG.length;
		const newSelectionStart = textarea.selectionStart;
		const newSelectionEnd = textarea.selectionEnd - decorationTagLength;

		const textBefore = textarea.value.slice(0, textarea.selectionStart);

		const textInTagStart = textarea.selectionStart + LEFT_TAG.length;
		const textInTagEnd = textarea.selectionEnd - RIGHT_TAG.length;
		const textInTag = textarea.value.slice(textInTagStart, textInTagEnd);

		const textAfter = textarea.value.slice(textarea.selectionEnd);
		const textWithoutTag = `${textBefore}${textInTag}${textAfter}`;

		textarea.value = textWithoutTag;
		textarea.selectionStart = newSelectionStart;
		textarea.selectionEnd = newSelectionEnd;

		return textWithoutTag;
	},
	addNewLine(textarea: HTMLTextAreaElement): string
	{
		const newSelectionPosition = textarea.selectionStart + 1;

		const textBefore = textarea.value.slice(0, textarea.selectionStart);
		const textAfter = textarea.value.slice(textarea.selectionEnd);
		const textWithNewLine = `${textBefore}${NEW_LINE}${textAfter}`;

		textarea.value = textWithNewLine;
		textarea.selectionStart = newSelectionPosition;
		textarea.selectionEnd = newSelectionPosition;

		return textWithNewLine;
	},
	insertText(textarea: HTMLTextAreaElement, config: InsertTextConfig = {}): string
	{
		const { text, withNewLine = false, replace = false } = config;
		const newSelectionPosition: number = textarea.selectionStart + text.length + 1;
		let resultText = '';

		if (replace)
		{
			resultText = '';
			textarea.value = '';
			textarea.selectionStart = 0;
			textarea.selectionEnd = 0;
		}

		if (textarea.value.length === 0)
		{
			resultText = text;
		}
		else
		{
			const textBefore = textarea.value.slice(0, textarea.selectionStart);
			const textAfter = textarea.value.slice(textarea.selectionEnd);
			resultText = withNewLine ? `${textarea.value}${NEW_LINE}${text}` : `${textBefore} ${text} ${textAfter}`;
		}

		textarea.focus({ preventScroll: true });
		textarea.value = resultText;
		textarea.selectionStart = newSelectionPosition;
		textarea.selectionEnd = newSelectionPosition;

		return resultText;
	},
	insertMention(textarea: HTMLTextAreaElement, config: InsertMentionConfig = {}): string
	{
		const { textToInsert, textToReplace = '' } = config;
		const isMentionWithSymbol = textToReplace.length > 0;
		let resultText = '';
		let newSelectionPosition = textarea.selectionStart + textToInsert.length + 1;

		if (isMentionWithSymbol)
		{
			newSelectionPosition -= textToReplace.length;
			const textBefore = textarea.value.slice(0, textarea.selectionStart - textToReplace.length);
			const textAfter = textarea.value.slice(textarea.selectionStart);
			resultText = `${textBefore}${textToInsert} ${textAfter}`;
		}
		else
		{
			const textBefore = textarea.value.slice(0, textarea.selectionStart);
			const textAfter = textarea.value.slice(textarea.selectionEnd);

			resultText = `${textBefore}${textToInsert} ${textAfter}`;
		}

		textarea.focus({ preventScroll: true });
		textarea.value = resultText;
		textarea.selectionStart = newSelectionPosition;
		textarea.selectionEnd = newSelectionPosition;

		return resultText;
	},
};
