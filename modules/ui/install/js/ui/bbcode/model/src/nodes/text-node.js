import { Type } from 'main.core';
import { Node, privateMap, nameSymbol, type NodeOptions } from './node';
import { Text } from '../reference/text';

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
	[nameSymbol]: string = Text.TEXT_NAME;
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

	getLength():number
	{
		return String(this[contentSymbol]).length;
	}

	clone(options): TextNode
	{
		const Constructor = this.constructor;

		return new Constructor({
			content: this.getContent(),
			scheme: this.getScheme(),
		});
	}

	splitText(offset: number): Array<TextNode | null>
	{
		if (!Type.isNumber(offset))
		{
			throw new TypeError('offset is not a number');
		}

		const contentLength = this.getLength();
		if (offset < 0 || offset > contentLength)
		{
			throw new TypeError(`offset '${offset}' is out of range ${0}-${contentLength}`);
		}

		const content = this.getContent();
		const rightContent = content.slice(offset, contentLength);

		const leftNode = (() => {
			if (offset === contentLength)
			{
				return this;
			}

			if (offset === 0)
			{
				return null;
			}

			return new TextNode({
				content: content.slice(0, offset),
				scheme: this.getScheme(),
			});
		})();

		const rightNode = (() => {
			if (offset === 0)
			{
				return this;
			}

			if (offset === contentLength)
			{
				return null;
			}

			return new TextNode({
				content: rightContent,
				scheme: this.getScheme(),
			});
		})();

		if (leftNode && rightNode)
		{
			this.replace(leftNode, rightNode);
		}

		return [leftNode, rightNode];
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
