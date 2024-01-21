import { type ContentNode } from '../nodes/node';

export class Text
{
	static TAB_CONTENT: string = '\t';
	static NEW_LINE_CONTENT: string = '\n';

	static SPECIAL_CHARS_CONTENT: Set<string> = new Set([
		Text.TAB_CONTENT,
		Text.NEW_LINE_CONTENT,
	]);

	static TEXT_NAME: string = '#text';
	static NEW_LINE_NAME: string = '#linebreak';
	static TAB_NAME: string = '#tab';

	static TEXT_NAMES: Set<string> = new Set([
		Text.TEXT_NAME,
		Text.NEW_LINE_NAME,
		Text.TAB_NAME,
	]);

	static isAnyTextNode(node: ContentNode): boolean
	{
		return node && Text.TEXT_NAMES.has(node.getName());
	}

	static isPlainTextNode(node: ContentNode): boolean
	{
		return node && node.getName() === Text.TEXT_NAME;
	}

	static isNewLineNode(node: ContentNode): boolean
	{
		return node && node.getName() === Text.NEW_LINE_NAME;
	}

	static isTabNode(node: ContentNode): boolean
	{
		return node && node.getName() === Text.TAB_NAME;
	}

	static isNewLineContent(content: string): boolean
	{
		return content === Text.NEW_LINE_CONTENT;
	}

	static isTabContent(content: string): boolean
	{
		return content === Text.TAB_CONTENT;
	}

	static isSpecialCharContent(content: string): boolean
	{
		return Text.SPECIAL_CHARS_CONTENT.has(content);
	}
}
