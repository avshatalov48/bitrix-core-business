import { nameSymbol } from './node';
import { BBCodeTextNode, contentSymbol, type BBCodeTextNodeContent, type BBCodeTextNodeOptions } from './text-node';

export class BBCodeTabNode extends BBCodeTextNode
{
	[nameSymbol]: string = '#tab';
	[contentSymbol]: string = '\t';

	constructor(options: BBCodeTextNodeOptions = {})
	{
		super(options);
	}

	setContent(options: BBCodeTextNodeContent)
	{}

	clone(options): BBCodeTabNode
	{
		return this.getScheme().createTab();
	}
}
