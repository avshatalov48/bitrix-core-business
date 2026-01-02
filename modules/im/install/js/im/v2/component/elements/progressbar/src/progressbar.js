import { BIcon, Outline as OutlineIcons } from 'ui.icon-set.api.vue';

import { FileStatus, Color } from 'im.v2.const';

import { formatProgressLabel } from './helpers/format-progress-label';

import './css/progressbar.css';

import type { ImModelFile } from 'im.v2.model';

export const ProgressBarSize = Object.freeze({
	S: 'S',
	L: 'L',
});

const MIN_PROGRESS = 1; // Minimum progress to show the circle animation

// @vue/component
export const ProgressBar = {
	name: 'ProgressBar',
	components: { BIcon },
	props: {
		item: {
			type: Object,
			required: true,
		},
		size: {
			type: String,
			default: ProgressBarSize.L,
		},
		handleStatus: {
			type: Array,
			default: () => {
				return [FileStatus.progress, FileStatus.upload];
			},
		},
		/**
		 * @value {[status: string]: { iconClass: string, labelText: string }}
		 */
		statusMap: {
			type: Object,
			default: () => {
				return {};
			},
		},
	},
	emits: ['cancelClick'],
	computed: {
		Color: () => Color,
		OutlineIcons: () => OutlineIcons,
		file(): ImModelFile
		{
			return this.item;
		},
		needProgressBar(): boolean
		{
			return this.handleStatus.includes(this.file.status);
		},
		progressStyles(): { strokeDashoffset: number, strokeDasharray: number }
		{
			const radius = 23;
			const circumference = 2 * Math.PI * radius;

			const adjustedProgress = Math.max(this.file.progress, MIN_PROGRESS);
			const offset = circumference - (adjustedProgress / 100) * circumference;

			return {
				strokeDasharray: circumference,
				strokeDashoffset: offset,
			};
		},
		labelText(): string
		{
			if (this.statusMap?.[this.file.status]?.labelText)
			{
				return this.statusMap?.[this.file.status]?.labelText;
			}

			return formatProgressLabel(this.file.progress, this.file.size);
		},
		iconClass(): string
		{
			if (this.statusMap?.[this.file.status]?.iconClass)
			{
				return this.statusMap[this.file.status].iconClass;
			}

			return OutlineIcons.CROSS_L;
		},
		containerClass(): string
		{
			return `--size-${this.size.toLowerCase()}`;
		},
		needLabel(): boolean
		{
			return this.size === ProgressBarSize.L;
		},
		iconSize(): number
		{
			return this.size === ProgressBarSize.L ? 28 : 24;
		},
	},
	methods: {
		onLoaderClick()
		{
			if (!this.handleStatus.includes(this.file.status))
			{
				return;
			}

			this.$emit('cancelClick', { file: this.file });
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div 
			v-if="needProgressBar"
			:class="containerClass"
			class="bx-im-progress-bar__container" 
		>
			<div class="bx-im-progress-bar__loader" @click="onLoaderClick">
				<svg viewBox="0 0 48 48">
					<circle
						:style="progressStyles"
						class="bx-im-progress-bar__loader-progress" 
						cx="24" 
						cy="24" 
						r="23"
					></circle>
				</svg>
				<BIcon
					:name="iconClass"
					:color="Color.white"
					:size="iconSize"
					class="bx-im-progress-bar__loader-icon"
				/>
			</div>
			<div v-if="needLabel" class="bx-im-progress-bar__label">
				{{ labelText }}
			</div>
		</div>
	`,
};
