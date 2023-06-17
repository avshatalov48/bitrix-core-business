const SEND_MESSAGE_COMBINATION = 'Enter';

// @vue/component
export const SendButton = {
	props:
	{
		editMode: {
			type: Boolean,
			default: false
		},
		isDisabled: {
			type: Boolean,
			default: false
		},
	},
	data()
	{
		return {};
	},
	computed:
	{
		buttonHint(): string
		{
			return this.loc('IM_TEXTAREA_ICON_SEND_TEXT', {
				'#SEND_MESSAGE_COMBINATION#': SEND_MESSAGE_COMBINATION
			});
		}
	},
	methods:
	{
		loc(phraseCode: string, replacements: {[string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		}
	},
	template: `
		<div
			:title="buttonHint"
			class="bx-im-send-panel__button_container"
			:class="{'--edit': editMode, '--disabled': isDisabled}"
		></div>
	`
};