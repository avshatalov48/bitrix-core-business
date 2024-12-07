/* eslint-disable no-underscore-dangle, @bitrix24/bitrix24-rules/no-pseudo-private */

import { Type, Dom } from 'main.core';

import {
	DecoratorNode,
	type DOMConversionMap,
	type DOMConversionOutput,
	type DOMExportOutput,
	type EditorConfig,
	type LexicalNode,
	type NodeKey,
	type SerializedDecoratorNode,
	type LexicalEditor,
} from 'ui.lexical.core';

import { FileImageComponent } from './file-image-component';

import type { UploaderFileInfo } from 'ui.uploader.core';
import type { DecoratorOptions } from '../../../types/decorator-options';

export type SerializedFileImageNode = SerializedDecoratorNode & {
	serverFileId: string | number,
	info: UploaderFileInfo,
	width: number,
	height: number,
};

import './file-image.css';

/** @memberof BX.UI.TextEditor.Plugins.File */
export class FileImageNode extends DecoratorNode
{
	__serverFileId: string | number;
	__info: UploaderFileInfo;
	__width: number;
	__height: number;

	constructor(
		serverFileId: string | number,
		info: UploaderFileInfo,
		width?: number,
		height?: number,
		key?: NodeKey,
	)
	{
		super(key);

		this.__serverFileId = serverFileId;
		this.__info = Type.isPlainObject(info) ? info : {};
		this.__width = Type.isNumber(width) && width > 0 ? Math.round(width) : this.__info.previewWidth;
		this.__height = Type.isNumber(height) && height > 0 ? Math.round(height) : this.__info.previewHeight;
	}

	static useDecoratorComponent = true;

	static getType(): string
	{
		return 'file-image';
	}

	static clone(node: FileImageNode): FileImageNode
	{
		return new FileImageNode(node.__serverFileId, node.__info, node.__width, node.__height, node.__key);
	}

	getId(): string | number
	{
		return this.__serverFileId;
	}

	getServerFileId(): string | number
	{
		return this.__serverFileId;
	}

	getInfo(): UploaderFileInfo
	{
		return this.__info;
	}

	setWidthAndHeight(width: number, height: number): void
	{
		const writable = this.getWritable();
		if (Type.isNumber(width))
		{
			writable.__width = Math.round(width);
		}

		if (Type.isNumber(height))
		{
			writable.__height = Math.round(height);
		}
	}

	getWidth(): number
	{
		const self = this.getLatest();

		return self.__width;
	}

	getHeight(): number
	{
		const self = this.getLatest();

		return self.__height;
	}

	isResized(): boolean
	{
		return this.__info.previewWidth !== this.getWidth() || this.__info.previewHeight !== this.getHeight();
	}

	static importJSON(serializedNode: SerializedFileImageNode): FileImageNode
	{
		return $createFileImageNode(
			serializedNode.serverFileId,
			serializedNode.info,
			serializedNode.width,
			serializedNode.height,
		);
	}

	static importDOM(): DOMConversionMap | null
	{
		return {
			img: (domNode: HTMLImageElement) => {
				if (!domNode.hasAttribute('data-file-image-id'))
				{
					return null;
				}

				return {
					conversion: (img: HTMLImageElement): DOMConversionOutput | null => {
						const { fileImageId, fileImageInfo } = img.dataset;
						let info = null;
						try
						{
							info = JSON.parse(fileImageInfo);
						}
						catch
						{
							return null;
						}

						const node = $createFileImageNode(fileImageId, info);

						return {
							node,
						};
					},
					priority: 1,
				};
			},
		};
	}

	exportDOM(): DOMExportOutput
	{
		return { element: null };
	}

	exportJSON(): SerializedFileImageNode
	{
		return {
			info: this.__info,
			serverFileId: this.__serverFileId,
			width: this.getWidth(),
			height: this.getHeight(),
			type: 'file-image',
			version: 1,
		};
	}

	createDOM(config: EditorConfig, editor: LexicalEditor): HTMLSpanElement
	{
		const span = document.createElement('span');
		if (Type.isStringFilled(config?.theme?.image?.container))
		{
			Dom.addClass(span, config.theme.image.container);
		}

		return span;
	}

	updateDOM(prevNode: FileImageNode, anchor: HTMLElement, config: EditorConfig): boolean
	{
		return false;
	}

	decorate(editor: LexicalEditor, config: EditorConfig): DecoratorOptions
	{
		return {
			componentClass: FileImageComponent,
			options: {
				src: this.__info.previewUrl,
				width: this.getWidth(),
				height: this.getHeight(),
				maxWidth: this.getWidth(),
				maxHeight: this.getHeight(),
				config,
				// maxWidth: this.__info.previewWidth,
				// maxHeight: this.__info.previewHeight,
			},
		};
	}

	isInline(): true
	{
		return true;
	}
}

export function $createFileImageNode(
	serverFileId: string | number,
	info: UploaderFileInfo = {},
	width: number = null,
	height: number = null,
): FileImageNode
{
	return new FileImageNode(serverFileId, info, width, height);
}

export function $isFileImageNode(node: LexicalNode | null | undefined): boolean
{
	return node instanceof FileImageNode;
}
