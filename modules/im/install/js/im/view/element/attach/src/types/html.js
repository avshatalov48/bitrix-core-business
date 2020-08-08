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
		template: `<div class="bx-im-element-attach-type-html" v-html="config.HTML"></div>`
	},
};