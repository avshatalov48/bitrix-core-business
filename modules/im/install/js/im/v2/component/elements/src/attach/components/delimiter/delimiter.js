import './delimiter.css';

import type { AttachDelimiterConfig } from 'im.v2.const';

// @vue/component
export const AttachDelimiter = {
	name: 'AttachDelimiter',
	props:
	{
		config: {
			type: Object,
			default: () => {},
		},
	},
	computed:
	{
		internalConfig(): AttachDelimiterConfig
		{
			return this.config;
		},
		styles(): Object
		{
			const result = {};

			if (this.internalConfig.delimiter.color)
			{
				result.backgroundColor = this.internalConfig.delimiter.color;
			}

			if (this.internalConfig.delimiter.size > 0)
			{
				result.width = `${this.internalConfig.delimiter.size}px`;
			}

			return result;
		},
	},
	template: `
		<div class="bx-im-attach-delimiter__container" :style="styles"></div>
	`,
};
