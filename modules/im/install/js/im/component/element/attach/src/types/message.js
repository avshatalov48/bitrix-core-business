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

export const AttachTypeMessage =
{
	property: 'MESSAGE',
	name: 'bx-messenger-element-attach-message',
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
				return MessagesModel.decodeBbCode({text: this.config.MESSAGE});
			}
		},
		template: `<div class="bx-im-element-attach-type-message" v-html="message"></div>`
	},
};