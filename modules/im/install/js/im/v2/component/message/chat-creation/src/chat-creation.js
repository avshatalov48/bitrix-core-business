import { Messenger } from 'im.public';
import { Button as ButtonComponent, ButtonSize, ButtonIcon } from 'im.v2.component.elements';
import { AddToChat } from 'im.v2.component.entity-selector';
import { BaseMessage } from 'im.v2.component.message.base';

import './css/chat-creation-message.css';

import { Analytics as CallAnalytics} from 'call.lib.analytics';
import type { CustomColorScheme } from 'im.v2.component.elements';
import { CallManager } from 'im.v2.lib.call';
import type { ImModelMessage } from 'im.v2.model';
import { hint } from 'ui.vue3.directives.hint';

const BUTTON_COLOR = '#00ace3';

// @vue/component
export const ChatCreationMessage = {
	name: 'ChatCreationMessage',
	directives: { hint },
	components: { ButtonComponent, AddToChat, BaseMessage },
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
	data(): {showAddToChatPopup: boolean}
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
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		hasActiveCurrentCall(): boolean
		{
			return CallManager
				.getInstance()
				.hasActiveCurrentCall(this.dialogId);
		},
		hasActiveAnotherCall(): boolean
		{
			return CallManager
				.getInstance()
				.hasActiveAnotherCall(this.dialogId);
		},
		isActive(): boolean
		{
			if (
				this.hasActiveCurrentCall
			)
			{
				return true;
			}

			if (this.hasActiveAnotherCall)
			{
				return false;
			}

			return CallManager
				.getInstance()
				.chatCanBeCalled(this.dialogId);
		},
		userLimit(): number
		{
			return CallManager
				.getInstance()
				.getCallUserLimit();
		},
		isChatUserLimitExceeded(): boolean
		{
			return CallManager
				.getInstance()
				.isChatUserLimitExceeded(this.dialogId);
		},
		hintContent(): Object | null
		{
			if (this.isChatUserLimitExceeded)
			{
				return {
					text: this.loc('IM_LIB_CALL_USER_LIMIT_EXCEEDED_TOOLTIP', { '#USER_LIMIT#': this.userLimit }),
					popupOptions: {
						bindOptions: {
							position: 'bottom',
						},
						angle: { position: 'top' },
						targetContainer: document.body,
						offsetLeft: 82,
						offsetTop: 0,
					},
				};
			}

			return null;
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
			CallAnalytics.getInstance().onChatCreationMessageStartCallClick({ chatId: this.chatId });
			Messenger.startVideoCall(this.dialogId);
		},
		onInviteButtonClick()
		{
			this.showAddToChatPopup = true;
		},
	},
	template: `
		<BaseMessage
			:dialogId="dialogId"
			:item="item"
			:withContextMenu="false"
			:withReactions="false"
			:withBackground="false"
			class="bx-im-message-chat-creation__scope"
		>
			<div class="bx-im-message-chat-creation__container">
				<div class="bx-im-message-chat-creation__image"></div>
				<div class="bx-im-message-chat-creation__content">
					<div class="bx-im-message-chat-creation__title">
						{{ loc('IM_MESSAGE_CHAT_CREATION_TITLE_V2') }}
					</div>
					<div class="bx-im-message-chat-creation__description">
						{{ loc('IM_MESSAGE_CHAT_CREATION_DESCRIPTION') }}
					</div>
					<div class="bx-im-message-chat-creation__buttons_container">
						<div class="bx-im-message-chat-creation__buttons_item">
							<ButtonComponent
								:size="ButtonSize.L" 
								:icon="ButtonIcon.Call" 
								:customColorScheme="buttonColorScheme"
								:isRounded="true"
								:text="loc('IM_MESSAGE_CHAT_CREATION_BUTTON_VIDEOCALL')"
								@click="onCallButtonClick"
								:isDisabled="!isActive"
								v-hint="hintContent"
							/>
						</div>
						<div class="bx-im-message-chat-creation__buttons_item">
							<ButtonComponent
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
					v-if="showAddToChatPopup"
					:bindElement="$refs['add-members-button'] || {}"
					:dialogId="dialogId"
					:popupConfig="{offsetTop: 0, offsetLeft: 0}"
					@close="showAddToChatPopup = false"
				/>
			</div>
		</BaseMessage>
	`,
};
