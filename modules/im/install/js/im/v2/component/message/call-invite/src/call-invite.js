import { Messenger } from 'im.public';
import { ChatActionType } from 'im.v2.const';
import { CallManager } from 'im.v2.lib.call';
import { PermissionManager } from 'im.v2.lib.permission';
import { Type } from 'main.core';

import { Button as ButtonComponent, ButtonSize, ButtonIcon } from 'im.v2.component.elements';
import { BaseMessage } from 'im.v2.component.message.base';
import { MessageHeader, DefaultMessageContent } from 'im.v2.component.message.elements';
import { Utils } from 'im.v2.lib.utils';

import './css/call-invite.css';

import type { CustomColorScheme } from 'im.v2.component.elements';
import type { ImModelMessage } from 'im.v2.model';

type ComponentParams = {
	link: string,
};

const BUTTON_COLOR = '#00ace3';

// @vue/component
export const CallInviteMessage = {
	name: 'CallInviteMessage',
	components: {
		ButtonComponent,
		BaseMessage,
		DefaultMessageContent,
		MessageHeader,
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
		ButtonSize: () => ButtonSize,
		ButtonIcon: () => ButtonIcon,
		buttonColorScheme(): CustomColorScheme
		{
			return {
				backgroundColor: 'transparent',
				borderColor: BUTTON_COLOR,
				iconColor: BUTTON_COLOR,
				textColor: BUTTON_COLOR,
				hoverColor: 'transparent',
			};
		},
		message(): ImModelMessage
		{
			return this.item;
		},
		componentParams(): ComponentParams
		{
			return this.item.componentParams;
		},
		canSetReactions(): boolean
		{
			return Type.isNumber(this.message.id);
		},
		isAvailable(): boolean
		{
			if (
				this.$store.getters['recent/calls/hasActiveCall'](this.dialogId)
				&& CallManager.getInstance().getCurrentCallDialogId() === this.dialogId
			)
			{
				return true;
			}

			if (this.$store.getters['recent/calls/hasActiveCall']())
			{
				return false;
			}

			const chatCanBeCalled = CallManager.getInstance().chatCanBeCalled(this.dialogId);
			const chatIsAllowedToCall = PermissionManager.getInstance().canPerformAction(ChatActionType.call, this.dialogId);

			return chatCanBeCalled && chatIsAllowedToCall;
		},
		inviteTitle(): string
		{
			return this.loc('IM_MESSENGER_MESSAGE_CALL_INVITE_TITLE_2');
		},
		descriptionTitle(): string
		{
			return this.loc('IM_MESSENGER_MESSAGE_CALL_INVITE_DESCRIPTION');
		},
	},
	methods:
	{
		loc(phraseCode: string, replacements: {[p: string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
		onCallButtonClick($event)
		{
			if (Utils.key.isAltOrOption($event))
			{
				Utils.browser.openLink(this.componentParams.link);
			}
			else
			{
				Messenger.startVideoCall(this.dialogId);
			}
		},
	},
	template: `
		<BaseMessage :dialogId="dialogId" :item="item">
			<div class="bx-im-message-call-invite__scope bx-im-message-call-invite__container">
				<MessageHeader :withTitle="withTitle" :item="item" />
				<div class="bx-im-message-call-invite__content-container">
					<div class="bx-im-message-call-invite__image"></div>
					<div class="bx-im-message-call-invite__content">
						<div class="bx-im-message-call-invite__title">
							{{ inviteTitle }}
						</div>
						<div class="bx-im-message-call-invite__description">
							{{ descriptionTitle }}
						</div>
						<div v-if="isAvailable" class="bx-im-message-call-invite__buttons_container">
							<div class="bx-im-message-call-invite__buttons_item">
								<ButtonComponent
									:size="ButtonSize.L"
									:icon="ButtonIcon.Call"
									:customColorScheme="buttonColorScheme"
									:isRounded="true"
									:text="loc('IM_MESSENGER_MESSAGE_CALL_INVITE_BUTTON_JOIN')"
									@click="onCallButtonClick"
								/>
							</div>
						</div>
					</div>
				</div>
				<DefaultMessageContent :item="item" :dialogId="dialogId" :withText="false" :withAttach="false" />
			</div>
		</BaseMessage>
	`,
};
