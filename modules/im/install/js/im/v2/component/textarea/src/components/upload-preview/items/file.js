import 'ui.icons';
import { Utils } from 'im.v2.lib.utils';

import type { ImModelFile } from 'im.v2.model';

// @vue/component
export const FilePreviewItem = {
	name: 'FilePreviewItem',
	props:
	{
		item: {
			type: Object,
			required: true,
		},
	},
	computed:
	{
		file(): ImModelFile
		{
			return this.item;
		},
		source(): string
		{
			return this.file.urlPreview;
		},
		name(): string
		{
			return this.file.name;
		},
		fileIconClass(): string
		{
			return `ui-icon ui-icon-file-${this.file.icon}`;
		},
		fileShortName(): string
		{
			const NAME_MAX_LENGTH = 25;

			return Utils.file.getShortFileName(this.file.name, NAME_MAX_LENGTH);
		},
		fileSize(): string
		{
			return Utils.file.formatFileSize(this.file.size);
		},
	},
	template: `
		<div class="bx-im-upload-preview-file-item__file-container">
			<div class="bx-im-upload-preview-file-item__icon">
				<div :class="fileIconClass"><i></i></div>
			</div>
			<div class="bx-im-upload-preview-file-item__info">
				<div class="bx-im-upload-preview-file-item__name">{{ fileShortName }}</div>
				<div class="bx-im-upload-preview-file-item__size">{{ fileSize }}</div>
			</div>
		</div>
	`,
};
