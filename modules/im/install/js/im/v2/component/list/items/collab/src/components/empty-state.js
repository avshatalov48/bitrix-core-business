// @vue/component
export const EmptyState = {
	name: 'EmptyState',
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-list-collab__empty">
			<div class="bx-im-list-collab__empty_icon"></div>
			<div class="bx-im-list-collab__empty_text">
				{{ loc('IM_LIST_COLLAB_EMPTY_V2') }}
			</div>
		</div>
	`,
};
