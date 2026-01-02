import { ChatTitle } from 'im.v2.component.elements.chat-title';
import { ChatAvatar, AvatarSize } from 'im.v2.component.elements.avatar';
import { CopilotManager } from 'im.v2.lib.copilot';
import { Feature, FeatureManager } from 'im.v2.lib.feature';

import { CopilotRole } from '../../../elements/copilot-role/copilot-role';
import { AIModel } from '../../../elements/ai-model/ai-model';
import { MuteChat } from '../../../elements/mute-chat/mute-chat';
import { ChatMembersAvatars } from '../../../elements/chat-members-avatars/chat-members-avatars';

import '../css/copilot-preview.css';

import type { ImModelChat } from 'im.v2.model';

// @vue/component
export const CopilotPreview = {
	name: 'CopilotPreview',
	components: { ChatAvatar, ChatTitle, MuteChat, ChatMembersAvatars, CopilotRole, AIModel },
	props: {
		dialogId: {
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
		chatId(): number
		{
			return this.dialog.chatId;
		},
		showMembers(): boolean
		{
			return (new CopilotManager()).isGroupCopilotChat(this.dialogId);
		},
		isAIModelChangeAllowed(): boolean
		{
			return FeatureManager.isFeatureAvailable(Feature.isAIModelChangeAllowed);
		},
	},
	template: `
		<div class="bx-im-sidebar-copilot-preview__scope">
			<div class="bx-im-sidebar-copilot-preview-group-chat__avatar-container">
				<ChatAvatar
					:avatarDialogId="dialogId"
					:contextDialogId="dialogId"
					:size="AvatarSize.XXXL"
					:withSpecialTypes="false"
				/>
				<ChatTitle :dialogId="dialogId" :twoLine="true" class="bx-im-sidebar-copilot-preview-group-chat__title" />
			</div>
			<div class="bx-im-sidebar-copilot-preview-group-chat__chat-members">
				<ChatMembersAvatars :showMembers="showMembers" :dialogId="dialogId" />
			</div>
			<div class="bx-im-sidebar-copilot-preview-group-chat__settings">
				<CopilotRole :dialogId="dialogId" />
				<AIModel v-if="isAIModelChangeAllowed" :dialogId="dialogId" />
				<MuteChat :dialogId="dialogId" />
			</div>
		</div>
	`,
};
