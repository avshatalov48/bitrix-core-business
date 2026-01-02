import { ChatTitle } from 'im.v2.component.elements.chat-title';

import { ChatMembersAvatars } from '../../../elements/chat-members-avatars/chat-members-avatars';

import '../css/task.css';

// @vue/component
export const TaskPreview = {
	name: 'TaskPreview',
	components: { ChatTitle, ChatMembersAvatars },
	props: {
		dialogId: {
			type: String,
			required: true,
		},
	},
	template: `
		<div class="bx-im-sidebar-task-preview__container">
			<div class="bx-im-sidebar-task-preview__avatar-container">
				<div class="bx-im-sidebar-task-preview__avatar"></div>
				<ChatTitle :dialogId="dialogId" :twoLine="true" class="bx-im-sidebar-task-preview__title" />
			</div>
			<div class="bx-im-sidebar-task-preview__chat-members">
				<ChatMembersAvatars :dialogId="dialogId" />
			</div>
		</div>
	`,
};
