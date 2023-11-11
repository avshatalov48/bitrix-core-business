import { Type } from 'main.core';
import { typeof ElementNode } from './element-node';
import { typeof TextNode } from './text-node';
import { typeof NewLineNode } from './new-line-node';
import { typeof TabNode } from './tab-node';
import { typeof RootNode } from './root-node';
import { typeof FragmentNode } from './fragment-node';

export type SpecialCharNode = NewLineNode | TabNode;
export type ContentNode = ElementNode | TextNode | SpecialCharNode;
export type ParentNode = RootNode | ElementNode | FragmentNode;

export type NodeOptions = {
	name?: string,
	parent?: ParentNode | null,
	children?: Array<ContentNode | FragmentNode>,
};

export type SerializedNode = {
	name: string,
	children: Array<SerializedNode>,
};

export const privateMap: WeakMap<Node | ContentNode | RootNode | FragmentNode, {[key: string]: any}> = new WeakMap();
export const nameSymbol = Symbol('name');

export class Node
{
	[nameSymbol]: string = 'unknown';
	children: Array<ContentNode | FragmentNode> = [];

	constructor(options: NodeOptions = {})
	{
		privateMap.set(this, {});
		this.setParent(options.parent);
		this.setName(options.name);
		this.setChildren(options.children);
	}

	static get ELEMENT_NODE(): number
	{
		return 1;
	}

	static get TEXT_NODE(): number
	{
		return 2;
	}

	static get ROOT_NODE(): number
	{
		return 3;
	}

	static get FRAGMENT_NODE(): number
	{
		return 4;
	}

	static freezeProperty(node: Node, property: string, value: any, enumerable: boolean = true)
	{
		Object.defineProperty(node, property, {
			value,
			writable: false,
			configurable: false,
			enumerable,
		});
	}

	static makeNonEnumerableProperty(node: Node, property: string)
	{
		Object.defineProperty(node, property, {
			writable: false,
			enumerable: false,
			configurable: false,
		});
	}

	static flattenChildren(children: Array<ContentNode | FragmentNode>): Array<ContentNode>
	{
		if (Type.isArrayFilled(children))
		{
			return children.flatMap((node: ContentNode | FragmentNode) => {
				if (node.getType() === Node.FRAGMENT_NODE)
				{
					return node.getChildren();
				}

				return node;
			});
		}

		return [];
	}

	setName(name: string)
	{
		if (Type.isString(name))
		{
			this[nameSymbol] = name;
		}
	}

	getName(): string
	{
		return this[nameSymbol];
	}

	setParent(parent: ParentNode | null = null)
	{
		privateMap.get(this).parent = parent;
	}

	getParent(): ParentNode | null
	{
		return privateMap.get(this).parent;
	}

	getType(): number
	{
		return privateMap.get(this).type;
	}

	hasParent(): boolean
	{
		return Boolean(privateMap.get(this).parent);
	}

	remove()
	{
		if (this.hasParent())
		{
			this.getParent().removeChild(this);
		}
	}

	setChildren(children: Array<ContentNode | FragmentNode>)
	{
		if (Type.isArray(children))
		{
			this.children = [];
			this.appendChild(...children);
		}
	}

	getChildren(): Array<ContentNode>
	{
		return [...this.children];
	}

	getLastChild(): ?ContentNode
	{
		return this.getChildren().at(-1);
	}

	getLastChildOfType(type: number): ?ContentNode
	{
		return this.getChildren().reverse().find((node: ContentNode) => {
			return node.getType() === type;
		});
	}

	getLastChildOfName(name: string): ?ContentNode
	{
		return this.getChildren().reverse().find((node: ContentNode) => {
			return node.getType() === Node.ELEMENT_NODE && node.getName() === name;
		});
	}

	getFirstChild(): ?ContentNode
	{
		return this.getChildren().at(0);
	}

	getFirstChildOfType(type: number): ?ContentNode
	{
		return this.getChildren().find((node: ContentNode) => {
			return node.getType() === type;
		});
	}

	getFirstChildOfName(name: string): ?ContentNode
	{
		return this.getChildren().find((node: ContentNode) => {
			return node.getType() === Node.ELEMENT_NODE && node.getName() === name;
		});
	}

	getPreviewsSibling(): ?ContentNode
	{
		if (this.hasParent())
		{
			const parentChildren: Array<ContentNode> = this.getParent().getChildren();
			const currentIndex: number = parentChildren.indexOf(this);
			if (currentIndex > 0)
			{
				return parentChildren.at(currentIndex - 1);
			}
		}

		return null;
	}

	getNextSibling(): ?ContentNode
	{
		if (this.hasParent())
		{
			const parentChildren: Array<ContentNode> = this.getParent().getChildren();
			const currentIndex: number = parentChildren.indexOf(this);
			if (currentIndex !== -1 && currentIndex !== parentChildren.length)
			{
				return parentChildren.at(currentIndex + 1);
			}
		}

		return null;
	}

	getChildrenCount(): number
	{
		return this.children.length;
	}

	hasChildren(): boolean
	{
		return this.getChildrenCount() > 0;
	}

	appendChild(...children: Array<ContentNode | FragmentNode>)
	{
		const flattenedChildren: Array<ContentNode> = Node.flattenChildren(children);

		flattenedChildren.forEach((node: ContentNode) => {
			node.remove();
			node.setParent(this);
			this.children.push(node);
		});
	}

	prependChild(...children: Array<ContentNode | FragmentNode>)
	{
		const flattenedChildren: Array<ContentNode> = Node.flattenChildren(children);

		flattenedChildren.forEach((node: ContentNode) => {
			node.remove();
			node.setParent(this);
			this.children.unshift(node);
		});
	}

	propagateChild(...children: Array<ContentNode>)
	{
		if (this.hasParent())
		{
			this.getParent().prependChild(
				...children.filter((node: ContentNode) => {
					return (
						node.getType() === Node.ELEMENT_NODE
						|| node.getName() === '#text'
					);
				}),
			);
		}
	}

	removeChild(...children: Array<ContentNode>)
	{
		this.children = this.children.reduce((acc: Array<ContentNode>, node: ContentNode) => {
			if (children.includes(node))
			{
				node.setParent(null);

				return acc;
			}

			return [...acc, node];
		}, []);
	}

	replaceChild(targetNode: ContentNode, ...children: Array<ContentNode | FragmentNode>)
	{
		this.children = this.children.flatMap((node: ContentNode) => {
			if (node === targetNode)
			{
				node.setParent(null);

				const flattenedChildren: Array<ContentNode> = Node.flattenChildren(children);

				return flattenedChildren.map((child: ContentNode) => {
					child.remove();
					child.setParent(this);

					return child;
				});
			}

			return node;
		});
	}

	toJSON(): SerializedNode
	{
		return {
			name: this.getName(),
			children: this.getChildren().map((child: ContentNode) => {
				return child.toJSON();
			}),
		};
	}
}
