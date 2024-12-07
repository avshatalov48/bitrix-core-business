import type { TextNode, RangeSelection } from 'ui.lexical.core';

export const NON_SINGLE_WIDTH_CHARS_REPLACEMENT: Readonly<Record<string, string>> = (
	Object.freeze({
		'\t': '\\t',
		'\n': '\\n',
	})
);

export const NON_SINGLE_WIDTH_CHARS_REGEX: RegExp = new RegExp(
	Object.keys(NON_SINGLE_WIDTH_CHARS_REPLACEMENT).join('|'),
	'g',
);

export const SYMBOLS: Record<string, string> = Object.freeze({
	ancestorHasNextSibling: '|',
	ancestorIsLastChild: ' ',
	hasNextSibling: '├',
	isLastChild: '└',
	selectedChar: '^',
	selectedLine: '>',
});

export const FORMAT_PREDICATES = [
	(node: TextNode | RangeSelection) => node.hasFormat('bold') && 'Bold',
	(node: TextNode | RangeSelection) => node.hasFormat('code') && 'Code',
	(node: TextNode | RangeSelection) => node.hasFormat('italic') && 'Italic',
	(node: TextNode | RangeSelection) => node.hasFormat('strikethrough') && 'Strikethrough',
	(node: TextNode | RangeSelection) => node.hasFormat('subscript') && 'Subscript',
	(node: TextNode | RangeSelection) => node.hasFormat('superscript') && 'Superscript',
	(node: TextNode | RangeSelection) => node.hasFormat('underline') && 'Underline',
];

export const DETAIL_PREDICATES = [
	(node: TextNode) => node.isDirectionless() && 'Directionless',
	(node: TextNode) => node.isUnmergeable() && 'Unmergeable',
];

export const MODE_PREDICATES = [
	(node: TextNode) => node.isToken() && 'Token',
	(node: TextNode) => node.isSegmented() && 'Segmented',
];
