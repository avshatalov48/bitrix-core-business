import { nameSymbol } from './node';
import { TextNode, contentSymbol, type TextNodeContent, type TextNodeOptions } from './text-node';
import { Text } from '../reference/text';

export class TabNode extends TextNode
{
	[nameSymbol]: string = Text.TAB_NAME;
	[contentSymbol]: string = Text.TAB_CONTENT;

	constructor(options: TextNodeOptions = {})
	{
		super(options);
	}

	setContent(options: TextNodeContent)
	{}
}
