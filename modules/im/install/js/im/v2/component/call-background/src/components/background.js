import {ProgressBarManager} from 'im.v2.lib.progressbar';

import {Background} from '../classes/items/background';

import '../css/background-item.css';

// @vue/component
export const BackgroundComponent = {
	props:
	{
		element: {
			type: Object,
			required: true
		},
		isSelected: {
			type: Boolean,
			required: true
		}
	},
	emits: ['click', 'remove', 'cancel'],
	data()
	{
		return {};
	},
	computed:
	{
		background(): Background
		{
			return this.element;
		},
		containerClasses(): string[]
		{
			const classes = [];

			if (this.isSelected)
			{
				classes.push('--selected');
			}

			if (!this.background.isSupported)
			{
				classes.push('--unsupported');
			}

			if (this.background.isLoading)
			{
				classes.push('--loading');
			}

			return classes;
		},
		imageStyle(): {backgroundImage: string}
		{
			let backgroundImage = '';
			if (this.background.preview)
			{
				backgroundImage = `url('${this.background.preview}')`;
			}

			return {backgroundImage};
		}
	},
	watch:
	{
		'background.uploadState.status'()
		{
			this.getProgressBarManager().update();
		},
		'background.uploadState.progress'()
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
		initProgressBar()
		{
			if (!this.background.uploadState || this.background.uploadState.progress === 100)
			{
				return;
			}

			this.progressBarManager = new ProgressBarManager({
				container: this.$refs['container'],
				uploadState: this.background.uploadState
			});

			this.progressBarManager.subscribe(ProgressBarManager.event.cancel, () => {
				this.$emit('cancel', this.background);
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
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		}
	},
	template:
	`
		<div @click="$emit('click')" :class="containerClasses" class="bx-im-call-background__item" ref="container">
			<div :style="imageStyle" class="bx-im-call-background__item_image"></div>
			<div v-if="background.isSupported && background.isVideo" class="bx-im-call-background__item_video"></div>
			<div v-if="!background.isLoading" class="bx-im-call-background__item_title_container">
				<span class="bx-im-call-background__item_title">{{background.title}}</span>
				<div
					v-if="background.canRemove"
					:title="loc('BX_IM_CALL_BG_REMOVE')"
					@click.stop="$emit('remove')"
					class="bx-im-call-background__item_remove"
				></div>
			</div>
		</div>
	`
};