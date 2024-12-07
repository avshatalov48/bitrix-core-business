/* eslint-disable no-underscore-dangle, @bitrix24/bitrix24-rules/no-pseudo-private */

import { Type, Dom } from 'main.core';

import {
	$applyNodeReplacement,
	DecoratorNode,
	type DOMConversionMap,
	type DOMExportOutput,
	type EditorConfig,
	type LexicalNode,
	type NodeKey,
	type SerializedDecoratorNode,
	type LexicalEditor,
} from 'ui.lexical.core';

import { FileVideoComponent } from './file-video-component';

import type { UploaderFileInfo } from 'ui.uploader.core';
import type { DecoratorOptions } from '../../../types/decorator-options';

export type SerializedFileVideoNode = SerializedDecoratorNode & {
	serverFileId: string | number,
	info: UploaderFileInfo,
	width: number,
	height: number,
};

import './file-video.css';

/** @memberof BX.UI.TextEditor.Plugins.File */
export class FileVideoNode extends DecoratorNode
{
	__serverFileId: string | number;
	__info: UploaderFileInfo;
	__width: number = 0;
	__height: number = 0;

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
		this.__width = (
			Type.isNumber(width) && width > 0
				? Math.round(width)
				: (this.__info.previewWidth > 0 ? this.__info.previewWidth : this.__width))
		;

		this.__height = (
			Type.isNumber(height) && height > 0
				? Math.round(height)
				: (this.__info.previewHeight > 0 ? this.__info.previewHeight : this.__height)
		);
	}

	static useDecoratorComponent = true;

	static getType(): string
	{
		return 'file-video';
	}

	static clone(node: FileVideoNode): FileVideoNode
	{
		return new FileVideoNode(node.__serverFileId, node.__info, node.__width, node.__height, node.__key);
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

	static importJSON(serializedNode: SerializedFileVideoNode): FileVideoNode
	{
		return $createFileVideoNode(
			serializedNode.serverFileId,
			serializedNode.info,
			serializedNode.width,
			serializedNode.height,
		);
	}

	static importDOM(): DOMConversionMap | null
	{
		return null;
	}

	exportDOM(): DOMExportOutput
	{
		return { element: null };
	}

	exportJSON(): SerializedFileVideoNode
	{
		return {
			info: this.__info,
			serverFileId: this.__serverFileId,
			width: this.getWidth(),
			height: this.getHeight(),
			type: 'file-video',
			version: 1,
		};
	}

	createDOM(config: EditorConfig, editor: LexicalEditor): HTMLSpanElement
	{
		const div = document.createElement('span');

		if (Type.isStringFilled(config?.theme?.video?.container))
		{
			Dom.addClass(div, config.theme.video.container);
		}

		return div;
	}

	updateDOM(prevNode: FileVideoNode, anchor: HTMLElement, config: EditorConfig): boolean
	{
		return false;
	}

	decorate(editor: LexicalEditor, config: EditorConfig): DecoratorOptions
	{
		return {
			componentClass: FileVideoComponent,
			options: {
				src: this.__info.downloadUrl,
				width: this.getWidth(),
				height: this.getHeight(),
				maxWidth: this.getWidth(),
				maxHeight: this.getHeight(),
				config,
			},
		};
	}

	isInline(): true
	{
		return true;
	}
}

export function $createFileVideoNode(
	serverFileId: string | number,
	info: UploaderFileInfo = {},
	width: number = null,
	height: number = null,
): FileVideoNode
{
	const node: FileVideoNode = new FileVideoNode(serverFileId, info, width, height);

	return $applyNodeReplacement(node);
}

export function $isFileVideoNode(node: LexicalNode | null | undefined): boolean
{
	return node instanceof FileVideoNode;
}
