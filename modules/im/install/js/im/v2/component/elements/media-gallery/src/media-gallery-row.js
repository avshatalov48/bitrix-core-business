import { Type } from 'main.core';
import { MediaGalleryItem } from './media-gallery-item';

import './css/media-gallery-row.css';

import type { ImModelFile } from 'im.v2.model';

type Size = {
	width: number,
	height: number,
};

const RATIO_THRESHOLD = 4;
const RATIO_FALLBACK = 3;

const MIN_PREVIEW_WIDTH = 70;
const MIN_PREVIEW_HEIGHT = 58;
const FALLBACK_WIDTH = 1600;
const FALLBACK_HEIGHT = 900;
const FALLBACK_SIZE: Size = {
	width: FALLBACK_WIDTH,
	height: FALLBACK_HEIGHT,
};

// @vue/component
export const MediaGalleryRow = {
	name: 'MediaGalleryRow',
	components: { MediaGalleryItem },
	props: {
		/** @type { Array<ImModelFile> } */
		files: {
			type: Array,
			required: true,
		},
		allowRemoveItem: {
			type: Boolean,
			default: false,
		},
		allowSorting: {
			type: Boolean,
			default: false,
		},
		handleLoading: {
			type: Boolean,
			default: false,
		},
		width: {
			type: Number,
			required: true,
		},
		spacingSize: {
			type: Number,
			default: 1,
		},
		viewerGroupBy: {
			type: String || null,
			default: null,
		},
		highlightDropzone: {
			type: Object,
			default: () => {
				return {};
			},
		},
	},
	emits: ['remove', 'cancelClick', 'itemDragStart', 'itemDragEnd', 'itemDragOver', 'itemDragLeave', 'itemDrop'],
	computed: {
		totalRatio(): number
		{
			return this.files.reduce((acc, file: ImModelFile) => {
				const size = this.getSafeSize(file);

				return acc + this.getSafeRatio(size);
			}, 0);
		},
		spacing(): number
		{
			return this.files.length - 1;
		},
		availableContainerWidth(): number
		{
			return this.width - (this.spacing * this.spacingSize);
		},
		sizes(): Array<Size>
		{
			const height = this.availableContainerWidth / this.totalRatio;

			return this.files.map((file: ImModelFile) => {
				const size = this.getSafeSize(file);
				const width = this.getSafeRatio(size) * height;

				return {
					width: Math.max(width, MIN_PREVIEW_WIDTH),
					height: Math.max(height, MIN_PREVIEW_HEIGHT),
				};
			});
		},
	},
	methods: {
		getSafeSize(file: ImModelFile): Size
		{
			if (
				Type.isNumber(file.image?.width)
				&& Type.isNumber(file.image?.height)
			)
			{
				return {
					width: file.image?.width,
					height: file.image?.height,
				};
			}

			return FALLBACK_SIZE;
		},
		getSafeRatio(size: Size): number
		{
			const abnormalRatio = Math.max(
				size.width / size.height,
				size.height / size.width,
			);

			if (abnormalRatio > RATIO_THRESHOLD)
			{
				if (size.height > size.width)
				{
					return 1 / RATIO_FALLBACK;
				}

				return RATIO_FALLBACK;
			}

			return size.width / size.height;
		},
		onRemove(event)
		{
			this.$emit('remove', event);
		},
		onCancel(event)
		{
			this.$emit('cancelClick', event);
		},
		onItemDragStart(event)
		{
			this.$emit('itemDragStart', event);
		},
		onItemDragEnd(event)
		{
			this.$emit('itemDragEnd', event);
		},
		onItemDragOver(event)
		{
			this.$emit('itemDragOver', event);
		},
		onItemDragLeave(event)
		{
			this.$emit('itemDragLeave', event);
		},
		onItemDrop(event)
		{
			this.$emit('itemDrop', event);
		},
		getHighlightDropzonePosition(file: ImModelFile): string | null
		{
			if (file.id === this.highlightDropzone.fileId)
			{
				return this.highlightDropzone.position;
			}

			return null;
		},
	},
	template: `
		<div class="bx-im-elements-media-gallery__row">
			<MediaGalleryItem
				v-for="(file, index) in files"
				:key="file.id"
				:file="file"
				:size="sizes[index]"
				:allowRemove="allowRemoveItem"
				:allowSorting="allowSorting"
				:handleLoading="handleLoading"
				:highlightDropzonePosition="getHighlightDropzonePosition(file)"
				viewerGroupBy="viewerGroupBy"
				@remove="onRemove"
				@cancelClick="onCancel"
				@itemDragStart="onItemDragStart"
				@itemDragEnd="onItemDragEnd"
				@itemDragOver="onItemDragOver"
				@itemDragLeave="onItemDragLeave"
				@itemDrop="onItemDrop"
			/>
		</div>
	`,
};
