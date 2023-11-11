// @vue/component
export const EmptyState = {
	data()
	{
		return {};
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-content-chat__start_message">
			<div class="bx-im-content-chat__start_message_icon"></div>
			<div class="bx-im-content-chat__start_message_text">
				{{ loc('IM_CONTENT_CHAT_START_MESSAGE') }}
			</div>
		</div>
	`,
};
