import 'ui.icons';
import 'ui.viewer';

import { ImModelFile, ImModelSidebarFileItem } from 'im.v2.model';
import { Utils } from 'im.v2.lib.utils';
import { lazyload } from 'ui.vue3.directives.lazyload';

import '../css/file-preview-item.css';

// @vue/component
export const FilePreviewItem = {
	name: 'FilePreviewItem',
	directives: { lazyload },
	props: {
		fileItem: {
			type: Object,
			required: true,
		},
	},
	computed:
	{
		sidebarFileItem(): ImModelSidebarFileItem
		{
			return this.fileItem;
		},
		file(): ImModelFile
		{
			return this.$store.getters['files/get'](this.sidebarFileItem.fileId, true);
		},
		previewImageStyles(): Object
		{
			if (!this.hasPreview)
			{
				return {};
			}

			return {
				backgroundImage: `url('${this.file.urlPreview}')`,
			};
		},
		hasPreview(): boolean
		{
			return this.file.urlPreview !== '';
		},
		fileShortName(): string
		{
			const NAME_MAX_LENGTH = 22;

			return Utils.file.getShortFileName(this.file.name, NAME_MAX_LENGTH);
		},
		viewerAttributes(): Object
		{
			return Utils.file.getViewerDataAttributes(this.file.viewerAttrs);
		},
		isImage(): boolean
		{
			return this.file.type === 'image';
		},
		isVideo(): boolean
		{
			return this.file.type === 'video';
		},
		isAudio(): boolean
		{
			return this.file.type === 'audio';
		},
		fileIconClass(): string
		{
			return `ui-icon ui-icon-file-${this.file.icon}`;
		},
		isViewerAvailable(): boolean
		{
			return Object.keys(this.viewerAttributes).length > 0;
		},
	},
	methods:
	{
		download()
		{
			if (this.isViewerAvailable)
			{
				return;
			}

			const urlToOpen = this.file.urlShow ? this.file.urlShow : this.file.urlDownload;
			window.open(urlToOpen, '_blank');
		},
	},
	template: `
		<div 
			class="bx-im-sidebar-file-preview-item__container bx-im-sidebar-file-preview-item__scope" 
			v-bind="viewerAttributes" 
			@click="download" 
			:title="file.name"
		>
			<img
				v-if="isImage"
				v-lazyload
				data-lazyload-dont-hide
				:data-lazyload-src="file.urlShow"
				:title="file.name"
				:alt="file.name"
				class="bx-im-sidebar-file-preview-item__preview-box"
			/>
			<div 
				v-else-if="isVideo" 
				class="bx-im-sidebar-file-preview-item__preview-box bx-im-sidebar-file-preview-item__preview-video-box"
				:style="previewImageStyles"
			>
				<video v-if="!hasPreview" class="bx-im-sidebar-file-preview-item__preview-video" preload="metadata" :src="file.urlDownload"></video>
				<div class="bx-im-sidebar-file-preview-item__preview-video-play-button"></div>
				<div class="bx-im-sidebar-file-preview-item__preview-video-play-icon"></div>
			</div>
			<div v-else-if="isAudio" class="bx-im-sidebar-file-preview-item__preview-box">
				<div class="bx-im-sidebar-file-preview-item__preview-audio-play-button"></div>
			</div>
			<div v-else class="bx-im-sidebar-file-preview-item__preview-box">
				<div :class="fileIconClass"><i></i></div>
			</div>
			<div class="bx-im-sidebar-file-preview-item__text">{{ fileShortName }}</div>
		</div>
	`,
};
