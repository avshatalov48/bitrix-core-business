import { Type, type JsonObject, type JsonValue } from 'main.core';
import { Node, type NodeOptions, type ContentNode, type SerializedNode, privateMap } from './node';
import { typeof FragmentNode } from './fragment-node';
import { NodeType, childConvertersMap, childFiltersMap, INLINE_ELEMENTS } from '../reference';

export type ElementNodeValue = string | number | boolean;

export type ElementNodeOptions = NodeOptions & {
	attributes?: JsonObject,
	value?: ElementNodeValue,
	void?: boolean,
	inline?: boolean,
};

export type SerializedElementNode = SerializedNode & {
	attributes: JsonObject,
	value: ElementNodeValue,
	void: boolean,
	inline: boolean,
};

export type FilteredChildren = {
	resolved: Array<ContentNode>,
	unresolved: Array<ContentNode>,
};

export class ElementNode extends Node
{
	attributes: JsonObject = {};
	value: JsonValue = '';
	void: boolean = false;
	inline: boolean = false;

	constructor(options: ElementNodeOptions = {})
	{
		super(options);
		privateMap.get(this).type = Node.ELEMENT_NODE;

		const preparedOptions = {
			inline: INLINE_ELEMENTS.has(options.name),
			...options,
		};
		this.setInline(preparedOptions.inline);
		this.setValue(preparedOptions.value);
		this.setVoid(preparedOptions.void);
		this.setAttributes(preparedOptions.attributes);
	}

	static filterChildren(node: ContentNode, children: Array<ContentNode>): FilteredChildren
	{
		const filteredChildren = { resolved: [], unresolved: [] };
		const byTagFilter = childFiltersMap.get(node.getName());
		if (byTagFilter)
		{
			return children.reduce((acc, child: ContentNode) => {
				const isAllowed = byTagFilter(child);
				if (isAllowed)
				{
					acc.resolved.push(child);
				}
				else
				{
					acc.unresolved.push(child);
				}

				return acc;
			}, filteredChildren);
		}

		if (node.isInline())
		{
			const inlineChildFilter = childFiltersMap.get('#inline');

			return children.reduce((acc, child: ContentNode) => {
				const isAllowed = inlineChildFilter(child);
				if (isAllowed)
				{
					acc.resolved.push(child);
				}
				else
				{
					acc.unresolved.push(child);
				}

				return acc;
			}, { resolved: [], unresolved: [] });
		}

		filteredChildren.resolved = children;

		return filteredChildren;
	}

	static convertChildren(node: ContentNode, children: Array<ContentNode>): Array<ContentNode>
	{
		const childConverter = childConvertersMap.get(node.getName());
		if (childConverter)
		{
			return children.map((child: Node) => {
				return childConverter(child);
			});
		}

		return children;
	}

	setValue(value: ElementNodeValue)
	{
		if (Type.isString(value) || Type.isNumber(value) || Type.isBoolean(value))
		{
			this.value = value;
		}
	}

	getValue(): ElementNodeValue
	{
		return this.value;
	}

	setVoid(value: boolean)
	{
		if (Type.isBoolean(value))
		{
			this.void = value;
		}
	}

	isVoid(): boolean
	{
		return this.void;
	}

	setInline(value: boolean)
	{
		if (Type.isBoolean(value))
		{
			this.inline = value;
		}
	}

	isInline(): boolean
	{
		return this.inline;
	}

	setAttributes(attributes: JsonObject)
	{
		if (Type.isPlainObject(attributes))
		{
			this.attributes = { ...attributes };
		}
	}

	setAttribute(name: string, value: any)
	{
		if (Type.isStringFilled(name))
		{
			if (Type.isNil(value))
			{
				delete this.attributes[name];
			}
			else
			{
				this.attributes[name] = value;
			}
		}
	}

	getAttribute(name: string): any
	{
		return this.attributes[name];
	}

	getAttributes(): JsonObject
	{
		return { ...this.attributes };
	}

	appendChild(...children: Array<ContentNode | FragmentNode>)
	{
		const flattenedChildren: Array<ContentNode> = Node.flattenChildren(children);
		const filteredChildren: FilteredChildren = ElementNode.filterChildren(this, flattenedChildren);
		const convertedChildren: Array<ContentNode> = ElementNode.convertChildren(this, filteredChildren.resolved);

		convertedChildren.forEach((node: ContentNode) => {
			node.remove();
			node.setParent(this);
			this.children.push(node);
		});

		if (Type.isArrayFilled(filteredChildren.unresolved))
		{
			this.propagateChild(...filteredChildren.unresolved);
		}
	}

