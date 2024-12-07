import { MessageStatus } from 'im.v2.component.message.elements';
import { FileType } from 'im.v2.const';

import { getGalleryElementsConfig } from '../helpers/get-gallery-elements-config';
import { getGalleryGridRowsConfig } from '../helpers/get-gallery-grid-rows-config';
import { GalleryItem } from './items/gallery-item';
import { VideoItem } from './items/video';

import '../css/items/media-content.css';

import type { ImModelMessage } from 'im.v2.model';

const FILES_LIMIT = 10;

// @vue/component
export const MediaContent = {
	name: 'MediaContent',
	components: { GalleryItem, VideoItem, MessageStatus },
	props:
	{
		item: {
			type: Object,
			required: true,
		},
	},
	computed:
	{
		message(): ImModelMessage
		{
			return this.item;
		},
		fileIds(): number[]
		{
			return this.message.files.slice(0, FILES_LIMIT);
		},
		firstFileId(): number
		{
			return this.message.files[0];
		},
		isGallery(): boolean
		{
			return this.message.files.length > 1;
		},
		galleryRowConfig(): { gridTemplateRows: string }
		{
			return getGalleryGridRowsConfig(this.fileIds.length);
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
			if (this.isGallery)
			{
				return false;
			}

			return this.$store.getters['files/get'](this.firstFileId, true).type === FileType.video;
		},
	},
	methods:
	{
		getGalleryElementStyles(index: number): { gridRowEnd: string, gridColumnEnd: string }
		{
			return getGalleryElementsConfig(this.fileIds.length, index);
		},
	},
	template: `
		<div class="bx-im-message-media-content__container">
			<div v-if="isGallery" class="bx-im-message-media-content__gallery" :style="galleryRowConfig">
				<GalleryItem
					v-for="(fileId, index) in fileIds"
					:key="fileId"
					:id="fileId"
					:isGallery="true"
					:message="message"
					:style="getGalleryElementStyles(index)"
				/>
			</div>
			<div v-else-if="isSingleVideo" class="bx-im-message-media-content__single-video">
				<VideoItem
					:id="firstFileId"
					:message="message"
				/>
			</div>
			<div v-else class="bx-im-message-media-content__single-image">
				<GalleryItem
					:id="firstFileId"
					:message="message"
				/>
			</div>
			<div v-if="onlyMedia" class="bx-im-message-media-content__status-container">
				<MessageStatus :item="message" :isOverlay="true" />
			</div>
		</div>
	`,
};
