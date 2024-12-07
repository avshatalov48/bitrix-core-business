import type { JsonObject } from 'main.core';

// @vue/component
export const EmptyState = {
	name: 'EmptyState',
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
		<div class="bx-im-list-channel__empty">
			<div class="bx-im-list-channel__empty_icon"></div>
			<div class="bx-im-list-channel__empty_text">{{ loc('IM_LIST_CHANNEL_EMPTY') }}</div>
		</div>
	`,
};
