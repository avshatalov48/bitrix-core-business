import { Type } from 'main.core';

import { Utils } from 'im.v2.lib.utils';
import { FileViewerContext, FileStatus } from 'im.v2.const';
import { VideoPlayer } from	'im.v2.component.elements.videoplayer';
import { ProgressBar } from 'im.v2.component.elements.progressbar';
import { Outline } from 'ui.icon-set.api.vue';

import '../../css/items/video.css';

import type { ImModelFile, ImModelMessage } from 'im.v2.model';

const VIDEO_SIZE_TO_AUTOPLAY = 5_000_000;
const MAX_WIDTH = 460;
const MAX_HEIGHT = 380;
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
	},
	emits: ['cancelClick'],
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
			return Utils.file.getViewerDataAttributes({
				viewerAttributes: this.file.viewerAttrs,
				previewImageSrc: this.file.urlPreview,
				context: FileViewerContext.dialog,
			});
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

			window.open(this.file.urlDownload, '_blank');
		},
		onCancelClick(event)
		{
			this.$emit('cancelClick', event);
		},
		getHandleStatus(): Array<string>
		{
			return [
				FileStatus.preparing,
				FileStatus.progress,
				FileStatus.upload,
			];
		},
		getStatusMap(): { [key: string]: { iconClass: string, labelText: string } }
		{
			return {
				[FileStatus.preparing]: {
					iconClass: Outline.CLOUD,
					labelText: this.$Bitrix.Loc.getMessage('IM_MESSAGE_FILE_PREPARING_PROGRESS_LABEL'),
				},
			};
		},
	},
	template: `
		<div
			class="bx-im-video-item__container bx-im-video-item__scope"
			:class="{'--with-forward': isForward}"
			@click="download"
		>
			<ProgressBar 
				:item="file"
				:handleStatus="getHandleStatus()"
				:statusMap="getStatusMap()"
				@cancelClick="onCancelClick"
			/>
			<VideoPlayer
				:fileId="file.id"
				:src="file.urlDownload"
				:previewImageUrl="file.urlPreview"
				:elementStyle="imageSize"
				:withAutoplay="autoplay"
				:withPlayerControls="isLoaded"
				:viewerAttributes="viewerAttributes"
			/>
		</div>
	`,
};
