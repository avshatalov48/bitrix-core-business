import {AudioPlayer} from 'ui.vue3.components.audioplayer';

import {MessageType} from 'im.v2.const';

import './css/audio.css';

import type {ImModelFile} from 'im.v2.model';

// @vue/component
export const Audio = {
	name: 'AudioComponent',
	components: {AudioPlayer},
	props:
	{
		item: {
			type: Object,
			required: true
		},
		messageType: {
			type: String,
			required: true
		}
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
		playerBackgroundType(): string
		{
			return this.messageType === MessageType.self ? 'dark' : 'light';
		}
	},
	template: `
		<div class="bx-im-media-audio__container">
			<AudioPlayer :id="file.id" :src="file.urlShow" :background="playerBackgroundType" />
		</div>
	`
};