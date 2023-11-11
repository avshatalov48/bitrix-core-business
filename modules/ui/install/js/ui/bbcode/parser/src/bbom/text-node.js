import { Type } from 'main.core';
import { Node, privateMap, nameSymbol, type NodeOptions } from './node';

export const contentSymbol = Symbol('content');

export type TextNodeContent = string | number;

export type TextNodeOptions = TextNodeContent | NodeOptions & {
	content?: string,
};

export type SerializedTextNode = {
	name: string,
	content: TextNodeContent,
};

export class TextNode extends Node
{
	[nameSymbol]: string = '#text';
	[contentSymbol]: string = '';

	constructor(options: TextNodeOptions = {})
	{
		const nodeOptions: TextNodeOptions = Type.isString(options) ? { content: options } : options;
		super(nodeOptions);
		privateMap.get(this).type = Node.TEXT_NODE;
		this.setContent(nodeOptions.content);
		Node.makeNonEnumerableProperty(this, 'children');
	}

	static isTextNodeContent(value: any): boolean
	{
		return Type.isString(value) || Type.isNumber(value);
	}

	static decodeSpecialChars(content: TextNodeContent): TextNodeContent
	{
		if (TextNode.isTextNodeContent(content))
		{
			return content
				.replaceAll('&#91;', '[')
				.replaceAll('&#93;', ']');
		}

		return content;
	}

	setName(name: string)
	{}

	setContent(content: TextNodeContent)
	{
		if (TextNode.isTextNodeContent(content))
		{
			this[contentSymbol] = TextNode.decodeSpecialChars(content);
		}
	}

	getContent(): TextNodeContent
	{
		return TextNode.decodeSpecialChars(this[contentSymbol]);
	}

	toString(): string
	{
		return this.getContent();
	}

	toJSON(): SerializedTextNode
	{
		return {
			name: this.getName(),
			content: this.toString(),
		};
	}
}
