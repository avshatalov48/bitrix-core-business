import { Type } from 'main.core';
import { TextEditor, TextEditorComponent } from 'ui.text-editor';
import { getFilesFromDataTransfer, hasDataTransferOnlyFiles, Uploader } from 'ui.uploader.core';
import { TileWidgetComponent, type TileWidgetOptions } from 'ui.uploader.tile-widget';

import { RichTextArea } from './rich-text-area';

import { DropArea } from './components/drop-area';
import { FileButton } from './components/action-bar/file-button';
import { ActionButton } from './components/action-bar/action-button';
import { CreateDocumentButton } from './components/action-bar/create-document-button';
import { RecordVideoButton } from './components/action-bar/record-video-button';

import { ref, type BitrixVueComponentProps } from 'ui.vue3';
import { type BaseEvent } from 'main.core.events';
import { type VueUploaderAdapter } from 'ui.uploader.vue';

import './css/rich-text-area.css';

/**
 * @memberof BX.UI.RichTextArea
 */
export const RichTextAreaComponent: BitrixVueComponentProps = {
	name: 'RichTextAreaComponent',
	components: {
		TextEditorComponent,
		TileWidgetComponent,
		DropArea,
		FileButton,
		ActionButton,
		CreateDocumentButton,
		RecordVideoButton,
	},
	props: {
		editorOptions: {
			type: Object,
		},
		editorInstance: {
			type: TextEditor,
		},
		uploaderOptions: {
			type: Object,
		},
		uploaderInstance: {
			type: Uploader,
		},
		widgetOptions: {
			type: Object,
			default: {},
		},
		files: {
			type: Array,
		},
	},
	data() {
		return {
			showDropArea: false,
			uploaderVisibility: false,
		};
	},
	beforeCreate(): void
	{
		this.richTextArea = new RichTextArea({
			editorOptions: this.editorOptions,
			editorInstance: this.editorInstance,
			uploaderOptions: this.uploaderOptions,
			uploaderInstance: this.uploaderInstance,
			widgetOptions: this.widgetOptions,
			files: this.files,
		});

		this.richTextArea.subscribe('Item:onAdd', () => {
			this.uploaderVisibility = true;
		});

		this.fileButtonRef = ref(null);
	},
	created()
	{
		this.uploaderVisibility = this.richTextArea.getFileCount() > 0;
	},
	methods: {
		getRichTextArea(): RichTextArea
		{
			return this.richTextArea;
		},
		getEditor(): TextEditor
		{
			return this.richTextArea.getEditor();
		},
		getUploader(): Uploader
		{
			return this.richTextArea.getUploader();
		},
		getUploaderAdapter(): VueUploaderAdapter
		{
			return this.richTextArea.getUploaderAdapter();
		},
		onDragOver(event: DragEvent): void
		{
			if (this.richTextArea.canDropFiles())
			{
				event.preventDefault();
			}
		},
		onDragEnter(event: DragEvent)
		{
			if (!this.richTextArea.canDropFiles())
			{
				return;
			}

			event.preventDefault();
			event.stopPropagation();

			void hasDataTransferOnlyFiles(event.dataTransfer, false).then((success: boolean): void => {
				if (!success)
				{
					return;
				}

				this.lastDropAreaEnterTarget = event.target;
				this.showDropArea = true;
			});
		},
		onDragLeave(event: DragEvent)
		{
			if (!this.richTextArea.canDropFiles())
			{
				return;
			}

			event.preventDefault();
			event.stopPropagation();

			if (this.lastDropAreaEnterTarget === event.target)
			{
				this.showDropArea = false;
			}
		},
		onDrop(event: DragEvent)
		{
			if (!this.richTextArea.canDropFiles())
			{
				return;
			}

			event.preventDefault();

			void getFilesFromDataTransfer(event.dataTransfer).then((files: File[]): void => {
				this.getUploader().addFiles(files);
				this.getEditor().expand();
			});

			this.showDropArea = false;
		},
	},
	computed: {
		tileWidgetOptions(): TileWidgetOptions
		{
			const options = this.widgetOptions;
			const tileWidgetOptions = {
				insertIntoText: Type.isBoolean(options.insertIntoText) ? options.insertIntoText : true,
				...(Type.isPlainObject(options.tileWidgetOptions) ? options.tileWidgetOptions : {}),
			};

			tileWidgetOptions.enableDropzone = false;

			if (tileWidgetOptions.insertIntoText)
			{
				tileWidgetOptions.events = tileWidgetOptions.events || {};
				tileWidgetOptions.events.onInsertIntoText = (event: BaseEvent) => {
					this.richTextArea.insertFile(event.getData().item);
				};
			}

			return tileWidgetOptions;
		},
		isUploadEnabled(): boolean
		{
			return this.getRichTextArea().isFilePluginEnabled();
		},
	},
	mounted(): void
	{
		if (this.isUploadEnabled)
		{
			this.getUploader().assignBrowse(this.fileButtonRef.value);
		}
	},
	unmounted()
	{
		this.richTextArea.destroy();
		this.richTextArea = null;
	},
	// language=Vue
	template: `
		<div 
			class="ui-rich-text-area"
			v-on="
				isUploadEnabled
				? { drop: onDrop, dragleave: onDragLeave, dragenter: onDragEnter, dragover: onDragOver }
				: {}
			"
		>
			<TextEditorComponent :editor-instance="getEditor()">
				<template #footer>
					<div class="ui-rich-text-area-actions">
						<slot name="before-buttons" :richTextArea="getRichTextArea()"></slot>
						<slot name="file-button" :richTextArea="getRichTextArea()">
							<FileButton v-if="isUploadEnabled" ref="fileButton" :buttonRef="fileButtonRef" />
						</slot>
						<slot name="after-buttons" :richTextArea="getRichTextArea()"></slot>
					</div>
					<slot name="uploader" :adapter="getUploaderAdapter()" :richTextArea="getRichTextArea()">
						<div class="ui-rich-text-area-uploader" :class="{ '--visible': uploaderVisibility }">
							<TileWidgetComponent
								:widgetOptions="tileWidgetOptions"
								:uploader-adapter="getUploaderAdapter()"
								ref="tileWidget"
							/>
						</div>
					</slot>
				</template>
			</TextEditorComponent>
			<DropArea :show="showDropArea" />
		</div>
	`,
};
