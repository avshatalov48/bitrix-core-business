import './html.css';
import {Parser} from 'im.v2.lib.parser';

import type {AttachHtmlConfig} from 'im.v2.const';

export const AttachHtml = {
	props:
	{
		config: {
			type: Object,
			default: () => {}
		}
	},
	computed:
	{
		internalConfig(): AttachHtmlConfig
		{
			return this.config;
		},
		html()
		{
			return Parser.decodeHtml(this.internalConfig.html);
		}
	},
	template: `
		<div class="bx-im-element-attach-type-html" v-html="html"></div>
	`
};