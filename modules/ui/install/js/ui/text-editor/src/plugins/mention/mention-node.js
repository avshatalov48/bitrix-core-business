/* eslint-disable no-underscore-dangle, @bitrix24/bitrix24-rules/no-pseudo-private */

import { Type, Dom } from 'main.core';

import {
	$applyNodeReplacement,
	ElementNode,
	$isRangeSelection,
	$createParagraphNode,
	type DOMConversionMap,
	type DOMConversionOutput,
	type DOMExportOutput,
	type EditorConfig,
	type LexicalNode,
	type NodeKey,
	type SerializedElementNode,
	type LexicalEditor,
	type RangeSelection,
} from 'ui.lexical.core';

export type SerializedMentionNode = {
	mentionName: string,
	entityId: string,
	id: string | number,
} & SerializedElementNode;

export class MentionNode extends ElementNode
{
	__id: string | number;
	__entityId: string;

	constructor(
		entityId: string,
		id: string | number,
		key?: NodeKey,
	)
	{
		super(key);

		this.__entityId = entityId;
		this.__id = id;
	}

	static getType(): string
	{
		return 'mention';
	}

	static clone(node: MentionNode): MentionNode
	{
		return new MentionNode(
			node.__entityId,
			node.__id,
			node.__key,
		);
	}

	getId(): string | number
	{
		const self = this.getLatest();

		return self.__id;
	}

	getEntityId(): string
	{
		const self = this.getLatest();

		return self.__entityId;
	}

	static importJSON(serializedNode: SerializedMentionNode): MentionNode
	{
		const node: MentionNode = $createMentionNode(
			serializedNode.entityId,
			serializedNode.id,
		);

		node.setFormat(serializedNode.format);
		node.setDirection(serializedNode.direction);

		return node;
	}

	static importDOM(): DOMConversionMap | null
	{
		return {
			span: (domNode: HTMLElement) => {
				if (!domNode.hasAttribute('data-mention-id'))
				{
					return null;
				}

				return {
					conversion: convertMentionElement,
					priority: 1,
				};
			},
			a: (domNode: HTMLElement) => {
				if (!domNode.hasAttribute('data-mention-id'))
				{
					return null;
				}

				return {
					conversion: convertMentionElement,
					priority: 1,
				};
			},
		};
	}

	exportDOM(): DOMExportOutput
	{
		const element = document.createElement('span');
		element.setAttribute('data-mention-entity-id', this.__entityId);
		element.setAttribute('data-mention-id', this.__id.toString());

		return { element };
	}

	exportJSON(): SerializedMentionNode
	{
		return {
			...super.exportJSON(),
			entityId: this.__entityId,
			id: this.__id,
			type: 'mention',
			version: 1,
		};
	}

	createDOM(config: EditorConfig, editor: LexicalEditor): HTMLSpanElement
	{
		const element = document.createElement('span');
		if (Type.isStringFilled(config?.theme?.mention))
		{
			Dom.addClass(element, config.theme.mention);
		}

		return element;
	}

	updateDOM(prevNode: MentionNode, anchor: HTMLElement, config: EditorConfig): boolean
	{
		return false;
	}

	canInsertTextBefore(): false
	{
		return false;
	}

	canInsertTextAfter(): false
	{
		return false;
	}

	canBeEmpty(): false
	{
		return false;
	}

	isInline(): true
	{
		return true;
	}

	insertNewAfter(selection: RangeSelection, restoreSelection: boolean): ParagraphNode
	{
		const newElement = $createParagraphNode();
		const direction = this.getDirection();
		newElement.setDirection(direction);
		this.insertAfter(newElement, restoreSelection);

		return newElement;
	}

	extractWithChild(
		child: LexicalNode,
		selection: RangeSelection,
		destination: 'clone' | 'html',
	): boolean
	{
		if (!$isRangeSelection(selection))
		{
			return false;
		}

		const anchor = selection.anchor;
		const focus = selection.focus;
		const anchorNode = anchor.getNode();
		const focusNode = focus.getNode();
		const isBackward: boolean = selection.isBackward();
		const selectionLength: number = (
			isBackward
				? anchor.offset - focus.offset
				: focus.offset - anchor.offset
		);

		return (
			this.isParentOf(anchorNode)
			&& this.isParentOf(focusNode)
			&& this.getTextContent().length === selectionLength
		);
	}
}

function convertMentionElement(domNode: HTMLElement): DOMConversionOutput | null
{
	const textContent = domNode.textContent;
	if (textContent !== null)
	{
		const { mentionEntityId, mentionId } = domNode.dataset;
		const node = $createMentionNode(mentionEntityId, mentionId);

		return {
			node,
		};
	}

	return null;
}

export function $createMentionNode(entityId: string, id: string | number): MentionNode
{
	const mentionNode: MentionNode = new MentionNode(entityId, id);

	return $applyNodeReplacement(mentionNode);
}

export function $isMentionNode(node: LexicalNode | null | undefined): boolean
{
	return node instanceof MentionNode;
}
