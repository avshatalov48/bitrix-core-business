import { Type } from 'main.core';

import { FileType } from 'im.v2.const';
import { Spinner, SpinnerSize } from 'im.v2.component.elements';

import type { ImModelFile } from 'im.v2.model';

// @vue/component
export const ImagePreviewItem = {
	name: 'ImagePreviewItem',
	components: { Spinner },
	props:
	{
		item: {
			type: Object,
			required: true,
		},
	},
	computed:
	{
		SpinnerSize: () => SpinnerSize,
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
		hasPreview(): boolean
		{
			return Type.isStringFilled(this.file.urlPreview);
		},
		isVideo(): boolean
		{
			return this.file.type === FileType.video;
		},
	},
	template: `
		<div class="bx-im-upload-preview-file-item__image-container">
			<Spinner v-if="!hasPreview" :size="SpinnerSize.s" />
			<img
				v-else
				:src="source"
				:alt="name"
				:title="name"
				class="bx-im-upload-preview-file-item__item-image"
			>
			<div v-if="isVideo" class="bx-im-upload-preview-file-item__play-icon-container">
				<div class="bx-im-upload-preview-file-item__play-icon"></div>
			</div>
		</div>
	`,
};
