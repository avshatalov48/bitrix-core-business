import { nameSymbol } from './node';
import { TextNode, contentSymbol, type TextNodeContent, type TextNodeOptions } from './text-node';
import { Text } from '../reference/text';

export class NewLineNode extends TextNode
{
	[nameSymbol]: string = Text.NEW_LINE_NAME;
	[contentSymbol]: string = Text.NEW_LINE_CONTENT;

	constructor(options: TextNodeOptions = {})
	{
		super(options);
	}

	setContent(options: TextNodeContent)
	{}
}
