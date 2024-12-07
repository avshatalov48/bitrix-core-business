import 'ui.notification';

import { Parser } from 'im.v2.lib.parser';
import { SendingService } from 'im.v2.provider.service';
import { Loc, Type } from 'main.core';

import { DefaultMessageContent, AuthorTitle, MessageStatus } from 'im.v2.component.message.elements';
import { BaseMessage } from 'im.v2.component.message.base';

import './css/copilot-answer.css';

import type { ImModelMessage } from 'im.v2.model';

// @vue/component
export const CopilotMessage = {
	name: 'CopilotMessage',
	components:
	{
		AuthorTitle,
		BaseMessage,
		DefaultMessageContent,
		MessageStatus,
	},
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
		hasMore(): boolean
		{
			return this.message.componentParams?.copilotHasMore === true;
		},
	},
	methods:
	{
		onCopyClick()
		{
			if (BX.clipboard?.copy(this.message.text))
			{
				BX.UI.Notification.Center.notify({
					content: Loc.getMessage('IM_MESSAGE_COPILOT_ANSWER_ACTION_COPY_SUCCESS'),
				});
			}
		},
		onContinueClick()
		{
			this.getSendingService().sendMessage({
				text: this.loc('IM_MESSAGE_COPILOT_ANSWER_CONTINUE_TEXT'),
				dialogId: this.dialogId,
			});
		},
		getSendingService(): SendingService
		{
			if (!this.sendingService)
			{
				this.sendingService = SendingService.getInstance();
			}

			return this.sendingService;
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<BaseMessage :item="item" :dialogId="dialogId" class="bx-im-message-copilot-base-message__container">
			<div class="bx-im-message-default__container bx-im-message-copilot-answer__container" :class="{'--error': isError}">
				<AuthorTitle v-if="withTitle" :item="item" />
				<div class="bx-im-message-default-content__container bx-im-message-default-content__scope">
					<div class="bx-im-message-default-content__text" v-html="formattedText"></div>
					<div class="bx-im-message-default-content__bottom-panel">
						<div v-if="!isError" class="bx-im-message-copilot-answer__actions">
							<div class="bx-im-message-copilot-answer__action" @click="onCopyClick">
								<div class="bx-im-message-copilot-answer__action_icon --copy"></div>
								<div class="bx-im-message-copilot-answer__action_text">
									{{ loc('IM_MESSAGE_COPILOT_ANSWER_ACTION_COPY') }}
								</div>
							</div>
							<div v-if="hasMore" class="bx-im-message-copilot-answer__action" @click="onContinueClick">
								<div class="bx-im-message-copilot-answer__action_icon --continue"></div>
								<div class="bx-im-message-copilot-answer__action_text">
									{{ loc('IM_MESSAGE_COPILOT_ANSWER_ACTION_CONTINUE') }}
								</div>
							</div>
						</div>
						<div class="bx-im-message-default-content__status-container">
							<MessageStatus :item="message" />
						</div>
					</div>
				</div>
			</div>
		</BaseMessage>
	`,
};
