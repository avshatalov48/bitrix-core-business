import { Type } from 'main.core';
import type { BBCodeEncoder } from 'ui.bbcode.encoder';
import { getByIndex } from '../../../shared';
import { typeof BBCodeElementNode } from './element-node';
import { typeof BBCodeTextNode } from './text-node';
import { typeof BBCodeNewLineNode } from './new-line-node';
import { typeof BBCodeTabNode } from './tab-node';
import { typeof BBCodeRootNode } from './root-node';
import { typeof BBCodeFragmentNode } from './fragment-node';
import { typeof BBCodeScheme } from '../scheme/bbcode-scheme';
import { type BBCodeNodeScheme } from '../scheme/node-schemes/node-scheme';

export type BBCodeSpecialCharNode = BBCodeNewLineNode | BBCodeTabNode;
export type BBCodeContentNode = BBCodeElementNode | BBCodeTextNode | BBCodeSpecialCharNode;
export type BBCodeParentNode = BBCodeRootNode | BBCodeElementNode | BBCodeFragmentNode;

export type BBCodeNodeOptions = {
	name?: string,
	parent?: BBCodeParentNode | null,
	children?: Array<BBCodeContentNode | BBCodeFragmentNode>,
	scheme: BBCodeScheme,
};

export type SerializedBBCodeNode = {
	name: string,
	children: Array<SerializedBBCodeNode>,
};

type PrivateMapKey = BBCodeNode | BBCodeContentNode | BBCodeRootNode | BBCodeFragmentNode;
type PrivateStorage = {[key: string]: any};

export const privateMap: WeakMap<PrivateMapKey, PrivateStorage> = new WeakMap();
export const nameSymbol: Symbol = Symbol('name');

export class BBCodeNode
{
	[nameSymbol]: string = '#unknown';
	children: Array<BBCodeContentNode | BBCodeFragmentNode> = [];

