import { EventEmitter } from 'main.core.events';

import { EventType } from 'im.v2.const';
import { ProgressBarManager } from 'im.v2.lib.progressbar';

import '../../css/items/progress-bar.css';

import type { ImModelFile } from 'im.v2.model';

// @vue/component
export const ProgressBar = {
	name: 'ProgressBar',
	props:
	{
		item: {
			type: Object,
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
			return this.item;
		},
	},
	watch:
	{
		'file.status': function()
		{
			this.getProgressBarManager().update();
		},
		'file.progress': function()
		{
			this.getProgressBarManager().update();
		},
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
				container: this.$refs['progress-bar'],
				uploadState: this.file,
				customConfig: { blurElement, hasTitle: false },
			});

			this.progressBarManager.subscribe(ProgressBarManager.event.cancel, () => {
				console.warn('test');
				EventEmitter.emit(EventType.uploader.cancel, {
					tempFileId: this.file.id,
					tempMessageId: this.messageId,
				});
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
			if (!this.getProgressBarManager())
			{
				return;
			}

			this.getProgressBarManager().destroy();
		},
		getProgressBarManager(): ProgressBarManager
		{
			return this.progressBarManager;
		},
	},
	template: `
		<div class="bx-im-progress-bar__container" ref="progress-bar"></div>
	`,
};
