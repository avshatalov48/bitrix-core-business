/* eslint-disable no-underscore-dangle, @bitrix24/bitrix24-rules/no-pseudo-private */
import { Dom, Type, Loc, Event } from 'main.core';

import { $createSpoilerContentNode, $isSpoilerContentNode, SpoilerContentNode } from './spoiler-content-node';
import { $createSpoilerTitleNode, $isSpoilerTitleNode, SpoilerTitleNode } from './spoiler-title-node';

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
	type NodeKey,
	type SerializedElementNode,
	type LexicalEditor,
} from 'ui.lexical.core';
import { $createSpoilerTitleTextNode } from './spoiler-title-text-node';

type SerializedSpoilerNode = SerializedElementNode & { open: boolean };

export class SpoilerNode extends ElementNode
{
	__open: boolean;

	constructor(open: boolean, key?: NodeKey)
	{
		super(key);

		this.__open = open;
	}

	static getType(): string
	{
		return 'spoiler';
	}

	static clone(node: SpoilerNode): SpoilerNode
	{
		return new SpoilerNode(node.__open, node.__key);
	}

	createDOM(config: EditorConfig, editor: LexicalEditor): HTMLElement
	{
		const details = document.createElement('details');
		if (Type.isStringFilled(config?.theme?.spoiler?.container))
		{
			Dom.addClass(details, config.theme.spoiler.container);
		}

		details.open = this.__open;

		Event.bind(details, 'toggle', () => {
			const open = editor.getEditorState().read(() => this.getOpen());
			if (open !== details.open)
			{
				editor.update(() => this.toggleOpen());
			}
		});

		return details;
	}

	updateDOM(prevNode: SpoilerNode, dom: HTMLDetailsElement, config: EditorConfig): boolean
	{
		if (prevNode.__open !== this.__open)
		{
			dom.open = this.__open;
		}

		return false;
	}

	static importDOM(): DOMConversionMap<HTMLDetailsElement> | null
	{
		return {
			details: (domNode: HTMLDetailsElement) => {
				return {
					conversion: (details: HTMLDetailsElement): DOMConversionOutput | null => {
						const isOpen = Type.isBoolean(details.open) ? details.open : true;

						return { node: $createSpoiler(isOpen) };
					},
					priority: 1,
				};
			},
		};
	}

	static importJSON(serializedNode: SerializedSpoilerNode): SpoilerNode
	{
		return $createSpoilerNode(serializedNode.open);
	}

	exportDOM(editor: LexicalEditor): DOMExportOutput
	{
		const details = document.createElement('details');
		if (this.__open)
		{
			details.setAttribute('open', true);
		}

		return { element: details };
	}

	exportJSON(): SerializedSpoilerNode
	{
		return {
			...super.exportJSON(),
			open: this.__open,
			type: 'spoiler',
			version: 1,
		};
	}

	isShadowRoot(): boolean
	{
		return true;
	}

	canBeEmpty(): false
	{
		return false;
	}

	append(...nodesToAppend: LexicalNode[]): this
	{
		for (const node of nodesToAppend)
		{
			if ($isSpoilerTitleNode(node))
			{
				const titleNode: SpoilerTitleNode = node;
				if (this.getTitleNode() === null)
				{
					super.append(titleNode);
				}
				else
				{
					this.getTitleNode().clear();
					this.getTitleNode().append($createSpoilerTitleTextNode(node.getTextContent()));
				}
			}
			else if ($isSpoilerContentNode(node))
			{
				const contentNode: SpoilerContentNode = node;
				if (this.getContentNode() === null)
				{
					super.append(contentNode);
				}
				else
				{
					this.getContentNode().append(...contentNode.getChildren());
				}
			}
			else if ($isElementNode(node) || $isDecoratorNode(node))
			{
				this.getContentNode().append(node);
			}
			else
			{
				this.getContentNode().append($createParagraphNode().append(node));
			}
		}

		return this;
	}

	getTitleNode(): SpoilerTitleNode | null
	{
		return this.getChildren()[0] || null;
	}

	getContentNode(): SpoilerContentNode | null
	{
		return this.getChildren()[1] || null;
	}

	setOpen(open: boolean): void
	{
		const writable = this.getWritable();
		writable.__open = open;
	}

	getOpen(): boolean
	{
		return this.getLatest().__open;
	}

	toggleOpen(): void
	{
		this.setOpen(!this.getOpen());
	}
}

export function $createSpoiler(isOpen: boolean, title: string = Loc.getMessage('TEXT_EDITOR_SPOILER_TITLE')): SpoilerNode
{
	return $createSpoilerNode(isOpen).append(
		$createSpoilerTitleNode().append($createSpoilerTitleTextNode(title)),
		$createSpoilerContentNode(),
	);
}

export function $createSpoilerNode(isOpen: boolean): SpoilerNode
{
	return new SpoilerNode(isOpen);
}

export function $isSpoilerNode(node: LexicalNode | null | undefined): boolean
{
	return node instanceof SpoilerNode;
}
