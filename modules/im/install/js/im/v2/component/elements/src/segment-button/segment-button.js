import { Type } from 'main.core';

import './segment-button.css';

import type { JsonObject } from 'main.core';

type Button = {
	id: string,
	title: string,
};

// @vue/component
export const SegmentButton = {
	name: 'SegmentButton',
	props:
	{
		tabs: {
			type: Array,
			required: true,
			validator(value): boolean
			{
				return Type.isArrayFilled(value);
			},
		},
	},
	emits: ['segmentSelected'],
	data(): JsonObject
	{
		return {
			activeTabId: this.tabs[0].id,
		};
	},
	methods:
	{
		isTabActive(tab: Button): boolean
		{
			return this.activeTabId === tab.id;
		},
		onTabClick(tab: Button): void
		{
			this.activeTabId = tab.id;
			this.$emit('segmentSelected', tab.id);
		},
	},
	template: `
		<div class="bx-im-segment-button__container">
			<button
				v-for="tab in tabs"
				:class="{'--active': isTabActive(tab)}"
				class="--ellipsis"
				@click="onTabClick(tab)"
			>
				{{ tab.title }}
			</button>
		</div>
	`,
};
