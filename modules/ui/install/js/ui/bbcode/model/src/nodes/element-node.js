import { Type, type JsonObject, type JsonValue } from 'main.core';
import { Node, type NodeOptions, type ContentNode, type SerializedNode, privateMap } from './node';
import { typeof FragmentNode } from './fragment-node';
import { Tag } from '../reference/tag';
import { BBCodeScheme } from '../scheme/scheme';
import { Text } from '../reference/text';

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
			inline: Tag.isInline(this.getName()),
			...options,
		};
		this.setInline(preparedOptions.inline);
		this.setValue(preparedOptions.value);
		this.setVoid(preparedOptions.void);
		this.setAttributes(preparedOptions.attributes);
	}

	filterChildren(children: Array<ContentNode>): FilteredChildren
	{
		const filteredChildren = { resolved: [], unresolved: [] };
		const byTagFilter = this.getScheme().getChildFilter(this.getName());
		if (byTagFilter)
		{
			return children.reduce((acc: typeof filteredChildren, child: ContentNode) => {
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

		if (this.isInline())
		{
			const inlineChildFilter = this.getScheme().getChildFilter('#inline');

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

	convertChildren(children: Array<ContentNode>): Array<ContentNode>
	{
		const childConverter = this.getScheme().getChildConverter(this.getName());
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

	appendChild(...children: Array<ContentNode | FragmentNode>)
	{
		const flattenedChildren: Array<ContentNode> = Node.flattenChildren(children);
		const filteredChildren: FilteredChildren = this.filterChildren(flattenedChildren);
		const convertedChildren: Array<ContentNode> = this.convertChildren(filteredChildren.resolved);

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
		const filteredChildren: FilteredChildren = this.filterChildren(flattenedChildren);
		const convertedChildren: Array<ContentNode> = this.convertChildren(filteredChildren.resolved);

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
				const filteredChildren: FilteredChildren = this.filterChildren(flattenedChildren);
				const convertedChildren: Array<ContentNode> = this.convertChildren(filteredChildren.resolved);

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
				const preparedKey: string = this.prepareCase(key);

				return attrValue ? `${preparedKey}=${attrValue}` : preparedKey;
			})
			.join(' ');
	}

	getNewLineAfterOpeningTag(): string
	{
		if (
			!this.isInline()
			&& this.getScheme().isAllowNewLineAfterBlockOpeningTag()
		)
		{
			const firstChild: ?ContentNode = this.getFirstChild();
			if (firstChild && firstChild.getName() !== '#linebreak')
			{
				return '\n';
			}
		}

		return '';
	}

	getNewLineBeforeClosingTag(): string
	{
		const scheme: BBCodeScheme = this.getScheme();
		if (scheme.isAllowNewLineBeforeBlockClosingTag())
		{
			if (!this.isInline())
			{
				const lastChild: ?ContentNode = this.getLastChild();
				if (lastChild && lastChild.getName() !== '#linebreak')
				{
					return '\n';
				}
			}

			if (
				Tag.isListItem(this.getName())
				&& scheme.isAllowNewLineAfterListItem()
			)
			{
				const lastChild: ContentNode = this.getParent().getLastChild();
				if (lastChild !== this)
				{
					return '\n';
				}
			}
		}

		return '';
	}

	getNewLineBeforeOpeningTag(): string
	{
		if (
			!this.isInline()
			&& this.hasParent()
			&& this.getScheme().isAllowNewLineBeforeBlockOpeningTag()
		)
		{
			const previewsSibling: ContentNode = this.getPreviewsSibling();
			if (
				previewsSibling
				&& (
					Text.isPlainTextNode(previewsSibling)
					|| Tag.isInline(previewsSibling.getName())
				)
			)
			{
				return '\n';
			}
		}

		return '';
	}

	getNewLineAfterClosingTag(): string
	{
		if (
			!this.isInline()
			&& this.hasParent()
			&& this.getScheme().isAllowNewLineAfterBlockClosingTag()
		)
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
		if (Tag.isListItem(this.getName()))
		{
			return this.getChildren()
				.reduceRight((acc: Array<ContentNode>, node: ContentNode) => {
					if (!Type.isArrayFilled(acc) && (node.getName() === '#linebreak' || node.getName() === '#tab'))
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
		const displayedName: string = this.getDisplayedName();
		const tagValue: ElementNodeValue = this.toStringValue();
		const attributes: JsonObject = this.toStringAttributes();
		const formattedAttributes: string = Type.isStringFilled(attributes) ? ` ${attributes}` : '';

		return `[${displayedName}${tagValue}${formattedAttributes}]`;
	}

	getClosingTag(): string
	{
		return `[/${this.getDisplayedName()}]`;
	}

	clone(options: { deep: boolean } = {}): ElementNode
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

		return new ElementNode({
			name: this.getName(),
			void: this.isVoid(),
			inline: this.isInline(),
			value: this.getValue(),
			attributes: { ...this.getAttributes() },
			scheme: this.getScheme(),
			children,
		});
	}

	toString(): string
	{
		const openingTag: string = this.getOpeningTag();

		if (this.isVoid())
		{
			return openingTag;
		}

		if (Tag.isListItem(this.getName()))
		{
			return `${openingTag}${this.getContent()}${this.getNewLineBeforeClosingTag()}`;
		}

		if (this.isInline())
		{
			return `${openingTag}${this.getContent()}${this.getClosingTag()}`;
		}

		return [
			this.getNewLineBeforeOpeningTag(),
			openingTag,
			this.getNewLineAfterOpeningTag(),
			this.getContent(),
			this.getNewLineBeforeClosingTag(),
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
