// @vue/component
export const NewMessagesBlock = {
	data()
	{
		return {};
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		}
	},
	template: `
		<div class="bx-im-dialog-chat__new-message-block">
			<div class="bx-im-dialog-chat__new-message-block_text">{{ loc('IM_DIALOG_CHAT_BLOCK_NEW_MESSAGES_2') }}</div>
		</div>
	`
};