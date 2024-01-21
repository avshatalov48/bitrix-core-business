import { Parser } from 'im.v2.lib.parser';

import '../css/message-panel.css';

import type { ImModelMessage, ImModelUser } from 'im.v2.model';

// @vue/component
export const ForwardPanel = {
	name: 'ForwardPanel',
	props:
	{
		messageId: {
			type: Number,
			required: true,
		},
	},
	emits: ['close'],
	computed:
	{
		message(): ImModelMessage
		{
			return this.$store.getters['messages/getById'](this.messageId);
		},
		forwardAuthor(): ImModelUser
		{
			const isForward = this.$store.getters['messages/isForward'](this.messageId);
			const userId: number = isForward ? this.message.forward.userId : this.message.authorId;

			return this.$store.getters['users/get'](userId, true);
		},
		forwardAuthorName(): string
		{
			let name = this.forwardAuthor.name;
			if (this.forwardAuthor.id === 0)
			{
				name = this.loc('IM_TEXTAREA_FORWARD_SYSTEM');
			}

			return `${name}: `;
		},
		messageText(): string
		{
			return Parser.purifyMessage(this.message);
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
		<div class="bx-im-message-panel__container">
			<div class="bx-im-message-panel__icon --forward"></div>
			<div class="bx-im-message-panel__content">
				<div class="bx-im-message-panel__title">{{ loc('IM_TEXTAREA_FORWARD_TITLE') }}</div>
				<div class="bx-im-message-panel__text">
					<span class="bx-im-message-panel__forward-author">{{ forwardAuthorName }}</span>
					<span class="bx-im-message-panel__forward-message-text">{{ messageText }}</span>
				</div>
			</div>
			<div @click="$emit('close')" class="bx-im-message-panel__close"></div>
		</div>
	`,
};
