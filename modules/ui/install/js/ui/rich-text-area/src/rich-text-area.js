import { Type, Runtime } from 'main.core';
import { EventEmitter, type BaseEvent } from 'main.core.events';
import { COMMAND_PRIORITY_LOW, PASTE_COMMAND } from 'ui.lexical.core';
import { Plugins, TextEditor, Commands, type TextEditorOptions } from 'ui.text-editor';
import { VueUploaderAdapter } from 'ui.uploader.vue';

import {
	FileEvent,
	getFilesFromDataTransfer,
	isFilePasted,
	Uploader,
	UploaderFile,
	type UploaderOptions,
	type UploaderFileInfo,
} from 'ui.uploader.core';

const { DRAG_END_COMMAND, DRAG_START_COMMAND } = Commands;

import type { TileWidgetItem } from 'ui.uploader.tile-widget';
import type { RichTextAreaOptions } from './rich-text-area-options';

export class RichTextArea extends EventEmitter
{
	#textEditor: TextEditor = null;
	#uploaderAdapter: VueUploaderAdapter = null;
	#uploader: Uploader = null;
	#allowDropFiles: boolean = true;
	#syncHighlightsDebounced = Runtime.debounce(this.#syncHighlights, 500);
	#lastInserted: Set<string | number> = new Set();

