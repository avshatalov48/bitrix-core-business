import { ChatAvatar, AvatarSize, ChatTitle } from 'im.v2.component.elements';

import { MuteChat } from '../../../elements/mute-chat/mute-chat';
import { AutoDelete } from '../../../elements/auto-delete/auto-delete';
import { ChatMembersAvatars } from '../../../elements/chat-members-avatars/chat-members-avatars';

import '../css/post-preview.css';

import type { ImModelChat } from 'im.v2.model';

// @vue/component
export const PostPreview = {
	name: 'PostPreview',
	components: { ChatAvatar, ChatTitle, MuteChat, ChatMembersAvatars, AutoDelete },
	props:
	{
		dialogId:
		{
			type: String,
			required: true,
		},
	},
	computed:
	{
		AvatarSize: () => AvatarSize,
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		postDialog(): ImModelChat
		{
			return this.$store.getters['chats/getByChatId'](this.dialog.parentChatId);
		},
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-sidebar-main-preview-post__scope">
			<div class="bx-im-sidebar-main-preview-post__avatar-container">
				<div class="bx-im-sidebar-main-preview-post__avatar">
					<ChatAvatar
						:avatarDialogId="postDialog.dialogId"
						:contextDialogId="postDialog.dialogId"
						:size="AvatarSize.XXXL" 
					/>
				</div>
				<div class="bx-im-sidebar-main-preview-post__title">{{ loc('IM_SIDEBAR_COMMENTS_POST_PREVIEW_TITLE') }}</div>
				<div class="bx-im-sidebar-main-preview-post__subtitle">{{ postDialog.name }}</div>
			</div>
			<div class="bx-im-sidebar-main-preview-post__settings">
				<!-- TODO: follow toggle -->
			</div>
		</div>
	`,
};
