import { SocialVideo } from 'ui.vue3.components.socialvideo';

import { Utils } from 'im.v2.lib.utils';
import { ProgressBar } from './progress-bar';

import '../../css/items/video.css';

import type { ImModelFile } from 'im.v2.model';

const VIDEO_SIZE_TO_AUTOPLAY = 5_000_000;
const MAX_WIDTH = 420;
const MAX_HEIGHT = 340;
const MIN_WIDTH = 200;
const MIN_HEIGHT = 100;
const DEFAULT_WIDTH = 320;
const DEFAULT_HEIGHT = 180;

// @vue/component
export const VideoItem = {
	name: 'VideoItem',
	components: { SocialVideo, ProgressBar },
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
		autoplay(): boolean
		{
			return this.file.size < VIDEO_SIZE_TO_AUTOPLAY;
		},
		canBeOpenedWithViewer(): boolean
		{
			return this.file.viewerAttrs && BX.UI?.Viewer;
		},
		viewerAttributes(): Object
		{
			return Utils.file.getViewerDataAttributes(this.file.viewerAttrs);
		},
		imageSize(): {width: string, height: string, backgroundSize: string}
		{
			let newWidth = this.file.image.width;
			let newHeight = this.file.image.height;

			if (!newHeight || !newWidth)
			{
				return {
					width: `${DEFAULT_WIDTH}px`,
					height: `${DEFAULT_HEIGHT}px`,
				};
			}

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
		getPlayCallback()
		{
			if (this.autoplay)
			{
				return null;
			}

			return () => {};
		},
	},
	template: `
		<div @click="download" class="bx-im-video-item__container bx-im-video-item__scope">
			<ProgressBar v-if="!isLoaded" :item="file" :messageId="messageId" />
			<SocialVideo
				v-bind="viewerAttributes"
				:id="file.id"
				:src="file.urlShow"
				:preview="file.urlPreview"
				:elementStyle="imageSize"
				:autoplay="autoplay"
				:showControls="isLoaded"
				:playCallback="getPlayCallback()"
			/>
		</div>
	`,
};
