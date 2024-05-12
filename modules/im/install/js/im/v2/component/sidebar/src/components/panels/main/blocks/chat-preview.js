import { Avatar, AvatarSize, ChatTitle } from 'im.v2.component.elements';

import { MuteChat } from '../../../elements/mute-chat/mute-chat';
import { AutoDelete } from '../../../elements/auto-delete/auto-delete';
import { ChatMembersAvatars } from '../../../elements/chat-members-avatars/chat-members-avatars';

import '../css/chat-preview.css';

// @vue/component
export const ChatPreview = {
	name: 'ChatPreview',
	components: { Avatar, ChatTitle, MuteChat, ChatMembersAvatars, AutoDelete },
	props: {
		dialogId: {
			type: String,
			required: true,
		},
	},
	computed:
	{
		AvatarSize: () => AvatarSize,
	},
	template: `
		<div class="bx-im-sidebar-main-preview__scope">
			<div class="bx-im-sidebar-main-preview-group-chat__avatar-container">
				<div class="bx-im-sidebar-main-preview-group-chat__avatar">
					<Avatar :size="AvatarSize.XXXL" :dialogId="dialogId" />
				</div>
				<ChatTitle :dialogId="dialogId" :twoLine="true" class="bx-im-sidebar-main-preview-group-chat__title" />
			</div>
			<div class="bx-im-sidebar-main-preview-group-chat__chat-members">
				<ChatMembersAvatars :dialogId="dialogId" />
			</div>
			<div class="bx-im-sidebar-main-preview-group-chat__settings">
				<MuteChat :dialogId="dialogId" />
				<AutoDelete />
			</div>
		</div>
	`,
};
