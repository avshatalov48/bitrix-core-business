import { Type } from 'main.core';
import { lazyload } from 'ui.vue3.directives.lazyload';
import { FileType, FileViewerContext } from 'im.v2.const';
import { Utils } from 'im.v2.lib.utils';
import { ProgressBar, ProgressBarSize } from 'im.v2.component.elements.progressbar';

import '../css/components/image-item.css';

import type { ImModelFile } from 'im.v2.model';

const MAX_WIDTH = 488;
const MAX_HEIGHT = 340;
const MIN_WIDTH = 200;
const MIN_HEIGHT = 100;

// @vue/component
export const ImageItem = {
	name: 'ImageItem',
	directives: { lazyload },
	components: { ProgressBar },
	props: {
		/** @type ImModelFile */
		file: {
			type: Object,
			required: true,
		},
		isGallery: {
			type: Boolean,
			default: true,
		},
		handleLoading: {
			type: Boolean,
			default: false,
		},
		allowRemove: {
			type: Boolean,
			default: false,
		},
	},
	emits: ['remove', 'cancelClick'],
	computed: {
		imageSize(): {width: string, height: string, backgroundSize: string}
		{
			if (this.isGallery)
			{
				return {};
			}

			let newWidth = this.file.image.width;
			let newHeight = this.file.image.height;

			if (this.file.image.width > MAX_WIDTH || this.file.image.height > MAX_HEIGHT)
			{
				const aspectRatio = this.file.image.width / this.file.image.height;

				if (this.file.image.width > MAX_WIDTH)
				{
					newWidth = MAX_WIDTH;
					newHeight = Math.round(MAX_WIDTH / aspectRatio);
				}

				if (newHeight > MAX_HEIGHT)
				{
					newWidth = Math.round(MAX_HEIGHT * aspectRatio);
					newHeight = MAX_HEIGHT;
				}
			}

			const sizes = {
				width: Math.max(newWidth, MIN_WIDTH),
				height: Math.max(newHeight, MIN_HEIGHT),
			};

			return {
				width: `${sizes.width}px`,
				height: `${sizes.height}px`,
				'object-fit': (sizes.width < 100 || sizes.height < 100) ? 'cover' : 'contain',
			};
		},
		viewerAttributes(): Object
		{
			return Utils.file.getViewerDataAttributes({
				viewerAttributes: this.file.viewerAttrs,
				previewImageSrc: this.file.urlPreview,
				context: FileViewerContext.dialog,
			});
		},
		canBeOpenedWithViewer(): boolean
		{
			return this.file.viewerAttrs && BX.UI?.Viewer;
		},
		imageTitle(): string
		{
			const size = Utils.file.formatFileSize(this.file.size);

			return this.loc(
				'IM_ELEMENTS_MEDIA_IMAGE_TITLE',
				{
					'#NAME#': this.file.name,
					'#SIZE#': size,
				},
			);
		},
		isLoaded(): boolean
		{
			return this.file.progress === 100;
		},
		isVideo(): boolean
		{
			return this.file.type === FileType.video;
		},
		showPlayIcon(): boolean
		{
			return this.isVideo && (this.isLoaded || !this.handleLoading);
		},
		isAnimated(): boolean
		{
			return this.file.extension === 'gif' || this.file.extension === 'webp';
		},
		previewSourceLink(): string
		{
			if (this.isAnimated)
			{
				return this.file.urlShow || this.file.urlDownload;
			}

			return this.file.urlPreview;
		},
		allowLazyLoad(): boolean
		{
			return !this.previewSourceLink.startsWith('blob:');
		},
		withoutPreview(): boolean
		{
			return !Type.isStringFilled(this.previewSourceLink);
		},
		progressBarSize(): string
		{
			return this.isGallery ? ProgressBarSize.S : ProgressBarSize.L;
		},
	},
	methods: {
		download()
		{
			if (this.file.progress !== 100 || this.canBeOpenedWithViewer)
			{
				return;
			}

			window.open(this.file.urlDownload, '_blank');
		},
		loc(phraseCode: string, replacements: {[string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
		onRemoveClick()
		{
			this.$emit('remove', { file: this.file });
		},
		onCancelClick(event: { file: ImModelFile })
		{
			this.$emit('cancelClick', event);
		},
	},
	template: `
		<div 
			v-bind="viewerAttributes" 
			class="bx-im-elements-media-gallery-image-item__container" 
			:class="{'--without-preview': withoutPreview}"
			@click="download"
			:style="imageSize"
		>
			<img
				v-if="allowLazyLoad"
				v-lazyload
				data-lazyload-dont-hide
				:data-lazyload-src="previewSourceLink"
				:title="imageTitle"
				:alt="file.name"
				class="bx-im-elements-media-gallery-image-item__source"
			/>
			<img
				v-else
				:src="previewSourceLink"
				:title="imageTitle"
				:alt="file.name"
				class="bx-im-elements-media-gallery-image-item__source"
			/>
			<ProgressBar 
				v-if="handleLoading" 
				:item="file" 
				:size="progressBarSize" 
				@cancelClick="onCancelClick"
			/>
			<div v-if="showPlayIcon" class="bx-im-elements-media-gallery-image-item__play-icon-container">
				<div class="bx-im-elements-media-gallery-image-item__play-icon"></div>
			</div>
			<div v-if="allowRemove" class="bx-im-elements-media-gallery-image-item__remove" @click="onRemoveClick">
				<div class="bx-im-elements-media-gallery-image-item__remove-icon"></div>
			</div>
		</div>
	`,
};
