import { ImageItem } from './components/image-item';

import type { ImModelFile } from 'im.v2.model';

const MediaComponents = Object.freeze({
	image: 'ImageItem',
	video: 'ImageItem',
});

// @vue/component
export const MediaItem = {
	name: 'MediaItem',
	components: { ImageItem },
	props: {
		/** @type { ImModelFile } */
		file: {
			type: Object,
			required: true,
		},
		allowRemove: {
			type: Boolean,
			default: false,
		},
		handleLoading: {
			type: Boolean,
			default: false,
		},
	},
	emits: ['remove', 'cancelClick'],
	computed: {
		componentName(): string
		{
			return MediaComponents[this.file.type];
		},
	},
	methods: {
		onRemove(event: { file: ImModelFile })
		{
			this.$emit('remove', event);
		},
		onCancel(event: { file: ImModelFile })
		{
			this.$emit('cancelClick', event);
		},
	},
	template: `
		<component
			:is="componentName"
			:file="file"
			:allowRemove="allowRemove"
			:handleLoading="handleLoading"
			@remove="onRemove"
			@cancelClick="onCancel"
		/>
	`,
};
