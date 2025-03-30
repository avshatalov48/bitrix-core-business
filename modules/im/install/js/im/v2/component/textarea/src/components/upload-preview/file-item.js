import { FileStatus } from 'im.v2.const';

import { FilePreviewItem } from './items/file';
import { ErrorPreviewItem } from './items/error';

import '../../css/upload-preview/file-item.css';

// @vue/component
export const FileItem = {
	name: 'FileItem',
	components: { FilePreviewItem, ErrorPreviewItem },
	props: {
		file: {
			type: Object,
			required: true,
		},
		removable: {
			type: Boolean,
			default: false,
		},
	},
	emits: ['onRemoveItem'],
	computed: {
		hasError(): boolean
		{
			return this.file.status === FileStatus.error;
		},
		previewComponentName(): string
		{
			if (this.hasError)
			{
				return ErrorPreviewItem.name;
			}

			return FilePreviewItem.name;
		},
	},
	methods: {
		onRemoveClick()
		{
			this.$emit('onRemoveItem', { file: this.file });
		},
	},
	template: `
		<div class="bx-im-upload-preview-file-item__scope">
			<component
				:is="previewComponentName"
				:file="file"
			/>
			<div v-if="removable" class="bx-im-upload-preview-file-item__remove" @click="onRemoveClick">
				<div class="bx-im-upload-preview-file-item__remove-icon"></div>
			</div>
		</div>
	`,
};
