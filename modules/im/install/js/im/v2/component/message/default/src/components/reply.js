import { Parser } from 'im.v2.lib.parser';

import type { ImModelMessage, ImModelUser } from 'im.v2.model';

// @vue/component
export const Reply = {
	name: 'ReplyComponent',
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
		replyId: {
			type: Number,
			required: true,
		},
	},
	computed:
	{
		replyMessage(): ImModelMessage
		{
			return this.$store.getters['messages/getById'](this.replyId);
		},
		replyAuthor(): ImModelUser
		{
			return this.$store.getters['users/get'](this.replyMessage.authorId);
		},
		replyTitle(): string
		{
			return this.replyAuthor ? this.replyAuthor.name : this.loc('IM_DIALOG_CHAT_QUOTE_DEFAULT_TITLE');
		},
		replyText(): string
		{
			let text = Parser.prepareQuote(this.replyMessage);
			text = Parser.decodeText(text);

			return text;
		},
		replyContext(): string
		{
			return `${this.dialogId}/${this.replyId}`;
		},
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-message-quote --with-context" :data-context="replyContext">
			<div class="bx-im-message-quote__wrap">
				<div class="bx-im-message-quote__name">
					<div class="bx-im-message-quote__name-text">
						{{ replyTitle }}
					</div>
				</div>
				<div class="bx-im-message-quote__text" v-html="replyText"></div>
			</div>
		</div>
	`,
};
