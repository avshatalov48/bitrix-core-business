import { AudioPlayer } from 'im.v2.component.elements.audioplayer';
import { ProgressBar, ProgressBarSize } from 'im.v2.component.elements.progressbar';

import { TranscriptionItem } from './transcription';
import { TranscriptionButtonItem } from './transcription-button';

import '../../css/items/audio.css';

import type { ImModelFile } from 'im.v2.model';

// @vue/component
export const AudioItem = {
	name: 'AudioItem',
	components: { AudioPlayer, ProgressBar, TranscriptionItem, TranscriptionButtonItem },
	props: {
		item: {
			type: Object,
			required: true,
		},
		messageId: {
			type: [String, Number],
			required: true,
		},
	},
	emits: ['cancelClick'],
	data(): { transcriptionStatus: boolean }
	{
		return {
			isTranscriptionOpened: false,
		};
	},
	computed:
	{
		ProgressBarSize: () => ProgressBarSize,
		file(): ImModelFile
		{
			return this.item;
		},
		timelineType(): number
		{
			return Math.floor(Math.random() * 5);
		},
	},
	methods:
	{
		onCancelClick(event: PointerEvent): void
		{
			this.$emit('cancelClick', event);
		},
		transcriptionToggle(status: boolean): void
		{
			this.isTranscriptionOpened = status;
		},
	},
	template: `
		<div class="bx-im-media-audio__container">
			<ProgressBar 
				:item="file"
				:size="ProgressBarSize.S"
				@cancelClick="onCancelClick"
			/>
			<AudioPlayer
				:id="file.id"
				:messageId="messageId"
				:src="file.urlDownload"
				:file="file"
				:timelineType="timelineType"
				:authorId="file.authorId"
				:withContextMenu="false"
				:withAvatar="false"
			>
				<template #transcription-control>
					<TranscriptionButtonItem
						:file="file"
						:isOpened="isTranscriptionOpened"
						@transcriptionToggle="transcriptionToggle"
					/>
				</template>
			</AudioPlayer>
			<TranscriptionItem
				:file="file"
				:isOpened="isTranscriptionOpened"
				:messageId="messageId"
			/>
		</div>
	`,
};
