import { Color } from 'im.v2.const';

import { AttachImageItem } from './image-item';

import './image.css';

import type { AttachImageConfig } from 'im.v2.const';

export const AttachImage = {
	name: 'AttachImage',
	components: { AttachImageItem },
	props:
	{
		config: {
			type: Object,
			default: () => {},
		},
		color: {
			type: String,
			default: Color.transparent,
		},
	},
	computed:
	{
		internalConfig(): AttachImageConfig
		{
			return this.config;
		},
	},
	template: `
		<div class="bx-im-attach-image__container bx-im-attach-image__scope">
			<AttachImageItem v-for="(image, index) in internalConfig.image" :config="image" :key="index" />
		</div>
	`,
};
