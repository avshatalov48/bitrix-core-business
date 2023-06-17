import {Event} from 'main.core';

import {Button, ButtonSize, ButtonIcon} from 'im.v2.component.elements';
import {AddToChat} from 'im.v2.component.entity-selector';

import './css/chat-creation-message.css';

import type {CustomColorScheme} from 'im.v2.component.elements';
import type {ImModelMessage} from 'im.v2.model';
import {Messenger} from 'im.public';

const BUTTON_COLOR = '#00ace3';

// @vue/component
export const ChatCreationMessage = {
	name: 'ChatCreationMessage',
	components: {Button, AddToChat},
	props:
	{
		item: {
			type: Object,
			required: true
		},
		dialogId: {
			type: String,
			required: true
		}
	},
	data()
	{
		return {
			showAddToChatPopup: false
		};
	},
	computed:
	{
		ButtonSize: () => ButtonSize,
		ButtonIcon: () => ButtonIcon,

		descriptionPhrase()
		{
			return this.loc('IM_MESSAGE_CHAT_CREATION_DESCRIPTION', {
				'#DECORATION_START#': '<span class="bx-im-message-chat-creation__action">',
				'#DECORATION_END#': '</span>',
			});
		},
		buttonColorScheme(): CustomColorScheme
		{
			return {
				backgroundColor: 'transparent',
				borderColor: BUTTON_COLOR,
				iconColor: BUTTON_COLOR,
				textColor: BUTTON_COLOR,
				hoverColor: 'transparent'
			};
		},
		message(): ImModelMessage
		{
			return this.item;
		},
		chatId(): number
		{
			return this.message.chatId;
		}
	},
	mounted()
	{
		const actionElement = document.querySelector('.bx-im-message-chat-creation__action');
		Event.bind(actionElement, 'click', () => {
			console.warn('ACTION CLICK');
		});
	},
	methods:
	{
		loc(phraseCode: string, replacements: {[p: string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
		onCallButtonClick()
		{
			Messenger.startVideoCall(this.dialogId);
		},
		onInviteButtonClick()
		{
			this.showAddToChatPopup = true;
		}
	},
	template: `
		<div class="bx-im-message-chat-creation__scope bx-im-message-chat-creation__container">
			<div class="bx-im-message-chat-creation__image"></div>
			<div class="bx-im-message-chat-creation__content">
				<div class="bx-im-message-chat-creation__title">{{ loc('IM_MESSAGE_CHAT_CREATION_TITLE_V2') }}</div>
				<div class="bx-im-message-chat-creation__description" v-html="descriptionPhrase"></div>
				<div class="bx-im-message-chat-creation__buttons_container">
					<div class="bx-im-message-chat-creation__buttons_item">
						<Button
							:size="ButtonSize.L" 
							:icon="ButtonIcon.Call" 
							:customColorScheme="buttonColorScheme"
							:isRounded="true"
							:text="loc('IM_MESSAGE_CHAT_CREATION_BUTTON_VIDEOCALL')"
							@click="onCallButtonClick"
						/>
					</div>
					<div class="bx-im-message-chat-creation__buttons_item">
						<Button
							:size="ButtonSize.L"
							:icon="ButtonIcon.AddUser"
							:customColorScheme="buttonColorScheme"
							:isRounded="true"
							:text="loc('IM_MESSAGE_CHAT_CREATION_BUTTON_INVITE_USERS')"
							@click="onInviteButtonClick"
							ref="add-members-button"
						/>
					</div>
				</div>
			</div>
			<AddToChat
				:bindElement="$refs['add-members-button'] || {}"
				:chatId="chatId"
				:dialogId="dialogId"
				:showPopup="showAddToChatPopup"
				:popupConfig="{offsetTop: 0, offsetLeft: 0}"
				@close="showAddToChatPopup = false"
			/>
		</div>
	`
};