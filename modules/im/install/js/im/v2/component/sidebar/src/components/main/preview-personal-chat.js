import {hint} from 'ui.vue3.directives.hint';
import {Avatar, AvatarSize, ChatTitle, Button, ButtonColor, ButtonSize} from 'im.v2.component.elements';
import {ChatOption} from 'im.v2.const';
import {ImModelDialog} from 'im.v2.model';
import {Utils} from 'im.v2.lib.utils';
import {AddToChat} from 'im.v2.component.entity-selector';
import {Settings} from './settings';
import '../../css/main/preview-personal-chat.css';

// @vue/component
export const PersonalChatPreview = {
	name: 'PersonalChatPreview',
	directives: {hint},
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
		userPosition(): string
		{
			return this.$store.getters['users/getPosition'](this.dialogId);
		},
		dialog(): ImModelDialog
		{
			return this.$store.getters['dialogues/get'](this.dialogId, true);
		},
		dialogInited(): boolean
		{
			return this.dialog.inited;
		},
		chatId(): number
		{
			return this.dialog.chatId;
		},
		canInviteMembers(): boolean
		{
			return this.$store.getters['dialogues/getChatOption'](this.dialog.type, ChatOption.extend);
		},
		userLink(): string
		{
			return Utils.user.getProfileLink(this.dialogId);
		},
	},
	methods:
	{
		onAddClick()
		{
			this.showAddToChatPopup = true;
		}
	},
	template: `
		<div class="bx-im-sidebar-main-preview__scope">
			<div v-if="!dialogInited" class="bx-im-sidebar-main-preview-personal-chat__avatar-skeleton"></div>
			<div v-else class="bx-im-sidebar-main-preview-personal-chat__avatar-container">
				<Avatar
					:size="AvatarSize.XXXL"
					:withStatus="false"
					:dialogId="dialogId"
					class="bx-im-sidebar-main-preview-personal-chat__avatar"
				/>
				<a :href="userLink" target="_blank">
					<ChatTitle :dialogId="dialogId" class="bx-im-sidebar-main-preview-personal-chat__user-name" />
				</a>
				<div class="bx-im-sidebar-main-preview-personal-chat__user-position" :title="userPosition">
					{{ userPosition }}
				</div>
			</div>
			
			<div v-if="isLoading" class="bx-im-sidebar-main-preview-personal-chat__invite-button-skeleton"></div>
			<div v-else class="bx-im-sidebar-main-preview-personal-chat__invite-button-container" ref="add-members">
				<Button
					v-if="canInviteMembers"
					:text="$Bitrix.Loc.getMessage('IM_SIDEBAR_INVITE_BUTTON_TEXT')"
					:size="ButtonSize.S"
					:color="ButtonColor.PrimaryLight"
					:isRounded="true"
					:isUppercase="false"
					icon="plus"
					@click="onAddClick"
				/>
			</div>
			<Settings :isLoading="isLoading" :dialogId="dialogId" />
			<AddToChat
				:bindElement="$refs['add-members'] || {}"
				:chatId="chatId"
				:dialogId="dialogId"
				:showPopup="showAddToChatPopup"
				:popupConfig="{offsetTop: -220, offsetLeft: -320}"
				@close="showAddToChatPopup = false"
			/>
		</div>
	`
};