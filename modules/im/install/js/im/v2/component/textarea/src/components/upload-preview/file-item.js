import { Spinner, SpinnerSize } from 'im.v2.component.elements';
import { FileStatus } from 'im.v2.const';

import '../../css/upload-preview/file-item.css';

import type { ImModelFile } from 'im.v2.model';

// @vue/component
export const FileItem = {
	name: 'FileItem',
	components: { Spinner },
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
		SpinnerSize: () => SpinnerSize,
		fileFromStore(): ImModelFile
		{
			return this.file;
		},
		hasPreview(): boolean
		{
			return this.fileFromStore.urlPreview !== '';
		},
		hasError(): boolean
		{
			return this.fileFromStore.status === FileStatus.error;
		},
	},
	template: `
		<div class="bx-im-upload-preview-file-item__container bx-im-upload-preview-file-item__scope">
			<div v-if="hasError" class="bx-im-upload-preview-file-item__item-error">
				<div class="bx-im-upload-preview-file-item__item-error-icon"></div>
				<div class="bx-im-upload-preview-file-item__item-error-text">
					{{ $Bitrix.Loc.getMessage('IM_TEXTAREA_UPLOAD_PREVIEW_POPUP_UPLOAD_ERROR') }}
				</div>
			</div>
			<Spinner v-else-if="!hasPreview" :size="SpinnerSize.s" />
			<img 
				v-else 
				:src="fileFromStore.urlPreview" 
				:alt="fileFromStore.name"
				:title="fileFromStore.name"
				class="bx-im-upload-preview-file-item__item-image"
			>
		</div>
	`,
};
