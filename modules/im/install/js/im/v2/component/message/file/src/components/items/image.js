import { Utils } from 'im.v2.lib.utils';
import type { ImModelFile } from 'im.v2.model';
import { lazyload } from 'ui.vue3.directives.lazyload';

import { ProgressBar } from './progress-bar';

import '../../css/items/image.css';

const MAX_WIDTH = 420;
const MAX_HEIGHT = 340;
const MIN_WIDTH = 200;
const MIN_HEIGHT = 100;

// @vue/component
export const ImageItem = {
	name: 'ImageItem',
	directives: { lazyload },
	components: { ProgressBar },
	props:
	{
		item: {
			type: Object,
			required: true,
		},
		messageId: {
			type: [String, Number],
			required: true,
		},
	},
	computed:
	{
		file(): ImModelFile
		{
			return this.item;
		},
		imageSize(): {width: string, height: string, backgroundSize: string}
		{
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
			class="bx-im-media-image__container" 
			@click="download"
			:style="imageSize"
		>
			<img
				v-lazyload
				data-lazyload-dont-hide
				:data-lazyload-src="file.urlShow"
				:title="imageTitle"
				:alt="file.name"
				class="bx-im-media-image__source"
			/>
			<ProgressBar v-if="!isLoaded" :item="file" :messageId="messageId" />
		</div>
	`,
};
