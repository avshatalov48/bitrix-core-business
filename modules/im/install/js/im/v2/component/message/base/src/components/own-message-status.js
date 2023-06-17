import {OwnMessageStatus as StatusType} from 'im.v2.const';

import type {ImModelMessage} from 'im.v2.model';

// @vue/component
export const OwnMessageStatus = {
	props:
	{
		item: {
			type: Object,
			required: true
		},
	},
	data()
	{
		return {};
	},
	computed:
	{
		message(): ImModelMessage
		{
			return this.item;
		},
		messageStatus(): $Values<typeof StatusType>
		{
			if (this.message.sending)
			{
				return StatusType.sending;
			}

			if (this.message.viewedByOthers)
			{
				return StatusType.viewed;
			}

			return StatusType.sent;
		}
	},
	template: `
		<div :class="'--' + messageStatus" class="bx-im-message-base__message-status"></div>
	`
};