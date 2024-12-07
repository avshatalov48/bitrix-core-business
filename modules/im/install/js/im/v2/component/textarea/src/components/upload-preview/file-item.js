import { FileStatus, FileType } from 'im.v2.const';

import { ErrorPreviewItem } from './items/error';
import { FilePreviewItem } from './items/file';
import { ImagePreviewItem } from './items/image';

import '../../css/upload-preview/file-item.css';

import type { ImModelFile } from 'im.v2.model';

// @vue/component
export const FileItem = {
	name: 'FileItem',
	components: { ErrorPreviewItem, ImagePreviewItem, FilePreviewItem },
	props: {
		file: {
			type: Object,
			required: true,
		},
	},
	data(): { text: string }
	{
		return {
			text: '',
			files: [],
		};
	},
	computed:
	{
		fileFromStore(): ImModelFile
		{
			return this.file;
		},
		hasError(): boolean
		{
			return this.fileFromStore.status === FileStatus.error;
		},
		previewComponentName(): string
		{
			if (this.hasError)
			{
				return ErrorPreviewItem;
			}

			if (this.fileFromStore.type === FileType.image || this.fileFromStore.type === FileType.video)
			{
				return ImagePreviewItem;
			}

			return FilePreviewItem;
		},
	},
	template: `
		<div class="bx-im-upload-preview-file-item__scope">
			<component
				:is="previewComponentName"
				:item="fileFromStore"
			/>
		</div>
	`,
};
