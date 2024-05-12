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
		<div class="bx-im-list-recent-compact__empty">
			{{ loc('IM_LIST_RECENT_COMPACT_EMPTY') }}
		</div>
	`,
};
