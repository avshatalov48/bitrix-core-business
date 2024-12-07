import 'ui.notification';
import { Dom, Loc, Type } from 'main.core';

import { Utils } from 'im.v2.lib.utils';
import { Parser } from 'im.v2.lib.parser';
import { BaseMessage } from 'im.v2.component.message.base';
import { ReactionList, MessageStatus, AuthorTitle } from 'im.v2.component.message.elements';

import './css/copilot-answer.css';

import type { ImModelMessage } from 'im.v2.model';

// @vue/component
export const CopilotMessage = {
	name: 'CopilotMessage',
	components: { AuthorTitle, BaseMessage, ReactionList, MessageStatus },
	props:
	{
		item: {
			type: Object,
			required: true,
		},
		dialogId: {
			type: String,
			required: true,
		},
		withTitle: {
			type: Boolean,
			default: true,
		},
	},
	computed:
	{
		message(): ImModelMessage
		{
			return this.item;
		},
		formattedText(): string
		{
			return Parser.decodeMessage(this.item);
		},
		canSetReactions(): boolean
		{
			return Type.isNumber(this.message.id);
		},
		isReply(): boolean
		{
			return this.message.replyId !== 0;
		},
		isError(): boolean
		{
			return this.message.componentParams?.copilotError === true;
		},
		warningText(): string
		{
			return this.loc(
				'IM_MESSAGE_COPILOT_ANSWER_WARNING',
				{
					'#LINK_START#': '<a class="bx-im-message-copilot-answer__warning_more">',
					'#LINK_END#': '</a>',
				},
			);
		},
	},
	methods:
	{
		async onCopyClick()
		{
			await Utils.text.copyToClipboard(this.message.text);
			BX.UI.Notification.Center.notify({
				content: Loc.getMessage('IM_MESSAGE_COPILOT_ANSWER_ACTION_COPY_SUCCESS'),
			});
		},
		onWarningDetailsClick(event: PointerEvent)
		{
			if (!Dom.hasClass(event.target, 'bx-im-message-copilot-answer__warning_more'))
			{
				return;
			}

			const ARTICLE_CODE = '20412666';
			BX.Helper?.show(`redirect=detail&code=${ARTICLE_CODE}`);
		},
		loc(phraseCode: string, replacements: {[p: string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<BaseMessage :item="item" :dialogId="dialogId" class="bx-im-message-copilot-base-message__container">
			<div class="bx-im-message-default__container bx-im-message-copilot-answer__container" :class="{'--error': isError}">
				<AuthorTitle v-if="withTitle" :item="item" />
				<div class="bx-im-message-default-content__container bx-im-message-default-content__scope">
					<div class="bx-im-message-default-content__text" v-html="formattedText"></div>
					<ReactionList
						v-if="canSetReactions"
						:messageId="message.id"
						:contextDialogId="dialogId"
						class="bx-im-message-default-content__reaction-list"
					/>
					<div v-if="isError" class="bx-im-message-default-content__bottom-panel">
						<div class="bx-im-message-default-content__status-container">
							<MessageStatus :item="message" />
						</div>
					</div>
				</div>
			</div>
			<div v-if="!isError" class="bx-im-message-copilot-answer__bottom-panel">
				<div class="bx-im-message-copilot-answer__panel-content">
					<button
						:title="loc('IM_MESSAGE_COPILOT_ANSWER_ACTION_COPY')"
						@click="onCopyClick"
						class="bx-im-message-copilot-answer__copy_icon"
					></button>
					<span 
						v-html="warningText"
						@click="onWarningDetailsClick"
						class="bx-im-message-copilot-answer__warning"
					></span>
				</div>
				<div class="bx-im-message-default-content__status-container">
					<MessageStatus :item="message" />
				</div>
			</div>
		</BaseMessage>
	`,
};
