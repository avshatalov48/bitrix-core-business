import { Type } from 'main.core';
import { BBCodeEncoder } from 'ui.bbcode.encoder';
import { BBCodeTagScheme } from './node-schemes/tag-scheme';
import { BBCodeNode, type BBCodeNodeOptions } from '../nodes/node';
import { BBCodeRootNode, type RootNodeOptions } from '../nodes/root-node';
import { BBCodeFragmentNode, type FragmentNodeOptions } from '../nodes/fragment-node';
import { BBCodeElementNode, type BBCodeElementNodeOptions } from '../nodes/element-node';
import { BBCodeTextNode, type BBCodeTextNodeOptions } from '../nodes/text-node';
import { BBCodeNewLineNode } from '../nodes/new-line-node';
import { BBCodeTabNode } from '../nodes/tab-node';
import { BBCodeNodeScheme } from './node-schemes/node-scheme';
import type { BBCodeGroupName, BBCodeNodeName } from './node-schemes/node-scheme';

export type OutputTagCases = $Values<BBCodeScheme.Case>;

export type ParentChildMap = Map<
	BBCodeNodeName | BBCodeGroupName,
	{
		allowedChildren: Array<BBCodeNodeName | BBCodeGroupName>,
		allowedIn: Array<BBCodeNodeName | BBCodeGroupName>,
		aliases: Array<BBCodeNodeName | BBCodeGroupName>,
	},
>;

export type BBCodeSchemeOptions = {
	tagSchemes: Array<BBCodeTagScheme>,
	outputTagCase?: OutputTagCases,
	unresolvedNodesHoisting?: boolean,
	encoder?: BBCodeEncoder,
};

export class BBCodeScheme
{
	static Case: {[key: string]: string} = {
		LOWER: 'lower',
		UPPER: 'upper',
	};

	tagSchemes: Array<BBCodeTagScheme> = [];
	outputTagCase: OutputTagCases = BBCodeScheme.Case.LOWER;
	unresolvedNodesHoisting: boolean = true;
	encoder: BBCodeEncoder = new BBCodeEncoder();
	parentChildMap: ?ParentChildMap = null;

	static isNodeScheme(value: any): boolean
	{
		return value instanceof BBCodeNodeScheme;
	}

	static getTagName(node: string | BBCodeNode): string | null
	{
		if (Type.isString(node))
		{
			return node;
		}

		if (Type.isObject(node) && node instanceof BBCodeNode)
		{
			return node.getName();
		}

		return null;
	}

	constructor(options: BBCodeSchemeOptions = {})
	{
		if (!Type.isPlainObject(options))
		{
			throw new TypeError('options is not a object');
		}

		this.onTagSchemeChange = this.onTagSchemeChange.bind(this);

		this.setTagSchemes(options.tagSchemes);
		this.setOutputTagCase(options.outputTagCase);
		this.setUnresolvedNodesHoisting(options.unresolvedNodesHoisting);
		this.setEncoder(options.encoder);
	}

	onTagSchemeChange()
	{
		this.parentChildMap = null;
	}

	setTagSchemes(tagSchemes: Array<BBCodeTagScheme>)
	{
		if (Type.isArray(tagSchemes))
		{
			const invalidSchemeIndex: number = tagSchemes.findIndex((scheme: BBCodeTagScheme): boolean => {
				return !BBCodeScheme.isNodeScheme(scheme);
			});

			if (invalidSchemeIndex > -1)
			{
				throw new TypeError(`tagScheme #${invalidSchemeIndex} is not TagScheme instance`);
			}

			tagSchemes.forEach((tagScheme: BBCodeTagScheme) => {
				tagScheme.setOnChangeHandler(this.onTagSchemeChange);
			});

			this.tagSchemes = [...tagSchemes];
		}
	}

