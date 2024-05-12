import { nameSymbol } from './node';
import { BBCodeTextNode, contentSymbol, type BBCodeTextNodeContent, type BBCodeTextNodeOptions } from './text-node';

export class BBCodeNewLineNode extends BBCodeTextNode
{
	[nameSymbol]: string = '#linebreak';
	[contentSymbol]: string = '\n';

	constructor(options: BBCodeTextNodeOptions = {})
	{
		super(options);
	}

	setContent(options: BBCodeTextNodeContent)
	{}

	clone(options): BBCodeNewLineNode
	{
		return this.getScheme().createNewLine();
	}
}
