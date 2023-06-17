/**
 * Bitrix Messenger
 * Vue component
 *
 * Message (attach type)
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

import {MessagesModel} from "im.model";
import {Utils} from "im.lib.utils";

export const AttachTypeMessage =
{
	property: 'MESSAGE',
	name: 'bx-im-view-element-attach-message',
	component:
	{
		props:
		{
			config: {type: Object, default: {}},
			color: {type: String, default: 'transparent'},
		},
		computed:
		{
			message()
			{
				return Utils.text.decode(this.config.MESSAGE);
			}
		},
		template: `<div class="bx-im-element-attach-type-message" v-html="message"></div>`
	},
};