	constructor(richTextAreaOptions: RichTextAreaOptions)
	{
		super();
		this.setEventNamespace('BX.UI.RichTextArea');

		const options: RichTextAreaOptions = Type.isPlainObject(richTextAreaOptions) ? richTextAreaOptions : {};

		this.subscribeFromOptions(options.widgetOptions.events);

		this.#createTextEditor(options.editorOptions, options.editorInstance);
		this.#createUploaderAdapter(options.uploaderOptions, options.uploaderInstance, options.files);

		const fileInfos = this.#uploaderAdapter.getUploader().getFiles().map((file: UploaderFile) => {
			return file.toJSON();
		});

		this.getEditor().dispatchCommand(Plugins.File.ADD_FILES_COMMAND, fileInfos);

		this.#registerCommands();
	}

	#createTextEditor(editorOptions: TextEditorOptions, editorInstance: TextEditor): TextEditor
	{
		if (editorInstance)
		{
			this.#textEditor = editorInstance;
		}
		else
		{
			const options: TextEditorOptions = Type.isPlainObject(editorOptions) ? { ...editorOptions } : {};
			this.#textEditor = new TextEditor(options);
		}

		this.#textEditor.subscribeFromOptions({
			onChange: (event: BaseEvent<{ isInitialChange: boolean, tags: Set<string> }>) => {
				const { tags, isInitialChange } = event.getData();
				if (tags.has('historic'))
				{
					// Undo/Redo case uses setEditorState that's why we need a new update circle
					this.getEditor().update(() => {
						this.#syncHighlights();
					});
				}
				else if (isInitialChange)
				{
					this.#syncHighlights(true);
				}
				else
				{
					this.#syncHighlightsDebounced();
				}
			},
		});

		return this.#textEditor;
	}

	#createUploaderAdapter(uploaderOptions: UploaderOptions, uploader: Uploader, files: UploaderFileInfo[]): void
	{
		if (uploader instanceof Uploader)
		{
			this.#uploaderAdapter = new VueUploaderAdapter(uploader);
		}
		else
		{
			const options: UploaderOptions = Type.isPlainObject(uploaderOptions) ? uploaderOptions : {};
			const defaultOptions: UploaderOptions = {
				imagePreviewHeight: 1200, // double size (see DiskUploaderController)
				imagePreviewWidth: 1200,
				imagePreviewQuality: 0.85,
				treatOversizeImageAsFile: true,
				ignoreUnknownImageTypes: true,
				multiple: true,
			};

			this.#uploaderAdapter = new VueUploaderAdapter({
				...defaultOptions,
				...options,
			});
		}

		this.#uploaderAdapter.subscribeFromOptions({
			'Item:onAdd': (event: BaseEvent<{ item: TileWidgetItem }>): void => {
				const item: TileWidgetItem = event.getData().item;
				const fileCount = this.getFileCount();

				this.emit('Item:onAdd', { item, fileCount });
			},
			'Item:onComplete': (event: BaseEvent<{ item: TileWidgetItem }>): void => {
				const item: TileWidgetItem = event.getData().item;
				const fileCount = this.getFileCount();

				this.getEditor().dispatchCommand(Plugins.File.ADD_FILE_COMMAND, item);

				this.emit('Item:onComplete', { item, fileCount });
			},
			'Item:onRemove': (event: BaseEvent<{ item: TileWidgetItem }>): void => {
				const item: TileWidgetItem = event.getData().item;

				this.removeFile(event.getData().item.serverFileId);
				const fileCount = this.getFileCount();

				this.emit('Item:onRemove', { item, fileCount });
			},
		});

		this.#uploaderAdapter.getUploader().addFiles(files);
	}

	getUploaderAdapter(): VueUploaderAdapter
	{
		return this.#uploaderAdapter;
	}

	getUploader(): Uploader
	{
		return this.#uploaderAdapter.getUploader();
	}

	getFileCount(): number
	{
		return this.getUploader().getFiles().length;
	}

	getEditor(): TextEditor
	{
		return this.#textEditor;
	}

	isFilePluginEnabled(): boolean
	{
		const filePlugin: typeof(Plugins.File.FilePlugin) = this.getEditor().getPlugin('File');

		return filePlugin?.isEnabled() === true;
	}

	canDropFiles(): boolean
	{
		return this.#allowDropFiles;
	}

	insertFile(fileInfo: TileWidgetItem | UploaderFileInfo): void
	{
		this.getEditor().dispatchCommand(Plugins.File.INSERT_FILE_COMMAND, {
			serverFileId: fileInfo.serverFileId,
			width: 600, // half size of imagePreviewWidth
			height: 600, // half size of imagePreviewHeight
			info: fileInfo,
		});
	}

	removeFile(serverFileId: string | number): void
	{
		this.getEditor().dispatchCommand(
			Plugins.File.REMOVE_FILE_COMMAND,
			{
				serverFileId,
				skipHistoryStack: true,
			},
		);

		this.#syncHighlights(); // onChange doesn't emit due to history-merge
	}

	#registerCommands(): void
	{
		this.getEditor().registerCommand(
			PASTE_COMMAND,
			(clipboardEvent: ClipboardEvent) => {
				const clipboardData: DataTransfer = clipboardEvent.clipboardData;
				if (!clipboardData || !isFilePasted(clipboardData))
				{
					return false;
				}

				clipboardEvent.preventDefault();

				getFilesFromDataTransfer(clipboardData)
					.then((files: File[]): void => {
						if (files.length > 0)
						{
							this.emit('onBeforeFilePaste');
						}

						files.forEach((file: File): void => {
							this.getUploader().addFile(file, {
								events: {
									[FileEvent.LOAD_ERROR]: () => {},
									[FileEvent.UPLOAD_ERROR]: () => {},
									[FileEvent.UPLOAD_COMPLETE]: (event: BaseEvent): void => {
										const uploaderFile: UploaderFile = event.getTarget();

										this.emit('onFilePaste', { file: uploaderFile });
										this.insertFile(uploaderFile.toJSON());
									},
								},
							});
						});
					})
					.catch((): void => {
						console.error('RichTextArea: clipboard pasting error.');
					})
				;

				return true;
			},
			COMMAND_PRIORITY_LOW,
		);

		this.getEditor().registerCommand(
			DRAG_START_COMMAND,
			() => {
				this.#allowDropFiles = false;
			},
			COMMAND_PRIORITY_LOW,
		);

		this.getEditor().registerCommand(
			DRAG_END_COMMAND,
			() => {
				this.#allowDropFiles = true;
			},
			COMMAND_PRIORITY_LOW,
		);
	}

	#syncHighlights(initialSync: boolean = false): void
	{
		this.getEditor().dispatchCommand(Plugins.File.GET_INSERTED_FILES_COMMAND, (nodes) => {
			const inserted: Set<number | string> = new Set();
			for (const node of nodes)
			{
				const { serverFileId } = node.getInfo();
				if (Type.isStringFilled(serverFileId) || Type.isNumber(serverFileId))
				{
					inserted.add(serverFileId);
				}
			}

			const isInsertedChanged: boolean = this.#isInsertedChanged(inserted);
			this.#lastInserted = new Set(inserted);

			let hasInsertedItems = false;
			this.getUploader().getFiles().forEach((file: UploaderFile) => {
				if (inserted.has(file.getServerFileId()))
				{
					hasInsertedItems = true;
					file.setCustomData('tileSelected', true);
					inserted.delete(file.getServerFileId());
				}
				else
				{
					file.setCustomData('tileSelected', false);
				}
			});

			// Redo/Undo history can have files that were removed from uploader
			for (const serverFileId of inserted)
			{
				this.richTextArea.removeFile(serverFileId);
			}

			if (!initialSync && isInsertedChanged)
			{
				this.emit('Item:onInsertChange', { hasInsertedItems });
			}
		});
	}

	#isInsertedChanged(inserted: Set<string | number>): boolean
	{
		if (this.#lastInserted.size !== inserted.size)
		{
			return true;
		}

		for (const serverFileId of this.#lastInserted)
		{
			if (!inserted.has(serverFileId))
			{
				return true;
			}
		}

		return false;
	}

	destroy(): void
	{
		this.#textEditor.destroy();
		this.#uploader.destroy();

		this.#textEditor = null;
		this.#uploader = null;
	}
}
