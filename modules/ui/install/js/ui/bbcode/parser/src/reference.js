import { Node, type ContentNode } from './bbom/node';
import { TextNode } from './bbom/text-node';

export const TAB: string = '\t';
export const NEW_LINE: string = '\n';
export const SPECIAL_CHARS: Set<string> = new Set([TAB, NEW_LINE]);

export const BOLD: string = 'b';
export const ITALIC: string = 'i';
export const STRIKE: string = 's';
export const UNDERLINE: string = 'u';
export const SIZE: string = 'size';
export const COLOR: string = 'color';
export const CENTER: string = 'center';
export const LEFT: string = 'left';
export const RIGHT: string = 'right';
export const URL: string = 'url';
export const IMG: string = 'img';
export const PARAGRAPH: string = 'p';
export const LIST: string = 'list';
export const LIST_UL: string = 'ul';
export const LIST_OL: string = 'ol';
export const LIST_ITEM: string = '*';
export const LIST_ITEM_LI: string = 'li';
export const TABLE: string = 'table';
export const TABLE_ROW: string = 'tr';
export const TABLE_CELL: string = 'td';
export const TABLE_HEAD_CELL: string = 'th';
export const QUOTE: string = 'quote';
export const CODE: string = 'code';
export const SPOILER: string = 'spoiler';

export const INLINE_ELEMENTS: Set<string> = new Set(
	[BOLD, ITALIC, STRIKE, UNDERLINE, SIZE, COLOR, CENTER, LEFT, RIGHT, URL, IMG, LIST_ITEM, LIST_ITEM_LI],
);

export const BLOCK_ELEMENTS: Set<string> = new Set(
	[PARAGRAPH, LIST, LIST_UL, LIST_OL, TABLE, QUOTE, CODE, SPOILER],
);

export const LIST_ELEMENTS: Set<string> = new Set([LIST, LIST_UL, LIST_OL]);
export const LIST_ITEM_ELEMENTS: Set<string> = new Set([LIST_ITEM, LIST_ITEM_LI]);

export class NodeType
{
	static isAnyText(node: ContentNode): boolean
	{
		return node.getType() === Node.TEXT_NODE;
	}

	static isText(node: ContentNode): boolean
	{
		return node && NodeType.isAnyText(node) && !SPECIAL_CHARS.has(node.getContent());
	}

	static isNewLine(node: ContentNode): boolean
	{
		return node && NodeType.isAnyText(node) && node.getContent() === NEW_LINE;
	}

	static isTab(node: ContentNode): boolean
	{
		return node && NodeType.isAnyText(node) && node.getContent() === TAB;
	}

	static isElement(node: ContentNode): boolean
	{
		return node && node.getType() === Node.ELEMENT_NODE;
	}

	static isList(node: ContentNode): boolean
	{
		return node && NodeType.isElement(node) && LIST_ELEMENTS.has(node.getName());
	}

	static isListItem(node: ContentNode): boolean
	{
		return node && NodeType.isElement(node) && LIST_ITEM_ELEMENTS.has(node.getName());
	}

	static isInline(node: ContentNode): boolean
	{
		return node && NodeType.isElement(node) && INLINE_ELEMENTS.has(node.getName());
	}

	static isTableCell(node: ContentNode): boolean
	{
		return node && NodeType.isElement(node) && [TABLE_CELL, TABLE_HEAD_CELL].includes(node.getName());
	}

	static isTable(node: ContentNode): boolean
	{
		return node && NodeType.isElement(node) && node.getName() === TABLE;
	}

	static isTableRow(node: ContentNode): boolean
	{
		return node && NodeType.isElement(node) && node.getName() === TABLE_ROW;
	}
}

const listChildFilter = (node: ContentNode) => {
	return NodeType.isListItem(node);
};

const ulOlListChildFilter = (node: ContentNode) => {
	return NodeType.isElement(node) && node.getName() === LIST_ITEM_LI;
};

const listItemChildFilter = (node: ContentNode) => {
	return (
		(NodeType.isAnyText(node) && !NodeType.isTab(node))
		|| (NodeType.isInline(node) && !NodeType.isListItem(node))
		|| NodeType.isList(node)
	);
};

const tableChildFilter = (node: ContentNode) => {
	return NodeType.isTableRow(node);
};

const tableRowChildFilter = (node: ContentNode) => {
	return NodeType.isTableCell(node);
};

const tableCellChildFilter = (node: ContentNode) => {
	return (
		NodeType.isText(node)
		|| NodeType.isNewLine(node)
		|| (
			NodeType.isInline(node)
			&& !NodeType.isListItem(node)
		)
	);
};

const inlineChildFilter = (node: ContentNode) => {
	return (
		(
			NodeType.isAnyText(node)
			&& !NodeType.isTab(node)
		)
		|| (
			NodeType.isInline(node)
			&& !NodeType.isListItem(node)
		)
	);
};

export const childFiltersMap: Map<string, (node: ContentNode) => boolean> = new Map();
childFiltersMap.set(LIST, listChildFilter);
childFiltersMap.set(LIST_ITEM, listItemChildFilter);
childFiltersMap.set(LIST_ITEM_LI, listItemChildFilter);
childFiltersMap.set(LIST_OL, ulOlListChildFilter);
childFiltersMap.set(LIST_UL, ulOlListChildFilter);
childFiltersMap.set(TABLE, tableChildFilter);
childFiltersMap.set(TABLE_ROW, tableRowChildFilter);
childFiltersMap.set(TABLE_CELL, tableCellChildFilter);
childFiltersMap.set(TABLE_HEAD_CELL, tableCellChildFilter);
childFiltersMap.set('#inline', inlineChildFilter);

export const childConvertersMap: Map<string, (node: ContentNode) => Node> = new Map();
childConvertersMap.set(
	CODE,
	(node: ContentNode): ContentNode => {
		if (node.getType() === Node.TEXT_NODE)
		{
			return node;
		}

		return new TextNode(node.toString());
	},
);
