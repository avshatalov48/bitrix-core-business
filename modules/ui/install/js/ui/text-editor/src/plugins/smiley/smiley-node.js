/* eslint-disable no-underscore-dangle,@bitrix24/bitrix24-rules/no-pseudo-private */

import { Dom, Type } from 'main.core';
import type { DecoratorOptions } from '../../types/decorator-options';

import {
	DecoratorNode,
	$applyNodeReplacement,
	type EditorConfig,
	type LexicalNode,
	type NodeKey,
	type DOMExportOutput,
	type LexicalEditor,
} from 'ui.lexical.core';

export type SerializedSmileyNode = {
	src: string;
	typing: string;
	width?: number;
	height?: number;
	type: 'smiley',
	version: number,
};

export class SmileyNode extends DecoratorNode
{
	__src: string;
	__typing: string;
	__width: number = null;
	__height: number = null;

	static getType(): string
	{
		return 'smiley';
	}

	static clone(node: SmileyNode): SmileyNode
	{
		return new SmileyNode(
			node.__src,
			node.__typing,
			node.__width,
			node.__height,
			node.__key,
		);
	}

	constructor(
		src: string,
		typing: string,
		width?: number,
		height?: number,
		key?: NodeKey,
	)
	{
		super(key);
		this.__src = src;
		this.__typing = typing;

		if (Type.isNumber(width))
		{
			this.__width = width;
		}

		if (Type.isNumber(height))
		{
			this.__height = height;
		}
	}

	getSrc(): string
	{
		return this.__src;
	}

	getTyping(): string
	{
		return this.__typing;
	}

	getWidth(): null | number
	{
		return this.__width;
	}

	getHeight(): null | number
	{
		return this.__height;
	}

	createDOM(config: EditorConfig): HTMLElement
	{
		const img: HTMLImageElement = document.createElement('img');
		img.src = encodeURI(this.__src);
		if (this.getWidth() > 0 && this.getHeight() > 0)
		{
			Dom.style(img, {
				width: `${this.getWidth()}px`,
				height: `${this.getHeight()}px`,
			});
		}

		if (Type.isStringFilled(config?.theme?.smiley))
		{
			Dom.addClass(img, config.theme.smiley);
		}

		Dom.attr(img, { draggable: false });

		return img;
	}

	updateDOM(prevNode: TextNode, dom: HTMLElement, config: EditorConfig): boolean
	{
		return false;
	}

	static importJSON(serializedNode: SerializedSmileyNode): SmileyNode
	{
		const { src, typing, width, height } = serializedNode;

		return $createSmileyNode(src, typing, width, height);
	}

	exportDOM(): DOMExportOutput
	{
		const span = document.createElement('span');
		span.textContent = this.getTyping();

		return { element: span };
	}

	exportJSON(): SerializedSmileyNode
	{
		return {
			src: this.getSrc(),
			typing: this.getTyping(),
			width: this.getWidth(),
			height: this.getHeight(),
			type: 'smiley',
			version: 1,
		};
	}

	decorate(editor: LexicalEditor, config: EditorConfig): DecoratorOptions
	{
		return {};
	}

	getTextContent(): string
	{
		return this.getTyping();
	}

	isInline(): true
	{
		return true;
	}

	isKeyboardSelectable(): boolean
	{
		return false;
	}

	isIsolated(): boolean
	{
		return false;
	}
}

export function $isSmileyNode(node: LexicalNode | null | undefined): boolean
{
	return node instanceof SmileyNode;
}

export function $createSmileyNode(src: string, typing: string, width: number, height: number): SmileyNode
{
	const node = new SmileyNode(src, typing, width, height);
	// node.setMode('token');
	// node.setDetail('unmergeable');

	return $applyNodeReplacement(node);
}
