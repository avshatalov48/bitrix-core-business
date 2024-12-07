/* eslint-disable no-underscore-dangle */

import { Type, Dom } from 'main.core';

import {
	ElementNode,
	$applyNodeReplacement,
	$createParagraphNode,
	$isElementNode,
	$isDecoratorNode,
	type EditorConfig,
	type LexicalNode,
	type SerializedElementNode,
	type LexicalEditor,
	type RangeSelection,
	type ParagraphNode,
	type DOMConversionMap,
	type DOMConversionOutput,
} from 'ui.lexical.core';

import { $isParagraphEmpty } from '../../helpers/is-paragraph-empty';

export class QuoteNode extends ElementNode
{
	static getType(): string
	{
		return 'quote';
	}

	static clone(node: QuoteNode): QuoteNode
	{
		return new QuoteNode(node.__key);
	}

	createDOM(config: EditorConfig, editor: LexicalEditor): HTMLElement
	{
		const element = document.createElement('blockquote');
		element.setAttribute('spellcheck', 'false');

		if (Type.isStringFilled(config?.theme?.quote))
		{
			Dom.addClass(element, config.theme.quote);
		}

		return element;
	}

	updateDOM(prevNode: QuoteNode, anchor: HTMLElement, config: EditorConfig): boolean
	{
		return false;
	}

	static importDOM(): DOMConversionMap | null
	{
		return {
			blockquote: (node: Node) => ({
				conversion: (element: HTMLElement): DOMConversionOutput => {
					return { node: $createQuoteNode() };
				},
				priority: 0,
			}),
		};
	}

	static importJSON(serializedNode: SerializedElementNode): QuoteNode
	{
		const node = $createQuoteNode();
		node.setFormat(serializedNode.format);
		node.setIndent(serializedNode.indent);
		node.setDirection(serializedNode.direction);

		return node;
	}

	exportJSON(): SerializedElementNode
	{
		return {
			...super.exportJSON(),
			type: 'quote',
		};
	}

	canIndent(): false
	{
		return false;
	}

	isInline(): false
	{
		return false;
	}

	canReplaceWith(replacement: LexicalNode): boolean
	{
		return false;
	}

	collapseAtStart(selection: RangeSelection): true
	{
		// const paragraph = $createParagraphNode();
		// const children = this.getChildren();
		// children.forEach((child) => paragraph.append(child));
		// this.replace(paragraph);
		$removeQuote(this);

		return true;
	}

	canBeEmpty(): false
	{
		return false;
	}

	isShadowRoot(): boolean
	{
		return true;
	}

	// insertNewAfter(selection: RangeSelection, restoreSelection = true): null | ParagraphNode
	// {
	// 	const children = this.getChildren();
	// 	const childrenLength = children.length;
	//
	// 	if (
	// 		childrenLength >= 2
	// 		&& children[childrenLength - 1].getTextContent() === '\n'
	// 		&& children[childrenLength - 2].getTextContent() === '\n'
	// 		&& selection.isCollapsed()
	// 		&& selection.anchor.key === this.__key
	// 		&& selection.anchor.offset === childrenLength
	// 	)
	// 	{
	// 		children[childrenLength - 1].remove();
	// 		children[childrenLength - 2].remove();
	// 		const newElement = $createParagraphNode();
	// 		this.insertAfter(newElement, restoreSelection);
	//
	// 		return newElement;
	// 	}
	//
	// 	selection.insertLineBreak();
	//
	// 	return null;
	// }
}

export function $createQuoteNode(): QuoteNode
{
	return $applyNodeReplacement(new QuoteNode());
}

export function $isQuoteNode(node: LexicalNode | null | undefined): boolean
{
	return node instanceof QuoteNode;
}

export function $removeQuote(quoteNode: QuoteNode): boolean
{
	if (!$isQuoteNode(quoteNode))
	{
		return false;
	}

	let lastElement = quoteNode;
	for (const child of quoteNode.getChildren())
	{
		if ($isElementNode(child) || $isDecoratorNode(child))
		{
			lastElement = lastElement.insertAfter(child);
		}
		else
		{
			lastElement = lastElement.insertAfter($createParagraphNode().append(child));
		}
	}

	quoteNode.remove();

	return true;
}
