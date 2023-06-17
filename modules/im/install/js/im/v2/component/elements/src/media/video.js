import {BitrixVue} from 'ui.vue3';
import {SocialVideo} from 'ui.vue3.components.socialvideo';

import {Image} from './image';

import './css/video.css';

const VIDEO_SIZE_TO_AUTOPLAY = 5000000;

// @vue/component
export const Video = BitrixVue.cloneComponent(Image, {
	name: 'VideoComponent',
	components: {SocialVideo},
	data()
	{
		return {};
	},
	computed:
	{
		autoplay()
		{
			return this.file.size < VIDEO_SIZE_TO_AUTOPLAY;
		}
	},
	template: `
		<div @click="download" class="bx-im-media-video__container" ref="container">
			<SocialVideo
				v-bind="viewerAttributes"
				:id="file.id"
				:src="file.urlShow"
				:preview="file.urlPreview"
				:containerStyle="{height: '162px'}"
				:elementStyle="imageSize"
				:autoplay="autoplay"
				:showControls="!file.viewerAttrs"
			/>
		</div>
	`
});