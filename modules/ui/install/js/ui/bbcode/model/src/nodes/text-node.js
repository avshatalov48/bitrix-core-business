import { Type } from 'main.core';
import { BBCodeNode, privateMap, nameSymbol, type BBCodeNodeOptions } from './node';

export const contentSymbol = Symbol('content');

export type BBCodeTextNodeContent = string | number;

export type BBCodeTextNodeOptions = BBCodeTextNodeContent | BBCodeNodeOptions & {
	content?: string,
};

export type BBCodeSerializedTextNode = {
	name: string,
	content: BBCodeTextNodeContent,
};

export class BBCodeTextNode extends BBCodeNode
{
	[nameSymbol]: string = '#text';
	[contentSymbol]: string = '';

	constructor(options: BBCodeTextNodeOptions = {})
	{
		const nodeOptions: BBCodeTextNodeOptions = Type.isString(options) ? { content: options } : options;
		super(nodeOptions);
		privateMap.get(this).type = BBCodeNode.TEXT_NODE;
		this.setContent(nodeOptions.content);
		BBCodeNode.makeNonEnumerableProperty(this, 'children');
	}

	static isTextNodeContent(value: any): boolean
	{
		return Type.isString(value) || Type.isNumber(value);
	}

	static decodeSpecialChars(content: BBCodeTextNodeContent): string
	{
		return String(content)
			.replaceAll('&#91;', '[')
			.replaceAll('&#93;', ']');
	}

	setName(name: string)
	{}

	setContent(content: BBCodeTextNodeContent)
	{
		if (BBCodeTextNode.isTextNodeContent(content))
		{
			this[contentSymbol] = BBCodeTextNode.decodeSpecialChars(content);
		}
	}

	getContent(): BBCodeTextNodeContent
	{
		return BBCodeTextNode.decodeSpecialChars(this[contentSymbol]);
	}

	adjustChildren()
	{}

	getLength(): number
	{
		return String(this[contentSymbol]).length;
	}

	isEmpty(): boolean
	{
		return this.getLength() === 0;
	}

	clone(options): BBCodeTextNode
	{
		return this.getScheme().createText({
			content: this.getContent(),
		});
	}

	split(options: { offset: number, byWord?: boolean}): Array<BBCodeTextNode | null>
	{
		const { offset: sourceOffset, byWord = false } = options;

		if (!Type.isNumber(sourceOffset))
		{
			throw new TypeError('offset is not a number');
		}

		const contentLength = this.getLength();
		if (sourceOffset < 0 || sourceOffset > contentLength)
		{
			throw new TypeError(`offset '${sourceOffset}' is out of range ${0}-${contentLength}`);
		}

		const content = this.getContent();

		const offset = (() => {
			if (byWord && sourceOffset !== contentLength)
			{
				const lastIndex = content.lastIndexOf(' ', sourceOffset);
				if (lastIndex !== -1)
				{
					if (sourceOffset > lastIndex)
					{
						return lastIndex + 1;
					}

					return lastIndex;
				}

				return 0;
			}

			return sourceOffset;
		})();

		const leftNode = (() => {
			if (offset === contentLength)
			{
				return this;
			}

			if (offset === 0)
			{
				return null;
			}

			const node = this.clone();
			node.setContent(content.slice(0, offset));

			return node;
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

			const node = this.clone();
			node.setContent(content.slice(offset, contentLength));

			return node;
		})();

		return [leftNode, rightNode];
	}

	toString(): string
	{
		return this.getContent();
	}

	toPlainText(): string
	{
		return this.toString();
	}

	toJSON(): BBCodeSerializedTextNode
	{
		return {
			name: this.getName(),
			content: this.toString(),
		};
	}
}
