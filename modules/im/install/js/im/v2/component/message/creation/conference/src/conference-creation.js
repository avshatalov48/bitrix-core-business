import { Messenger } from 'im.public';
import { ChatButton, ButtonSize, ButtonIcon } from 'im.v2.component.elements.button';
import { BaseMessage } from 'im.v2.component.message.base';
import { Notifier } from 'im.v2.lib.notifier';
import { Analytics as CallAnalytics } from 'call.lib.analytics';

import './css/conference-creation-message.css';

import type { JsonObject } from 'main.core';
import type { CustomColorScheme } from 'im.v2.component.elements.button';
import type { ImModelMessage, ImModelChat } from 'im.v2.model';

const BUTTON_COLOR = '#00ace3';

// @vue/component
export const ConferenceCreationMessage = {
	name: 'ConferenceCreationMessage',
	components: { ChatButton, BaseMessage },
	props: {
		item: {
			type: Object,
			required: true,
		},
		dialogId: {
			type: String,
			required: true,
		},
	},
	data(): JsonObject
	{
		return {
			showAddToChatPopup: false,
		};
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
		chatId(): number
		{
			return this.message.chatId;
		},
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/getByChatId'](this.chatId);
		},
	},
	methods:
	{
		onStartButtonClick()
		{
			CallAnalytics.getInstance().onChatStartConferenceClick({ chatId: this.chatId });

			void Messenger.openConference({
				code: this.dialog.public.code,
			});
		},
		onCopyLinkClick()
		{
			if (BX.clipboard.copy(this.dialog.public.link))
			{
				Notifier.conference.onCopyLinkComplete();
			}
		},
		loc(phraseCode: string, replacements: {[p: string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<BaseMessage
			:dialogId="dialogId"
			:item="item"
			:withContextMenu="false"
			:withReactions="false"
			:withBackground="false"
			class="bx-im-message-conference-creation__scope"
		>
			<div class="bx-im-message-conference-creation__container">
				<div class="bx-im-message-conference-creation__image"></div>
				<div class="bx-im-message-conference-creation__content">
					<div class="bx-im-message-conference-creation__title">
						{{ loc('IM_MESSAGE_CONFERENCE_CREATION_TITLE') }}
					</div>
					<div class="bx-im-message-conference-creation__description">
						{{ loc('IM_MESSAGE_CONFERENCE_CREATION_DESCRIPTION') }}
					</div>
					<div class="bx-im-message-conference-creation__buttons_container">
						<div class="bx-im-message-conference-creation__buttons_item">
							<ChatButton
								:size="ButtonSize.L" 
								:icon="ButtonIcon.Camera" 
								:customColorScheme="buttonColorScheme"
								:isRounded="true"
								:text="loc('IM_MESSAGE_CONFERENCE_CREATION_BUTTON_START')"
								@click="onStartButtonClick"
							/>
						</div>
						<div class="bx-im-message-conference-creation__buttons_item">
							<ChatButton
								:size="ButtonSize.L"
								:icon="ButtonIcon.Link"
								:customColorScheme="buttonColorScheme"
								:isRounded="true"
								:text="loc('IM_MESSAGE_CONFERENCE_CREATION_BUTTON_COPY_LINK')"
								@click="onCopyLinkClick"
							/>
						</div>
					</div>
				</div>
			</div>
		</BaseMessage>
	`,
};
