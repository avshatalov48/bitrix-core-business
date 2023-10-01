import { Type } from 'main.core';
import { RootNode } from './bbom/root-node';
import { Node } from './bbom/node';
import { VoidNode } from './bbom/void-node';
import { TextNode } from './bbom/text-node';
import { NewLineNode } from './bbom/new-line-node';

type AllowedCases = 'initial' | 'lowerCase' | 'upperCase';
type ParserOptions = {
	tagNameCase: AllowedCases,
	attributeNameCase: AllowedCases,
};

const TAG_REGEX = /\[(\/)?(\w+|\*)([\s\w./:=]+)?]/gs;

class Parser
{
	constructor(options: ParserOptions = {})
	{
		this.options = {
			tagNameCase: 'lowerCase',
			attributeNameCase: 'lowerCase',
			...options,
		};
	}

	static prepareCase(value: string, resultCase: AllowedCases): string
	{
		if (Type.isStringFilled(value))
		{
			if (resultCase === 'lowerCase')
			{
				return value.toLowerCase();
			}

			if (resultCase === 'upperCase')
			{
				return value.toUpperCase();
			}
		}

		return value;
	}

	prepareTagNameCase(name: string): string
	{
		return Parser.prepareCase(name, this.options.tagNameCase);
	}

	prepareAttributeNameCase(name: string): string
	{
		return Parser.prepareCase(name, this.options.attributeNameCase);
	}

	parseText(text: string, parent = null): Array<TextNode>
	{
		if (Type.isStringFilled(text))
		{
			const fragments: Array<string> = (() => {
				const result = text.split('\n');
				if (/^\n+$/g.test(text))
				{
					return result.slice(1);
				}

				return result;
			})();

			return fragments.map((fragment: string) => {
				if (Type.isStringFilled(fragment))
				{
					return new TextNode({ content: fragment, parent });
				}

				return new NewLineNode({ content: '\n', parent });
			});
		}

		return [];
	}

	static findNextTagIndex(bbcode: string, startIndex = 0): number
	{
		const nextContent = bbcode.slice(startIndex);
		const [nextTag] = nextContent.match(new RegExp(TAG_REGEX)) || [];
		if (nextTag)
		{
			return bbcode.indexOf(nextTag, startIndex);
		}

		return -1;
	}

	parseAttributes(sourceAttributes: string): { value: ?string, attributes: Array<[string, string]> }
	{
		const result = { value: '', attributes: [] };

		if (Type.isStringFilled(sourceAttributes))
		{
			return sourceAttributes
				.trim()
				.split(' ')
				.filter(Boolean)
				.reduce((acc: typeof result, item: string) => {
					if (item.startsWith('='))
					{
						acc.value = item.slice(1);

						return acc;
					}

					const [key, value = ''] = item.split('=');
					acc.attributes.push([this.prepareAttributeNameCase(key), value]);

					return acc;
				}, result);
		}

		return result;
	}

	isListTag(tagName: string): boolean
	{
		return ['list', 'ul', 'ol'].includes(tagName);
	}

	isListItemTag(tagName: string): boolean
	{
		return ['*', 'li'].includes(tagName);
	}

	// eslint-disable-next-line sonarjs/cognitive-complexity
	parse(bbcode: string): RootNode
	{
		const result = new RootNode();
		const stack = [];
		let level = -1;
		let current = null;

		const firstTagIndex = Parser.findNextTagIndex(bbcode);
		if (firstTagIndex !== 0)
		{
			const textBeforeFirstTag = firstTagIndex === -1 ? bbcode : bbcode.slice(0, firstTagIndex);
			// eslint-disable-next-line @bitrix24/bitrix24-rules/no-native-dom-methods
			result.appendChild(
				...this.parseText(textBeforeFirstTag),
			);
		}

		bbcode.replace(TAG_REGEX, (fullTag: string, slash: ?string, tagName: string, attrs: ?string, index: number) => {
			const isOpenTag = Boolean(slash) === false;
			const startIndex = fullTag.length + index;
			const nextContent = bbcode.slice(startIndex);
			const attributes = this.parseAttributes(attrs);
			const caseSensitivityTagName = this.prepareTagNameCase(tagName);
			let parent = null;

			if (isOpenTag)
			{
				level++;

				if (
					nextContent.includes(`[/${tagName}]`)
					|| this.isListItemTag(caseSensitivityTagName)
				)
				{
					current = new Node({
						name: caseSensitivityTagName,
						value: attributes.value,
						attributes: Object.fromEntries(attributes.attributes),
					});

					const nextTagIndex = Parser.findNextTagIndex(bbcode, startIndex);
					if (nextTagIndex !== 0)
					{
						const content = nextTagIndex === -1 ? nextContent : bbcode.slice(startIndex, nextTagIndex);
						// eslint-disable-next-line @bitrix24/bitrix24-rules/no-native-dom-methods
						current.appendChild(
							...this.parseText(content, current),
						);
					}
				}
				else
				{
					current = new VoidNode({
						name: caseSensitivityTagName,
						value: attributes.value,
						attributes: Object.fromEntries(attributes.attributes),
					});
				}

				if (level === 0)
				{
					// eslint-disable-next-line @bitrix24/bitrix24-rules/no-native-dom-methods
					result.appendChild(current);
				}

				parent = stack[level - 1];

				if (this.isListTag(current.getName()))
				{
					if (parent && this.isListTag(parent.getName()))
					{
						current.setParent(stack[level]);
						// eslint-disable-next-line @bitrix24/bitrix24-rules/no-native-dom-methods
						stack[level].appendChild(current);
					}
				}
				else if (parent)
				{
					current.setParent(parent);
					// eslint-disable-next-line @bitrix24/bitrix24-rules/no-native-dom-methods
					parent.appendChild(current);
				}

				stack[level] = current;

				if (this.isListItemTag(caseSensitivityTagName) && level > -1)
				{
					level--;
					current = level === -1 ? result : stack[level];
				}
			}

			if (!isOpenTag || current instanceof VoidNode)
			{
				if (level > -1 && current.getName() === caseSensitivityTagName)
				{
					level--;
					current = level === -1 ? result : stack[level];
				}

				const nextTagIndex = Parser.findNextTagIndex(bbcode, startIndex);
				if (nextTagIndex !== startIndex)
				{
					parent = level === -1 ? result : stack[level];

					const content = bbcode.slice(startIndex, nextTagIndex);
					// eslint-disable-next-line @bitrix24/bitrix24-rules/no-native-dom-methods
					parent.appendChild(
						...this.parseText(content, current),
					);
				}
			}
		});

		return result;
	}
}

export {
	Parser,
	RootNode,
	Node,
	TextNode,
	NewLineNode,
	VoidNode,
};
