/*
eslint-disable no-underscore-dangle,
@bitrix24/bitrix24-rules/no-pseudo-private,
@bitrix24/bitrix24-rules/no-native-dom-methods
*/

import { Dom, Type } from 'main.core';

import { type SpoilerNode, $createSpoilerNode } from './spoiler-node';

import {
	ElementNode,
	$isElementNode,
	$isDecoratorNode,
	$createParagraphNode,
	type EditorConfig,
	type LexicalNode,
	type DOMConversionMap,
	type DOMConversionOutput,
	type DOMExportOutput,
	type SerializedElementNode,
	type LexicalEditor,
} from 'ui.lexical.core';

type SerializedSpoilerContentNode = SerializedElementNode;

export function convertSpoilerContentElement(domNode: HTMLElement): DOMConversionOutput | null
{
	const node = $createSpoilerContentNode();

	return { node };
}

export class SpoilerContentNode extends ElementNode
{
	static getType(): string
	{
		return 'spoiler-content';
	}

	static clone(node: SpoilerContentNode): SpoilerContentNode
	{
		return new SpoilerContentNode(node.__key);
	}

	createDOM(config: EditorConfig, editor: LexicalEditor): HTMLElement
	{
		const dom = document.createElement('div');

		if (Type.isStringFilled(config?.theme?.spoiler?.content))
		{
			Dom.addClass(dom, config.theme.spoiler.content);
		}

		return dom;
	}

	updateDOM(prevNode: SpoilerContentNode, dom: HTMLElement, config: EditorConfig): boolean
	{
		return false;
	}

	static importDOM(): DOMConversionMap | null
	{
		return {
			div: (domNode: HTMLElement) => {
				if (!domNode.hasAttribute('data-spoiler-content'))
				{
					return null;
				}

				return {
					conversion: convertSpoilerContentElement,
					priority: 2,
				};
			},
		};
	}

	static importJSON(serializedNode: SerializedSpoilerContentNode): SpoilerContentNode
	{
		return $createSpoilerContentNode();
	}

	exportDOM(): DOMExportOutput
	{
		const element = document.createElement('div');
		element.setAttribute('data-spoiler-content', 'true');

		return { element };
	}

	exportJSON(): SerializedSpoilerContentNode
	{
		return {
			...super.exportJSON(),
			type: 'spoiler-content',
			version: 1,
		};
	}

	isShadowRoot(): boolean
	{
		return true;
	}

	isParentRequired(): boolean
	{
		return true;
	}

	createParentElementNode(): SpoilerNode
	{
		return $createSpoilerNode();
	}

	canIndent(): false
	{
		return false;
	}

	canInsertAfter(node: LexicalNode): false
	{
		return false;
	}

	canReplaceWith(replacement: LexicalNode): false
	{
		return false;
	}

	insertBefore(node: LexicalNode): LexicalNode
	{
		const firstChild = this.getFirstChild();
		const nodeToInsert = (
			$isElementNode(node) || $isDecoratorNode(node)
				? node
				: $createParagraphNode().append(node)
		);

		if (firstChild === null)
		{
			this.append(nodeToInsert);
		}
		else
		{
			firstChild.insertBefore(nodeToInsert);
		}

		return nodeToInsert;
	}

	insertAfter(node: LexicalNode): LexicalNode
	{
		const nodeToInsert = (
			$isElementNode(node) || $isDecoratorNode(node)
				? node
				: $createParagraphNode().append(node)
		);

		this.append(nodeToInsert);

		return nodeToInsert;
	}
}

export function $createSpoilerContentNode(): SpoilerContentNode
{
	return new SpoilerContentNode();
}

export function $isSpoilerContentNode(node: LexicalNode | null | undefined): boolean
{
	return node instanceof SpoilerContentNode;
}
