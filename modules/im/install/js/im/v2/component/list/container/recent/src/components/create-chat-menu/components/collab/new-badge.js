import '../../css/new-badge.css';

// @vue/component
export const NewBadge = {
	name: 'NewBadge',
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-create-chat-menu-new-badge__container">
			<div class="bx-im-create-chat-menu-new-badge__content">{{ loc('IM_RECENT_CREATE_COLLAB_NEW_BADGE') }}</div>
		</div>
	`,
};
