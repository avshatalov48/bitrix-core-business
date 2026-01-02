import type { ImModelChat } from 'im.v2.model';

// @vue/component
export const ChatSingleRead = {
	name: 'ChatSingleRead',
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
			const { firstViewer } = this.dialog.lastMessageViews;

			return this.loc('IM_MESSAGE_LIST_STATUS_READ_CHAT', {
				'#USER#': firstViewer.userName,
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
