import { Type } from 'main.core';
import { MessageStatus } from 'im.v2.component.message.elements';
import { MediaGallery, MediaGalleryItem } from 'im.v2.component.elements.media-gallery';
import { FileType } from 'im.v2.const';

import { VideoItem } from './items/video';

import '../css/items/media-content.css';

import type { ImModelMessage, ImModelFile } from 'im.v2.model';

type Size = { width: number, height: number };
type MaxSize = { maxWidth: number, maxHeight: number };
type MinSize = { minWidth: number, minHeight: number };

const SINGLE_IMAGE_CONTAINER_MAX_SIZE: MaxSize = {
	maxWidth: 460,
	maxHeight: 590,
};

const SINGLE_IMAGE_CONTAINER_MIN_SIZE: MinSize = {
	minWidth: 120,
	minHeight: 120,
};

const SINGLE_IMAGE_WITH_TEXT_CONTAINER_MAX_SIZE: MaxSize = {
	maxWidth: 460,
	maxHeight: 590,
};

const SINGLE_IMAGE_WITH_TEXT_CONTAINER_MIN_SIZE: MinSize = {
	minWidth: 460,
	minHeight: 260,
};

const GALLERY_MAX_WIDTH: number = 460;

// @vue/component
export const MediaContent = {
	name: 'MediaContent',
	components: { VideoItem, MessageStatus, MediaGallery, MediaGalleryItem },
	props: {
		item: {
			type: Object,
			required: true,
		},
		containerHeight: {
			type: [Number, null],
			default: null,
		},
	},
	emits: ['cancelClick'],
	computed: {
		message(): ImModelMessage
		{
			return this.item;
		},
		files(): Array<ImModelFile>
		{
			return this.message.files.map((fileId) => {
				return this.$store.getters['files/get'](fileId);
			});
		},
		filesCount(): number
		{
			return this.files.length;
		},
		firstFile(): ?ImModelFile
		{
			return this.files[0];
		},
		firstFileId(): ?ImModelFile['id']
		{
			return this.firstFile?.id;
		},
		hasText(): boolean
		{
			return this.message.text.length > 0;
		},
		hasAttach(): boolean
		{
			return this.message.attach.length > 0;
		},
		onlyMedia(): boolean
		{
			return !this.hasText && !this.hasAttach;
		},
		isSingleVideo(): boolean
		{
			return (
				this.filesCount === 1
				&& this.firstFile.type === FileType.video
			);
		},
		isSingleImage(): boolean
		{
			return (
				this.filesCount === 1
				&& this.firstFile.type === FileType.image
			);
		},
		singleImageMaxSize(): MaxSize
		{
			if (Type.isNumber(this.containerHeight))
			{
				const { maxWidth, maxHeight } = SINGLE_IMAGE_CONTAINER_MAX_SIZE;
				const maxAllowedPreviewHeight = Math.min(maxHeight, this.containerHeight * 0.9);

				return {
					maxWidth,
					maxHeight: maxAllowedPreviewHeight,
				};
			}

			return SINGLE_IMAGE_CONTAINER_MAX_SIZE;
		},
		imageWithTextMaxSize(): MaxSize
		{
			if (Type.isNumber(this.containerHeight))
			{
				const { maxWidth, maxHeight } = SINGLE_IMAGE_WITH_TEXT_CONTAINER_MAX_SIZE;
				const maxAllowedPreviewHeight = Math.min(maxHeight, this.containerHeight * 0.9);

				return {
					maxWidth,
					maxHeight: maxAllowedPreviewHeight,
				};
			}

			return SINGLE_IMAGE_WITH_TEXT_CONTAINER_MAX_SIZE;
		},
		singleImageSize(): { width: number; height: number }
		{
			if (this.onlyMedia)
			{
				return this.calcPreviewSize({
					...this.firstFile.image,
					...SINGLE_IMAGE_CONTAINER_MIN_SIZE,
					...this.singleImageMaxSize,
				});
			}

			return this.calcPreviewSize({
				...this.firstFile.image,
				...SINGLE_IMAGE_WITH_TEXT_CONTAINER_MIN_SIZE,
				...this.imageWithTextMaxSize,
			});
		},
	},
	methods: {
		onCancel(event)
		{
			this.$emit('cancelClick', event);
		},
		calcPreviewSize(options: Size & MinSize & MaxSize): Size
		{
			const { width, height, minWidth, minHeight, maxWidth, maxHeight } = options;

			const adjustedWidth = Math.min(Math.max(width, minWidth), maxWidth);
			const adjustedHeight = Math.min(Math.max(height, minHeight), maxHeight);

			if (adjustedWidth === width && adjustedHeight === height)
			{
				return { width, height };
			}

			const scale = Math.min(
				maxWidth / width,
				maxHeight / height,
				adjustedWidth / width,
				adjustedHeight / height,
			);

			return {
				width: Math.max(Math.min(width * scale, maxWidth), minWidth),
				height: Math.max(Math.min(height * scale, maxHeight), minHeight),
			};
		},
		getGalleryMaxWidth(): number
		{
			return GALLERY_MAX_WIDTH;
		},
	},
	template: `
		<div class="bx-im-message-media-content__container">
			<div v-if="isSingleImage" class="bx-im-message-media-content__single-image">
				<MediaGalleryItem
					:file="firstFile"
					:size="singleImageSize"
					:handleLoading="true"
					@cancelClick="onCancel"
				/>
			</div>
			<div v-else-if="isSingleVideo" class="bx-im-message-media-content__single-video">
				<VideoItem
					:id="firstFileId"
					:message="message"
					@cancelClick="onCancel"
				/>
			</div>
			<div v-else class="bx-im-message-media-content__gallery">
				<MediaGallery
					:files="files"
					:width="getGalleryMaxWidth()"
					:handleLoading="true"
					@cancelClick="onCancel"
				/>
			</div>
			<div v-if="onlyMedia" class="bx-im-message-media-content__status-container">
				<MessageStatus :item="message" :isOverlay="true" />
			</div>
		</div>
	`,
};
