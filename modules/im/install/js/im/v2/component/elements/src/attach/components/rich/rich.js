import {Color} from 'im.v2.const';

import {AttachRichItem} from './rich-item';

import './rich.css';

import type {AttachRichConfig} from 'im.v2.const';

// @vue/component
export const AttachRich = {
	components: {AttachRichItem},
	props:
	{
		config: {
			type: Object,
			default: () => {}
		},
		color: {
			type: String,
			default: Color.transparent
		}
	},
	computed:
	{
		internalConfig(): AttachRichConfig
		{
			return this.config;
		}
	},
	template: `
		<div class="bx-im-attach-rich__container">
			<AttachRichItem v-for="(rich, index) in internalConfig.RICH_LINK" :config="rich" :color="color" :key="index" />
		</div>
	`
};