import './line-loader.css';

import type { JsonObject } from 'main.core';

// @vue/component
export const LineLoader = {
	name: 'LineLoader',
	props:
	{
		width: {
			type: Number,
			required: true,
		},
		height: {
			type: Number,
			required: true,
		},
	},
	data(): JsonObject
	{
		return {};
	},
	computed:
	{
		containerStyles(): { width: string, height: string }
		{
			return {
				width: `${this.width}px`,
				height: `${this.height}px`,
			};
		},
	},
	template: `
		<div class="bx-im-elements-line-loader__container" :style="containerStyles">
			<div class="bx-im-elements-line-loader__content"></div>
		</div>
	`,
};
