import {Color} from 'im.v2.const';

import {AttachGridItem} from './grid-item';

import './grid.css';

import type {AttachGridConfig} from 'im.v2.const';

// @vue/component
export const AttachGrid = {
	name: 'AttachGrid',
	components: {AttachGridItem},
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
		internalConfig(): AttachGridConfig
		{
			return this.config;
		},
	},
	template: `
		<div class="bx-im-attach-grid__container">
			<AttachGridItem
				v-for="(gridItem, index) in internalConfig.grid"
				:config="gridItem"
				:key="index"
			/>
		</div>
	`
};