	setTagScheme(...tagSchemes: Array<BBCodeTagScheme>)
	{
		const invalidSchemeIndex: number = tagSchemes.findIndex((scheme: BBCodeTagScheme): boolean => {
			return !BBCodeScheme.isNodeScheme(scheme);
		});

		if (invalidSchemeIndex > -1)
		{
			throw new TypeError(`tagScheme #${invalidSchemeIndex} is not TagScheme instance`);
		}

		const newTagSchemesNames: Array<string> = tagSchemes.flatMap((scheme: BBCodeTagScheme) => {
			return scheme.getName();
		});

		const currentTagSchemes: Array<BBCodeTagScheme> = this.getTagSchemes();
		currentTagSchemes.forEach((scheme: BBCodeTagScheme) => {
			scheme.removeName(...newTagSchemesNames);
		});

		const filteredCurrentTagSchemes: Array<BBCodeTagScheme> = currentTagSchemes.filter((scheme: BBCodeTagScheme) => {
			return Type.isArrayFilled(scheme.getName());
		});

		this.setTagSchemes([
			...filteredCurrentTagSchemes,
			...tagSchemes,
		]);
	}

	getTagSchemes(): Array<BBCodeTagScheme>
	{
		return [...this.tagSchemes];
	}

	getTagScheme(node: string | BBCodeNode): ?BBCodeTagScheme
	{
		const tagName: ?string = BBCodeScheme.getTagName(node);
		if (Type.isString(tagName))
		{
			return this.getTagSchemes().find((scheme: BBCodeTagScheme): boolean => {
				return scheme.getName().includes(tagName.toLowerCase());
			});
		}

		return null;
	}

	setOutputTagCase(tagCase: $Values<BBCodeScheme.Case>)
	{
		if (!Type.isNil(tagCase))
		{
			const allowedCases = Object.values(BBCodeScheme.Case);
			if (allowedCases.includes(tagCase))
			{
				this.outputTagCase = tagCase;
			}
			else
			{
				throw new TypeError(`'${tagCase}' is not allowed`);
			}
		}
	}

	getOutputTagCase(): $Values<BBCodeScheme.Case>
	{
		return this.outputTagCase;
	}

	setUnresolvedNodesHoisting(value: boolean)
	{
		if (!Type.isNil(value))
		{
			if (Type.isBoolean(value))
			{
				this.unresolvedNodesHoisting = value;
			}
			else
			{
				throw new TypeError(`'${value}' is not allowed value`);
			}
		}
	}

	isAllowedUnresolvedNodesHoisting(): boolean
	{
		return this.unresolvedNodesHoisting;
	}

	setEncoder(encoder: BBCodeEncoder)
	{
		if (encoder instanceof BBCodeEncoder)
		{
			this.encoder = encoder;
		}
	}

	getEncoder(): BBCodeEncoder
	{
		return this.encoder;
	}

	getAllowedTags(): Array<string>
	{
		return this.getTagSchemes().flatMap((tagScheme: BBCodeTagScheme) => {
			return tagScheme.getName();
		});
	}

	isAllowedTag(node: string | BBCodeNode): boolean
	{
		const allowedTags: Array<string> = this.getAllowedTags();
		const tagName: ?string = BBCodeScheme.getTagName(node);

		return allowedTags.includes(String(tagName).toLowerCase());
	}

	isVoid(node: string | BBCodeNode): boolean
	{
		const tagScheme: ?BBCodeTagScheme = this.getTagScheme(node);
		if (tagScheme)
		{
			return tagScheme.isVoid();
		}

		return false;
	}

	isElement(node: BBCodeNode): boolean
	{
		return node && node.getType() === BBCodeNode.ELEMENT_NODE;
	}

	isRoot(node: BBCodeNode): boolean
	{
		return node && node.getName() === '#root';
	}

	isFragment(node: BBCodeNode): boolean
	{
		return node && node.getName() === '#fragment';
	}

	isAnyText(node: BBCodeNode): boolean
	{
		return node && node.getType() === BBCodeNode.TEXT_NODE;
	}

	isText(node: BBCodeNode): boolean
	{
		return node && node.getName() === '#text';
	}

	isNewLine(node: BBCodeNode): boolean
	{
		return node && node.getName() === '#linebreak';
	}

	isTab(node: BBCodeNode): boolean
	{
		return node && node.getName() === '#tab';
	}

