import { DateFormatter, DateTemplate } from 'im.v2.lib.date-formatter';

import type { ImModelChat } from 'im.v2.model';

// @vue/component
export const UserRead = {
	name: 'UserRead',
	props:
	{
		dialogId: {
			required: true,
			type: String,
		},
	},
	computed:
	{
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		formattedStatus(): string
		{
			const { date } = this.dialog.lastMessageViews.firstViewer;

			return this.loc('IM_MESSAGE_LIST_STATUS_READ_USER_MSGVER_1', {
				'#DATE#': DateFormatter.formatByTemplate(date, DateTemplate.messageReadStatus),
			});
		},
	},
	methods:
	{
		loc(phraseCode: string, replacements: {[string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<div class="bx-im-dialog-chat-status__text">{{ formattedStatus }}</div>
	`,
};
