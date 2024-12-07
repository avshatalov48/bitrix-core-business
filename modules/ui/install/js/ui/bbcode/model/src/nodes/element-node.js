import { Type, type JsonObject, type JsonValue } from 'main.core';
import { BBCodeNode, type BBCodeNodeOptions, type BBCodeContentNode, type SerializedBBCodeNode, privateMap } from './node';
import { typeof BBCodeFragmentNode } from './fragment-node';
import { type BBCodeNodeStringifier } from '../scheme/node-schemes/node-scheme';
import { typeof BBCodeTagScheme } from '../scheme/node-schemes/tag-scheme';
import { typeof BBCodeScheme } from '../scheme/bbcode-scheme';
import { type BBCodeToStringOptions } from './root-node';

export type BBCodeElementNodeValue = string | number | boolean;

export type BBCodeElementNodeOptions = BBCodeNodeOptions & {
	attributes?: JsonObject,
	value?: BBCodeElementNodeValue,
};

export type SerializedBBCodeElementNode = SerializedBBCodeNode & {
	attributes: JsonObject,
	value: BBCodeElementNodeValue,
};

export type FilteredChildren = {
	resolved: Array<BBCodeContentNode>,
	unresolved: Array<BBCodeContentNode>,
};

const voidSymbol: Symbol = Symbol('void');

export class BBCodeElementNode extends BBCodeNode
{
	attributes: JsonObject = {};
	value: JsonValue = '';
	[voidSymbol]: boolean = false;

	constructor(options: BBCodeElementNodeOptions = {})
	{
		super(options);
		privateMap.get(this).type = BBCodeNode.ELEMENT_NODE;

		const tagScheme: BBCodeTagScheme = this.getTagScheme();

		this[voidSymbol] = tagScheme.isVoid();

		this.setValue(options.value);
		this.setAttributes(options.attributes);
	}

	setScheme(scheme: BBCodeScheme, onUnknown: (node: BBCodeContentNode) => any)
	{
		this.getChildren().forEach((node: BBCodeContentNode) => {
			node.setScheme(scheme, onUnknown);
		});

		if (scheme.isAllowedTag(this.getName()))
		{
			super.setScheme(scheme);

			const tagScheme: BBCodeTagScheme = this.getTagScheme();
			this[voidSymbol] = tagScheme.isVoid();
		}
		else
		{
			super.setScheme(scheme);
			onUnknown(this, scheme);
		}
	}

	filterChildren(children: Array<BBCodeContentNode>): FilteredChildren
	{
		const filteredChildren: FilteredChildren = {
			resolved: [],
			unresolved: [],
		};
		const scheme: BBCodeScheme = this.getScheme();

		children.forEach((child: BBCodeContentNode) => {
			if (scheme.isChildAllowed(this, child))
			{
				filteredChildren.resolved.push(child);
			}
			else
			{
				filteredChildren.unresolved.push(child);
			}
		});

		return filteredChildren;
	}

	convertChildren(children: Array<BBCodeContentNode>): Array<BBCodeContentNode>
	{
		const tagScheme: BBCodeTagScheme = this.getTagScheme();
		const childConverter = tagScheme.getChildConverter();
		if (childConverter)
		{
			const scheme: BBCodeScheme = this.getScheme();

			return children.map((child: BBCodeNode) => {
				return childConverter(child, scheme);
			});
		}

		return children;
	}

	setValue(value: BBCodeElementNodeValue)
	{
		if (Type.isString(value) || Type.isNumber(value) || Type.isBoolean(value))
		{
			this.value = value;
		}
	}

	getValue(): BBCodeElementNodeValue
	{
		return this.value;
	}

	isVoid(): boolean
	{
		return this[voidSymbol];
	}

	canBeEmpty(): boolean
	{
		return this.getTagScheme().canBeEmpty();
	}

	hasGroup(groupName: string): boolean
	{
		return this.getTagScheme().hasGroup(groupName);
	}

	setAttributes(attributes: JsonObject)
	{
		if (Type.isPlainObject(attributes))
		{
			const entries = Object.entries(attributes).map(([key, value]) => {
				return [key.toLowerCase(), value];
			});

			this.attributes = Object.fromEntries(entries);
		}
	}

