import 'ui.icons.disk';
import { Type } from 'main.core';

import { FileViewerContext } from 'im.v2.const';
import { Utils } from 'im.v2.lib.utils';
import { ProgressBar, ProgressBarSize } from 'im.v2.component.elements.progressbar';

import '../../css/items/base-file.css';

import type { ImModelFile } from 'im.v2.model';

// @vue/component
export const BaseFileItem = {
	name: 'BaseFileItem',
	components: { ProgressBar },
	props:
	{
		id: {
			type: [String, Number],
			required: true,
		},
		messageId: {
			type: [String, Number],
			required: true,
		},
	},
	emits: ['cancelClick'],
	computed:
	{
		ProgressBarSize: () => ProgressBarSize,
		file(): ImModelFile
		{
			return this.$store.getters['files/get'](this.id, true);
		},
		fileShortName(): string
		{
			const NAME_MAX_LENGTH = 20;

			return Utils.file.getShortFileName(this.file.name, NAME_MAX_LENGTH);
		},
		fileSize(): string
		{
			return Utils.file.formatFileSize(this.file.size);
		},
		iconClass(): string
		{
			const iconType = Utils.file.getIconTypeByFilename(this.file.name);

			return `ui-icon-file-${iconType}`;
		},
		canBeOpenedWithViewer(): boolean
		{
			return this.file.viewerAttrs && BX.UI?.Viewer;
		},
		viewerAttributes(): Object
		{
			return Utils.file.getViewerDataAttributes({
				viewerAttributes: this.file.viewerAttrs,
				previewImageSrc: this.file.urlPreview,
				context: FileViewerContext.dialog,
			});
		},
		isLoaded(): boolean
		{
			return this.file.progress === 100;
		},
		imageStyles(): {backgroundImage: string}
		{
			return {
				backgroundImage: `url(${this.file.urlPreview})`,
			};
		},
		hasPreview(): boolean
		{
			return Type.isStringFilled(this.file.urlPreview);
		},
	},
	methods:
	{
		download()
		{
			if (this.file.progress !== 100 || this.canBeOpenedWithViewer)
			{
				return;
			}

			window.open(this.file.urlDownload, '_blank');
		},
		loc(phraseCode: string, replacements: {[string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
		openContextMenu(event: PointerEvent)
		{
			this.$emit('openContextMenu', {
				event,
				fileId: this.id,
			});
		},
		onCancelClick(event)
		{
			this.$emit('cancelClick', event);
		},
	},
	template: `
		<div class="bx-im-base-file-item__container">
			<div class="bx-im-base-file-item__viewer-container" v-bind="viewerAttributes" @click="download">
				<div class="bx-im-base-file-item__icon-container" ref="loader-icon">
					<ProgressBar 
						v-if="!isLoaded" 
						:item="file"
						:size="ProgressBarSize.S"
						@cancelClick="onCancelClick"
					/>
				<div v-if="hasPreview" :style="imageStyles" class="bx-im-base-file-item__image"></div>
					<div v-else :class="iconClass" class="bx-im-base-file-item__type-icon ui-icon"><i></i></div>
				</div>
				<div class="bx-im-base-file-item__content">
					<span :title="file.name" class="bx-im-base-file-item__title">
						{{ fileShortName }}
					</span>
					<div class="bx-im-base-file-item__size">{{ fileSize }}</div>
				</div>
			</div>
			<div 
				class="bx-im-base-file-item__download-icon"
				:class="{'--not-active': !isLoaded}"
				@click="openContextMenu"
			></div>
		</div>
	`,
};
