import { nameSymbol } from './node';
import { TextNode, contentSymbol, type TextNodeContent, type TextNodeOptions } from './text-node';
import { NEW_LINE } from '../reference';

export class NewLineNode extends TextNode
{
	[nameSymbol]: string = '#linebreak';
	[contentSymbol]: string = NEW_LINE;

	constructor(options: TextNodeOptions = {})
	{
		super(options);
	}

	setContent(options: TextNodeContent)
	{}
}
