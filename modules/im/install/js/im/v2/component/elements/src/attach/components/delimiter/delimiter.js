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
				backgroundColor: this.internalConfig.DELIMITER.COLOR ?? this.color
			};

			if (this.internalConfig.DELIMITER.SIZE)
			{
				result.width = `${this.internalConfig.DELIMITER.SIZE}px`;
			}

			return result;
		}
	},
	template: `
		<div class="bx-im-attach-delimiter__container" :style="styles"></div>
	`
};