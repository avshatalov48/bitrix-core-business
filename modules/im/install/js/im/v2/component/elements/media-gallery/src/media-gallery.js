import type { ImModelFile } from 'im.v2.model';
import { MediaGalleryRow } from './media-gallery-row';

import './css/media-gallery.css';

const rowsLayouts = {
	1: [1],
	2: [2],
	3: [3],
	4: [2, 2],
	5: [2, 3],
	6: [2, 2, 2],
	7: [2, 3, 2],
	8: [2, 3, 3],
	9: [2, 3, 2, 2],
	10: [2, 3, 2, 3],
};

// @vue/component
export const MediaGallery = {
	name: 'MediaGallery',
	components: { MediaGalleryRow },
	props: {
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
			default: 376,
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
	emits: ['removeItem', 'cancelClick', 'itemDragStart', 'itemDragEnd', 'itemDragOver', 'itemDragLeave', 'itemDrop'],
	computed: {
		rows(): number
		{
			const lengths = rowsLayouts[this.files.length];
			let cursor = 0;

			return lengths.map((rowSize) => {
				const row = this.files.slice(cursor, cursor + rowSize);
				cursor += rowSize;

				return row;
			});
		},
	},
	methods: {
		onRemoveItem(event: { file: ImModelFile })
		{
			this.$emit('removeItem', event);
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
	},
	template: `
		<div class="bx-im-elements-media-gallery">
			<MediaGalleryRow
				v-for="(row) in rows"
				:files="row"
				:allowRemoveItem="allowRemoveItem"
				:allowSorting="allowSorting"
				:handleLoading="handleLoading"
				:width="width"
				:highlightDropzone="highlightDropzone"
				@remove="onRemoveItem"
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
