import { DialogType } from 'im.v2.const';

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
		DialogType: () => DialogType,
		chatType(): $Values<typeof DialogType>
		{
			return this.entityId;
		},
	},
	template: `
		<div class="bx-im-content-create-chat__container bx-im-content-create-chat__scope">
			<GroupChatCreation v-if="chatType === DialogType.chat" />
			<ConferenceCreation v-else-if="chatType === DialogType.videoconf" />
		</div>
	`,
};
