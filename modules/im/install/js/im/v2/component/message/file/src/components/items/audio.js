import { AudioPlayer } from 'im.v2.component.elements';

import { ProgressBar } from './progress-bar';

import '../../css/items/audio.css';

import type { ImModelFile } from 'im.v2.model';

// @vue/component
export const AudioItem = {
	name: 'AudioItem',
	components: { AudioPlayer, ProgressBar },
	props:
	{
		item: {
			type: Object,
			required: true,
		},
		messageType: {
			type: String,
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
		isLoaded(): boolean
		{
			return this.file.progress === 100;
		},
	},
	template: `
		<div class="bx-im-media-audio__container">
			<ProgressBar v-if="!isLoaded" :item="file" :messageId="messageId" />
			<AudioPlayer
				:id="file.id"
				:messageId="messageId"
				:src="file.urlShow"
				:file="file"
				:timelineType="Math.floor(Math.random() * 5)"
				:authorId="file.authorId"
				:withContextMenu="false"
				:withAvatar="false"
			/>
		</div>
	`,
};
