import '../css/copilot-role-selection-button.css';

// @vue/component
export const CopilotRoleSelectionButton = {
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div
			class="bx-im-create-chat-menu-item__button --copilot"
			:title="loc('IM_RECENT_CREATE_COPILOT_ROLE_SELECTION_TITLE')"
		>
			<div class="bx-im-create-chat-menu-item__icon-more"></div>
		</div>
	`,
};
