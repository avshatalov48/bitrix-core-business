import { Color } from 'im.v2.const';

import { AttachRichItem } from './rich-item';

import './rich.css';

import type { AttachRichConfig } from 'im.v2.const';

// @vue/component
export const AttachRich = {
	components: { AttachRichItem },
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
		attachId: {
			type: String,
			required: true,
		},
	},
	computed:
	{
		internalConfig(): AttachRichConfig
		{
			return this.config;
		},
	},
	template: `
		<div class="bx-im-attach-rich__container">
			<AttachRichItem 
				v-for="(rich, index) in internalConfig.richLink" 
				:config="rich" 
				:color="color" 
				:key="index" 
				:attachId="attachId" 
			/>
		</div>
	`,
};