	getParentChildMap(): ParentChildMap
	{
		if (Type.isNull(this.parentChildMap))
		{
			const tagSchemes: Array<BBCodeTagScheme> = this.getTagSchemes();
			const map = new Map();

			tagSchemes.forEach((tagScheme: BBCodeTagScheme) => {
				const groups: Array<BBCodeGroupName> = tagScheme.getGroup();
				const schemeNames: Array<string> = [
					...tagScheme.getName(),
					...groups,
					...(tagScheme.isVoid() ? ['#void'] : []),
				];

				const allowedChildren = tagScheme.getAllowedChildren();
				const allowedIn = tagScheme.getAllowedIn();

				schemeNames.forEach((name) => {
					if (!map.has(name))
					{
						map.set(
							name,
							{
								allowedChildren: new Set(),
								allowedIn: new Set(),
								aliases: new Set(),
							},
						);
					}

					const entry: {
						allowedChildren: Set,
						allowedIn: Set,
						aliases: Set,
					} = map.get(name);

					const newEntry = {
						allowedChildren: new Set([...entry.allowedChildren, ...allowedChildren]),
						allowedIn: new Set([...entry.allowedIn, ...allowedIn]),
						aliases: new Set([name, ...groups, ...(tagScheme.isVoid() ? ['#void'] : [])]),
					};

					map.set(name, newEntry);
				});
			});

			this.parentChildMap = map;
		}

		return this.parentChildMap;
	}

	isChildAllowed(parent: string | BBCodeNode, child: string | BBCodeNode): boolean
	{
		const parentName: ?string = BBCodeScheme.getTagName(parent);
		const childName: ?string = BBCodeScheme.getTagName(child);

		if (
			Type.isStringFilled(parentName)
			&& Type.isStringFilled(childName)
		)
		{
			if (parentName === '#fragment')
			{
				return true;
			}

			const parentChildMap = this.getParentChildMap();
			const parentMap = parentChildMap.get(parentName);
			const childMap = parentChildMap.get(childName);

			if (
				Type.isPlainObject(parentMap)
				&& Type.isPlainObject(childMap)
			)
			{
				return (
					(
						parentMap.allowedChildren.size === 0
						|| [...childMap.aliases].some((name) => {
							return parentMap.allowedChildren.has(name);
						})
					)
					&& (
						childMap.allowedIn.size === 0
						|| [...parentMap.aliases].some((name) => {
							return childMap.allowedIn.has(name);
						})
					)
				);
			}
		}

		return false;
	}

	createRoot(options: RootNodeOptions = {}): BBCodeRootNode
	{
		return new BBCodeRootNode({
			...options,
			scheme: this,
		});
	}

	createNode(options: BBCodeNodeOptions): BBCodeNode
	{
		if (!Type.isPlainObject(options))
		{
			throw new TypeError('options is not a object');
		}

		if (!Type.isStringFilled(options.name))
		{
			throw new TypeError('options.name is required');
		}

		if (!this.isAllowedTag(options.name))
		{
			throw new TypeError(`Scheme for "${options.name}" tag is not specified.`);
		}

		return new BBCodeNode({
			...options,
			scheme: this,
		});
	}

	createElement(options: BBCodeElementNodeOptions = {}): BBCodeElementNode
	{
		if (!Type.isPlainObject(options))
		{
			throw new TypeError('options is not a object');
		}

		if (!Type.isStringFilled(options.name))
		{
			throw new TypeError('options.name is required');
		}

		if (!this.isAllowedTag(options.name))
		{
			throw new TypeError(`Scheme for "${options.name}" tag is not specified.`);
		}

		return new BBCodeElementNode({
			...options,
			scheme: this,
		});
	}

	createText(options: BBCodeTextNodeOptions = {}): BBCodeTextNode
	{
		const preparedOptions = Type.isPlainObject(options) ? options : { content: options };

		return new BBCodeTextNode({
			...preparedOptions,
			scheme: this,
		});
	}

	createNewLine(options: BBCodeTextNodeOptions = {}): BBCodeNewLineNode
	{
		const preparedOptions = Type.isPlainObject(options) ? options : { content: options };

		return new BBCodeNewLineNode({
			...preparedOptions,
			scheme: this,
		});
	}

	createTab(options: BBCodeTextNodeOptions = {}): BBCodeTabNode
	{
		const preparedOptions = Type.isPlainObject(options) ? options : { content: options };

		return new BBCodeTabNode({
			...preparedOptions,
			scheme: this,
		});
	}

	createFragment(options: FragmentNodeOptions = {}): BBCodeFragmentNode
	{
		return new BBCodeFragmentNode({
			...options,
			scheme: this,
		});
	}
}
