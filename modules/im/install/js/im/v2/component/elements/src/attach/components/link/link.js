import {Color} from 'im.v2.const';

import {AttachLinkItem} from './link-item';

import './link.css';

import type {AttachLinkConfig} from 'im.v2.const';

// @vue/component
export const AttachLink = {
	name: 'AttachLink',
	components: {AttachLinkItem},
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
		internalConfig(): AttachLinkConfig
		{
			return this.config;
		}
	},
	template: `
		<div class="bx-im-attach-link__container">
			<AttachLinkItem v-for="(link, index) in internalConfig.link" :config="link" :key="index" />
		</div>
	`
};