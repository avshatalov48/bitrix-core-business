import {EventEmitter} from 'main.core.events';
import {Utils} from 'im.v2.lib.utils';
import {ProgressBarManager} from 'im.v2.lib.progressbar';
import {EventType, FileType} from 'im.v2.const';

import 'ui.icons.disk';
import './css/file.css';

import type {ImModelFile} from 'im.v2.model';

// @vue/component
export const File = {
	name: 'FileComponent',
	props:
	{
		item: {
			type: Object,
			required: true
		},
	},
	data()
	{
		return {};
	},
	computed:
	{
		file(): ImModelFile
		{
			return this.item;
		},
		fileShortName(): string
		{
			const NAME_MAX_LENGTH = 70;

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
		isImage(): boolean
		{
			return this.file.type !== FileType.image;
		},
		isVideo(): boolean
		{
			return this.file.type !== FileType.video;
		},
		canBeOpenedWithViewer(): boolean
		{
			return this.file.viewerAttrs && BX.UI?.Viewer;
		},
		viewerAttributes(): Object
		{
			return Utils.file.getViewerDataAttributes(this.file.viewerAttrs);
		},
	},
	watch:
	{
		'file.status'()
		{
			this.getProgressBarManager().update();
		},
		'file.progress'()
		{
			this.getProgressBarManager().update();
		}
	},
	mounted()
	{
		this.initProgressBar();
	},
	beforeUnmount()
	{
		this.removeProgressBar();
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
		initProgressBar()
		{
			if (this.file.progress === 100)
			{
				return;
			}

			let blurElement;
			if (this.file.progress < 0 || (!this.isImage && !this.isVideo))
			{
				blurElement = false;
			}

			this.progressBarManager = new ProgressBarManager({
				container: this.$refs['container'],
				uploadState: this.file,
				customConfig: {blurElement}
			});

			this.progressBarManager.subscribe(ProgressBarManager.event.cancel, () => {
				EventEmitter.emit(EventType.uploader.cancel, {taskId: this.file.id});
			});
			this.progressBarManager.subscribe(ProgressBarManager.event.destroy, () => {
				if (this.progressBar)
				{
					this.progressBar = null;
				}
			});

			this.progressBarManager.start();
		},
		removeProgressBar()
		{
			if (!this.progressBarManager)
			{
				return;
			}

			this.progressBarManager.destroy();
		},
		getProgressBarManager(): ProgressBarManager
		{
			return this.progressBarManager;
		},
		loc(phraseCode: string, replacements: {[string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		}
	},
	template: `
		<div @click="download" v-bind="viewerAttributes" class="bx-im-media-file__container bx-im-media-file__scope" ref="container">
			<div class="bx-im-media-file__icon">
				<div :class="iconClass" class="ui-icon"><i></i></div>
			</div>
			<div class="bx-im-media-file__right">
				<div :title="file.name" class="bx-im-element-file-name bx-im-media-file__name">
					{{ fileShortName }}
				</div>
				<div class="bx-im-element-file-size bx-im-media-file__size">{{ fileSize }}</div>
			</div>
		</div>
	`
};