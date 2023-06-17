import {Loc} from 'main.core';
import {EventEmitter} from 'main.core.events';

import {Avatar, AvatarSize, ChatTitle} from 'im.v2.component.elements';
import {ChatService} from 'im.v2.provider.service';
import {DialogType, EventType, SidebarDetailBlock} from 'im.v2.const';
import {AddToChat} from 'im.v2.component.entity-selector';
import {Utils} from 'im.v2.lib.utils';
import {CallManager} from 'im.v2.lib.call';
import {Messenger} from 'im.public';

import 'ui.notification';

import {EditableChatTitle} from './editable-chat-title';

import '../../css/chat-header.css';

import type {ImModelUser, ImModelDialog} from 'im.v2.model';

// @vue/component
export const ChatHeader = {
	name: 'ChatHeader',
	components: {Avatar, ChatTitle, EditableChatTitle, AddToChat},
	props:
	{
		dialogId: {
			type: String,
			default: ''
		},
		sidebarOpened: {
			type: Boolean,
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
		AvatarSize: () => AvatarSize,
		user(): ImModelUser
		{
			return this.$store.getters['users/get'](this.dialogId, true);
		},
		dialog(): ImModelDialog
		{
			return this.$store.getters['dialogues/get'](this.dialogId, true);
		},
		isInited(): boolean
		{
			return this.dialog.inited;
		},
		isUser(): boolean
		{
			return this.dialog.type === DialogType.user;
		},
		isChat(): boolean
		{
			return !this.isUser;
		},
		avatarStyle(): {backgroundImage: string}
		{
			return {backgroundImage: `url('${this.dialog.avatar}')`};
		},
		chatId(): number
		{
			return this.dialog.chatId;
		},
		dialogDescription(): string
		{
			if (this.isUser)
			{
				return this.$store.getters['users/getPosition'](this.dialogId);
			}

			return Loc.getMessagePlural('IM_CONTENT_CHAT_HEADER_USER_COUNT', this.dialog.userCounter, {
				'#COUNT#': this.dialog.userCounter
			});
		},
		userLink(): string
		{
			return Utils.user.getProfileLink(this.dialogId);
		},
		userLastOnline(): string
		{
			return this.$store.getters['users/getLastOnline'](this.dialogId);
		},
		chatCanBeCalled(): boolean
		{
			return CallManager.getInstance().chatCanBeCalled(this.dialog.dialogId);
		}
	},
	methods:
	{
		toggleRightPanel()
		{
			this.$emit('toggleRightPanel');
		},
		onMembersClick()
		{
			if (this.isUser || !this.isInited)
			{
				return;
			}

			EventEmitter.emit(EventType.sidebar.open, {detailBlock: SidebarDetailBlock.main});
		},
		onNewTitleSubmit(newTitle: string)
		{
			this.getChatService().renameChat(this.dialogId, newTitle).catch(() => {
				BX.UI.Notification.Center.notify({
					content: this.loc('IM_CONTENT_CHAT_HEADER_RENAME_ERROR')
				});
			});
		},
		getChatService(): ChatService
		{
			if (!this.chatService)
			{
				this.chatService = new ChatService();
			}

			return this.chatService;
		},
		openInvitePopup()
		{
			this.showAddToChatPopup = true;
		},
		startVideoCall()
		{
			if (!this.chatCanBeCalled)
			{
				return;
			}

			Messenger.startVideoCall(this.dialog.dialogId);
		},
		loc(phraseCode: string, replacements: {[string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		}
	},
	template: `
		<div class="bx-im-chat-header__scope bx-im-chat-header__container">
			<div class="bx-im-chat-header__left">
				<div class="bx-im-chat-header__avatar">
					<Avatar v-if="isChat" :dialogId="dialogId" :size="AvatarSize.L" :withStatus="true" />
					<a v-else :href="userLink" target="_blank">
						<Avatar :dialogId="dialogId" :size="AvatarSize.L" :withStatus="true" />
					</a>
				</div>
				<div v-if="isChat" class="bx-im-chat-header__info">
					<EditableChatTitle :dialogId="dialogId" @newTitleSubmit="onNewTitleSubmit" />
					<div :title="loc('IM_CONTENT_CHAT_HEADER_OPEN_MEMBERS')" @click="onMembersClick" class="bx-im-chat-header__subtitle --click" >
						{{ dialogDescription }}
					</div>
				</div>
				<div v-else class="bx-im-chat-header__info">
					<div class="bx-im-chat-header__title --user">
						<a :href="userLink" target="_blank" class="bx-im-chat-header__title_container">
							<ChatTitle :dialogId="dialogId" />
						</a>
						<span class="bx-im-chat-header__user-status">{{ userLastOnline }}</span>
					</div>
					<div class="bx-im-chat-header__subtitle">{{ dialogDescription }}</div>
				</div>
			</div>
			<div class="bx-im-chat-header__right">
				<div class="bx-im-chat-header__button" :class="{'--disabled': !chatCanBeCalled}" @click="startVideoCall">
					{{ loc('IM_CONTENT_CHAT_HEADER_VIDEOCALL_HD') }}
				</div>
				<div 
					class="bx-im-chat-header__icon --add-people"
					:class="{'--active': showAddToChatPopup}"
					@click="openInvitePopup" 
					ref="add-members"
				></div>
				<!--<div class="bx-im-chat-header__icon --search"></div>-->
				<div @click="toggleRightPanel" class="bx-im-chat-header__icon --panel" :class="{'--active': sidebarOpened}"></div>
			</div>
			<AddToChat
				:bindElement="$refs['add-members'] || {}"
				:chatId="chatId"
				:dialogId="dialogId"
				:showPopup="showAddToChatPopup"
				:popupConfig="{offsetTop: 15, offsetLeft: -300}"
				@close="showAddToChatPopup = false"
			/>
		</div>
	`
};