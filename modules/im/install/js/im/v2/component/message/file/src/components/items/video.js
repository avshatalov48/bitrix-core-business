import { Type } from 'main.core';

import { Utils } from 'im.v2.lib.utils';
import { VideoPlayer } from	'im.v2.component.elements';

import { ProgressBar } from './progress-bar';

import '../../css/items/video.css';

import type { ImModelFile, ImModelMessage } from 'im.v2.model';

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
	components: { VideoPlayer, ProgressBar },
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
		handleLoading: {
			type: Boolean,
			default: true,
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
		isForward(): boolean
		{
			return Type.isStringFilled(this.messageItem.forward.id);
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
	},
	template: `
		<div
			class="bx-im-video-item__container bx-im-video-item__scope"
			:class="{'--with-forward': isForward}"
			@click="download"
		>
			<ProgressBar v-if="!isLoaded && handleLoading" :item="file" :messageId="messageItem.id" />
			<VideoPlayer
				:fileId="file.id"
				:src="file.urlShow"
				:previewImageUrl="file.urlPreview"
				:elementStyle="imageSize"
				:withAutoplay="autoplay"
				:withPlayerControls="isLoaded"
				:viewerAttributes="viewerAttributes"
			/>
		</div>
	`,
};
