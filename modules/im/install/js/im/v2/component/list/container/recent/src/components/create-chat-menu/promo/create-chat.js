import { DialogType } from 'im.v2.const';

import { GroupChatPromo } from './group-chat';
import { ConferencePromo } from './conference';

import type { JsonObject } from 'main.core';

// @vue/component
export const CreateChatPromo = {
	name: 'CreateChatPromo',
	components: { GroupChatPromo, ConferencePromo },
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
		DialogType: () => DialogType,
	},
	template: `
		<GroupChatPromo v-if="chatType === DialogType.chat" @close="$emit('close')" @continue="$emit('continue')" />
		<ConferencePromo v-else-if="chatType === DialogType.videoconf" @close="$emit('close')" @continue="$emit('continue')" />
	`,
};
