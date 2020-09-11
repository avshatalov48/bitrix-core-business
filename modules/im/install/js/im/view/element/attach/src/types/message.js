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
				let text = Utils.text.htmlspecialchars(this.config.MESSAGE);

				text = text.replace(/\[USER=([0-9]{1,})\](.*?)\[\/USER\]/ig, (whole, userId, userName) => {
					const user = this.$store.getters['users/get'](userId);
					userName = user? Utils.text.htmlspecialchars(user.name): userName;
					return '<span class="bx-im-mention" data-type="USER" data-value="'+userId+'">'+userName+'</span>'
				});

				return MessagesModel.decodeBbCode({text});
			}
		},
		template: `<div class="bx-im-element-attach-type-message" v-html="message"></div>`
	},
};