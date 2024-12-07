import { Type } from 'main.core';
import { lazyload } from 'ui.vue3.directives.lazyload';

import { FileType } from 'im.v2.const';
import { Utils } from 'im.v2.lib.utils';
import { ImModelMessage } from 'im.v2.model';

import { ProgressBar } from './progress-bar';

import '../../css/items/gallery-item.css';

import type { ImModelFile } from 'im.v2.model';

const MAX_WIDTH = 488;
const MAX_HEIGHT = 340;
const MIN_WIDTH = 200;
const MIN_HEIGHT = 100;

// @vue/component
export const GalleryItem = {
	name: 'GalleryItem',
	directives: { lazyload },
	components: { ProgressBar },
	props:
	{
		id: {
			type: [String, Number],
			required: true,
		},
		message: {
			type: Object,
			required: true,
		},
		isGallery: {
			type: Boolean,
			default: false,
		},
	},
	computed:
	{
		messageItem(): ImModelMessage
		{
			return this.message;
		},
		file(): ImModelFile
		{
			return this.$store.getters['files/get'](this.id, true);
		},
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
			return Utils.file.getViewerDataAttributes(this.file.viewerAttrs);
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
		isForward(): boolean
		{
			return Type.isStringFilled(this.messageItem.forward.id);
		},
		isVideo(): boolean
		{
			return this.file.type === FileType.video;
		},
		previewSourceLink(): string
		{
			// for a video, we use "urlPreview", because there is an image preview.
			// for an image, we use "urlShow", because for large gif files in "urlPreview" we have
			// a static image (w/o animation) .
			return this.isVideo ? this.file.urlPreview : this.file.urlShow;
		},
	},
	methods:
	{
		download()
		{
			if (this.file.progress !== 100 || this.canBeOpenedWithViewer)
			{
				return;
			}

			const url = this.file.urlDownload ?? this.file.urlShow;
			window.open(url, '_blank');
		},
		loc(phraseCode: string, replacements: {[string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<div 
			v-bind="viewerAttributes" 
			class="bx-im-gallery-item__container" 
			:class="{'--with-forward': isForward}"
			@click="download"
			:style="imageSize"
		>
			<img
				v-lazyload
				data-lazyload-dont-hide
				:data-lazyload-src="previewSourceLink"
				:title="imageTitle"
				:alt="file.name"
				class="bx-im-gallery-item__source"
			/>
			<ProgressBar v-if="!isLoaded" :item="file" :messageId="messageItem.id" :withLabels="!isGallery" />
			<div v-if="isVideo" class="bx-im-gallery-item__play-icon-container">
				<div class="bx-im-gallery-item__play-icon"></div>
			</div>
		</div>
	`,
};
