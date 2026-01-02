import { FileIcon } from 'ui.uploader.tile-widget';

import { BaseItem } from '../base-item';

import type { BitrixVueComponentProps } from 'ui.vue3';

const MAX_FILENAME_LENGTH = 60;
const CROP_FILENAME_END_LENGTH = 5;

// @vue/component
export const Item: BitrixVueComponentProps = {
	// eslint-disable-next-line vue/multi-word-component-names
	name: 'Item',

	components: {
		FileIcon,
	},

	extends: BaseItem,

	computed: {
		formatFileName(): string
		{
			const fileName = this.fileNameWithoutExtension;
			if (fileName.length <= MAX_FILENAME_LENGTH)
			{
				return fileName;
			}

			const start = fileName.slice(0, MAX_FILENAME_LENGTH - CROP_FILENAME_END_LENGTH - 1);
			const end = fileName.slice(-1 * CROP_FILENAME_END_LENGTH);

			return `${start}...${end}`;
		},

		fileNameWithoutExtension(): string
		{
			if (!this.file.extension)
			{
				return this.file.name;
			}

			const start = 0;
			const end = this.file.name.length - this.file.extension.length - 1;

			return this.file.name.slice(start, end);
		},

		formatExtension(): string
		{
			return this.file.extension ? `.${this.file.extension}` : '';
		},
	},

	// language=Vue
	template: `
		<div class="main-file-field-selectable-view__list-item">
			<div class="main-file-field-selectable-view__list-item-preview-container">
				<div v-if="file.isImage" class="main-file-field-selectable-view__list-item-preview --image">
					<img class="main-file-field-selectable-view__image" :src="file.url" alt="">
				</div>
				<FileIcon
					v-else
					class="main-file-field-selectable-view__list-item-preview --icon"
					:name="file.extension || '...'"
					:size="16"
				/>
			</div>
			<div class="main-file-field-selectable-view__list-item-filename-container" :title="file.name">
				<span class="main-file-field-selectable-view__list-item-filename">{{ formatFileName }}</span>
				<span class="main-file-field-selectable-view__list-item-extension">{{ formatExtension }}</span>
			</div>
		</div>
	`,
};
