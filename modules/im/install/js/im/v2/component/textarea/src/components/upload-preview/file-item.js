import { FileStatus, FileViewerContext } from 'im.v2.const';
import { Utils } from 'im.v2.lib.utils';

import { FilePreviewItem } from './items/file';
import { ErrorPreviewItem } from './items/error';

import '../../css/upload-preview/file-item.css';

// @vue/component
export const FileItem = {
	name: 'FileItem',
	components: { FilePreviewItem, ErrorPreviewItem },
	props: {
		file: {
			type: Object,
			required: true,
		},
		removable: {
			type: Boolean,
			default: false,
		},
		highlightDropzone: {
			type: Object,
			default: null,
		},
		viewerGroupBy: {
			type: String || null,
			default: null,
		},
	},
	emits: ['removeItem', 'itemDragStart', 'itemDragEnd', 'itemDragOver', 'itemDragLeave', 'itemDrop'],
	data()
	{
		return {
			isDraggable: false,
		};
	},
	computed: {
		hasError(): boolean
		{
			return this.file.status === FileStatus.error;
		},
		previewComponentName(): string
		{
			if (this.hasError)
			{
				return ErrorPreviewItem.name;
			}

			return FilePreviewItem.name;
		},
		draggableClasses(): { [key: string]: boolean }
		{
			const classes = {};

			if (this.highlightDropzone.fileId === this.file.id)
			{
				classes[`--dropzone-${this.highlightDropzone.position}`] = true;
			}

			classes['--draggable'] = this.isDraggable;

			return classes;
		},
		viewerAttributes(): Object
		{
			if (this.file.viewerAttrs)
			{
				return Utils.file.getViewerDataAttributes({
					viewerAttributes: this.file.viewerAttrs,
					previewImageSrc: this.file.urlPreview,
					context: FileViewerContext.dialog,
				});
			}

			return Utils.file.getViewerDataAttributes({
				viewerAttributes: {
					viewer: true,
					viewerResized: true,
					viewerType: this.file.type,
					title: this.file.name,
					src: this.file.urlDownload,
					viewerGroupBy: this.viewerGroupBy,
				},
				previewImageSrc: this.file.urlPreview,
				context: FileViewerContext.dialog,
			});
		},
	},
	methods: {
		onRemoveClick()
		{
			this.$emit('removeItem', { file: this.file });
		},
		onDragStart(event)
		{
			this.$emit('itemDragStart', { file: this.file, axis: 'y', event });

			this.isDraggable = true;
		},
		onDragEnd(event)
		{
			this.$emit('itemDragEnd', { file: this.file, axis: 'y', event });

			this.isDraggable = false;
			event.target.removeAttribute('draggable');
		},
		onDragOver(event)
		{
			this.$emit('itemDragOver', { file: this.file, axis: 'y', event });
		},
		onDragLeave(event)
		{
			this.$emit('itemDragLeave', { file: this.file, axis: 'y', event });
		},
		onDrop(event)
		{
			this.$emit('itemDrop', { file: this.file, axis: 'y', event });

			this.isDraggable = false;
		},
		onMouseDown()
		{
			this.$refs.dragElement.setAttribute('draggable', 'true');
		},
	},
	template: `
		<div 
			class="bx-im-upload-preview-file-item__scope"
			:class="this.draggableClasses"
			@dragstart="onDragStart"
			@dragend="onDragEnd"
			@dragover="onDragOver"
			@dragleave="onDragLeave"
			@drop="onDrop"
			ref="dragElement"
		>
			<div 
				class="bx-im-upload-preview-file-item__drag"
				@mousedown="onMouseDown"
			>
				<div class="bx-im-upload-preview-file-item__drag-icon"></div>
			</div>
			<component
				:is="previewComponentName"
				:file="file"
				v-bind="viewerAttributes"
			/>
			<div v-if="removable" class="bx-im-upload-preview-file-item__remove" @click="onRemoveClick">
				<div class="bx-im-upload-preview-file-item__remove-icon"></div>
			</div>
		</div>
	`,
};
