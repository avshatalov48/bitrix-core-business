// @vue/component
export const DeletedMessage = {
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
		<div class="bx-im-message-base__deleted_container">
			<div class="bx-im-message-base__deleted_icon"></div>
			<div class="bx-im-message-base__deleted_text">{{ loc('IM_MESSENGER_MESSAGE_DELETED') }}</div>
		</div>
	`
};