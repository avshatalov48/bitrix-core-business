import {Color} from 'im.v2.const';

import './delimiter.css';

import type {AttachDelimiterConfig} from 'im.v2.const';

// @vue/component
export const AttachDelimiter = {
	name: 'AttachDelimiter',
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
		internalConfig(): AttachDelimiterConfig
		{
			return this.config;
		},
		styles(): Object
		{
			const result = {
				backgroundColor: this.internalConfig.delimiter.color ?? this.color
			};

			if (this.internalConfig.delimiter.size)
			{
				result.width = `${this.internalConfig.delimiter.size}px`;
			}

			return result;
		}
	},
	template: `
		<div class="bx-im-attach-delimiter__container" :style="styles"></div>
	`
};