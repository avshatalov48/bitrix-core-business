import { Type } from 'main.core';
import { Utils } from 'im.v2.lib.utils';
import 'ui.icons';

// @vue/component
export const FilePreviewItem = {
	name: 'FilePreviewItem',
	props: {
		file: {
			type: Object,
			required: true,
		},
		maxNameLength: {
			type: Number,
			default: 25,
		},
	},
	computed: {
		fileIconClass(): string
		{
			return `ui-icon ui-icon-file-${this.file.icon}`;
		},
		fileShortName(): string
		{
			return Utils.file.getShortFileName(this.file.name, this.maxNameLength);
		},
		fileSize(): string
		{
			return Utils.file.formatFileSize(this.file.size);
		},
		hasPreview(): boolean
		{
			return Type.isStringFilled(this.file.urlPreview);
		},
		imageStyles(): { backgroundImage: string }
		{
			return {
				backgroundImage: `url(${this.file.urlPreview})`,
			};
		},
	},
	template: `
		<div class="bx-im-upload-preview-file-item__file-container">
			<div class="bx-im-upload-preview-file-item__icon">
				<div v-if="hasPreview" :style="imageStyles" class="bx-im-upload-preview-file-item__preview"></div>
				<div v-else :class="fileIconClass"><i></i></div>
			</div>
			<div class="bx-im-upload-preview-file-item__info">
				<div class="bx-im-upload-preview-file-item__name">{{ fileShortName }}</div>
				<div class="bx-im-upload-preview-file-item__size">{{ fileSize }}</div>
			</div>
		</div>
	`,
};
