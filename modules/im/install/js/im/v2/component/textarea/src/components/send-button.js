import { Settings } from 'im.v2.const';
import { Utils } from 'im.v2.lib.utils';

import type { ImModelChat } from 'im.v2.model';

// @vue/component
export const SendButton = {
	props:
	{
		dialogId: {
			type: String,
			default: '',
		},
		editMode: {
			type: Boolean,
			default: false,
		},
		isDisabled: {
			type: Boolean,
			default: false,
		},
	},
	computed:
	{
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		dialogTypeClass(): string
		{
			return `--${this.dialog.type}`;
		},
		buttonHint(): string
		{
			const sendByEnter = this.$store.getters['application/settings/get'](Settings.hotkey.sendByEnter);
			const ctrlKey = Utils.platform.isMac() ? 'Cmd' : 'Ctrl';
			const sendCombination = sendByEnter ? 'Enter' : `${ctrlKey} + Enter`;

			return this.loc('IM_TEXTAREA_ICON_SEND_TEXT', {
				'#SEND_MESSAGE_COMBINATION#': sendCombination,
			});
		},
	},
	methods:
	{
		loc(phraseCode: string, replacements: {[string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<div
			:title="buttonHint"
			class="bx-im-send-panel__button_container"
			:class="[{'--edit': editMode, '--disabled': isDisabled, }, dialogTypeClass]"
		></div>
	`,
};
