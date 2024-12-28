import { ChatType } from 'im.v2.const';

import { GroupChatCreation } from './components/create/group-chat';
import { ConferenceCreation } from './components/create/conference';
import { ChannelCreation } from './components/create/channel';
import { CollabCreation } from './components/create/collab/collab';

import './css/chat-forms-content.css';

import type { BitrixVueComponentProps } from 'ui.vue3';

export type CreatableChatType = $Values<typeof CreatableChat>;

export const CreatableChat = {
	chat: 'chat',
	videoconf: 'videoconf',
	channel: 'channel',
	collab: 'collab',
};

const CreationComponentByChatType = {
	[ChatType.chat]: GroupChatCreation,
	[ChatType.videoconf]: ConferenceCreation,
	[ChatType.channel]: ChannelCreation,
	[ChatType.collab]: CollabCreation,
	default: GroupChatCreation,
};

// @vue/component
export const CreateChatContent = {
	name: 'CreateChatContent',
	components: { GroupChatCreation, ConferenceCreation, ChannelCreation, CollabCreation },
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
			return this.entityId;
		},
		creationComponent(): BitrixVueComponentProps
		{
			return CreationComponentByChatType[this.chatType] ?? CreationComponentByChatType.default;
		},
	},
	template: `
		<div class="bx-im-content-chat-forms__container">
			<component :is="creationComponent" />
		</div>
	`,
};
