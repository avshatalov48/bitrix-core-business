import { Type } from 'main.core';
import { getByIndex } from '../../shared';
import {
	BBCodeScheme,
	DefaultBBCodeScheme,
	BBCodeNode,
	typeof BBCodeRootNode,
	typeof BBCodeElementNode,
	typeof BBCodeTextNode,
	typeof BBCodeTagScheme,
	type BBCodeContentNode,
	type BBCodeSpecialCharNode,
} from 'ui.bbcode.model';
import { ParserScheme } from './parser-scheme';

const TAG_REGEX: RegExp = /\[(\/)?(\w+|\*)(.*?)]/gs;
const isSpecialChar = (symbol: string): boolean => {
	return ['\n', '\t'].includes(symbol);
};

const isList = (tagName: string): boolean => {
	return ['list', 'ul', 'ol'].includes(String(tagName).toLowerCase());
};

const isListItem = (tagName: string): boolean => {
	return ['*', 'li'].includes(String(tagName).toLowerCase());
};

const parserScheme = new ParserScheme();

type BBCodeParserOptions = {
	scheme?: BBCodeScheme,
	onUnknown?: (node: BBCodeContentNode, scheme: BBCodeScheme) => void,
};

class BBCodeParser
{
	scheme: BBCodeScheme;
	onUnknownHandler: () => any;

	constructor(options: BBCodeParserOptions = {})
	{
		if (options.scheme)
		{
			this.setScheme(options.scheme);
		}
		else
		{
			this.setScheme(new DefaultBBCodeScheme());
		}

		if (Type.isFunction(options.onUnknown))
		{
			this.setOnUnknown(options.onUnknown);
		}
		else
		{
			this.setOnUnknown(BBCodeParser.defaultOnUnknownHandler);
		}
	}

	setScheme(scheme: BBCodeScheme)
	{
		this.scheme = scheme;
	}

	getScheme(): BBCodeScheme
	{
		return this.scheme;
	}

	setOnUnknown(handler: () => any)
	{
		if (!Type.isFunction(handler))
		{
			throw new TypeError('handler is not a function');
		}

		this.onUnknownHandler = handler;
	}

	getOnUnknownHandler(): () => any
	{
		return this.onUnknownHandler;
	}

	static defaultOnUnknownHandler(node: BBCodeContentNode, scheme: BBCodeScheme): ?Array<BBCodeContentNode>
	{
		if (node.getType() === BBCodeNode.ELEMENT_NODE)
		{
			const nodeName: string = node.getName();
			if (['left', 'center', 'right', 'justify'].includes(nodeName))
			{
				node.replace(
					scheme.createElement({
						name: 'p',
						children: node.getChildren(),
					}),
				);
			}
			else if (['background', 'color', 'size'].includes(nodeName))
			{
				node.replace(
					scheme.createElement({
						name: 'b',
						children: node.getChildren(),
					}),
				);
			}
			else
			{
				const openingTag: string = node.getOpeningTag();
				const closingTag: string = node.getClosingTag();

				node.replace(
					scheme.createText(openingTag),
					...node.getChildren(),
					scheme.createText(closingTag),
				);
			}
		}
	}

	static toLowerCase(value: string): string
	{
		if (Type.isStringFilled(value))
		{
			return value.toLowerCase();
		}

		return value;
	}

