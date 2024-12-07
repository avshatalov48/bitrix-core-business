/* eslint-disable no-underscore-dangle, @bitrix24/bitrix24-rules/no-pseudo-private */

import { UNFORMATTED } from '../../constants';

import {
	TextNode,
	$applyNodeReplacement,
	type EditorConfig,
	type LexicalNode,
	type NodeKey,
	type SerializedTextNode,
	type EditorThemeClasses,
} from 'ui.lexical.core';

import { addClassNamesToElement, removeClassNamesFromElement } from 'ui.lexical.utils';

import { $createCodeNode, type CodeNode } from './code-node';

type SerializedCodeTokenNode = { highlightType: string | null | undefined } & SerializedTextNode;

export class CodeTokenNode extends TextNode
{
	/** @internal */
	__highlightType: string | null | undefined;
	__flags: number = UNFORMATTED;

	constructor(text: string, highlightType?: string | null | undefined, key?: NodeKey)
	{
		super(text, key);
		this.__highlightType = highlightType;
	}

	static getType(): string
	{
		return 'code-token';
	}

	static clone(node: CodeTokenNode): CodeTokenNode
	{
		return new CodeTokenNode(
			node.__text,
			node.__highlightType || undefined,
			node.__key,
		);
	}

	getHighlightType(): string | null | undefined
	{
		const self = this.getLatest();

		return self.__highlightType;
	}

	createDOM(config: EditorConfig): HTMLElement
	{
		const element = super.createDOM(config);
		const className = getHighlightThemeClass(
			config.theme,
			this.__highlightType,
		);

		addClassNamesToElement(element, className);

		return element;
	}

	updateDOM(prevNode: CodeTokenNode, dom: HTMLElement, config: EditorConfig): boolean
	{
		const update = super.updateDOM(prevNode, dom, config);
		const prevClassName = getHighlightThemeClass(config.theme, prevNode.__highlightType);
		const nextClassName = getHighlightThemeClass(config.theme, this.__highlightType);
		if (prevClassName !== nextClassName)
		{
			if (prevClassName)
			{
				removeClassNamesFromElement(dom, prevClassName);
			}

			if (nextClassName)
			{
				addClassNamesToElement(dom, nextClassName);
			}
		}

		return update;
	}

	static importJSON(serializedNode: SerializedCodeTokenNode): CodeTokenNode
	{
		const node = $createCodeTokenNode(serializedNode.text, serializedNode.highlightType);
		node.setFormat(serializedNode.format);
		node.setDetail(serializedNode.detail);
		node.setMode(serializedNode.mode);
		node.setStyle(serializedNode.style);

		return node;
	}

	exportJSON(): SerializedCodeTokenNode
	{
		return {
			...super.exportJSON(),
			highlightType: this.getHighlightType(),
			type: 'code-token',
			version: 1,
		};
	}

	// Prevent formatting (bold, underline, etc)
	setFormat(format: number): this
	{
		return this;
	}

	isParentRequired(): true
	{
		return true;
	}

	createParentElementNode(): CodeNode
	{
		return $createCodeNode();
	}
}

function getHighlightThemeClass(
	theme: EditorThemeClasses,
	highlightType: string | null | undefined,
): string | null | undefined
{
	return (
		highlightType
		&& theme
		&& theme.codeHighlight
		&& theme.codeHighlight[highlightType]
	);
}

export function $createCodeTokenNode(text: string, highlightType?: string | null | undefined): CodeTokenNode
{
	return $applyNodeReplacement(new CodeTokenNode(text, highlightType));
}

export function $isCodeTokenNode(node: LexicalNode | CodeTokenNode | null | undefined): boolean
{
	return node instanceof CodeTokenNode;
}
