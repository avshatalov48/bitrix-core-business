import { nameSymbol } from './node';
import { TextNode, contentSymbol, type TextNodeContent, type TextNodeOptions } from './text-node';
import { TAB } from '../reference';

export class TabNode extends TextNode
{
	[nameSymbol]: string = '#tab';
	[contentSymbol]: string = TAB;

	constructor(options: TextNodeOptions = {})
	{
		super(options);
	}

	setContent(options: TextNodeContent)
	{}
}