	setAttribute(name: string, value: any)
	{
		if (Type.isStringFilled(name))
		{
			const preparedName: string = name.toLowerCase();
			if (Type.isNil(value))
			{
				delete this.attributes[preparedName];
			}
			else
			{
				this.attributes[preparedName] = value;
			}
		}
	}

	getAttribute(name: string): string | number | null
	{
		if (Type.isString(name))
		{
			return this.attributes[name.toLowerCase()];
		}

		return null;
	}

	getAttributes(): JsonObject
	{
		return { ...this.attributes };
	}

	appendChild(...children: Array<BBCodeContentNode | BBCodeFragmentNode>)
	{
		const flattenedChildren: Array<BBCodeContentNode> = BBCodeNode.flattenChildren(children);
		const convertedChildren: Array<BBCodeContentNode> = this.convertChildren(flattenedChildren);
		const filteredChildren: FilteredChildren = this.filterChildren(convertedChildren);

		filteredChildren.resolved.forEach((node: BBCodeContentNode) => {
			node.remove();
			node.setParent(this);
			this.children.push(node);
		});

		if (Type.isArrayFilled(filteredChildren.unresolved))
		{
			const tagScheme: BBCodeTagScheme = this.getTagScheme();
			if (tagScheme.hasNotAllowedChildrenCallback())
			{
				tagScheme.runNotAllowedChildrenCallback({
					node: this,
					children: filteredChildren.unresolved,
					scheme: this.getScheme(),
				});
			}
			else if (this.getScheme().isAllowedUnresolvedNodesHoisting())
			{
				this.propagateChild(...filteredChildren.unresolved);
			}
			else
			{
				filteredChildren.unresolved.forEach((node: BBCodeContentNode) => {
					node.remove();
				});
			}
		}
	}

	prependChild(...children: Array<BBCodeContentNode | BBCodeFragmentNode>)
	{
		const flattenedChildren: Array<BBCodeContentNode> = BBCodeNode.flattenChildren(children);
		const convertedChildren: Array<BBCodeContentNode> = this.convertChildren(flattenedChildren);
		const filteredChildren: FilteredChildren = this.filterChildren(convertedChildren);

		filteredChildren.resolved.forEach((node: BBCodeContentNode) => {
			node.remove();
			node.setParent(this);
			this.children.unshift(node);
		});

		if (Type.isArrayFilled(filteredChildren.unresolved))
		{
			const tagScheme: BBCodeTagScheme = this.getTagScheme();
			if (tagScheme.hasNotAllowedChildrenCallback())
			{
				tagScheme.runNotAllowedChildrenCallback({
					node: this,
					children: filteredChildren.unresolved,
					scheme: this.getScheme(),
				});
			}
			else if (this.getScheme().isAllowedUnresolvedNodesHoisting())
			{
				this.propagateChild(...filteredChildren.unresolved);
			}
			else
			{
				filteredChildren.unresolved.forEach((node: BBCodeContentNode) => {
					node.remove();
				});
			}
		}
	}

	replaceChild(targetNode: BBCodeContentNode, ...children: Array<BBCodeContentNode | BBCodeFragmentNode>)
	{
		this.children = this.children.flatMap((node: BBCodeContentNode) => {
			if (node === targetNode)
			{
				node.setParent(null);

				const flattenedChildren: Array<BBCodeContentNode> = BBCodeNode.flattenChildren(children);
				const convertedChildren: Array<BBCodeContentNode> = this.convertChildren(flattenedChildren);
				const filteredChildren: FilteredChildren = this.filterChildren(convertedChildren);

				return filteredChildren.resolved.map((child: BBCodeContentNode) => {
					child.remove();
					child.setParent(this);

					return child;
				});
			}

			return node;
		});
	}

	toStringValue(): string
	{
		const value: BBCodeElementNodeValue = this.getValue();
		const encodedValue: string = this.getEncoder().encodeAttribute(value);

		return value ? `=${encodedValue}` : '';
	}

