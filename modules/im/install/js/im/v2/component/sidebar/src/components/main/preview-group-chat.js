import {ImModelDialog} from 'im.v2.model';
import {ChatOption} from 'im.v2.const';
import {AddToChat} from 'im.v2.component.entity-selector';
import {Avatar, AvatarSize, ChatTitle, Button, ButtonSize, ButtonColor} from 'im.v2.component.elements';
import {Settings} from './settings';
import '../../css/main/preview-group-chat.css';

// @vue/component
export const GroupChatPreview = {
	name: 'GroupChatPreview',
	components: {Avatar, ChatTitle, Button, AddToChat, Settings},
	props: {
		dialogId: {
			type: String,
			required: true
		},
		isLoading: {
			type: Boolean,
			default: false
		},
	},
	emits: ['openDetail'],
	data() {
		return {
			showAddToChatPopup: false
		};
	},
	computed:
	{
		AvatarSize: () => AvatarSize,
		ButtonSize: () => ButtonSize,
		ButtonColor: () => ButtonColor,
		dialog(): ImModelDialog
		{
			return this.$store.getters['dialogues/get'](this.dialogId, true);
		},
		chatId(): number
		{
			return this.dialog.chatId;
		},
		dialogInited(): boolean
		{
			return this.dialog.inited;
		},
		dialogIds(): string[]
		{
			const PREVIEW_USERS_COUNT = 4;
			const userIds = this.$store.getters['sidebar/members/get'](this.chatId);

			return userIds.map(id => id.toString()).slice(0, PREVIEW_USERS_COUNT);
		},
		usersInChatCount(): number
		{
			return this.dialog.userCounter;
		},
		moreUsersCount(): number
		{
			return Math.max(this.usersInChatCount - this.dialogIds.length, 0);
		},
		canSeeMembers(): boolean
		{
			return this.$store.getters['dialogues/getChatOption'](this.dialog.type, ChatOption.userList);
		},
		canInviteMembers(): boolean
		{
			return this.$store.getters['dialogues/getChatOption'](this.dialog.type, ChatOption.extend);
		},
	},
	methods:
	{
		onOpenUsers()
		{
			this.$emit('openDetail');
		},
		onOpenInvitePopup()
		{
			this.showAddToChatPopup = true;
		}
	},
	template: `
		<div class="bx-im-sidebar-main-preview__scope">
			<div v-if="!dialogInited" class="bx-im-sidebar-main-preview-group-chat__avatar-skeleton"></div>
			<div v-else class="bx-im-sidebar-main-preview-group-chat__avatar-container">
				<div class="bx-im-sidebar-main-preview-group-chat__avatar">
					<Avatar :size="AvatarSize.XXXL" :withStatus="false" :dialogId="dialogId" />
				</div>
				<ChatTitle :dialogId="dialogId" :twoLine="true" class="bx-im-sidebar-main-preview-group-chat__title" />
			</div>
			<div v-if="isLoading" class="bx-im-sidebar-main-preview-group-chat__members-skeleton"></div>
			<div v-else class="bx-im-sidebar-main-preview-group-chat__members-container">
				<div v-if="canSeeMembers" class="bx-im-sidebar-main-preview-group-chat__members" @click="onOpenUsers">
					<div class="bx-im-sidebar-main-preview-group-chat__members-avatars" >
						<Avatar
							class="bx-im-sidebar-main-preview-group-chat__chat-user-avatar"
							v-for="id in dialogIds"
							:size="AvatarSize.S"
							:withStatus="false"
							:dialogId="id"
						/>
					</div>
					<div v-if="moreUsersCount > 0" class="bx-im-sidebar-main-preview-group-chat__more-users-count-text">
						+{{ moreUsersCount }}
					</div>
				</div>
				<div ref="add-members">
					<Button
						v-if="canInviteMembers"
						:text="$Bitrix.Loc.getMessage('IM_SIDEBAR_INVITE_BUTTON_TEXT')"
						:size="ButtonSize.S"
						:color="ButtonColor.PrimaryLight"
						:isRounded="true"
						:isUppercase="false"
						icon="plus"
						@click="onOpenInvitePopup"
					/>
				</div>
			</div>
			<Settings :isLoading="isLoading" :dialogId="dialogId" />
			<AddToChat
				:bindElement="$refs['add-members'] || {}"
				:chatId="chatId"
				:dialogId="dialogId"
				:showPopup="showAddToChatPopup"
				:popupConfig="{offsetTop: -220, offsetLeft: -420}"
				@close="showAddToChatPopup = false"
			/>
		</div>
	`
};