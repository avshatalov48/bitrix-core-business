import { Type } from 'main.core';
import {
	BBCodeNodeScheme,
	type BBCodeNodeSchemeOptions,
	type BBCodeNodeConverter,
	type BBCodeNodeName,
} from './node-scheme';
import { typeof BBCodeElementNode } from '../../nodes/element-node';
import { BBCodeNode, type BBCodeContentNode } from '../../nodes/node';

export type BBCodeTagSchemeOptions = BBCodeNodeSchemeOptions & {
	void?: boolean,
	canBeEmpty?: boolean,
	convertChild?: BBCodeNodeConverter,
	allowedChildren?: Array<BBCodeNodeName>,
};

const canBeEmptySymbol = Symbol('@canBeEmpty');
const voidSymbol = Symbol('@void');

export class BBCodeTagScheme extends BBCodeNodeScheme
{
	[voidSymbol]: boolean = false;
	[canBeEmptySymbol]: boolean = true;
	childConverter: BBCodeNodeConverter | null = null;
	allowedChildren: Array<BBCodeNodeName> = [];

	constructor(options: BBCodeTagSchemeOptions)
	{
		super(options);
		this.setVoid(options.void);
		this.setCanBeEmpty(options.canBeEmpty);
		this.setChildConverter(options.convertChild);
		this.setAllowedChildren(options.allowedChildren);
	}

	static defaultBlockStringifier(node: BBCodeElementNode): string
	{
		const isAllowNewlineBeforeOpeningTag: boolean = (() => {
			const previewsSibling: ?BBCodeContentNode = node.getPreviewsSibling();

			return previewsSibling && previewsSibling.getName() !== '#linebreak';
		})();
		const isAllowNewlineAfterOpeningTag: boolean = (() => {
			const firstChild: ?BBCodeContentNode = node.getFirstChild();

			return firstChild && firstChild.getName() !== '#linebreak';
		})();
		const isAllowNewlineBeforeClosingTag: boolean = (() => {
			const lastChild: ?BBCodeContentNode = node.getLastChild();

			return lastChild && lastChild.getName() !== '#linebreak';
		})();
		const isAllowNewlineAfterClosingTag: boolean = (() => {
			const nextSibling: ?BBCodeContentNode = node.getNextSibling();

			return (
				nextSibling
				&& nextSibling.getName() !== '#linebreak'
				&& !(
					nextSibling.getType() === BBCodeNode.ELEMENT_NODE
					&& !nextSibling.getTagScheme().getGroup().includes('#inline')
				)
			);
		})();

		const openingTag: string = node.getOpeningTag();
		const content: string = node.getContent();
		const closingTag: string = node.getClosingTag();

		return [
			isAllowNewlineBeforeOpeningTag ? '\n' : '',
			openingTag,
			isAllowNewlineAfterOpeningTag ? '\n' : '',
			content,
			isAllowNewlineBeforeClosingTag ? '\n' : '',
			closingTag,
			isAllowNewlineAfterClosingTag ? '\n' : '',
		].join('');
	}

	setName(name: Array<BBCodeNodeName>)
	{
		super.setName(name);
	}

	setVoid(value: boolean)
	{
		if (Type.isBoolean(value))
		{
			this[voidSymbol] = value;
		}
	}

	isVoid(): boolean
	{
		return this[voidSymbol];
	}

	setCanBeEmpty(value: boolean)
	{
		if (Type.isBoolean(value))
		{
			this[canBeEmptySymbol] = value;
		}
	}

	canBeEmpty(): boolean
	{
		return this[canBeEmptySymbol];
	}

	setChildConverter(converter: BBCodeNodeConverter | null)
	{
		if (Type.isFunction(converter) || Type.isNull(converter))
		{
			this.childConverter = converter;
		}
	}

	getChildConverter(): BBCodeNodeConverter | null
	{
		return this.childConverter;
	}

	setAllowedChildren(allowedChildren: Array<BBCodeNodeName>)
	{
		if (Type.isArray(allowedChildren))
		{
			this.allowedChildren = allowedChildren;
		}
	}

	getAllowedChildren(): Array<BBCodeNodeName>
	{
		return this.allowedChildren;
	}

	isChildAllowed(tagName: string): boolean
	{
		const allowedChildren: Array<BBCodeNodeName> = this.getAllowedChildren();

		return (
			!Type.isArrayFilled(allowedChildren)
			|| (
				Type.isArrayFilled(allowedChildren)
				&& allowedChildren.includes(tagName)
			)
		);
	}
}
