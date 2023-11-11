import 'ui.notification';

import { Messenger } from 'im.public';
import { Button as ButtonComponent, ButtonSize, ButtonIcon } from 'im.v2.component.elements';
import { BaseMessage } from 'im.v2.component.message.base';

import './css/conference-creation-message.css';

import type { JsonObject } from 'main.core';
import type { CustomColorScheme } from 'im.v2.component.elements';
import type { ImModelMessage, ImModelDialog } from 'im.v2.model';

const BUTTON_COLOR = '#00ace3';

// @vue/component
export const ConferenceCreationMessage = {
	name: 'ConferenceCreationMessage',
	components: { ButtonComponent, BaseMessage },
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
		dialog(): ImModelDialog
		{
			return this.$store.getters['dialogues/getByChatId'](this.chatId);
		},
	},
	methods:
	{
		onStartButtonClick()
		{
			Messenger.openConference({
				code: this.dialog.public.code,
			});
		},
		onCopyLinkClick()
		{
			if (BX.clipboard.copy(this.dialog.public.link))
			{
				BX.UI.Notification.Center.notify({
					content: this.loc('IM_MESSAGE_CONFERENCE_CREATION_LINK_COPY_SUCCESS'),
				});
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
							<ButtonComponent
								:size="ButtonSize.L" 
								:icon="ButtonIcon.Camera" 
								:customColorScheme="buttonColorScheme"
								:isRounded="true"
								:text="loc('IM_MESSAGE_CONFERENCE_CREATION_BUTTON_START')"
								@click="onStartButtonClick"
							/>
						</div>
						<div class="bx-im-message-conference-creation__buttons_item">
							<ButtonComponent
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
