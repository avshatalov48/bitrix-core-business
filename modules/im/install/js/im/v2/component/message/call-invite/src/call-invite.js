import { Type } from 'main.core';

import { Button as ButtonComponent, ButtonSize, ButtonIcon } from 'im.v2.component.elements';
import { BaseMessage } from 'im.v2.component.message.base';
import { AuthorTitle, DefaultMessageContent, ReactionSelector } from 'im.v2.component.message.elements';
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
		ReactionSelector,
		AuthorTitle,
		DefaultMessageContent,
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
	},
	methods:
	{
		loc(phraseCode: string, replacements: {[p: string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
		onCallButtonClick()
		{
			Utils.browser.openLink(this.componentParams.link);
		},
	},
	template: `
		<BaseMessage :dialogId="dialogId" :item="item">
			<div class="bx-im-message-call-invite__scope bx-im-message-call-invite__container">
				<AuthorTitle v-if="withTitle" :item="item" />
				<div class="bx-im-message-call-invite__content-container">
					<div class="bx-im-message-call-invite__image"></div>
					<div class="bx-im-message-call-invite__content">
						<div class="bx-im-message-call-invite__title">
							{{ loc('IM_MESSENGER_MESSAGE_CALL_INVITE_TITLE_2') }}
						</div>
						<div class="bx-im-message-call-invite__description">
							{{ loc('IM_MESSENGER_MESSAGE_CALL_INVITE_DESCRIPTION') }}
						</div>
						<div class="bx-im-message-call-invite__buttons_container">
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
				<DefaultMessageContent :item="item" :dialogId="dialogId" :withText="false" />
				<ReactionSelector :messageId="message.id" />
			</div>
		</BaseMessage>
	`,
};
