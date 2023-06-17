/**
 * Bitrix Messenger
 * Vue component
 *
 * Rich Attach type
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

import "./html.css";
import { Utils } from 'im.lib.utils';

export const AttachTypeHtml =
{
	property: 'HTML',
	name: 'bx-im-view-element-attach-html',
	component:
	{
		props:
		{
			config: {type: Object, default: {}},
			color: {type: String, default: 'transparent'},
		},
		computed:
		{
			html()
			{
				const text = this.config.HTML.replace(/&nbsp;/gi, " ");
				return Utils.text.decode(text);
			}
		},
		template: `<div class="bx-im-element-attach-type-html" v-html="html"></div>`
	},
};