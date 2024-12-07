import 'ui.icons.disk';

import { FileType } from 'im.v2.const';
import { Utils } from 'im.v2.lib.utils';

import { ProgressBar } from './progress-bar';
import { BaseFileContextMenu } from '../../classes/base-file-context-menu';

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
	computed:
	{
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
			return Utils.file.getViewerDataAttributes(this.file.viewerAttrs);
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
		isImage(): boolean
		{
			return this.file.type === FileType.image;
		},
	},
	created()
	{
		this.contextMenu = new BaseFileContextMenu();
	},
	beforeUnmount()
	{
		this.contextMenu.destroy();
	},
	methods:
	{
		download()
		{
			if (this.file.progress !== 100 || this.canBeOpenedWithViewer)
			{
				return;
			}

			const url = this.file.urlDownload ?? this.file.urlShow;
			window.open(url, '_blank');
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
	},
	template: `
		<div class="bx-im-base-file-item__container">
			<div class="bx-im-base-file-item__icon-container" ref="loader-icon" v-bind="viewerAttributes" @click="download">
				<ProgressBar v-if="!isLoaded" :item="file" :messageId="messageId" :withLabels="false" />
				<div v-if="isImage" :style="imageStyles" class="bx-im-base-file-item__image"></div>
				<div v-else :class="iconClass" class="bx-im-base-file-item__type-icon ui-icon"><i></i></div>
			</div>
			<div class="bx-im-base-file-item__content" v-bind="viewerAttributes" @click="download">
				<span :title="file.name" class="bx-im-base-file-item__title">
					{{ fileShortName }}
				</span>
				<div class="bx-im-base-file-item__size">{{ fileSize }}</div>
			</div>
			<div 
				class="bx-im-base-file-item__download-icon"
				:class="{'--not-active': !isLoaded}"
				@click="openContextMenu"
			></div>
		</div>
	`,
};
