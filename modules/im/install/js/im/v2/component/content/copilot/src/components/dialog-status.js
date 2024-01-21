import '../css/dialog-status.css';

import type { JsonObject } from 'main.core';
import type { ImModelChat } from 'im.v2.model';

// @vue/component
export const DialogStatus = {
	props:
	{
		dialogId: {
			required: true,
			type: String,
		},
	},
	data(): JsonObject
	{
		return {};
	},
	computed:
	{
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		typingStatus(): string
		{
			if (!this.dialog.inited || this.dialog.writingList.length === 0)
			{
				return '';
			}

			return this.loc('IM_CONTENT_COPILOT_DIALOG_STATUS_TYPING');
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
		<div class="bx-im-dialog-copilot-status__container">
			<div v-if="typingStatus" class="bx-im-dialog-copilot-status__content">
				<div class="bx-im-dialog-copilot-status__icon --typing"></div>
				<div class="bx-im-dialog-copilot-status__text">{{ typingStatus }}</div>
			</div>
		</div>
	`,
};
