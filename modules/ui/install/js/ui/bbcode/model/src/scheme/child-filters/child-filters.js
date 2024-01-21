import { Tag } from '../../reference/tag';
import { Text } from '../../reference/text';
import { type ContentNode } from '../../nodes/node';

export const childFilters = {
	[Tag.LIST]: (node: ContentNode) => {
		return node.getName() === Tag.LIST_ITEM;
	},
	[Tag.LIST_OL]: (node: ContentNode) => {
		return node.getName() === Tag.LIST_ITEM_LI;
	},
	[Tag.LIST_UL]: (node: ContentNode) => {
		return node.getName() === Tag.LIST_ITEM_LI;
	},
	[Tag.LIST_ITEM]: (node: ContentNode) => {
		return (
			node
			&& (
				Tag.isList(node.getName())
				|| Text.isPlainTextNode(node)
				|| Text.isNewLineNode(node)
				|| (
					Tag.isInline(node.getName()) && !Tag.isListItem(node.getName())
				)
			)
		);
	},
	[Tag.LIST_ITEM_LI]: (node: ContentNode) => {
		return (
			Tag.isListItem(node.getName())
			|| Text.isPlainTextNode(node)
			|| Text.isNewLineNode(node)
			|| (
				node.isInline() && !Tag.isListItem(node.getName())
			)
		);
	},
	[Tag.TABLE]: (node: ContentNode) => {
		return node.getName() === Tag.TABLE_ROW;
	},
	[Tag.TABLE_ROW]: (node: ContentNode) => {
		return node.getName() === Tag.TABLE_CELL || node.getName() === Tag.TABLE_HEAD_CELL;
	},
	[Tag.TABLE_CELL]: (node: ContentNode) => {
		return (
			Tag.isInline(node.getName())
			|| Text.isPlainTextNode(node)
			|| Text.isNewLineNode(node)
		);
	},
	[Tag.TABLE_HEAD_CELL]: (node: ContentNode) => {
		return (
			Tag.isInline(node.getName())
			|| Text.isPlainTextNode(node)
			|| Text.isNewLineNode(node)
		);
	},
	[Tag.PARAGRAPH]: (node: ContentNode) => {
		return (
			Tag.isInline(node.getName())
			|| Text.isPlainTextNode(node)
			|| Text.isNewLineNode(node)
		);
	},
	'#inline': (node: ContentNode) => {
		return (
			Tag.isInline(node.getName())
			|| Text.isPlainTextNode(node)
			|| Text.isNewLineNode(node)
		);
	},
};
