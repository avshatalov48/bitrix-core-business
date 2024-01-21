import { ChatType } from 'im.v2.const';

import { GroupChatCreation } from './components/group-chat';
import { ConferenceCreation } from './components/conference';

import type { JsonObject } from 'main.core';

// @vue/component
export const CreateChatContent = {
	name: 'CreateChatContent',
	components: { GroupChatCreation, ConferenceCreation },
	props:
	{
		entityId: {
			type: String,
			required: true,
		},
	},
	data(): JsonObject
	{
		return {};
	},
	computed:
	{
		ChatType: () => ChatType,
		chatType(): $Values<typeof ChatType>
		{
			return this.entityId;
		},
	},
	template: `
		<div class="bx-im-content-create-chat__container bx-im-content-create-chat__scope">
			<GroupChatCreation v-if="chatType === ChatType.chat" />
			<ConferenceCreation v-else-if="chatType === ChatType.videoconf" />
		</div>
	`,
};