	parseText(text: string): Array<BBCodeTextNode | BBCodeSpecialCharNode>
	{
		if (Type.isStringFilled(text))
		{
			return [...text]
				.reduce((acc: Array<BBCodeTextNode | BBCodeSpecialCharNode>, symbol: string) => {
					if (isSpecialChar(symbol))
					{
						acc.push(symbol);
					}
					else
					{
						const lastItem: string = getByIndex(acc, -1);
						if (isSpecialChar(lastItem) || Type.isNil(lastItem))
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
					if (fragment === '\n')
					{
						return parserScheme.createNewLine();
					}

					if (fragment === '\t')
					{
						return parserScheme.createTab();
					}

					return parserScheme.createText({ content: fragment });
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

	static trimQuotes(value: string): string
	{
		const source = String(value);
		if ((/^["'].*["']$/g).test(source))
		{
			return source.slice(1, -1);
		}

		return value;
	}

	parseAttributes(sourceAttributes: string): { value: ?string, attributes: Array<[string, string]> }
	{
		const result: {value: string, attributes: Array<Array<string, string>>} = { value: '', attributes: [] };

		if (Type.isStringFilled(sourceAttributes))
		{
			if (sourceAttributes.startsWith('='))
			{
				result.value = BBCodeParser.trimQuotes(
					sourceAttributes.slice(1),
				);

				return result;
			}

			return sourceAttributes
				.trim()
				.split(' ')
				.filter(Boolean)
				.reduce((acc: typeof result, item: string) => {
					const [key: string, value: string = ''] = item.split('=');
					acc.attributes.push([
						BBCodeParser.toLowerCase(key),
						BBCodeParser.trimQuotes(value),
					]);

					return acc;
				}, result);
		}

		return result;
	}

	parse(bbcode: string): BBCodeRootNode
	{
		const result: BBCodeRootNode = parserScheme.createRoot();

		const firstTagIndex: number = BBCodeParser.findNextTagIndex(bbcode);
		if (firstTagIndex !== 0)
		{
			const textBeforeFirstTag: string = firstTagIndex === -1 ? bbcode : bbcode.slice(0, firstTagIndex);
			result.appendChild(
				...this.parseText(textBeforeFirstTag),
			);
		}

		const stack: Array<BBCodeElementNode> = [result];
		const wasOpened: Array<string> = [];
		let current: ?BBCodeElementNode = null;
		let level: number = 0;

		bbcode.replace(TAG_REGEX, (fullTag: string, slash: ?string, tagName: string, attrs: ?string, index: number) => {
			const isOpeningTag: boolean = Boolean(slash) === false;
			const startIndex: number = fullTag.length + index;
			const nextContent: string = bbcode.slice(startIndex);
			const attributes = this.parseAttributes(attrs);
			const lowerCaseTagName: string = BBCodeParser.toLowerCase(tagName);
			let parent: ?(BBCodeRootNode | BBCodeElementNode) = stack[level];

			if (isOpeningTag)
			{
				const isPotentiallyVoid: boolean = !nextContent.includes(`[/${tagName}]`);
				if (
					isPotentiallyVoid
					&& !isListItem(lowerCaseTagName)
				)
				{
					const tagScheme: BBCodeTagScheme = this.getScheme().getTagScheme(lowerCaseTagName);
					const isAllowedVoidTag: boolean = tagScheme && tagScheme.isVoid();
					if (isAllowedVoidTag)
					{
						current = parserScheme.createElement({
							name: lowerCaseTagName,
							value: attributes.value,
							attributes: Object.fromEntries(attributes.attributes),
						});

						current.setScheme(this.getScheme());
						parent.appendChild(current);
					}
					else
					{
						parent.appendChild(
							parserScheme.createText(fullTag),
						);
					}

					const nextTagIndex: number = BBCodeParser.findNextTagIndex(bbcode, startIndex);
					if (nextTagIndex !== 0)
					{
						const content: string = nextTagIndex === -1 ? nextContent : bbcode.slice(startIndex, nextTagIndex);
						parent.appendChild(
							...this.parseText(content),
						);
					}
				}
				else
				{
					if (isListItem(lowerCaseTagName) && current && isListItem(current.getName()))
					{
						level--;
						parent = stack[level];
					}

					current = parserScheme.createElement({
						name: lowerCaseTagName,
						value: attributes.value,
						attributes: Object.fromEntries(attributes.attributes),
					});

					const nextTagIndex: number = BBCodeParser.findNextTagIndex(bbcode, startIndex);
					if (nextTagIndex !== 0)
					{
						const content: string = nextTagIndex === -1 ? nextContent : bbcode.slice(startIndex, nextTagIndex);
						current.appendChild(
							...this.parseText(content),
						);
					}

					parent.appendChild(current);

					level++;
					stack[level] = current;
					wasOpened.push(lowerCaseTagName);
				}
			}
			else
			{
				if (wasOpened.includes(lowerCaseTagName))
				{
					level--;
					const openedTagIndex: number = wasOpened.indexOf(lowerCaseTagName);
					wasOpened.splice(openedTagIndex, 1);
				}
				else
				{
					stack[level].appendChild(
						parserScheme.createText(fullTag),
					);
				}

				if (isList(lowerCaseTagName) && level > 0)
				{
					level--;
				}

				const nextTagIndex: number = BBCodeParser.findNextTagIndex(bbcode, startIndex);
				if (nextTagIndex !== 0 && stack[level])
				{
					const content: string = nextTagIndex === -1 ? nextContent : bbcode.slice(startIndex, nextTagIndex);
					stack[level].appendChild(
						...this.parseText(content),
					);
				}

				if (isListItem(stack[level].getName()) && level > 0)
				{
					level--;
				}
			}
		});

		const getFinalLineBreaksIndexes = (node: BBCodeContentNode) => {
			let skip = false;

			return node
				.getChildren()
				.reduceRight((acc: Array<BBCodeContentNode>, child: BBCodeContentNode, index: number) => {
					if (!skip && child.getName() === '#linebreak')
					{
						acc.push(index);
					}
					else if (!skip && child.getName() !== '#tab')
					{
						skip = true;
					}

					return acc;
				}, []);
		};

		BBCodeNode.flattenAst(result).forEach((node: BBCodeContentNode) => {
			if (node.getName() === '*')
			{
				const finalLinebreaksIndexes: Array<number> = getFinalLineBreaksIndexes(node);
				if (finalLinebreaksIndexes.length === 1)
				{
					node.setChildren(
						node.getChildren().slice(0, getByIndex(finalLinebreaksIndexes, 0)),
					);
				}

				if (finalLinebreaksIndexes.length > 1 && (finalLinebreaksIndexes & 2) === 0)
				{
					node.setChildren(
						node.getChildren().slice(0, getByIndex(finalLinebreaksIndexes, 0)),
					);
				}
			}
		});

		result.setScheme(
			this.getScheme(),
			this.getOnUnknownHandler(),
		);

		return result;
	}
}

export {
	BBCodeParser,
};
