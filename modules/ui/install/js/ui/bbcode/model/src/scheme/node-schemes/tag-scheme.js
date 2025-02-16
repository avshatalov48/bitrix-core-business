import { Type } from 'main.core';
import { type BBCodeToStringOptions } from '../../nodes/root-node';
import { typeof BBCodeScheme } from '../bbcode-scheme';
import {
	BBCodeNodeScheme,
	type BBCodeNodeSchemeOptions,
	type BBCodeNodeConverter,
	type BBCodeNodeName,
} from './node-scheme';
import { typeof BBCodeElementNode } from '../../nodes/element-node';
import { BBCodeNode, type BBCodeContentNode } from '../../nodes/node';

export type NotAllowedChildrenCallbackOptions = {
	node: BBCodeContentNode,
	scheme: BBCodeScheme,
	children: Array<BBCodeContentNode>,
};

export type BBCodeTagSchemeOptions = BBCodeNodeSchemeOptions & {
	void?: boolean,
	canBeEmpty?: boolean,
	convertChild?: BBCodeNodeConverter,
	allowedChildren?: Array<BBCodeNodeName>,
	onNotAllowedChildren?: () => void,
};

const canBeEmptySymbol = Symbol('@canBeEmpty');
const voidSymbol = Symbol('@void');

export class BBCodeTagScheme extends BBCodeNodeScheme
{
	[voidSymbol]: boolean = false;
	[canBeEmptySymbol]: boolean = true;
	childConverter: BBCodeNodeConverter | null = null;
	allowedChildren: Array<BBCodeNodeName> = [];
	notAllowedChildrenCallback: (NotAllowedChildrenCallbackOptions) => void = null;

	constructor(options: BBCodeTagSchemeOptions)
	{
		super(options);
		this.setVoid(options.void);
		this.setCanBeEmpty(options.canBeEmpty);
		this.setChildConverter(options.convertChild);
		this.setAllowedChildren(options.allowedChildren);
		this.setOnChangeHandler(options.onChange);
		this.setNotAllowedChildrenCallback(options.onNotAllowedChildren);
	}

	static defaultBlockStringifier(
		node: BBCodeElementNode,
		scheme: BBCodeTagScheme,
		options: BBCodeToStringOptions = {},
	): string
	{
		const isAllowNewlineBeforeOpeningTag: boolean = (() => {
			const previewsSibling: ?BBCodeContentNode = node.getPreviewsSibling();

			return previewsSibling && previewsSibling.getName() !== '#linebreak';
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
		const content: string = node.getContent(options);
		const closingTag: string = node.getClosingTag();

		const isAllowContentLinebreaks: boolean = content.length > 0;

		return [
			isAllowNewlineBeforeOpeningTag ? '\n' : '',
			openingTag,
			isAllowContentLinebreaks ? '\n' : '',
			content,
			isAllowContentLinebreaks ? '\n' : '',
			closingTag,
			isAllowNewlineAfterClosingTag ? '\n' : '',
		].join('');
	}

	setVoid(value: boolean)
	{
		if (Type.isBoolean(value))
		{
			this[voidSymbol] = value;
			this.runOnChangeHandler();
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
			this.runOnChangeHandler();
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
			this.runOnChangeHandler();
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

	setNotAllowedChildrenCallback(callback: (NotAllowedChildrenCallbackOptions) => void)
	{
		this.notAllowedChildrenCallback = callback;
	}

	hasNotAllowedChildrenCallback(): boolean
	{
		return Type.isFunction(this.notAllowedChildrenCallback);
	}

	runNotAllowedChildrenCallback(options: NotAllowedChildrenCallbackOptions)
	{
		if (Type.isFunction(this.notAllowedChildrenCallback))
		{
			this.notAllowedChildrenCallback(options);
		}
	}
}
