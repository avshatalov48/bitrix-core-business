import { ChatType } from 'im.v2.const';

import { GroupChatPromo } from './components/group-chat';
import { ConferencePromo } from './components/conference';
import { ChannelPromo } from './components/channel';

import './css/create-chat-promo.css';

import type { JsonObject } from 'main.core';

// @vue/component
export const CreateChatPromo = {
	name: 'CreateChatPromo',
	components: { GroupChatPromo, ConferencePromo, ChannelPromo },
	props:
	{
		chatType: {
			type: String,
			required: true,
		},
	},
	emits: ['continue', 'close'],
	data(): JsonObject
	{
		return {};
	},
	computed:
	{
		ChatType: () => ChatType,
	},
	template: `
		<GroupChatPromo v-if="chatType === ChatType.chat" @close="$emit('close')" @continue="$emit('continue')" />
		<ConferencePromo v-else-if="chatType === ChatType.videoconf" @close="$emit('close')" @continue="$emit('continue')" />
		<ChannelPromo v-else-if="chatType === ChatType.channel" @close="$emit('close')" @continue="$emit('continue')" />
	`,
};
