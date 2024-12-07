import { CopilotManager } from 'im.v2.lib.copilot';

import { ChatTitle } from '../chat-title/chat-title';

import type { ImModelMessage } from 'im.v2.model';

// @vue/component
export const MessageAuthorTitle = {
	name: 'MessageAuthorTitle',
	components: { ChatTitle },
	props:
	{
		dialogId: {
			type: [Number, String],
			default: 0,
		},
		messageId: {
			type: [Number, String],
			default: 0,
		},
		text: {
			type: String,
			default: '',
		},
		showItsYou: {
			type: Boolean,
			default: true,
		},
		withLeftIcon: {
			type: Boolean,
			default: true,
		},
		withColor: {
			type: Boolean,
			default: false,
		},
		withMute: {
			type: Boolean,
			default: false,
		},
		onlyFirstName: {
			type: Boolean,
			default: false,
		},
		twoLine: {
			type: Boolean,
			default: false,
		},
	},
	computed:
	{
		message(): ImModelMessage
		{
			return this.$store.getters['messages/getById'](this.messageId);
		},
		authorId(): number
		{
			return this.message.authorId;
		},
		customAuthorName(): string
		{
			const copilotManager = new CopilotManager();
			if (!copilotManager.isCopilotBot(this.dialogId))
			{
				return '';
			}

			return copilotManager.getNameWithRole({
				dialogId: this.dialogId,
				messageId: this.messageId,
			});
		},
	},
	template: `
		<ChatTitle 
			:dialogId="dialogId"
			:text="customAuthorName"
			:showItsYou="showItsYou"
			:withLeftIcon="withLeftIcon"
			:withColor="withColor"
			:withMute="withMute"
			:onlyFirstName="onlyFirstName"
			:twoLine="twoLine"
		/>
	`,
};
