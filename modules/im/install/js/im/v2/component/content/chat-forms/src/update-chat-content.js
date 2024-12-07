import { ChatType } from 'im.v2.const';

import { GroupChatUpdating } from './components/update/group-chat';
import { ChannelUpdating } from './components/update/channel';

import './css/chat-forms-content.css';

// @vue/component
export const UpdateChatContent = {
	name: 'UpdateChatContent',
	components: { GroupChatUpdating, ChannelUpdating },
	props:
	{
		entityId: {
			type: String,
			required: true,
		},
	},
	computed:
	{
		ChatType: () => ChatType,
		chatType(): $Values<typeof ChatType>
		{
			const chat = this.$store.getters['chats/get'](this.entityId, true);

			return chat.type;
		},
		isChannel(): boolean
		{
			const editableChannelTypes = new Set([ChatType.channel, ChatType.openChannel]);

			return editableChannelTypes.has(this.chatType);
		},
		isChat(): boolean
		{
			const editableChatTypes = new Set([ChatType.chat, ChatType.open, ChatType.videoconf]);

			return editableChatTypes.has(this.chatType);
		},
	},
	template: `
		<div class="bx-im-content-chat-forms__container">
			<GroupChatUpdating v-if="isChat" :dialogId="this.entityId" />
			<ChannelUpdating v-else-if="isChannel" :dialogId="this.entityId" />
		</div>
	`,
};
