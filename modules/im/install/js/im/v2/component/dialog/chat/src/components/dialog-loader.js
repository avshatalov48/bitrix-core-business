import '../css/dialog-loader.css';

// @vue/component
export const DialogLoader = {
	name: 'DialogLoader',
	props:
	{
		fullHeight: {
			type: Boolean,
			default: true
		},
	},
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
		<div class="bx-im-dialog-loader__container" :class="{'--full-height': fullHeight}">
			<div class="bx-im-dialog-loader__spinner"></div>
			<div class="bx-im-dialog-loader__text">{{ loc('IM_DIALOG_CHAT_LOADER_TEXT') }}</div>
		</div>
	`
};