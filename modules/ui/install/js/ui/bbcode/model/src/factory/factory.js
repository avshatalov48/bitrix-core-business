import { Type } from 'main.core';
import { RootNode, type RootNodeOptions } from '../nodes/root-node';
import { ElementNode, type ElementNodeOptions } from '../nodes/element-node';
import { FragmentNode, type FragmentNodeOptions } from '../nodes/fragment-node';
import { NewLineNode } from '../nodes/new-line-node';
import { TabNode } from '../nodes/tab-node';
import { TextNode, type TextNodeOptions } from '../nodes/text-node';
import { Node, type NodeOptions } from '../nodes/node';
import { BBCodeScheme } from '../scheme/scheme';

export type ModelFactoryOptions = {
	scheme: BBCodeScheme,
};

export class ModelFactory
{
	/** @private */
	scheme: BBCodeScheme;

	constructor(options: ModelFactoryOptions = {})
	{
		if (Type.isObject(options.scheme))
		{
			this.setScheme(options.scheme);
		}
		else
		{
			this.setScheme(new BBCodeScheme());
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

	createRootNode(options: RootNodeOptions = {}): RootNode
	{
		return new RootNode({ ...options, scheme: this.getScheme() });
	}

	createElementNode(options: ElementNodeOptions = {}): ElementNode
	{
		return new ElementNode({ ...options, scheme: this.getScheme() });
	}

	createTextNode(options: TextNodeOptions = {}): TextNode
	{
		const preparedOptions = Type.isString(options) ? { content: options } : options;

		return new TextNode({ ...preparedOptions, scheme: this.getScheme() });
	}

	createNewLineNode(options: TextNodeOptions = {}): NewLineNode
	{
		const preparedOptions = Type.isString(options) ? { content: options } : options;

		return new NewLineNode({ ...preparedOptions, scheme: this.getScheme() });
	}

	createTabNode(options: TextNodeOptions = {}): TabNode
	{
		const preparedOptions = Type.isString(options) ? { content: options } : options;

		return new TabNode({ ...preparedOptions, scheme: this.getScheme() });
	}

	createFragmentNode(options: FragmentNodeOptions = {}): FragmentNode
	{
		return new FragmentNode({ ...options, scheme: this.getScheme() });
	}

	createNode(options: NodeOptions = {}): Node
	{
		return new Node({ ...options, scheme: this.getScheme() });
	}
}
