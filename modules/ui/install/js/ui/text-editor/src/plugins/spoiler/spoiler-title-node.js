/*
eslint-disable no-underscore-dangle,
@bitrix24/bitrix24-rules/no-pseudo-private,
@bitrix24/bitrix24-rules/no-native-dom-methods
*/

import { Dom, Type } from 'main.core';
import { UNFORMATTED } from '../../constants';

import { $isSpoilerContentNode, SpoilerContentNode } from './spoiler-content-node';
import { $createSpoilerNode, $isSpoilerNode, type SpoilerNode } from './spoiler-node';
import { trimSpoilerTitle } from './spoiler-plugin';

import {
	ElementNode,
	$createParagraphNode,
	$isElementNode,
	$isDecoratorNode,
	type EditorConfig,
	type LexicalNode,
	type DOMConversionMap,
	type DOMConversionOutput,
	type DOMExportOutput,
	type RangeSelection,
	type SerializedElementNode,
	type LexicalEditor,
} from 'ui.lexical.core';
import { $createSpoilerTitleTextNode } from './spoiler-title-text-node';

type SerializedSpoilerTitleNode = SerializedElementNode;

export function convertSummaryElement(domNode: HTMLElement): DOMConversionOutput | null
{
	const node = $createSpoilerTitleNode();

	return { node };
}

export class SpoilerTitleNode extends ElementNode
{
	__language: string = 'hack';
	__flags: number = UNFORMATTED;

	static getType(): string
	{
		return 'spoiler-title';
	}

	static clone(node: SpoilerTitleNode): SpoilerTitleNode
	{
		return new SpoilerTitleNode(node.__key);
	}

	createDOM(config: EditorConfig, editor: LexicalEditor): HTMLElement
	{
		const dom = document.createElement('summary');

		if (Type.isStringFilled(config?.theme?.spoiler?.title))
		{
			Dom.addClass(dom, config.theme.spoiler.title);
		}

		Dom.addClass(dom, 'ui-icon-set__scope');

		return dom;
	}

	updateDOM(prevNode: SpoilerTitleNode, dom: HTMLElement, config: EditorConfig): boolean
	{
		return false;
	}

	static importDOM(): DOMConversionMap | null
	{
		return {
			summary: (domNode: HTMLElement) => {
				return {
					conversion: convertSummaryElement,
					priority: 1,
				};
			},
		};
	}

	static importJSON(serializedNode: SerializedSpoilerTitleNode): SpoilerTitleNode
	{
		return $createSpoilerTitleNode();
	}

	exportDOM(): DOMExportOutput
	{
		const element = document.createElement('summary');

		return { element };
	}

	exportJSON(): SerializedSpoilerTitleNode
	{
		return {
			...super.exportJSON(),
			type: 'spoiler-title',
			version: 1,
		};
	}

	collapseAtStart(selection: RangeSelection): boolean
	{
		const spoilerNode: SpoilerNode = this.getParent();
		if (!$isSpoilerNode(spoilerNode))
		{
			return false;
		}

		return $removeSpoiler(spoilerNode);
	}

	insertNewAfter(selection: RangeSelection, restoreSelection = true): ElementNode
	{
		const containerNode: SpoilerNode = this.getParentOrThrow();

		if (!$isSpoilerNode(containerNode))
		{
			throw new Error(
				'SpoilerTitleNode expects to be child of SpoilerNode',
			);
		}

		if (containerNode.getOpen())
		{
			const contentNode: SpoilerContentNode = this.getNextSibling();
			if (!$isSpoilerContentNode(contentNode))
			{
				throw new Error(
					'SpoilerTitleNode expects to have SpoilerContentNode sibling',
				);
			}

			const firstChild = contentNode.getFirstChild();
			if ($isElementNode(firstChild) || $isDecoratorNode(firstChild))
			{
				return firstChild;
			}

			const paragraph = $createParagraphNode();
			contentNode.append(paragraph);

			return paragraph;
		}

		const paragraph = $createParagraphNode();
		containerNode.insertAfter(paragraph, restoreSelection);

		return paragraph;
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

	insertAfter(nodeToInsert: LexicalNode): LexicalNode
	{
		const textContent = nodeToInsert.getTextContent();
		this.clear();
		this.append($createSpoilerTitleTextNode(trimSpoilerTitle(textContent)));

		return this;
	}
}

export function $createSpoilerTitleNode(): SpoilerTitleNode
{
	return new SpoilerTitleNode();
}

export function $isSpoilerTitleNode(node: LexicalNode | null | undefined): boolean
{
	return node instanceof SpoilerTitleNode;
}

export function $removeSpoiler(spoilerNode: SpoilerNode): boolean
{
	if (!$isSpoilerNode(spoilerNode))
	{
		return false;
	}

	const contentNode: SpoilerContentNode = spoilerNode.getContentNode();
	let lastElement = spoilerNode;

	if (contentNode !== null)
	{
		for (const child of contentNode.getChildren())
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
	}

	spoilerNode.remove();

	return true;
}
