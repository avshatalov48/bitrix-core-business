import { Type } from 'main.core';

type TextNodeOptions = {
	content?: string | number,
	parent?: any,
};

export class TextNode
{
	content: string = '';
	parent: any = null;

	constructor(options: TextNodeOptions = {})
	{
		this.setContent(options.content);
		this.setParent(options.parent);
	}

	setContent(content: string | number)
	{
		if (Type.isString(content) || Type.isNumber(content))
		{
			this.content = content;
		}
	}

	getContent(): string | number
	{
		return this.content;
	}

	setParent(node: any)
	{
		this.parent = node;
	}

	getParent(): any
	{
		return this.parent;
	}

	toString(): string
	{
		return this.getContent();
	}
}