	toStringAttributes(): string
	{
		return Object
			.entries(this.getAttributes())
			.map(([key: string, attrValue: string]) => {
				const preparedKey: string = this.prepareCase(key);
				const encodedValue: string = this.getEncoder().encodeAttribute(attrValue);

				return attrValue ? `${preparedKey}=${encodedValue}` : preparedKey;
			})
			.join(' ');
	}

	getContent(options: BBCodeToStringOptions = {}): string
	{
		return this.getChildren()
			.map((child: BBCodeContentNode) => {
				return child.toString(options);
			})
			.join('');
	}

	getOpeningTag(): string
	{
		const displayedName: string = this.getDisplayedName();
		const tagValue: BBCodeElementNodeValue = this.toStringValue();
		const attributes: JsonObject = this.toStringAttributes();
		const formattedAttributes: string = Type.isStringFilled(attributes) ? ` ${attributes}` : '';

		return `[${displayedName}${tagValue}${formattedAttributes}]`;
	}

	getClosingTag(): string
	{
		return `[/${this.getDisplayedName()}]`;
	}

	clone(options: { deep: boolean } = {}): BBCodeElementNode
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

		return this.getScheme().createElement({
			name: this.getName(),
			void: this.isVoid(),
			value: this.getValue(),
			attributes: { ...this.getAttributes() },
			children,
		});
	}

	splitByChildIndex(index: number): Array<BBCodeElementNode | null>
	{
		if (!Type.isNumber(index))
		{
			throw new TypeError('index is not a number');
		}

		const childrenCount = this.getChildrenCount();
		if (index < 0 || index > childrenCount)
		{
			throw new TypeError(`index '${index}' is out of range ${0}-${childrenCount}`);
		}

		const leftNode = (() => {
			if (index === childrenCount)
			{
				return this;
			}

			if (index === 0)
			{
				return null;
			}

			const leftChildren = this.getChildren().filter((child, childIndex) => {
				return childIndex < index;
			});

			const node = this.clone();
			node.setChildren(leftChildren);

			return node;
		})();

		const rightNode = (() => {
			if (index === 0)
			{
				return this;
			}

			if (index === childrenCount)
			{
				return null;
			}

			const rightChildren = this.getChildren();
			const node = this.clone();
			node.setChildren(rightChildren);

			return node;
		})();

		if (leftNode && rightNode)
		{
			this.replace(leftNode, rightNode);
		}

		return [leftNode, rightNode];
	}

	getTagScheme(): BBCodeTagScheme
	{
		return super.getTagScheme();
	}

	trimStartLinebreaks()
	{
		const firstChild: BBCodeContentNode = this.getFirstChild();
		if (firstChild && firstChild.getName() === '#linebreak')
		{
			firstChild.remove();
			this.trimStartLinebreaks();
		}
	}

	trimEndLinebreaks()
	{
		const lastChild: BBCodeContentNode = this.getLastChild();
		if (lastChild && lastChild.getName() === '#linebreak')
		{
			lastChild.remove();
			this.trimEndLinebreaks();
		}
	}

	trimLinebreaks()
	{
		this.trimStartLinebreaks();
		this.trimEndLinebreaks();
	}

	toString(options: BBCodeToStringOptions = {}): string
	{
		const tagScheme: BBCodeTagScheme = this.getTagScheme();
		const stringifier: BBCodeNodeStringifier = tagScheme.getStringifier();
		if (Type.isFunction(stringifier))
		{
			const scheme: BBCodeScheme = this.getScheme();

			return stringifier(this, scheme, options);
		}

		const openingTag: string = this.getOpeningTag();
		const content: string = this.getContent(options);

		if (this.isVoid())
		{
			return `${openingTag}${content}`;
		}

		const closingTag: string = this.getClosingTag();

		return `${openingTag}${content}${closingTag}`;
	}

	toJSON(): SerializedBBCodeElementNode
	{
		return {
			...super.toJSON(),
			value: this.getValue(),
			attributes: this.getAttributes(),
			void: this.isVoid(),
		};
	}
}
