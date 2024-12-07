/* eslint-disable no-underscore-dangle, @bitrix24/bitrix24-rules/no-pseudo-private */

import {
	$createParagraphNode,
	$isTextNode,
	$hasUpdateTag,
	$isRootNode,
	ParagraphNode,
	type RangeSelection,
	type NodeKey,
	type SerializedParagraphNode,
	type DOMConversionMap,
	type DOMConversionOutput,
	type DOMConversion,
} from 'ui.lexical.core';

import { NewLineMode } from '../../constants';
import type { NewLineModeType } from '../../types/new-line-mode-type';

export type SerializedCustomParagraphNode = SerializedParagraphNode & {
	mode: NewLineModeType,
}

export class CustomParagraphNode extends ParagraphNode
{
	__mode: NewLineModeType = NewLineMode.MIXED;

	constructor(__mode: NewLineModeType, key?: NodeKey)
	{
		super(key);
		this.__mode = __mode;
	}

	static getType(): string
	{
		return 'custom-paragraph';
	}

	static clone(node: CustomParagraphNode): CustomParagraphNode
	{
		return new CustomParagraphNode(node.__mode, node.__key);
	}

	insertNewAfter(selection: RangeSelection, restoreSelection: boolean): ParagraphNode
	{
		if (this.__mode === NewLineMode.PARAGRAPH)
		{
			return super.insertNewAfter(selection, restoreSelection);
		}

		if (this.__mode === NewLineMode.MIXED)
		{
			const children = this.getChildren();
			const childrenLength = children.length;

			if (
				childrenLength >= 1
				&& children[childrenLength - 1].getTextContent() === '\n'
				&& selection.isCollapsed()
				&& selection.anchor.key === this.__key
				&& selection.anchor.offset === childrenLength
			)
			{
				children[childrenLength - 1].remove();
				const newElement = $createParagraphNode();
				this.insertAfter(newElement, restoreSelection);

				return newElement;
			}

			if ($hasUpdateTag('paste'))
			{
				return super.insertNewAfter(selection, restoreSelection);
			}
		}

		selection.insertLineBreak();

		return null;
	}

	// createDOM(config) {
	// 	const dom = super.createDOM(config);
	// 	dom.style = "border: 1px dashed tomato";
	//
	// 	return dom;
	// }

	exportJSON(): SerializedCustomParagraphNode
	{
		return {
			...super.exportJSON(),
			mode: this.__mode,
			type: 'custom-paragraph',
			version: 1,
		};
	}

	static importDOM(): DOMConversionMap | null
	{
		return {
			p: (node: Node): DOMConversion => ({
				conversion: (element: HTMLElement): DOMConversionOutput => {
					return { node: $createParagraphNode() };
				},
				priority: 1,
			}),
			h1: (node: Node): DOMConversion => ({
				conversion: convertHeadingElement,
				priority: 1,
			}),
			h2: (node: Node): DOMConversion => ({
				conversion: convertHeadingElement,
				priority: 1,
			}),
			h3: (node: Node): DOMConversion => ({
				conversion: convertHeadingElement,
				priority: 1,
			}),
			h4: (node: Node): DOMConversion => ({
				conversion: convertHeadingElement,
				priority: 1,
			}),
			h5: (node: Node): DOMConversion => ({
				conversion: convertHeadingElement,
				priority: 1,
			}),
			h6: (node: Node): DOMConversion => ({
				conversion: convertHeadingElement,
				priority: 1,
			}),
		};
	}

	collapseAtStart(): boolean
	{
		const children = this.getChildren();
		// If we have an empty (trimmed) first paragraph and try and remove it,
		// delete the paragraph as long as we have another sibling to go to
		if (
			children.length === 0
			|| ($isTextNode(children[0]) && children[0].getTextContent().trim() === '')
		)
		{
			const nextSibling = this.getNextSibling();
			if (nextSibling !== null)
			{
				this.selectNext();
				this.remove();

				return true;
			}

			const prevSibling = this.getPreviousSibling();
			if (prevSibling !== null)
			{
				this.selectPrevious();
				this.remove();

				return true;
			}

			const parentNode = this.getParent();
			if (
				parentNode !== null
				&& !$isRootNode(parentNode)
				&& Object.getPrototypeOf(parentNode).hasOwnProperty('collapseAtStart'))
			{
				return parentNode.collapseAtStart();
			}
		}

		return false;
	}

	static importJSON(serializedParagraphNode: SerializedCustomParagraphNode): ParagraphNode
	{
		return super.importJSON(serializedParagraphNode);
	}
}

function convertHeadingElement(element: HTMLElement): DOMConversionOutput
{
	return {
		node: $createParagraphNode(),
		forChild: (lexicalNode) => {
			if ($isTextNode(lexicalNode))
			{
				lexicalNode.toggleFormat('bold');
			}

			return lexicalNode;
		},
	};
}
