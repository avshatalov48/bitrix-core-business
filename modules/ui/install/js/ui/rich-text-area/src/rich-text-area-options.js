import type { BaseEvent } from 'main.core.events';
import type { TextEditorOptions } from 'ui.text-editor';
import type { Uploader, UploaderFileInfo, UploaderOptions } from 'ui.uploader.core';
import type { TileWidgetOptions } from 'ui.uploader.tile-widget';

export type RichTextAreaOptions = {
	editorOptions: TextEditorOptions,
	editorInstance: TextEditor,
	uploaderOptions: UploaderOptions,
	uploaderInstance: Uploader,
	widgetOptions: RichTextAreaWidgetOptions,
	files: UploaderFileInfo[],
};

export type RichTextAreaWidgetOptions = {
	tileWidgetOptions?: TileWidgetOptions,
	insertIntoText?: boolean,
	events?: { [eventName: string]: (event: BaseEvent) => void },
};
