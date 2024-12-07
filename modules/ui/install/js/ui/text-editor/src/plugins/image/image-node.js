/* eslint-disable no-underscore-dangle, @bitrix24/bitrix24-rules/no-pseudo-private */

import { Type } from 'main.core';
import { validateImageUrl } from '../../helpers/validate-image-url';
import type { DecoratorOptions } from '../../types/decorator-options';
import ImageComponent from './image-component';

import {
	DecoratorNode,
	$applyNodeReplacement,
	type DOMConversionMap,
	type DOMConversionOutput,
	type DOMExportOutput,
	type EditorConfig,
	type LexicalNode,
	type LexicalEditor,
	type NodeKey,
	type SerializedLexicalNode,
} from 'ui.lexical.core';

export interface ImagePayload {
	src: string;
	width?: number;
	height?: number;
	maxWidth?: number;
	key?: NodeKey;
}

export type SerializedImageNode = SerializedLexicalNode & {
	src: string;
	width?: number;
	height?: number;
	maxWidth?: number;
};

export class ImageNode extends DecoratorNode
{
	__src: string;
	__width: 'inherit' | number = 'inherit';
	__height: 'inherit' | number = 'inherit';
	__maxWidth: number = 'none';

	constructor(
		src: string,
		width?: 'inherit' | number,
		height?: 'inherit' | number,
		maxWidth?: number,
		key?: NodeKey,
	)
	{
		super(key);

		if (validateImageUrl(src))
		{
			this.__src = src;
		}
		else
		{
			this.__src = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
		}

		if (Type.isNumber(width))
		{
			this.__width = Math.round(width);
		}

		if (Type.isNumber(height))
		{
			this.__height = Math.round(height);
		}

		if (Type.isNumber(maxWidth))
		{
			this.__maxWidth = Math.round(maxWidth);
		}
	}

	static useDecoratorComponent = true;

	static getType(): string
	{
		return 'image';
	}

	static clone(node: ImageNode): ImageNode
	{
		return new ImageNode(
			node.__src,
			node.__width,
			node.__height,
			node.__maxWidth,
			node.__key,
		);
	}

	static importJSON(serializedNode: SerializedImageNode): ImageNode
	{
		const { width, height, src, maxWidth } = serializedNode;

		return $createImageNode({ src, width, height, maxWidth });
	}

	exportDOM(): DOMExportOutput
	{
		const element = document.createElement('img');
		element.setAttribute('src', this.__src);
		element.setAttribute('width', this.__width.toString());
		element.setAttribute('height', this.__height.toString());

		return { element };
	}

	static importDOM(): DOMConversionMap | null
	{
		return {
			img: (node: Node) => ({
				conversion: (domNode: HTMLImageElement): null | DOMConversionOutput => {
					if (domNode instanceof HTMLImageElement && validateImageUrl(domNode.src))
					{
						const { src, width, height } = domNode;
						const imageNode = $createImageNode({ src, width, height });

						return { node: imageNode };
					}

					return null;
				},
				priority: 0,
			}),
		};
	}

	exportJSON(): SerializedImageNode
	{
		return {
			src: this.getSrc(),
			width: this.getWidth(),
			height: this.getHeight(),
			maxWidth: this.getMaxWidth(),
			type: 'image',
			version: 1,
		};
	}

	setWidthAndHeight(width: 'inherit' | number, height: 'inherit' | number): void
	{
		const writable = this.getWritable();
		if (Type.isNumber(width))
		{
			writable.__width = Math.round(width);
		}
		else if (width === 'inherit')
		{
			writable.__width = width;
		}

		if (Type.isNumber(height))
		{
			writable.__height = Math.round(height);
		}
		else if (height === 'inherit')
		{
			writable.__height = height;
		}
	}

	setMaxWidth(maxWidth: number | 'none'): void
	{
		if (Type.isNumber(maxWidth) || maxWidth === 'none')
		{
			const writable = this.getWritable();
			writable.__maxWidth = Type.isNumber(maxWidth) ? Math.round(maxWidth) : maxWidth;
		}
	}

	createDOM(config: EditorConfig): HTMLElement
	{
		const span = document.createElement('span');
		const theme = config.theme;
		const className = theme?.image?.container;
		if (className !== undefined)
		{
			span.className = className;
		}

		return span;
	}

	updateDOM(): false
	{
		return false;
	}

	getSrc(): string
	{
		return this.__src;
	}

	getWidth(): 'inherit' | number
	{
		const self = this.getLatest();

		return self.__width;
	}

	getHeight(): 'inherit' | number
	{
		const self = this.getLatest();

		return self.__height;
	}

	getMaxWidth(): 'none' | number
	{
		const self = this.getLatest();

		return self.__maxWidth;
	}

	decorate(editor: LexicalEditor, config: EditorConfig): DecoratorOptions
	{
		return {
			componentClass: ImageComponent,
			options: {
				src: this.getSrc(),
				width: this.getWidth(),
				height: this.getHeight(),
				maxWidth: this.getMaxWidth(),
				config,
			},
		};
	}

	isInline(): true
	{
		return true;
	}
}

export function $createImageNode({ src, width, height, maxWidth, key }): ImageNode
{
	return $applyNodeReplacement(new ImageNode(src, width, height, maxWidth, key));
}

export function $isImageNode(node: LexicalNode | null | undefined): boolean
{
	return node instanceof ImageNode;
}
