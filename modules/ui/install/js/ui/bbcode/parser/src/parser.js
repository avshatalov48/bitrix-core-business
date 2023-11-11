import { Type } from 'main.core';
import { RootNode } from './bbom/root-node';
import { Node, type SpecialCharNode, type ContentNode } from './bbom/node';
import { ElementNode } from './bbom/element-node';
import { TextNode } from './bbom/text-node';
import { NewLineNode } from './bbom/new-line-node';
import { FragmentNode } from './bbom/fragment-node';
import { TabNode } from './bbom/tab-node';
import { TAB, NEW_LINE, SPECIAL_CHARS, LIST_ELEMENTS, LIST_ITEM_ELEMENTS } from './reference';

const TAG_REGEX: RegExp = /\[(\/)?(\w+|\*)([\s\w./:=]+)?]/gs;

class Parser
{
	static toLowerCase(value: string): string
	{
		if (Type.isStringFilled(value))
		{
			return value.toLowerCase();
		}

		return value;
	}

	parseText(text: string): Array<TextNode | SpecialCharNode>
	{
		if (Type.isStringFilled(text))
		{
			return [...text]
				.reduce((acc: Array<TextNode | SpecialCharNode>, symbol: string) => {
					if (SPECIAL_CHARS.has(symbol))
					{
						acc.push(symbol);
					}
					else
					{
						const lastItem: string = acc.at(-1);
						if (SPECIAL_CHARS.has(lastItem) || Type.isNil(lastItem))
						{
							acc.push(symbol);
						}
						else
						{
							acc[acc.length - 1] += symbol;
						}
					}

					return acc;
				}, [])
				.map((fragment: string) => {
					if (fragment === NEW_LINE)
					{
						return new NewLineNode();
					}

					if (fragment === TAB)
					{
						return new TabNode();
					}

					return new TextNode(fragment);
				});
		}

		return [];
	}

	static findNextTagIndex(bbcode: string, startIndex = 0): number
	{
		const nextContent: string = bbcode.slice(startIndex);
		const [nextTag: ?string] = nextContent.match(new RegExp(TAG_REGEX)) || [];
		if (nextTag)
		{
			return bbcode.indexOf(nextTag, startIndex);
		}

		return -1;
	}

	parseAttributes(sourceAttributes: string): { value: ?string, attributes: Array<[string, string]> }
	{
		const result: {value: string, attributes: Array<Array<string, string>>} = { value: '', attributes: [] };

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

					const [key: string, value: string = ''] = item.split('=');
					acc.attributes.push([Parser.toLowerCase(key), value]);

					return acc;
				}, result);
		}

		return result;
	}

	parse(bbcode: string): RootNode
	{
		const result: RootNode = new RootNode();
		const stack: Array<ElementNode> = [];
		let current: ?ElementNode = null;
		let level: number = -1;

		const firstTagIndex: number = Parser.findNextTagIndex(bbcode);
		if (firstTagIndex !== 0)
		{
			const textBeforeFirstTag: string = firstTagIndex === -1 ? bbcode : bbcode.slice(0, firstTagIndex);
			result.appendChild(
				...this.parseText(textBeforeFirstTag),
			);
		}

		bbcode.replace(TAG_REGEX, (fullTag: string, slash: ?string, tagName: string, attrs: ?string, index: number) => {
			const isOpenTag: boolean = Boolean(slash) === false;
			const startIndex: number = fullTag.length + index;
			const nextContent: string = bbcode.slice(startIndex);
			const attributes = this.parseAttributes(attrs);
			const lowerCaseTagName: string = Parser.toLowerCase(tagName);
			let parent: ?(RootNode | ElementNode) = null;

			if (isOpenTag)
			{
				level++;

				if (
					nextContent.includes(`[/${tagName}]`)
					|| LIST_ITEM_ELEMENTS.has(lowerCaseTagName)
				)
				{
					current = new ElementNode({
						name: lowerCaseTagName,
						value: attributes.value,
						attributes: Object.fromEntries(attributes.attributes),
					});

					const nextTagIndex: number = Parser.findNextTagIndex(bbcode, startIndex);
					if (nextTagIndex !== 0)
					{
						const content: string = nextTagIndex === -1 ? nextContent : bbcode.slice(startIndex, nextTagIndex);
						current.appendChild(
							...this.parseText(content),
						);
					}
				}
				else
				{
					current = new ElementNode({
						name: lowerCaseTagName,
						value: attributes.value,
						attributes: Object.fromEntries(attributes.attributes),
						void: true,
					});
				}

				if (level === 0)
				{
					result.appendChild(current);
				}

				parent = stack[level - 1];

				if (LIST_ELEMENTS.has(current.getName()))
				{
					if (parent && LIST_ELEMENTS.has(parent.getName()))
					{
						stack[level].appendChild(current);
					}
				}
				else if (
					parent
					&& LIST_ELEMENTS.has(parent.getName())
					&& !LIST_ITEM_ELEMENTS.has(current.getName())
				)
				{
					const lastItem: ?ContentNode = parent.getChildren().at(-1);
					if (lastItem)
					{
						lastItem.appendChild(current);
					}
				}
				else if (parent)
				{
					parent.appendChild(current);
				}

				stack[level] = current;

				if (LIST_ITEM_ELEMENTS.has(lowerCaseTagName) && level > -1)
				{
					level--;
					current = level === -1 ? result : stack[level];
				}
			}

			if (!isOpenTag || current.isVoid())
			{
				if (level > -1 && current.getName() === lowerCaseTagName)
				{
					level--;
					current = level === -1 ? result : stack[level];
				}

				const nextTagIndex: number = Parser.findNextTagIndex(bbcode, startIndex);
				if (nextTagIndex !== startIndex)
				{
					parent = level === -1 ? result : stack[level];

					const content: ?string = bbcode.slice(startIndex, nextTagIndex === -1 ? undefined : nextTagIndex);
					if (LIST_ELEMENTS.has(parent.getName()))
					{
						const lastItem: ?ContentNode = parent.getChildren().at(-1);
						if (lastItem)
						{
							lastItem.appendChild(
								...this.parseText(content),
							);
						}
					}
					else
					{
						parent.appendChild(
							...this.parseText(content),
						);
					}
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
	ElementNode,
	TextNode,
	NewLineNode,
	FragmentNode,
	TabNode,
};
