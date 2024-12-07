/* eslint-disable no-underscore-dangle, @bitrix24/bitrix24-rules/no-pseudo-private */

import { Type, Dom } from 'main.core';

import {
	TextNode,
	type DOMConversionMap,
	type DOMConversionOutput,
	type DOMExportOutput,
	type EditorConfig,
	type LexicalNode,
	type NodeKey,
	type SerializedDecoratorNode,
	type LexicalEditor,
} from 'ui.lexical.core';

import type { UploaderFileInfo } from 'ui.uploader.core';

export type SerializedFileNode = SerializedDecoratorNode & {
	serverFileId: string | number,
	info: UploaderFileInfo,
};

import './file.css';

/** @memberof BX.UI.TextEditor.Plugins.File */
export class FileNode extends TextNode
{
	__serverFileId: string | number;
	__info: UploaderFileInfo;

	constructor(
		serverFileId: string | number,
		info: UploaderFileInfo,
		key?: NodeKey,
	)
	{
		const fileInfo = Type.isPlainObject(info) ? info : {};

		super(fileInfo.name || '', key);

		this.__serverFileId = serverFileId;
		this.__info = fileInfo;
	}

	static getType(): string
	{
		return 'file';
	}

	static clone(node: FileNode): FileNode
	{
		return new FileNode(node.__serverFileId, node.__info, node.__key);
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

	getName(): string
	{
		return this.__info.name || 'unknown';
	}

	static importJSON(serializedNode: SerializedFileNode): FileNode
	{
		return $createFileNode(serializedNode.serverFileId, serializedNode.info);
	}

	static importDOM(): DOMConversionMap | null
	{
		return {
			span: (domNode: HTMLElement) => {
				if (!domNode.hasAttribute('data-file-id'))
				{
					return null;
				}

				return {
					conversion: (span: HTMLSpanElement): DOMConversionOutput | null => {
						const { fileId, fileInfo } = domNode.dataset;
						let info = null;
						try
						{
							info = JSON.parse(fileInfo);
						}
						catch
						{
							return null;
						}

						const node = $createFileNode(fileId, info);

						return { node };
					},
					priority: 1,
				};
			},
		};
	}

	exportDOM(): DOMExportOutput
	{
		const element = document.createElement('span');
		element.textContent = this.getName();
		element.setAttribute('data-file-id', this.__serverFileId);
		element.setAttribute('data-file-info', JSON.stringify(this.__info));

		return { element };
	}

	exportJSON(): SerializedFileNode
	{
		return {
			...super.exportJSON(),
			info: this.__info,
			serverFileId: this.__serverFileId,
			type: 'file',
			version: 1,
		};
	}

	createDOM(config: EditorConfig, editor: LexicalEditor): HTMLSpanElement
	{
		const span = document.createElement('span');
		if (Type.isStringFilled(config?.theme?.file))
		{
			Dom.addClass(span, config.theme.file);
		}

		span.textContent = this.getName();

		return span;
	}

	updateDOM(prevNode: FileNode, anchor: HTMLElement, config: EditorConfig): boolean
	{
		return false;
	}
}

export function $createFileNode(serverFileId: string | number, info: UploaderFileInfo = {}): FileNode
{
	return new FileNode(serverFileId, info).setMode('token');
}

export function $isFileNode(node: LexicalNode | null | undefined): boolean
{
	return node instanceof FileNode;
}