	constructor(options: BBCodeNodeOptions = {})
	{
		privateMap.set(this, {
			delayedChildren: [],
		});

		this.setName(options.name);
		privateMap.get(this).scheme = options.scheme;
		this.setParent(options.parent);
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

	static freezeProperty(node: BBCodeNode, property: string, value: any, enumerable: boolean = true)
	{
		Object.defineProperty(node, property, {
			value,
			writable: false,
			configurable: false,
			enumerable,
		});
	}

	static makeNonEnumerableProperty(node: BBCodeNode, property: string)
	{
		Object.defineProperty(node, property, {
			writable: false,
			enumerable: false,
			configurable: false,
		});
	}

	static flattenChildren(children: Array<BBCodeContentNode | BBCodeFragmentNode>): Array<BBCodeContentNode>
	{
		if (Type.isArrayFilled(children))
		{
			return children.flatMap((node: BBCodeContentNode | BBCodeFragmentNode) => {
				if (node.getType() === BBCodeNode.FRAGMENT_NODE)
				{
					return node.getChildren();
				}

				return node;
			});
		}

		return [];
	}

	setScheme(scheme: BBCodeScheme, onUnknown: (node: BBCodeContentNode) => any)
	{
		privateMap.get(this).scheme = scheme;
	}

	getScheme(): BBCodeScheme
	{
		return privateMap.get(this).scheme;
	}

	getTagScheme(): BBCodeNodeScheme
	{
		return this.getScheme().getTagScheme(this.getName());
	}

	getEncoder(): BBCodeEncoder
	{
		return this.getScheme().getEncoder();
	}

	prepareCase(value: string): string
	{
		const scheme: BBCodeScheme = this.getScheme();
		const currentCase = scheme.getOutputTagCase();
		if (currentCase === 'upper')
		{
			return value.toUpperCase();
		}

		return value.toLowerCase();
	}

	setName(name: string)
	{
		if (Type.isString(name))
		{
			this[nameSymbol] = name.toLowerCase();
		}
	}

	getName(): string
	{
		return this[nameSymbol];
	}

	getDisplayedName(): string
	{
		return this.prepareCase(this.getName());
	}

	setParent(parent: BBCodeParentNode | null = null)
	{
		const mounted = !this.hasParent() && parent;
		privateMap.get(this).parent = parent;

		if (mounted)
		{
			this.onNodeDidMount();
		}
	}

	getParent(): BBCodeParentNode | null
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

	setChildren(children: Array<BBCodeContentNode | BBCodeFragmentNode>)
	{
		if (Type.isArray(children))
		{
			this.children = [];
			this.appendChild(...children);
		}
	}

	getChildren(): Array<BBCodeContentNode>
	{
		return [...this.children];
	}

	getLastChild(): ?BBCodeContentNode
	{
		return getByIndex(this.getChildren(), -1);
	}

	getLastChildOfType(type: number): ?BBCodeContentNode
	{
		return this.getChildren().reverse().find((node: BBCodeContentNode) => {
			return node.getType() === type;
		});
	}

	getLastChildOfName(name: string): ?BBCodeContentNode
	{
		return this.getChildren().reverse().find((node: BBCodeContentNode) => {
			return node.getType() === BBCodeNode.ELEMENT_NODE && node.getName() === name;
		});
	}

	getFirstChild(): ?BBCodeContentNode
	{
		return getByIndex(this.getChildren(), 0);
	}

	getFirstChildOfType(type: number): ?BBCodeContentNode
	{
		return this.getChildren().find((node: BBCodeContentNode) => {
			return node.getType() === type;
		});
	}

	getFirstChildOfName(name: string): ?BBCodeContentNode
	{
		return this.getChildren().find((node: BBCodeContentNode) => {
			return node.getType() === BBCodeNode.ELEMENT_NODE && node.getName() === name;
		});
	}

	getPreviewsSibling(): ?BBCodeContentNode
	{
		if (this.hasParent())
		{
			const parentChildren: Array<BBCodeContentNode> = this.getParent().getChildren();
			const currentIndex: number = parentChildren.indexOf(this);
			if (currentIndex > 0)
			{
				return getByIndex(parentChildren, currentIndex - 1);
			}
		}

		return null;
	}

	getPreviewsSiblings(): ?Array<BBCodeContentNode>
	{
		if (this.hasParent())
		{
			const parentChildren: Array<BBCodeContentNode> = this.getParent().getChildren();
			const currentIndex: number = parentChildren.indexOf(this);

			return parentChildren.filter((child: BBCodeContentNode, index) => {
				return index < currentIndex;
			});
		}

		return null;
	}

	getNextSibling(): ?BBCodeContentNode
	{
		if (this.hasParent())
		{
			const parentChildren: Array<BBCodeContentNode> = this.getParent().getChildren();
			const currentIndex: number = parentChildren.indexOf(this);
			if (currentIndex !== -1 && currentIndex !== parentChildren.length)
			{
				return getByIndex(parentChildren, currentIndex + 1);
			}
		}

		return null;
	}

	getNextSiblings(): ?Array<BBCodeContentNode>
	{
		if (this.hasParent())
		{
			const parentChildren: Array<BBCodeContentNode> = this.getParent().getChildren();
			const currentIndex: number = parentChildren.indexOf(this);

			return parentChildren.filter((child: BBCodeContentNode, index) => {
				return index > currentIndex;
			});
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

	isEmpty(): boolean
	{
		return this.getChildrenCount() === 0;
	}

	adjustChildren()
	{
		this.setChildren(this.getChildren());
	}

	setDelayedChildren(children: Array<BBCodeContentNode>)
	{
		if (Type.isArray(children))
		{
			privateMap.get(this).delayedChildren = children;
		}
	}

	addDelayedChildren(children: Array<BBCodeContentNode>)
	{
		if (Type.isArrayFilled(children))
		{
			this.setDelayedChildren([
				...this.getDelayedChildren(),
				...children,
			]);
		}
	}

	hasDelayedChildren(): boolean
	{
		return privateMap.get(this).delayedChildren.length > 0;
	}

	getDelayedChildren(): Array<BBCodeContentNode>
	{
		return [...privateMap.get(this).delayedChildren];
	}

	appendChild(...children: Array<BBCodeContentNode | BBCodeFragmentNode>)
	{
		const flattenedChildren: Array<BBCodeContentNode> = BBCodeNode.flattenChildren(children);

		flattenedChildren.forEach((node: BBCodeContentNode) => {
			node.remove();
			node.setParent(this);
			this.children.push(node);
		});
	}

	prependChild(...children: Array<BBCodeContentNode | BBCodeFragmentNode>)
	{
		const flattenedChildren: Array<BBCodeContentNode> = BBCodeNode.flattenChildren(children);

		flattenedChildren.forEach((node: BBCodeContentNode) => {
			node.remove();
			node.setParent(this);
			this.children.unshift(node);
		});
	}

	insertBefore(...nodes: Array<BBCodeContentNode>)
	{
		if (this.hasParent() && Type.isArrayFilled(nodes))
		{
			const parent: BBCodeContentNode = this.getParent();
			const parentChildren: Array<BBCodeContentNode> = parent.getChildren();
			const currentNodeIndex: number = parentChildren.indexOf(this);

			const deleteCount: number = 0;
			parentChildren.splice(currentNodeIndex, deleteCount, ...nodes);

			parent.setChildren(parentChildren);
		}
	}

	insertAfter(...nodes: Array<BBCodeContentNode>)
	{
		if (this.hasParent() && Type.isArrayFilled(nodes))
		{
			const parent: BBCodeContentNode = this.getParent();
			const parentChildren: Array<BBCodeContentNode> = parent.getChildren();
			const currentNodeIndex: number = parentChildren.indexOf(this);

			const startIndex: number = currentNodeIndex + 1;
			const deleteCount: number = 0;
			parentChildren.splice(startIndex, deleteCount, ...nodes);

			parent.setChildren(parentChildren);
		}
	}

	propagateChild(...children: Array<BBCodeContentNode>)
	{
		if (this.hasParent())
		{
			this.insertBefore(
				...children.filter((child: BBCodeContentNode) => {
					return !['#linebreak', '#tab'].includes(child.getName());
				}),
			);
		}
		else
		{
			this.addDelayedChildren(children);
		}
	}

	onNodeDidMount()
	{
		const delayedChildren = this.getDelayedChildren();
		if (Type.isArrayFilled(delayedChildren))
		{
			this.propagateChild(...delayedChildren);
			this.setDelayedChildren([]);
		}
	}

	removeChild(...children: Array<BBCodeContentNode>)
	{
		const filteredChildren = [];
		this.children.forEach((node: BBCodeContentNode) => {
			if (children.includes(node))
			{
				node.setParent(null);
			}
			else
			{
				filteredChildren.push(node);
			}
		});

		this.children = filteredChildren;
	}

	replaceChild(targetNode: BBCodeContentNode, ...children: Array<BBCodeContentNode | BBCodeFragmentNode>)
	{
		this.children = this.children.flatMap((node: BBCodeContentNode) => {
			if (node === targetNode)
			{
				node.setParent(null);

				const flattenedChildren: Array<BBCodeContentNode> = BBCodeNode.flattenChildren(children);

				return flattenedChildren.map((child: BBCodeContentNode) => {
					child.remove();
					child.setParent(this);

					return child;
				});
			}

			return node;
		});
	}

	replace(...children: Array<BBCodeContentNode | BBCodeFragmentNode>)
	{
		if (this.hasParent())
		{
			const parent = this.getParent();
			parent.replaceChild(this, ...children);
		}
	}

	clone(options: { deep: boolean } = {}): BBCodeNode
	{
		const children = (() => {
			if (options.deep)
			{
				return this.getChildren().map((child) => {
					return child.clone(options);
				});
			}

			return [];
		})();

		return this.getScheme().createNode({
			name: this.getName(),
			parent: this.getParent(),
			children,
		});
	}

	toPlainText(): string
	{
		return this.getChildren().map((child) => {
			return child.toPlainText();
		}).join('');
	}

	getTextContent(): string
	{
		return this.toPlainText();
	}

	getPlainTextLength(): number
	{
		return this.toPlainText().length;
	}

	removePreviewsSiblings()
	{
		const removePreviewsSiblings = (node: BBCodeContentNode) => {
			const previewsSiblings = node.getPreviewsSiblings();
			if (Type.isArray(previewsSiblings))
			{
				previewsSiblings.forEach((sibling: BBCodeContentNode) => {
					sibling.remove();
				});
			}

			const parent = node.getParent();
			if (parent)
			{
				removePreviewsSiblings(parent);
			}
		};

		removePreviewsSiblings(this);
	}

	removeNextSiblings()
	{
		const removeNextSiblings = (node: BBCodeContentNode) => {
			const nextSiblings = node.getNextSiblings();
			if (Type.isArray(nextSiblings))
			{
				nextSiblings.forEach((sibling: BBCodeContentNode) => {
					sibling.remove();
				});
			}

			const parent = node.getParent();
			if (parent)
			{
				removeNextSiblings(parent);
			}
		};

		removeNextSiblings(this);
	}

	findByTextIndex(index: number): ?{ node: BBCodeTextNode, startIndex: number, endIndex: number }
	{
		let currentIndex = 0;
		let startIndex = 0;
		let endIndex = 0;

		const node = BBCodeNode.flattenAst(this).find((child: BBCodeContentNode) => {
			if (
				child.getName() === '#text'
				|| child.getName() === '#linebreak'
				|| child.getName() === '#tab'
			)
			{
				startIndex = currentIndex;
				endIndex = startIndex + child.getLength();
				currentIndex = endIndex;

				return index >= startIndex && endIndex >= index;
			}

			return false;
		});

		if (node)
		{
			return { node, startIndex, endIndex };
		}

		return null;
	}

	split(options: { offset: number, byWord?: boolean}): Array<BBCodeContentNode>
	{
		const { offset, byWord = false } = options;
		const plainTextLength = this.getPlainTextLength();

		const leftTree = (() => {
			if (plainTextLength === offset)
			{
				return this.clone({ deep: true });
			}

			if (offset <= 0 || offset > plainTextLength)
			{
				return null;
			}

			const tree = this.clone({ deep: true });
			const { node, startIndex } = tree.findByTextIndex(offset);
			const [leftNode, rightNode] = node.split({ offset: offset - startIndex, byWord });
			if (leftNode)
			{
				node.replace(leftNode);
				leftNode.removeNextSiblings();
			}
			else if (rightNode)
			{
				rightNode.removeNextSiblings();
				rightNode.remove();
			}

			return tree;
		})();

		const rightTree = (() => {
			if (plainTextLength === offset)
			{
				return null;
			}

			if (offset === 0)
			{
				return this.clone({ deep: true });
			}

			const tree = this.clone({ deep: true });
			const { node, startIndex } = tree.findByTextIndex(offset);
			const [leftNode, rightNode] = node.split({ offset: offset - startIndex, byWord });
			if (rightNode)
			{
				node.replace(rightNode);
				rightNode.removePreviewsSiblings();
			}
			else if (leftNode)
			{
				leftNode.removePreviewsSiblings();
				if (leftNode.hasParent())
				{
					const parent = leftNode.getParent();
					leftNode.remove();
					if (parent.getChildrenCount() === 0)
					{
						parent.remove();
					}
				}
			}

			return tree;
		})();

		return [leftTree, rightTree];
	}

	static flattenAst(ast): Array<any>
	{
		const flat = [];

		const traverse = (node: BBCodeContentNode) => {
			flat.push(node);
			if (node.hasChildren())
			{
				node.getChildren().forEach((child: BBCodeContentNode) => {
					traverse(child);
				});
			}
		};

		if (ast.hasChildren())
		{
			ast.getChildren().forEach((child: BBCodeContentNode) => {
				traverse(child);
			});
		}

		return flat;
	}

	toJSON(): SerializedBBCodeNode
	{
		return {
			name: this.getName(),
			children: this.getChildren().map((child: BBCodeContentNode) => {
				return child.toJSON();
			}),
		};
	}
}
