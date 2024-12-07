import { Type, Text } from 'main.core';

import {
	$createTextNode,
	createCommand,
	$isRootOrShadowRoot,
	$insertNodes,
	$createParagraphNode,
	$nodesOfType,
	COMMAND_PRIORITY_EDITOR,
	type LexicalCommand,
	type LexicalNode,
} from 'ui.lexical.core';

import { $wrapNodeInElement } from 'ui.lexical.utils';
import { calcImageSize } from '../../helpers/calc-image-size';

import { registerDraggableNode } from '../../helpers/register-draggable-node';
import type { SchemeValidationOptions } from '../../types/scheme-validation-options';
import BasePlugin from '../base-plugin';

import { FileNode, $createFileNode } from './file/file-node';
import { FileImageNode, $createFileImageNode } from './image/file-image-node';
import { FileVideoNode, $createFileVideoNode } from './video/file-video-node';

import type {
	BBCodeConversion,
	BBCodeExportOutput,
	BBCodeImportConversion,
	BBCodeExportConversion,
	BBCodeConversionFn,
} from '../../bbcode';

import type { UploaderFileInfo } from 'ui.uploader.core';
import { type TextEditor } from '../../text-editor';
import type { BBCodeElementNode } from 'ui.bbcode.model';

type InsertFilePayload = {
	serverFileId: string | number,
	info: UploaderFileInfo,
	width?: number,
	height?: number,
};

type RemoveFilePayload = {
	serverFileId: string | number,
	skipHistoryStack?: boolean,
};

/** @memberof BX.UI.TextEditor.Plugins.File */
export const FileType = {
	FILE: 'file',
	IMAGE: 'image',
	VIDEO: 'video',
};

/** @memberof BX.UI.TextEditor.Plugins.File */
export const ADD_FILE_COMMAND: LexicalCommand<InsertFilePayload> = createCommand('ADD_FILE_COMMAND');
export const ADD_FILES_COMMAND: LexicalCommand<InsertFilePayload> = createCommand('ADD_FILES_COMMAND');
export const INSERT_FILE_COMMAND: LexicalCommand<InsertFilePayload> = createCommand('INSERT_FILE_COMMAND');

/** @memberof BX.UI.TextEditor.Plugins.File */
export const REMOVE_FILE_COMMAND: LexicalCommand<string | number> = createCommand('REMOVE_FILE_COMMAND');

/** @memberof BX.UI.TextEditor.Plugins.File */
export const GET_INSERTED_FILES_COMMAND: LexicalCommand = createCommand('GET_INSERTED_FILES_COMMAND');

/** @memberof BX.UI.TextEditor.Plugins.File */
export class FilePlugin extends BasePlugin
{
	#enabled: boolean = false;
	#mode: 'disk' | 'file' = 'file';
	#files: Map<string | number, UploaderFileInfo> = new Map();

