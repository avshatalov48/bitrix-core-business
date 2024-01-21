import { Core } from 'im.v2.application.core';
import { OwnMessageStatus as StatusType } from 'im.v2.const';
import { DateCode, DateFormatter } from 'im.v2.lib.date-formatter';

import './message-status.css';

import type { ImModelMessage } from 'im.v2.model';

// @vue/component
export const MessageStatus = {
	name: 'MessageStatus',
	props:
	{
		item: {
			type: Object,
			required: true,
		},
		isOverlay: {
			type: Boolean,
			default: false,
		},
	},
	computed:
	{
		message(): ImModelMessage
		{
			return this.item;
		},
		formattedDate(): string
		{
			return DateFormatter.formatByCode(this.message.date, DateCode.shortTimeFormat);
		},
		isSelfMessage(): boolean
		{
			return this.message.authorId === Core.getUserId();
		},
		messageStatus(): $Values<typeof StatusType>
		{
			if (this.message.error)
			{
				return StatusType.error;
			}

			if (this.message.sending)
			{
				return StatusType.sending;
			}

			if (this.message.viewedByOthers)
			{
				return StatusType.viewed;
			}

			return StatusType.sent;
		},
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-message-status__container bx-im-message-status__scope" :class="{'--overlay': isOverlay}">
			<div v-if="message.isEdited && !message.isDeleted" class="bx-im-message-status__edit-mark">
				{{ loc('IM_MESSENGER_MESSAGE_EDITED') }}
			</div>
			<div class="bx-im-message-status__date" :class="{'--overlay': isOverlay}">
				{{ formattedDate }}
			</div>
			<div v-if="isSelfMessage" :class="'--' + messageStatus" class="bx-im-message-status__icon"></div>
		</div>
	`,
};
