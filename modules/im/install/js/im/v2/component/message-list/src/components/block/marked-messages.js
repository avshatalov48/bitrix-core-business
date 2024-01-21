import { type JsonObject } from 'main.core';

// @vue/component
export const MarkedMessagesBlock = {
	data(): JsonObject
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
		<div class="bx-im-message-list-new-message__container">
			<div class="bx-im-message-list-new-message__text">
				{{ loc('IM_DIALOG_CHAT_BLOCK_MARKED_MESSAGES') }}
			</div>
		</div>
	`,
};