	constructor(editor: TextEditor)
	{
		super(editor);

		const modeOption = editor.getOption('file.mode');
		this.#enabled = ['file', 'disk'].includes(modeOption);
		if (!this.#enabled)
		{
			return;
		}

		this.#mode = modeOption;

		const files: UploaderFileInfo[] = editor.getOption('file.files', []);
		this.addFiles(files);

		this.#registerListeners();

		this.cleanUpRegister(
			registerDraggableNode(
				this.getEditor(),
				FileImageNode,
				(data) => {
					this.getEditor().dispatchCommand(INSERT_FILE_COMMAND, data);
				},
			),
			registerDraggableNode(
				this.getEditor(),
				FileVideoNode,
				(data) => {
					this.getEditor().dispatchCommand(INSERT_FILE_COMMAND, data);
				},
			),
		);
	}

	static getName(): string
	{
		return 'File';
	}

	static getNodes(editor: TextEditor): Array<Class<LexicalNode>>
	{
		return [FileNode, FileImageNode, FileVideoNode];
	}

	importBBCode(): BBCodeImportConversion | null
	{
		if (!this.isEnabled())
		{
			return null;
		}

		return {
			[this.getMode()]: (): BBCodeConversion => ({
				conversion: (node: BBCodeElementNode): BBCodeConversionFn | null => {
					// [DISK FILE ID=n14194]
					// [DISK FILE ID=14194]

					// [FILE ID=5b87ba3b-edb1-49df-a840-50d17b6c3e8c.fbbdd477d5ff19d61...a875e731fa89cfd1e1]
					// [FILE ID=14194]
					const serverFileId = node.getAttribute('id');
					const createTextNode = () => {
						return { node: $createTextNode(node.toString()) };
					};

					if (
						!Type.isStringFilled(serverFileId)
						|| (this.getMode() === 'disk' && !/^n?\d+$/i.test(serverFileId))
						|| (this.getMode() === 'file' && !/^(\d+|[\da-f-]{36}\.[\da-f]{32,})$/i.test(serverFileId))
					)
					{
						return createTextNode();
					}

					const info = this.getFile(serverFileId);
					if (info === null)
					{
						return createTextNode();
					}

					const fileType = this.getFileType(info);
					if (fileType === FileType.IMAGE)
					{
						const width = Text.toInteger(node.getAttribute('width'));
						const height = Text.toInteger(node.getAttribute('height'));

						return { node: $createFileImageNode(serverFileId, info, width, height) };
					}

					if (fileType === FileType.VIDEO)
					{
						const width = Text.toInteger(node.getAttribute('width'));
						const height = Text.toInteger(node.getAttribute('height'));

						return { node: $createFileVideoNode(serverFileId, info, width, height) };
					}

					return { node: $createFileNode(serverFileId, info) };
				},
				priority: 0,
			}),
		};
	}

	exportBBCode(): BBCodeExportConversion | null
	{
		if (!this.isEnabled())
		{
			return null;
		}

		return {
			file: (lexicalNode: FileNode): BBCodeExportOutput => {
				const scheme = this.getEditor().getBBCodeScheme();
				const attributes = this.getMode() === 'disk' ? { file: '' } : {};
				attributes.id = lexicalNode.getServerFileId();

				return {
					node: scheme.createElement({ name: this.getMode(), attributes, inline: true }),
				};
			},
			'file-video': (lexicalNode: FileVideoNode): BBCodeExportOutput => {
				const scheme = this.getEditor().getBBCodeScheme();
				const attributes = this.getMode() === 'disk' ? { file: '' } : {};
				attributes.id = lexicalNode.getServerFileId();

				const node = scheme.createElement({ name: this.getMode(), attributes, inline: false });
				node.setAttribute('width', lexicalNode.getWidth());
				node.setAttribute('height', lexicalNode.getHeight());

				return { node };
			},
			'file-image': (lexicalNode: FileImageNode): BBCodeExportOutput => {
				const scheme = this.getEditor().getBBCodeScheme();
				const attributes = this.getMode() === 'disk' ? { file: '' } : {};
				attributes.id = lexicalNode.getServerFileId();

				const node = scheme.createElement({ name: this.getMode(), attributes, inline: true });
				if (lexicalNode.isResized())
				{
					node.setAttribute('width', lexicalNode.getWidth());
					node.setAttribute('height', lexicalNode.getHeight());
				}

				return { node };
			},
		};
	}

	validateScheme(): SchemeValidationOptions | null
	{
		if (!this.isEnabled())
		{
			return null;
		}

		return {
			bbcodeMap: {
				file: this.getMode(),
				'file-image': this.getMode(),
				'file-video': this.getMode(),
			},
		};
	}

	isEnabled(): boolean
	{
		return this.#enabled;
	}

	getMode(): 'disk' | 'file'
	{
		return this.#mode;
	}

	addFile(file: UploaderFileInfo): void
	{
		if (Type.isPlainObject(file) && (Type.isStringFilled(file.serverFileId) || Type.isNumber(file.serverFileId)))
		{
			const serverFileId = file.serverFileId.toString();
			if (!this.#files.has(serverFileId))
			{
				this.#files.set(file.serverFileId.toString(), file);
			}
		}
	}

	addFiles(files: UploaderFileInfo[]): void
	{
		if (Type.isArrayFilled(files))
		{
			files.forEach((file: UploaderFileInfo) => {
				this.addFile(file);
			});
		}
	}

	getFile(serverFileId: string | number): UploaderFileInfo | null
	{
		if (Type.isStringFilled(serverFileId) || Type.isNumber(serverFileId))
		{
			return this.#files.get(serverFileId.toString()) || null;
		}

		return null;
	}

	getFileType(file: UploaderFileInfo): string
	{
		if (file?.isImage)
		{
			return FileType.IMAGE;
		}

		if (file?.isVideo)
		{
			return FileType.VIDEO;
		}

		return FileType.FILE;
	}

	removeFile(serverFileId: string | number, skipHistoryStack: boolean = true): void
	{
		if (Type.isStringFilled(serverFileId) || Type.isNumber(serverFileId))
		{
			this.#files.delete(serverFileId.toString());

			this.getEditor().update(() => {
				const nodes = [
					...$nodesOfType(FileNode),
					...$nodesOfType(FileImageNode),
					...$nodesOfType(FileVideoNode),
				];

				nodes.forEach((node: FileNode | FileImageNode | FileVideoNode) => {
					if (node.getServerFileId().toString() === serverFileId.toString())
					{
						node.remove();
					}
				});
			}, skipHistoryStack ? { tag: 'history-merge' } : {});
		}
	}

	#registerListeners(): void
	{
		this.cleanUpRegister(
			this.getEditor().registerCommand(
				INSERT_FILE_COMMAND,
				(payload: InsertFilePayload) => {
					if (
						!Type.isPlainObject(payload)
						|| !Type.isPlainObject(payload.info)
						|| (!Type.isNumber(payload.serverFileId) && !Type.isStringFilled(payload.serverFileId))
					)
					{
						return false;
					}

					this.addFile(payload.info);

					const fileType = this.getFileType(payload.info);
					let node = null;

					const previewWidth = payload.info.previewWidth;
					const previewHeight = payload.info.previewHeight;
					const renderWidth = payload.width;
					const renderHeight = payload.height;
					if (fileType === FileType.IMAGE)
					{
						const [width, height] = calcImageSize(previewWidth, previewHeight, renderWidth, renderHeight);
						node = $createFileImageNode(payload.serverFileId, payload.info, width, height);
					}
					else if (fileType === FileType.VIDEO)
					{
						let width = 0;
						let height = 0;
						if (previewWidth > 0 && previewHeight > 0)
						{
							[width, height] = calcImageSize(previewWidth, previewHeight, renderWidth, renderHeight);
						}

						node = $createFileVideoNode(payload.serverFileId, payload.info, width, height);
					}
					else
					{
						node = $createFileNode(payload.serverFileId, payload.info);
					}

					// const selection: RangeSelection = $getSelection();
					// if ($isRangeSelection(selection) && fileType !== FileType.FILE && payload.inline !== true)
					// {
					// 	const focus: PointType = selection.focus;
					// 	const focusNode: TextNode | ElementNode = focus.getNode();
					// 	if (!selection.isCollapsed())
					// 	{
					// 		focusNode.selectEnd();
					// 	}
					//
					// 	const parentNode: ParagraphNode = $findMatchingParent(
					// 		focusNode,
					// 		(parent: ElementNode) => $isParagraphNode(parent),
					// 	);
					//
					// 	if (parentNode === null)
					// 	{
					// 		$insertNodes([node]);
					// 		if ($isRootOrShadowRoot(node.getParentOrThrow()))
					// 		{
					// 			$wrapNodeInElement(node, $createParagraphNode).selectEnd();
					// 		}
					// 	}
					// 	else if (parentNode.isEmpty())
					// 	{
					// 		parentNode.append(node);
					// 		node.selectEnd();
					// 	}
					// 	else
					// 	{
					// 		// const paragraph = $createParagraphNode();
					// 		// paragraph.append(node);
					// 		// parentNode.insertAfter(paragraph);
					// 		parentNode.append($createLineBreakNode());
					// 		parentNode.append(node);
					// 		node.selectEnd();
					// 	}
					// }
					// else
					// {
					// 	$insertNodes([node]);
					// 	if ($isRootOrShadowRoot(node.getParentOrThrow()))
					// 	{
					// 		$wrapNodeInElement(node, $createParagraphNode).selectEnd();
					// 	}
					// }

					$insertNodes([node]);
					if ($isRootOrShadowRoot(node.getParentOrThrow()))
					{
						$wrapNodeInElement(node, $createParagraphNode).selectEnd();
					}

					return true;
				},
				COMMAND_PRIORITY_EDITOR,
			),

			this.getEditor().registerCommand(
				REMOVE_FILE_COMMAND,
				(payload: RemoveFilePayload): boolean => {
					if (
						!Type.isPlainObject(payload)
						|| (!Type.isNumber(payload.serverFileId) && !Type.isStringFilled(payload.serverFileId))
					)
					{
						return false;
					}

					this.removeFile(payload.serverFileId, payload.skipHistoryStack);

					return true;
				},
				COMMAND_PRIORITY_EDITOR,
			),

			this.getEditor().registerCommand(
				GET_INSERTED_FILES_COMMAND,
				(fn: Function): boolean => {
					if (!Type.isFunction(fn))
					{
						return false;
					}

					const nodes = [
						...$nodesOfType(FileNode),
						...$nodesOfType(FileImageNode),
						...$nodesOfType(FileVideoNode),
					];

					fn(nodes);

					return true;
				},
				COMMAND_PRIORITY_EDITOR,
			),

			this.getEditor().registerCommand(
				ADD_FILE_COMMAND,
				(file: UploaderFileInfo): boolean => {
					this.addFile(file);

					return true;
				},
				COMMAND_PRIORITY_EDITOR,
			),

			this.getEditor().registerCommand(
				ADD_FILES_COMMAND,
				(files: UploaderFileInfo[]): boolean => {
					this.addFiles(files);

					return true;
				},
				COMMAND_PRIORITY_EDITOR,
			),
		);
	}
}
