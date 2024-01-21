import { Type } from 'main.core';

const toLowerCase = (value: string): string => {
	if (Type.isString(value))
	{
		return value.toLowerCase();
	}

	return value;
};

export class Tag
{
	static BOLD: string = 'b';
	static ITALIC: string = 'i';
	static STRIKE: string = 's';
	static UNDERLINE: string = 'u';
	static SIZE: string = 'size';
	static COLOR: string = 'color';
	static CENTER: string = 'center';
	static LEFT: string = 'left';
	static RIGHT: string = 'right';
	static URL: string = 'url';
	static IMG: string = 'img';

	static PARAGRAPH: string = 'p';
	static LIST: string = 'list';
	static LIST_UL: string = 'ul';
	static LIST_OL: string = 'ol';
	static LIST_ITEM: string = '*';
	static LIST_ITEM_LI: string = 'li';
	static TABLE: string = 'table';
	static TABLE_ROW: string = 'tr';
	static TABLE_CELL: string = 'td';
	static TABLE_HEAD_CELL: string = 'th';
	static QUOTE: string = 'quote';
	static CODE: string = 'code';
	static SPOILER: string = 'spoiler';

	static INLINE_TAGS: Set<string> = new Set([
		Tag.BOLD,
		Tag.ITALIC,
		Tag.STRIKE,
		Tag.UNDERLINE,
		Tag.SIZE,
		Tag.COLOR,
		Tag.CENTER,
		Tag.LEFT,
		Tag.RIGHT,
		Tag.URL,
		Tag.IMG,
		Tag.LIST_ITEM,
		Tag.LIST_ITEM_LI,
	]);

	static BLOCK_TAGS: Set<string> = new Set([
		Tag.PARAGRAPH,
		Tag.LIST,
		Tag.LIST_UL,
		Tag.LIST_OL,
		Tag.TABLE,
		Tag.TABLE_ROW,
		Tag.TABLE_HEAD_CELL,
		Tag.TABLE_CELL,
		Tag.QUOTE,
		Tag.CODE,
		Tag.SPOILER,
	]);

	static LIST_TAGS: Set<string> = new Set([
		Tag.LIST,
		Tag.LIST_UL,
		Tag.LIST_OL,
	]);

	static LIST_ITEM_TAGS: Set<string> = new Set([
		Tag.LIST_ITEM,
		Tag.LIST_ITEM_LI,
	]);

	static isInline(tagName: string): boolean
	{
		return Tag.INLINE_TAGS.has(toLowerCase(tagName));
	}

	static isBlock(tagName: string): boolean
	{
		return Tag.BLOCK_TAGS.has(toLowerCase(tagName));
	}

	static isList(tagName: string): boolean
	{
		return Tag.LIST_TAGS.has(toLowerCase(tagName));
	}

	static isListItem(tagName: string): boolean
	{
		return Tag.LIST_ITEM_TAGS.has(toLowerCase(tagName));
	}
}