	prependChild(...children: Array<ContentNode | FragmentNode>)
	{
		const flattenedChildren: Array<ContentNode> = Node.flattenChildren(children);
		const filteredChildren: FilteredChildren = ElementNode.filterChildren(this, flattenedChildren);
		const convertedChildren: Array<ContentNode> = ElementNode.convertChildren(this, filteredChildren.resolved);

		convertedChildren.forEach((node: ContentNode) => {
			node.remove();
			node.setParent(this);
			this.children.unshift(node);
		});

		if (Type.isArrayFilled(filteredChildren.unresolved))
		{
			this.propagateChild(...filteredChildren.unresolved);
		}
	}

	replaceChild(targetNode: ContentNode, ...children: Array<ContentNode | FragmentNode>)
	{
		this.children = this.children.flatMap((node: ContentNode) => {
			if (node === targetNode)
			{
				node.setParent(null);

				const flattenedChildren: Array<ContentNode> = Node.flattenChildren(children);
				const filteredChildren: FilteredChildren = ElementNode.filterChildren(this, flattenedChildren);
				const convertedChildren: Array<ContentNode> = ElementNode.convertChildren(this, filteredChildren.resolved);

				return convertedChildren.map((child: ContentNode) => {
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
		const value: ElementNodeValue = this.getValue();

		return value ? `=${value}` : '';
	}

	toStringAttributes(): string
	{
		return Object
			.entries(this.getAttributes())
			.map(([key: string, attrValue: string]) => {
				return attrValue ? `${key}=${attrValue}` : key;
			})
			.join(' ');
	}

	getNewLineBeforeContent(): string
	{
		if (!this.isInline())
		{
			const firstChild: ?ContentNode = this.getFirstChild();
			if (firstChild && !NodeType.isNewLine(firstChild))
			{
				return '\n';
			}
		}

		return '';
	}

	getNewLineAfterContent(): string
	{
		if (!this.isInline())
		{
			const lastChild: ?ContentNode = this.getLastChild();
			if (lastChild && !NodeType.isNewLine(lastChild))
			{
				return '\n';
			}
		}

		if (NodeType.isListItem(this))
		{
			const lastChild: ContentNode = this.getParent().getLastChild();
			if (lastChild !== this)
			{
				return '\n';
			}
		}

		return '';
	}

	getNewLineBeforeOpeningTag(): string
	{
		if (!this.isInline() && this.hasParent())
		{
			const previewsSibling: ContentNode = this.getPreviewsSibling();
			if (NodeType.isText(previewsSibling) || NodeType.isInline(previewsSibling))
			{
				return '\n';
			}
		}

		return '';
	}

	getNewLineAfterClosingTag(): string
	{
		if (!this.isInline() && this.hasParent())
		{
			const nextSibling: ContentNode = this.getNextSibling();
			if (nextSibling && nextSibling.getName() !== '#linebreak')
			{
				return '\n';
			}
		}

		return '';
	}

	getContent(): string
	{
		if (NodeType.isListItem(this))
		{
			return this.getChildren()
				.reduceRight((acc: Array<ContentNode>, node: ContentNode) => {
					if (!Type.isArrayFilled(acc) && (NodeType.isNewLine(node) || NodeType.isTab(node)))
					{
						return acc;
					}

					return [node.toString(), ...acc];
				}, []);
		}

		return this.getChildren()
			.map((child: ContentNode) => {
				return child.toString();
			})
			.join('');
	}

	getOpeningTag(): string
	{
		const tagName: string = this.getName();
		const tagValue: ElementNodeValue = this.toStringValue();
		const attributes: JsonObject = this.toStringAttributes();
		const formattedAttributes: string = Type.isStringFilled(attributes) ? ` ${attributes}` : '';

		return `[${tagName}${tagValue}${formattedAttributes}]`;
	}

	getClosingTag(): string
	{
		return `[/${this.getName()}]`;
	}

	toString(): string
	{
		const openingTag: string = this.getOpeningTag();

		if (this.isVoid())
		{
			return openingTag;
		}

		if (NodeType.isListItem(this))
		{
			return `${openingTag}${this.getContent()}${this.getNewLineAfterContent()}`;
		}

		if (this.isInline())
		{
			return `${openingTag}${this.getContent()}${this.getClosingTag()}`;
		}

		return [
			this.getNewLineBeforeOpeningTag(),
			openingTag,
			this.getNewLineBeforeContent(),
			this.getContent(),
			this.getNewLineAfterContent(),
			this.getClosingTag(),
			this.getNewLineAfterClosingTag(),
		].join('');
	}

	toJSON(): SerializedElementNode
	{
		return {
			...super.toJSON(),
			value: this.getValue(),
			attributes: this.getAttributes(),
			void: this.isVoid(),
			inline: this.isInline(),
		};
	}
}
