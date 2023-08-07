// @vue/component
import '../css/extensions/unsupported.css';

export const UnsupportedExtension = {
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-message-base__unsupported_container">
			<div class="bx-im-message-base__unsupported_icon"></div>
			<div class="bx-im-message-base__unsupported_text">{{ loc('IM_MESSENGER_MESSAGE_UNSUPPORTED_EXTENSION') }}</div>
		</div>
	`,
};
